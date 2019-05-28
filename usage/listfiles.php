<?php

/**
 * Sample file for deleting a file from a B2 bucket.
 * See https://www.backblaze.com/b2/docs/b2_list_file_names.html
 * for the complete list of parameters returned.
 */

require __DIR__ . '/../vendor/autoload.php';

$appKeyId = "";
$appKey = "";
$bucketId = "";

$b2 = new \Demafelix\B2($appKeyId, $appKey, $bucketId);

// Send the delete request
try {
    $delete = $b2->listFiles();
} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}

// Process the response
// $delete is an instance of a guzzlehttp client request.
$response = json_decode($delete->getBody());

// Loop through files
foreach ($response->files as $file) {
    // Process file, example:
    echo "File: " . $file->fileName . "<br>";
    echo "File ID: " . $file->fileId . "<br><br>";
}