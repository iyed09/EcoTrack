<?php
require_once 'includes/config.php';
require_once 'includes/TrashAnalyzer.php';

echo "Testing Mistral AI Integration...\n";
echo "Key: " . substr(MISTRAL_API_KEY, 0, 5) . "...\n";

$analyzer = new TrashAnalyzer();
$description = "A broken thermometer with mercury leaking out";

$result = $analyzer->analyze($description);

print_r($result);
?>
