<?php
/**
 * Chatbot API Endpoint - Mistral AI Integration
 * Handles chatbot requests and communicates with Mistral AI API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get request data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['message']) || empty(trim($data['message']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit();
}

$userMessage = trim($data['message']);
$conversationHistory = isset($data['history']) ? $data['history'] : [];

// Mistral AI API configuration
$apiKey = '9fhLe6pWtqKT4ng2ApIowMKZhJ1QgBzD';
$apiUrl = 'https://api.mistral.ai/v1/chat/completions';

// Build messages array for Mistral API
$messages = [
    [
        'role' => 'system',
        'content' => 'Tu es un assistant virtuel pour EcoTrack, une plateforme communautaire dédiée à l\'écologie et au développement durable. Tu dois répondre en français de manière amicale et encourageante. Ton rôle est d\'aider les utilisateurs avec des questions sur l\'écologie, le fonctionnement de la plateforme EcoTrack, et de donner des conseils pour un mode de vie plus écologique. Reste toujours positif et encourage les initiatives vertes.'
    ]
];

// Add conversation history (limit to last 10 messages to avoid token limits)
if (!empty($conversationHistory)) {
    $recentHistory = array_slice($conversationHistory, -10);
    $messages = array_merge($messages, $recentHistory);
}

// Add current user message
$messages[] = [
    'role' => 'user',
    'content' => $userMessage
];

// Prepare API request
$requestData = [
    'model' => 'mistral-tiny',
    'messages' => $messages,
    'temperature' => 0.7,
    'max_tokens' => 500
];

// Initialize cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Handle cURL errors
if ($curlError) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to connect to AI service',
        'details' => $curlError
    ]);
    exit();
}

// Handle API errors
if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode([
        'error' => 'AI service error',
        'details' => $response
    ]);
    exit();
}

// Parse response
$apiResponse = json_decode($response, true);

if (!isset($apiResponse['choices'][0]['message']['content'])) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Invalid response from AI service'
    ]);
    exit();
}

// Return successful response
$botMessage = $apiResponse['choices'][0]['message']['content'];
echo json_encode([
    'success' => true,
    'message' => $botMessage
]);
