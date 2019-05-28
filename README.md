# Backblaze B2 PHP SDK

![https://github.com/liamdemafelix](https://img.shields.io/badge/author-%40liamdemafelix-blue.svg?style=flat-square) ![https://raw.githubusercontent.com/demafelix/b2sdk/master/LICENSE.md](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)

`b2sdk` is a PHP SDK for Backblaze B2 that **uses application keys** instead of master keys.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=GUV2KKLLSGXES)

# Installation

```
$ composer require demafelix/b2sdk
```

# Usage

> Do **not** use your master key. Unlike other PHP libraries, this uses application keys.

```
<?php

/**
 * Sample for uploading files to a B2 bucket.
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

// Check upload status
// Backblaze returns 200, but this should really be 201
if ($responseCode == 200) {
    // Upload successful
} else {
    // Upload failed
}
```

See the `usage/` folder for more examples.

# License

This library is licensed under the MIT Open Source license.