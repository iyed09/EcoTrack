<?php
// frontoffice/chatbot_api.php
header('Content-Type: application/json');

// Clé API Gemini (hardcodée pour ce fichier, mais idéalement devrait être en variable d'env)
// Note: Le user a fourni la clé dans le chat.
$apiKey = 'AIzaSyAcnugmZPzTq6cbiAcPzsCED_SQCdLa4ZM';

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

// Récupérer le message et l'image
$message = $_POST['message'] ?? '';
$imageFile = $_FILES['image'] ?? null;

if (empty($message) && !$imageFile) {
    echo json_encode(['success' => false, 'error' => 'Aucun message ou image fourni.']);
    exit;
}

// Préparer le contenu pour Gemini
$contents = [];
$parts = [];

// Ajouter le texte si présent
if (!empty($message)) {
    $parts[] = ['text' => $message];
}

// Ajouter l'image si présente
if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
    $mimeType = mime_content_type($imageFile['tmp_name']);
    $imageData = base64_encode(file_get_contents($imageFile['tmp_name']));
    
    $parts[] = [
        'inline_data' => [
            'mime_type' => $mimeType,
            'data' => $imageData
        ]
    ];
}

$contents[] = ['parts' => $parts];

// Préparer la requête JSON
$payload = [
    'contents' => $contents
];

// URL de l'API Gemini (gemini-flash-latest est toujours à jour)
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=$apiKey";

// Initialiser cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pour éviter les erreurs SSL en local si nécessaire

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['success' => false, 'error' => 'Erreur cURL : ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Traiter la réponse
if ($httpCode === 200) {
    $data = json_decode($response, true);
    
    // Extraire le texte de la réponse Gemini
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        $reply = $data['candidates'][0]['content']['parts'][0]['text'];
        echo json_encode(['success' => true, 'reply' => $reply]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Format de réponse Gemini inattendu.', 'raw' => $data]);
    }
} else {
    // Gérer les erreurs avec des messages clairs
    $data = json_decode($response, true);
    $errorMessage = '';
    
    switch ($httpCode) {
        case 403:
            $errorMessage = "🔒 Accès refusé. La clé API n'a peut-être pas les bonnes permissions ou a atteint sa limite. Veuillez vérifier votre clé API dans Google AI Studio.";
            break;
        case 429:
            $errorMessage = "⏱️ Trop de requêtes. Veuillez patienter quelques instants avant de réessayer.";
            break;
        case 503:
            $errorMessage = "⚠️ Le service Gemini est temporairement surchargé. Veuillez réessayer dans quelques instants.";
            break;
        case 500:
            $errorMessage = "❌ Erreur interne du serveur Gemini. Veuillez réessayer plus tard.";
            break;
        default:
            $apiError = $data['error']['message'] ?? 'Erreur inconnue';
            $errorMessage = "Erreur API Gemini ($httpCode): $apiError";
    }
    
    echo json_encode([
        'success' => false, 
        'error' => $errorMessage
    ]);
}
