<?php

include __DIR__ . '/vendor/autoload.php';

$ini_file = __DIR__ . '/.config.ini';
if (!file_exists($ini_file)) {
    die('INI file (.config.ini) not found.');
}

$INI    = parse_ini_file($ini_file);
$phone  = filter_input(INPUT_GET, 'phone', FILTER_SANITIZE_NUMBER_INT);

$message = 'Emtel SMS test from AWS. ' . date('Y-m-d H:i:s');
$params  = [
    'username' => $INI['EMTEL_USER'],
    'password' => $INI['EMTEL_PASS'],
    'to'       => $phone,
    'text'     => $message,
];

$url = $INI['EMTEL_SEND_SMS_API_ENDPOINT'] . '?' . http_build_query($params);

try {
    $client   = new \GuzzleHttp\Client();
    $response = $client->request('GET', $url);

    echo json_encode([
        'message-sent'  => $message,
        'to-phone'      => $phone,
        'error'         => false,
        'status-code'   => $response->getStatusCode(),
        'content-type'  => $response->getHeaderLine('content-type'),
        'response-body' => $response->getBody(),
    ], JSON_PRETTY_PRINT);
}
catch (Exception $e) {
    echo json_encode([
        'message-sent' => $message,
        'to-phone'     => $phone,
        'error'        => true,
        'error-msg'    => $e->getMessage(),
    ], JSON_PRETTY_PRINT);
}
