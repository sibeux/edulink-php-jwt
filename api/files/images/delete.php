<?php
require '../../../vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

// Ambil API key dari endpoint kamu
$azureUrl = "https://sibeux.my.id/cloud-music-player/database/mobile-music-player/api/gdrive_api.php";
$azureResponse = file_get_contents($azureUrl);

if ($azureResponse === FALSE) {
    die(json_encode(['status' => 'error', 'message' => 'Error accessing API.']));
}

$data = json_decode($azureResponse, true);
$accountKey = null; // Initialize accountKey as null

// Iterate through the array to find the email "azure_api_edulink"
foreach ($data as $item) {
    if (isset($item['email']) && $item['email'] === 'azure_api_edulink') {
        $accountKey = $item['gdrive_api'];
        break; // Stop iterating once the correct email is found
    }
}

$accountName = 'edulink';
$containerName = 'images';
$connectionString = "DefaultEndpointsProtocol=https;AccountName=$accountName;AccountKey=$accountKey;EndpointSuffix=core.windows.net";
$blobClient = BlobRestProxy::createBlobService($connectionString);

// Baca input JSON
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['filename']) && is_array($input['filename'])) {
    $results = [];

    foreach ($input['filename'] as $filename) {
        $filename = basename($filename); // amankan path
        try {
            $blobClient->deleteBlob($containerName, $filename);
            $results[] = ['filename' => $filename, 'status' => 'deleted'];
        } catch (ServiceException $e) {
            $results[] = ['filename' => $filename, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }

    echo json_encode(['status' => 'done', 'results' => $results]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request format.']);
}