<?php
require '../../../vendor/autoload.php';

// Seperti biasa, install dulu pakai composer.
// composer require microsoft/azure-storage-blob

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;

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
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $name = basename($file['name']);
    $file_size = $file['size'];
    $tmp_name = $file['tmp_name'];
    $imageFileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    // Cek ukuran
    if ($file_size > 2 * 1024 * 1024) {
        $errors[] = ["status" => "error", "message" => "File '$name' is too large (max 2MB)."];
    }

    // Cek format
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'webp'])) {
        $errors[] = ["status" => "error", "message" => "File '$name' is not a valid image format."];
    }

    // Cek apakah file sudah ada di Azure
    try {
        $blobList = $blobClient->listBlobs($containerName);
        foreach ($blobList->getBlobs() as $blob) {
            if ($blob->getName() === $name) {
                $errors[] = ["status" => "error", "message" => "File '$name' already exists on Azure."];
                break;
            }
        }
    } catch (ServiceException $e) {
        $errors[] = ["status" => "error", "message" => "Azure error: " . $e->getMessage()];
    }

    // Jika tidak ada error, upload file
    if (empty($errors)) {
        $content = fopen($tmp_name, "r");
        $options = new CreateBlockBlobOptions();
        $options->setContentType(mime_content_type($tmp_name));

        try {
            $blobClient->createBlockBlob($containerName, $name, $content, $options);
            echo json_encode(["status" => "success", "message" => "File '$name' uploaded successfully."]);
        } catch (ServiceException $e) {
            echo json_encode(["status" => "error", "message" => "Upload failed: " . $e->getMessage()]);
        }
    } else {
        echo json_encode($errors);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No file uploaded."]);
}