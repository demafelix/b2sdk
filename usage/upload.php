<?php

/**
 * Sample file for uploading to B2 bucket.
 * See https://www.backblaze.com/b2/docs/b2_upload_file.html
 * for the complete list of parameters returned.
 */

require __DIR__ . '/../vendor/autoload.php';

$appKeyId = "";
$appKey = "";
$bucketId = "";

$b2 = new \Demafelix\B2($appKeyId, $appKey, $bucketId);

// The file to upload.
// NOTE: This is a string (the path to the file).
// This is NOT an fread() or a stream.
$file = "";

// Begin upload
try {
    $upload = $b2->store($file, "/folder/inside/bucket/", "saveAsFile.jpg");
} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}

// Process the response
// $upload is an instance of a guzzlehttp client request.
$response = json_decode($upload->getBody());

// Get response code
$responseCode = $upload->getStatusCode();

// Get remote file name
$remoteFilename = $response->fileName;

// Get file ID
$fileId = $response->fileId;

// Check upload status
// Backblaze returns 200, but this should really be 201
if ($responseCode == 200) {
    // Upload successful
} else {
    // Upload failed
}