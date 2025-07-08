<?php
namespace mod_aicodeassignment\api;

defined('MOODLE_INTERNAL') || die();

class codegrader {
    public static function evaluate_submission($question, $studentcode, $submission_language, $solutioncode, $language = 'C++') {
        $apiKey = get_config('mod_aicodeassignment', 'apikey');
        $url = get_config('mod_aicodeassignment', 'apiendpoint');

        $prompt = <<<EOD
            You are a strict programming instructor evaluating student code.

            Compare the student's code with the correct solution. Provide:
            - A score out of 100
            - 2-3 lines of feedback
            - Focus on logic, correctness, readability

            Question: "$question"
            
            🧠 ACTUAL SOLUTION CODE:
            $solutioncode
            Language: $language

            👨‍🎓 STUDENT CODE:
            $studentcode
            STUDENT CODE LANGUAGE: $submission_language

            Respond ONLY in this JSON format:
            {
            "grade": 85,
            "feedback": "Your solution works well, but lacks error handling and proper input format."
            }
        EOD;

        $postData = [
            'model' => 'mistralai/mistral-7b-instruct', // or any model from OpenRouter/OpenAI
            'messages' => [
                ['role' => 'system', 'content' => 'You evaluate student code.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.2
        ];

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: https://yourdomain.com', // Replace with your Moodle domain
            'X-Title: Moodle Code Grader'
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return null;
        }

        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $json = json_decode($data['choices'][0]['message']['content'], true);
            if (is_array($json) && isset($json['grade'])) {
                return $json;
            }
        }
        return null;
    }
}
