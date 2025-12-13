<?php
require_once 'includes/config.php';

$apiKey = 'AIzaSyDKL28URiyZkjpEL8HMBJSnFXwtGeFxu8Q'; // Use the NEW key explicitly
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

echo "Available Models for New Key:\n";
$data = json_decode($response, true);

if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        if (strpos($model['supportedGenerationMethods'][0], 'generateContent') !== false) {
            echo $model['name'] . "\n";
        }
    }
} else {
    echo "Error: " . $response;
}
?>
