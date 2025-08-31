<?php
// download.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PERBAIKAN: Gunakan path absolut ke config.php
$rootPath = dirname(__DIR__, 2); // Naik 2 level dari views/documents ke root
require_once $rootPath . '/config/config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo 'Unauthorized access - Please login first';
    exit();
}

// Check if document ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Document ID required';
    exit();
}

try {
    // Initialize database and document model
    $database = new Database();
    $db = $database->getConnection();
    
    // Load Document model
    require_once $rootPath . '/models/Document.php';
    $documentModel = new Document($db);

    $docId = intval($_GET['id']);
    $documentData = $documentModel->getById($docId);

    // Check if document exists
    if (!$documentData) {
        header('HTTP/1.1 404 Not Found');
        echo 'Document not found';
        exit();
    }

    // Check if file exists
    if (empty($documentData['file_path'])) {
        header('HTTP/1.1 404 Not Found');
        echo 'No file attached to this document';
        exit();
    }

    if (!file_exists($documentData['file_path'])) {
        header('HTTP/1.1 404 Not Found');
        echo 'File not found on server: ' . $documentData['file_path'];
        exit();
    }

    $filePath = $documentData['file_path'];
    $fileName = $documentData['file_name'];
    $fileSize = filesize($filePath);

    // Determine content type based on file extension
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $contentTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'txt' => 'text/plain'
    ];

    $contentType = $contentTypes[$fileExtension] ?? 'application/octet-stream';

    // Set headers for download
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Expires: 0');

    // Clear output buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Read the file and output it
    readfile($filePath);
    exit;

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error: ' . $e->getMessage();
    exit();
}
?>