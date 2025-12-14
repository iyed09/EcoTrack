<?php
// cli_test_chatbot.php

// URL correcte pour le script local appelé via PHP CLI ?
// En fait, on ne peut pas appeler l'URL localhost facilement si le serveur web n'est pas up ou si on ne connait pas le port.
// Mais on peut inclure le fichier chatbot_api.php en mockant $_SERVER, $_POST, etc.

// Mock environnement
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['message'] = 'Bonjour Gemini, qui es-tu ? Réponds en un mot.';
$_FILES = []; // Pas d'image pour ce test

// Capture output
ob_start();
require '../view/frontoffice/chatbot_api.php';
$output = ob_get_clean();

echo "Output from chatbot_api.php:\n";
echo $output . "\n";

$json = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "\nJSON Valid: Yes\n";
    if ($json['success'] && !empty($json['reply'])) {
        echo "Success: Yes\n";
        echo "Reply: " . $json['reply'] . "\n";
    } else {
        echo "Success: No\n";
        echo "Error: " . ($json['error'] ?? 'Unknown') . "\n";
    }
} else {
    echo "\nJSON Valid: No\n";
}
