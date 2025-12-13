<?php
// Define the key manually to ensure we are testing the right context if config isn't picking it up (though it should)
require_once 'includes/config.php';
require_once 'includes/TrashAnalyzer.php';

echo "Testing Gemini 2.0 Flash Integration...\n";
echo "Key: " . substr(GEMINI_API_KEY, 0, 5) . "...\n";

$analyzer = new TrashAnalyzer();
$description = "A broken thermometer with mercury leaking out";

// We want to see if it calls the API or Fallback.
// The TrashAnalyzer currently prints errors to error_log. 
// We will rely on the output content to judge.

$result = $analyzer->analyze($description);

print_r($result);
?>
