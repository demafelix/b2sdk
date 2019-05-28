<?php

/**
 * Sample file for deleting a file from a B2 bucket.
 * See https://www.backblaze.com/b2/docs/b2_delete_file_version.html
 * for the complete list of parameters returned.
 */

require __DIR__ . '/../vendor/autoload.php';

$appKeyId = "";
$appKey = "";
$bucketId = "";

$b2 = new \Demafelix\B2($appKeyId, $appKey, $bucketId);

// The file to delete
$fileId = '4_z9ef9e8d88013f7c369a20c18_f108f35507a36172e_d20190528_m054951_c002_v0001112_t0043';
$fileName = 'folder/name.jpg';

// Send the delete request
try {
    $delete = $b2->delete($fileId, $fileName);
} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}

// Process the response
// $delete is an instance of a guzzlehttp client request.
$response = json_decode($delete->getBody());

// Check if deletion is successful
if ($delete->getStatusCode() == 200) {
    // File deleted
} else {
    // Failed to delete file
}