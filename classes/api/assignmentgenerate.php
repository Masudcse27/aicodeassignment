<?php
namespace mod_aicodeassignment\api;

defined('MOODLE_INTERNAL') || die();

class assignmentgenerate
{
    public static function generate($prompt, $difficulty)
    {
        $apiKey = get_config('mod_aicodeassignment', 'apikey');
        $url = get_config('mod_aicodeassignment', 'apiendpoint');

        // Force the AI to generate full, clear description with examples.
        
        $formatInstruction = <<<EOD
            You are an expert problem setter for programming contests like Codeforces.

            Create a programming problem based on this topic: "$prompt" (difficulty: $difficulty).
            Requirements:
            - Do NOT mention the topic name in the title or description.
            - Use a creative, original title.
            - Describe the problem clearly, including a short story or scenario.
            - Provide precise input and output formats. The formats must EXACTLY match the data structures used in the test cases.
            - Provide 2-3 meaningful test cases.
            - Each test case's "input" and "output" must conform exactly to the described formats.
            - Do NOT include any explanations inside the test cases.
            - Ensure all data types (arrays, integers, objects) in the test cases match the description.
            - Provide a creative problem title and engaging problem description.
            - Clearly specify input/output format.
            - **Provide a working sample solution in C++ code that solves the problem.**

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
                    "output": ...
                }
                ],
                "solution_cpp": "string with C++ code"
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
