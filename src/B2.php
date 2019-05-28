<?php

namespace Demafelix;

use GuzzleHttp\Client;

class B2
{
    /**
     * B2 application key ID.
     *
     * @var string
     */
    protected $appKeyId;

    /**
     * B2 application key.
     *
     * @var string
     */
    protected $appKey;

    /**
     * guzzlehttp client.
     *
     * @var Client
     */
    protected $client;

    /**
     * Holds authorization data.
     *
     * @var string
     */
    protected $authorization;

    /**
     * Holds the default bucket ID.
     *
     * @var string
     */
    protected $bucketId;

    public function __construct($appKeyId, $appKey, $bucketId = null)
    {
        $this->appKeyId = $appKeyId;
        $this->appKey = $appKey;
        $this->bucketId = $bucketId;
        $this->client = new Client();
        $this->authorization = $this->authorize();
    }

    /**
     * Gets the authorization token from B2.
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function authorize()
    {
        // Set credentials
        $credentials = base64_encode($this->appKeyId . ":" . $this->appKey);

        // Execute
        $request = $this->client->request('GET', 'https://api.backblazeb2.com/b2api/v2/b2_authorize_account', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . $credentials
            ]
        ]);
        return json_decode($request->getBody());
    }

    /**
     * Returns the authorization token.
     *
     * @return mixed
     */
    public function getToken()
    {
        return $this->authorization->authorizationToken;
    }

    /**
     * Returns the account ID.
     *
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->authorization->accountId;
    }

    /**
     * Returns the API URL base.
     *
     * @return mixed
     */
    public function getApiUrl()
    {
        return $this->authorization->apiUrl;
    }

    /**
     * Returns a list of buckets.
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBuckets()
    {
        $request = $this->client->request('POST', $this->getApiUrl() . '/b2api/v2/b2_list_buckets', [
            'headers' => [
                'Authorization' => $this->getToken()
            ],
            'body' => json_encode(['accountId' => $this->getAccountId()])
        ]);
        return json_decode($request->getBody());
    }

    /**
     * Requests for an upload URL from B2.
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUploadAuth()
    {
        if (empty($this->bucketId)) {
            throw new \BadMethodCallException("No bucket ID specified.");
        }

        $request = $this->client->request('POST', $this->getApiUrl() . '/b2api/v2/b2_get_upload_url', [
            'headers' => [
                'Authorization' => $this->getToken()
            ],
            'body' => json_encode(['bucketId' => $this->bucketId])
        ]);

        return json_decode($request->getBody());
    }

    /**
     * Basic PHP string handler to ensure that $haystack starts with $needle.
     *
     * @param $haystack
     * @param $needle
     * @return bool
     */
    private function startsWith($haystack, $needle)
    {
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }

    /**
     * Basic PHP string handler to ensure that $haystack ends with $needle.
     * @param $haystack
     * @param $needle
     * @return bool
     */
    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * Uploads a file.
     *
     * @param $file
     * @param string $remote
     * @param null $filename
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store($file, $remote = "/", $filename = null)
    {
        $uploadAuth = $this->getUploadAuth();
        if (empty($uploadAuth->uploadUrl)) {
            throw new \InvalidArgumentException("B2 returned an invalid upload URL.");
        }
        if (empty($uploadAuth->authorizationToken)) {
            throw new \InvalidArgumentException("B2 failed to return an authorization token.");
        }

        // Prepare the parameters
        $handle = @fopen($file, 'r');
        if (!$handle) {
            throw new \Exception("Cannot read file for upload.");
        }
        $data = fread($handle, filesize($file));
        $sha1 = sha1_file($file);
        if (empty($filename)) {
            $filename = basename($file);
        }

        // Fix remote path
        if ($this->startsWith($remote, '/')) {
            $remote = substr($remote, 1);
        }
        if (!$this->endsWith($remote, '/')) {
            $remote .= "/";
        }

        // Begin upload
        $request = $this->client->request('POST', $uploadAuth->uploadUrl, [
            'headers' => [
                'Authorization' => $uploadAuth->authorizationToken,
                'Content-Type' => 'b2/x-auto',
                'X-Bz-File-Name' => $remote . $filename,
                'X-Bz-Content-Sha1' => $sha1
            ],
            'body' => $data
        ]);

        fclose($handle);

        // Return
        return $request;
    }

    /**
     * Deletes a file.
     *
     * @param $fileId
     * @param $fileName
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($fileId, $fileName)
    {
        // Attempt to delete
        $request = $this->client->request('POST', $this->getApiUrl() . '/b2api/v2/b2_delete_file_version', [
            'headers' => [
                'Authorization' => $this->getToken()
            ],
            'body' => json_encode(['fileId' => $fileId, 'fileName' => $fileName])
        ]);

        // Return
        return $request;
    }

    /**
     * List files in a bucket.
     *
     * @param null $prefix
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listFiles($prefix = null)
    {
        $params = ['bucketId' => $this->bucketId];
        if (!empty($prefix)) {
            $params['prefix'] = $prefix;
        }
        // List files in the bucket
        $request = $this->client->request('POST', $this->getApiUrl() . '/b2api/v2/b2_list_file_names', [
            'headers' => [
                'Authorization' => $this->getToken()
            ],
            'body' => json_encode($params)
        ]);

        // Return
        return $request;
    }
}