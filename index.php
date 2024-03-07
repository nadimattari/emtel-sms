<?php

include __DIR__ . '/vendor/autoload.php';

$ini_file = __DIR__ . '/.config.ini';
if (!file_exists($ini_file)) {
    die('INI file (.config.ini) not found.');
}

$INI    = parse_ini_file($ini_file);
$phone  = filter_input(INPUT_GET, 'phone', FILTER_SANITIZE_NUMBER_INT);

$message = 'Emtel SMS test on AWS. ' . date('Y-m-d H:i:s') . ' ~nadim';
$params  = [
    'username' => $INI['EMTEL_USER'],
    'password' => $INI['EMTEL_PASS'],
    'to'       => $phone,
    'text'     => $message,
];

$url = $INI['EMTEL_SEND_SMS_API_ENDPOINT'] . '?' . http_build_query($params);

try {
    /**
     * $ curl -k
     *      --request GET
     *      --url '$url'
     *      --ciphers 'DEFAULT@SECLEVEL=0'
     */
    $client   = new \GuzzleHttp\Client();
    $response = $client->request('GET', $url, [
        'verify' => false,
        'curl'   => [
            CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=0',
        ]
    ]);
    
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
    $msg = str_replace(
        [$INI['EMTEL_USER'], $INI['EMTEL_PASS']], 
        ['[USER]', '[PASSWD]'], 
        $e->getMessage()
    );
    echo json_encode([
        'message-sent' => $message,
        'to-phone'     => $phone,
        'error'        => true,
        'error-msg'    => $msg,
    ], JSON_PRETTY_PRINT);
}
