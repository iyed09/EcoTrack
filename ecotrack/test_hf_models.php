<?php
require_once 'includes/config.php';

// Ensure we use the HF key we have
$key = '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'; // Hardcoded from user input earlier to be sure

function test_model($model, $key) {
    echo "Testing $model... ";
    $url = "https://router.huggingface.co/models/" . $model;
    
    // Simple prompt valid for most models
    $data = ["inputs" => "Classify this trash: Broken thermometer regarding mercury."];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $key
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "[$http_code]\n";
    if ($http_code == 200) {
        echo "SUCCESS! Response sample: " . substr($response, 0, 100) . "\n";
        return true;
    } else {
        echo "Fail. Response: " . substr($response, 0, 100) . "\n";
        return false;
    }
}

// List of standard "free tier likely" models
$models = [
    "google/flan-t5-base",
    "google/flan-t5-large",
    "facebook/bart-large-mnli", 
    "gpt2",
    "bert-base-uncased"
];

foreach ($models as $m) {
    if (test_model($m, $key)) {
        echo "\nWINNER FOUND: $m\n";
        break; // Stop at first working one
    }
}
?>
