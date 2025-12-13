<?php
require_once 'includes/config.php';

function test_url($url, $key, $provider) {
    echo "Testing $provider ($url)...\n";
    
    $prompt = "Test";
    $json_data = [];
    $header_key = "";

    if ($provider == 'HF') {
        $json_data = ["inputs" => $prompt];
        $header_key = "Authorization: Bearer $key";
    } else {
        $json_data = ["contents" => [["parts" => [["text" => $prompt]]]]];
        // Gemini key is in URL
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($provider == 'HF') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", $header_key]);
    } else {
         curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Status: $http_code\n";
    echo "Response: " . substr($response, 0, 100) . "...\n\n";
}

// 1. Test HF Router with Mistral
test_url("https://router.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.3", HUGGINGFACE_API_KEY, 'HF');

// 2. Test HF Old API with Zephyr
test_url("https://api-inference.huggingface.co/models/HuggingFaceH4/zephyr-7b-beta", HUGGINGFACE_API_KEY, 'HF');

// 3. Test Gemini (Use the NEW key if possible, currently configured key might be HF...)
// We'll read the file to see what's in config, but assuming config has HF key currently.
// Let's manually test the Gemini Key user gave earlier: 'AIzaSyDKL28URiyZkjpEL8HMBJSnFXwtGeFxu8Q'
test_url("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=AIzaSyDKL28URiyZkjpEL8HMBJSnFXwtGeFxu8Q", null, 'Gemini');
?>
