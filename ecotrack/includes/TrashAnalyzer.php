<?php

class TrashAnalyzer {
    
    // Knowledge Base for "Fallback" detection
    private $patterns = [
        'plastic' => ['plastic', 'bottle', 'container', 'wrapper', 'bag', 'straw', 'cup', 'pvc'],
        'metal' => ['metal', 'can', 'tin', 'aluminum', 'foil', 'steel', 'wire', 'scrap'],
        'glass' => ['glass', 'bottle', 'jar', 'shard', 'broken glass'],
        'organic' => ['food', 'fruit', 'vegetable', 'peel', 'remains', 'leaves', 'garden', 'compost'],
        'electronic' => ['electronic', 'battery', 'device', 'phone', 'computer', 'wire', 'cable', 'screen', 'tv'],
        'hazardous' => ['chemical', 'oil', 'paint', 'battery', 'medical', 'needle', 'syringe', 'toxic']
    ];

    private $decompositionTimes = [
        'plastic' => '450+ years',
        'metal' => '50-200 years',
        'glass' => '1 million years (undetermined)',
        'organic' => '2-6 weeks',
        'electronic' => 'Toxic leakage risk',
        'hazardous' => 'Immediate environmental threat',
        'unknown' => 'Unknown'
    ];

    /**
     * Analyze text description to infer waste type and severity.
     * Uses Mistral API if configured, otherwise falls back to keyword matching.
     * 
     * @param string $description The user's description of the trash.
     * @return array Analysis result.
     */
    public function analyze($description) {
        // Check if API Key is configured and not default
        if (defined('MISTRAL_API_KEY') && MISTRAL_API_KEY !== '') {
            $aiResult = $this->callMistralAPI($description);
            if ($aiResult) {
                return $aiResult;
            }
        }
        
        // Fallback to local logic
        return $this->fallbackAnalyze($description);
    }

    private function callMistralAPI($description) {
        $apiKey = MISTRAL_API_KEY;
        $url = "https://api.mistral.ai/v1/chat/completions";
        $model = "mistral-tiny";

        $prompt = "
        You are an ecological waste expert. Analyze the following trash description: '$description'.
        Return strictly a JSON object with the following keys:
        - type: The primary category (Plastic, Metal, Glass, Organic, Electronic, Hazardous, or General Waste).
        - detected_materials: An array of specific materials identified.
        - severity: 'Low', 'Medium', 'High', or 'Critical'.
        - score: A number from 0-100 indicating environmental impact (100 is worst).
        - decomposition: Estimated decomposition time (e.g., '450 years').
        - message: A short, helpful one-sentence status message about what was detected.
        - icon: A Bootstrap Icon class name (e.g., 'bi-trash', 'bi-battery', 'bi-flower1', 'bi-exclamation-triangle').
        ";

        $data = [
            "model" => $model,
            "messages" => [
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "response_format" => ["type" => "json_object"]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        // Disable SSL verification for local dev
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            error_log("EcoTrack Mistral API Network Error: " . curl_error($ch));
            curl_close($ch);
            return null;
        }
        
        curl_close($ch);
        $json = json_decode($response, true);

        // Parse Mistral Response
        if (isset($json['choices'][0]['message']['content'])) {
            $rawText = $json['choices'][0]['message']['content'];
            $parsed = json_decode($rawText, true);
            if ($parsed) {
                // Add verification tag to prove it's the real API
                if (isset($parsed['message'])) {
                    $parsed['message'] .= " (Verified Mistral AI)";
                }
                return $parsed;
            }
        } else {
            // Log the error response for debugging
            error_log("EcoTrack Mistral API Error: " . $response);
        }

        return null;
    }

    private function fallbackAnalyze($description) {
        $description = strtolower($description);
        $detectedTypes = [];
        
        // Expanded "Smart" Patterns
        $advancedPatterns = [
            'hazardous' => [
                'mercury', 'thermometer', 'asbestos', 'lead', 'acid', 'radioactive', 'biohazard', 
                'needle', 'syringe', 'medical', 'blood', 'toxic', 'poison', 'chemical', 'pesticide',
                'paint', 'solvent', 'battery', 'batteries', 'car battery', 'lithium'
            ],
            'electronic' => [
                'phone', 'laptop', 'computer', 'screen', 'monitor', 'tv', 'cable', 'wire', 'charger',
                'circuit', 'chip', 'electronic', 'device', 'printer', 'tablet'
            ],
            'plastic' => [
                'plastic', 'bottle', 'wrapper', 'bag', 'straw', 'cup', 'container', 'pvc', 'nylon',
                'packaging', 'polystyrene', 'styrofoam'
            ],
            'metal' => [
                'metal', 'can', 'tin', 'aluminum', 'steel', 'iron', 'copper', 'scrap', 'foil',
                'nail', 'screw', 'bolt', 'wire'
            ],
            'glass' => [
                'glass', 'bottle', 'jar', 'pane', 'mirror', 'shard', 'broken glass', 'window'
            ],
            'organic' => [
                'food', 'fruit', 'vegetable', 'meat', 'bread', 'peel', 'remains', 'bannana', 'apple',
                'garden', 'leaf', 'leaves', 'grass', 'wood', 'branch', 'flower', 'compost'
            ]
        ];

        foreach ($advancedPatterns as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($description, $keyword) !== false) {
                    $detectedTypes[] = $type;
                }
            }
        }

        $detectedTypes = array_unique($detectedTypes);
        
        // Default execution if nothing found
        if (empty($detectedTypes)) {
            return [
                'type' => 'General Waste',
                'severity' => 'Low',
                'score' => 20,
                'decomposition' => 'Varies',
                'message' => 'Could not identify specific materials. Please ensure it is disposed of in a general waste bin.',
                'icon' => 'bi-trash'
            ];
        }

        // Logic for "Simulated" Synthesis
        $primaryType = $detectedTypes[0]; 
        
        // Specific Smart Responses for specific keywords
        $message = "Detected " . implode(', ', $detectedTypes) . ".";
        
        if (stripos($description, 'mercury') !== false || stripos($description, 'thermometer') !== false) {
            $primaryType = 'hazardous';
            $detectedTypes = ['hazardous', 'glass'];
            $severityScore = 95;
            $severityLabel = 'Critical';
            $icon = 'bi-radioactive';
            $message = "DANGER: Mercury is highly toxic. Do not touch with bare hands. Seal in an airtight container immediately.";
            $decomposition = "Does not decompose (Toxic)";
        } elseif (in_array('hazardous', $detectedTypes) || in_array('electronic', $detectedTypes)) {
            $severityScore = 90;
            $severityLabel = 'Critical';
            $icon = 'bi-exclamation-triangle-fill';
            $message = "This contains hazardous materials. Do not place in general bins. Find a specialized e-waste or hazard drop-off.";
            $decomposition = "Indefinite (Toxic capability)";
        } elseif (in_array('plastic', $detectedTypes)) {
            $severityScore = 70;
            $severityLabel = 'High';
            $icon = 'bi-box-seam';
            $message = "Plastic waste persists for centuries. Please ensure this is clean and recycled if possible.";
            $decomposition = "450+ years";
        } elseif (in_array('metal', $detectedTypes) || in_array('glass', $detectedTypes)) {
            $severityScore = 50;
            $severityLabel = 'Medium';
            $icon = 'bi-tools';
            $message = "Recyclable material detected. Metal and glass can often be recycled indefinitely.";
            $decomposition = $primaryType === 'glass' ? '1 million+ years' : '50-200 years';
        } else {
            $severityScore = 30;
            $severityLabel = 'Low';
            $icon = 'bi-recycle';
            $message = "Organic waste can be composted to create nutrient-rich soil.";
            $decomposition = "2-6 weeks";
        }

        return [
            'type' => ucfirst($primaryType),
            'detected_materials' => $detectedTypes,
            'severity' => $severityLabel,
            'score' => $severityScore,
            'decomposition' => $decomposition,
            'message' => $message,
            'icon' => $icon
        ];
    }
}
?>
