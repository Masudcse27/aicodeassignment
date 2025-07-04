<?php
namespace mod_aicodeassignment\api;

defined('MOODLE_INTERNAL') || die();

class assignmentgenerate
{
    public static function generate($prompt, $difficulty)
    {
        $apiKey = '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';
        $url = 'https://openrouter.ai/api/v1/chat/completions';

        // Force the AI to generate full, clear description with examples.
        $formatInstruction = <<<EOD
            You are an expert problem setter for online coding contests like Codeforces.

            Create a high-quality programming problem based on this topic: "$prompt" (difficulty: $difficulty).

            🧠 Your task:
            - Do NOT mention the topic name in the title or description.
            - Use a creative, original title (like "Lost in the Labyrinth", "Sorting Potatoes", etc.)
            - Describe the problem in an engaging way, possibly with a short story or realistic scenario.
            - Clearly specify input/output formats like a real contest.
            - Include 2-3 meaningful test cases.

            Respond ONLY with JSON in this format:
            {
            "assignment": {
                "title": "string", 
                "description": "string",
                "input_format": "string",
                "output_format": "string",
                "test_cases": [
                {
                    "input": [...],
                    "target": ...,
                    "output": ...
                }
                ]
            }
            }
            EOD;

        $postData = [
            'model' => 'mistralai/mistral-7b-instruct',
            'messages' => [
                ['role' => 'system', 'content' => 'You generate programming assignments in structured JSON.'],
                ['role' => 'user', 'content' => $formatInstruction]
            ],
            'temperature' => 0.7
        ];

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: https://yourdomain.com', // Replace with your Moodle domain
            'X-Title: Moodle AI Assignment Generator'
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => "API request failed: $error"];
        }
        curl_close($ch);

        return $response;
    }
}
