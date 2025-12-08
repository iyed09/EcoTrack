<?php
// controller/aiController.php

header('Content-Type: application/json');

// ==========================================
// CONFIGURATION
// ==========================================
// TODO: Replace with your actual Gemini API Key
// You can get one here: https://aistudio.google.com/app/apikey
define('GEMINI_API_KEY', 'AIzaSyCzCcIOq4H093Ji-CP_k70TGtQ245URDNg'); 

// ==========================================
// HELPER FUNCTIONS
// ==========================================

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

// 1. Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Only POST requests are allowed', 405);
}

// 2. Get message from POST data
// Support both JSON body and Form Data
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = '';

if (isset($_POST['message'])) {
    $userMessage = trim($_POST['message']);
} else if ($input && isset($input['message'])) {
    $userMessage = trim($input['message']);
}

if (empty($userMessage)) {
    sendError('Message is required.');
}

// 3. Check API Key
if (GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY') {
    // If key is not configured, return a specific code for frontend fallback
    echo json_encode(['success' => false, 'error' => 'API_KEY_MISSING', 'fallback' => true]);
    exit;
}

// 4. Call Gemini API
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . GEMINI_API_KEY;

// Prompt formatting
$promptData = [
    'contents' => [
        [
            'parts' => [
                ['text' => "You are EcoBot, a helpful assistant for the EcoTrack community. Answer concisely and politely.\nUser: " . $userMessage]
            ]
        ]
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($promptData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// Execute
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// 5. Handle Response
if ($curlError) {
    echo json_encode(['success' => false, 'error' => 'Connection failed', 'fallback' => true]);
    exit;
}

if ($httpCode !== 200) {
    // API Warning/Error
    echo json_encode(['success' => false, 'error' => 'API Error: ' . $httpCode, 'fallback' => true, 'raw' => $response]);
    exit;
}

// Parse Gemini Response
$responseData = json_decode($response, true);

if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $botReply = $responseData['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(['success' => true, 'reply' => $botReply]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid API format', 'fallback' => true]);
}
