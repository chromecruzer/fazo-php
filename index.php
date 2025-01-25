<?php
// Include Composer's autoloader
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use Fazoacademy\Learn\MailSender;

// Initialize Dotenv
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Define a helper function to get MIME types
function getMimeType($filePath)
{
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

    // Map file extensions to MIME types
    $mimeTypes = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
    ];

    return $mimeTypes[$extension] ?? 'application/octet-stream';
}

// Function to handle form submission and send email
function handleFormSubmission($requestUri, $recipientEmail, $subjectPrefix)
{
    // Get the raw POST data
    $rawPostData = file_get_contents("php://input");

    // Decode the JSON data
    $data = json_decode($rawPostData, true);

    // Check if the JSON was decoded properly
    if ($data) {
        // Log each key-value pair in the JSON data to the PHP error log (optional)
        error_log("Received JSON data:\n" . print_r($data, true));

        // Prepare the email body by dynamically collecting fields from the form data
        $emailBody = '
        <h2>' . $subjectPrefix . '</h2>
        <p><strong>Name:</strong> ' . htmlspecialchars($data['name'] ?? 'N/A') . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($data['email'] ?? 'N/A') . '</p>
        <p><strong>Subject:</strong> ' . htmlspecialchars($data['subject'] ?? 'N/A') . '</p>
        <p><strong>Message:</strong> ' . nl2br(htmlspecialchars($data['message'] ?? 'N/A')) . '</p>
        <p><strong>Mobile:</strong> ' . htmlspecialchars($data['mobile'] ?? 'N/A') . '</p>
        <h3><strong>Course:</strong> ' . htmlspecialchars($data['courses'] ?? 'N/A') . '</h3>
        ';

        // Create the MailSender object and send the email
        $mailSender = new MailSender();
        $result = $mailSender->sendMail(
            $recipientEmail,  // Recipient email (from .env)
            $subjectPrefix,   // Email subject
            $emailBody        // Dynamically generated email body
        );

        // Send a response back to the client
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Data received']);
    } else {
        // If JSON is invalid, log the error
        error_log("Invalid JSON data.\n");

        // Return error response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
    }

    exit;
}

// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'];

// Define the path to the React `dist` folder
$distPath = __DIR__ . '/frontend';

// Map the request to a file in the `dist` folder
$filePath = $distPath . $requestUri;

// Serve static files if they exist
if ($requestUri !== '/' && file_exists($filePath)) {
    $mimeType = getMimeType($filePath);
    header("Content-Type: $mimeType");
    readfile($filePath);
    exit;
}

// Check for POST request to /api/contact, /api/course, or /api/header
$recipientEmail = $_ENV['RECIEPIENT_EMAIL'] ?? null;  // Get recipient email from the .env file
if (!$recipientEmail) {
    error_log("Recipient email is not set in the environment variables.");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (strpos($requestUri, '/api/contact') === 0) {
        handleFormSubmission($requestUri, $recipientEmail, 'Contact Form Submission');
    } elseif (strpos($requestUri, '/api/course') === 0) {
        handleFormSubmission($requestUri, $recipientEmail, 'Course Enquiry');
    } elseif (strpos($requestUri, '/api/header') === 0) {
        handleFormSubmission($requestUri, $recipientEmail, 'Course Enquiry Popup');
    }
}

// If no matching route, fallback to React's index.html
include $distPath . '/index.html';
