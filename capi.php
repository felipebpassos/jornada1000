<?php
// capi.php — envia um evento para a Facebook Conversion API
header('Content-Type: application/json; charset=utf-8');

// Config
$PIXEL_ID = '1329665138673602';
$ACCESS_TOKEN = 'EABjCOG31MuoBPGMZBkVxuMvKeYZA3mZCcKREfyjZCZA9ZAonbEYOB7kUFDe4P3RLXjNn8NzzSwrBuaF4UHihs1YCFoVfTSZAnEu14jdVHslbMZCTeZCSdLfvZCgSMWqQzNiCZBlVvTVw3w2JMWfqiYBiIx1x4zmDu5BlXxe5jL0vGjy2b2Rklyw9DsaLgPc2tZCZBqvnsGAZDZD';
// Ideal: mover o token para variável de ambiente

// Entrada
$raw = file_get_contents('php://input');
$in = json_decode($raw ?: '[]', true);

// Monta evento
$event = [
    'event_name' => $in['event_name'] ?? 'PageView',
    'event_time' => time(),
    'event_id' => $in['event_id'] ?? ('ev-' . time()),
    'event_source_url' => $in['event_source_url'] ?? '',
    'action_source' => 'website',
    'user_data' => [
        'client_user_agent' => $in['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'client_ip_address' => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '',
        'fbp' => $in['fbp'] ?? null,
        'fbc' => $in['fbc'] ?? null,
    ],
];

$payload = json_encode(['data' => [$event]]);
$endpoint = "https://graph.facebook.com/v18.0/{$PIXEL_ID}/events?access_token={$ACCESS_TOKEN}";

// Envia
$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 8,
]);
$res = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

// Resposta
if ($http >= 200 && $http < 300) {
    echo $res;
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'http' => $http, 'err' => $err, 'res' => $res, 'sent' => $event]);
}
