<?php
$apiKey = 'lsm6xhmBJfc9JBHbxjT1IdjIZfk8QIjd';
$url = 'https://api.mistral.ai/v1/chat/completions';

$data = [
    'model' => 'mistral-tiny',
    'messages' => [
        ['role' => 'user', 'content' => 'Say hello']
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $http_code\n";
echo "Response: " . substr($response, 0, 200) . "\n";
?>
