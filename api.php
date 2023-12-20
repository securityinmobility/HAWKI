<?php

define('ALLOWED_KEYS', ["model", "stream", "messages"]);

session_start();

if (file_exists(".env")){
	$env = parse_ini_file('.env');
}
$apiKey = isset($env) ? $env['OPENAI_API_KEY'] : getenv('OPENAI_API_KEY');;

if (!isset($_SESSION['username'])) {
	http_response_code(401);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	$url = 'https://api.openai.com/v1/chat/completions';
	$payload = file_get_contents("php://input");

	// Validate JSON payload
	$data = json_decode($payload, true);
	if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
		die("invalid json");
	}
	// check for additional query keys
	$keys = array_keys($data);
	$diff = array_diff($keys, ALLOWED_KEYS);
	if (!empty($diff)) {
		die("invalid json");
	}

	$headers = array(
		"Authorization: Bearer $apiKey",
		'Accept: application/json',
		'Content-Type: application/json'
	);

	$options = array(
		CURLOPT_URL => $url,
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_POSTFIELDS => $payload,
		CURLOPT_RETURNTRANSFER => true
	);

	$curl = curl_init();
	curl_setopt_array($curl, $options);
	$response = curl_exec($curl);
	curl_close($curl);

	// Process the response
	if ($response === false) {
		echo json_encode(['error' => curl_error($curl)]);
		http_response_code(500);
		exit;
	} else {
		echo $response;
		exit;
	}

}

?>
