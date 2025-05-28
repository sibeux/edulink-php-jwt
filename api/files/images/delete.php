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
$accountKey = $data[0]['gdrive_api'];

$accountName = 'edulink';
$containerName = 'images';

$connectionString = "DefaultEndpointsProtocol=https;AccountName=$accountName;AccountKey=$accountKey;EndpointSuffix=core.windows.net";
$blobClient = BlobRestProxy::createBlobService($connectionString);

// Cek apakah ada request POST dan parameter 'filename'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filename'])) {
    $filename = $_POST['filename'];

    try {
        // Delete blob
        $blobClient->deleteBlob($containerName, $filename);
        echo json_encode(['status' => 'success', 'message' => "File '$filename' has been deleted."]);
    } catch (ServiceException $e) {
        echo json_encode(['status' => 'error', 'message' => "Failed to delete: " . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No filename provided.']);
}