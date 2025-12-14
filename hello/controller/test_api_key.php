<?php
// Test simple de la clé API
$apiKey = 'AIzaSyAcnugmZPzTq6cbiAcPzsCED_SQCdLa4ZM';

// Test 1: Vérifier si on peut lister les modèles
echo "=== Test 1: Liste des modèles ===\n";
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    echo "✓ La clé API fonctionne pour lister les modèles\n";
} else {
    echo "✗ Erreur: $httpCode\n";
    $data = json_decode($response, true);
    echo "Message: " . ($data['error']['message'] ?? 'Inconnu') . "\n";
}

// Test 2: Essayer generateContent avec un message simple
echo "\n=== Test 2: generateContent ===\n";
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=$apiKey";
$payload = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Dis bonjour en un mot']
            ]
        ]
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "✓ Réponse: " . ($data['candidates'][0]['content']['parts'][0]['text'] ?? 'N/A') . "\n";
} else {
    echo "✗ Erreur: $httpCode\n";
    $data = json_decode($response, true);
    echo "Message: " . ($data['error']['message'] ?? 'Inconnu') . "\n";
    echo "Status: " . ($data['error']['status'] ?? 'Inconnu') . "\n";
    echo "Details: " . json_encode($data['error']['details'] ?? [], JSON_PRETTY_PRINT) . "\n";
}
