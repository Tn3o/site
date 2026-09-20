<?php
// Приём заявок с сайта Diana Darso.
// ВПИШИТЕ СВОЮ ПОЧТУ между кавычками ниже, туда будут приходить заявки:
$to = 'd.ismailova2013@yandex.ru';

header('Content-Type: application/json; charset=utf-8');

function finish($code, $ok) {
    http_response_code($code);
    echo json_encode(['ok' => $ok]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { finish(405, false); }

// Защита от роботов: скрытое поле должно быть пустым
if (!empty($_POST['website'])) { finish(200, true); }

// Пока почта не вписана, отправка не работает (форма на сайте предложит Telegram)
if (strpos($to, 'example.com') !== false) { finish(500, false); }

function clean($v) {
    $v = trim((string)$v);
    $v = preg_replace('/[\r\n]+/', ' ', $v);
    return mb_substr($v, 0, 200);
}

$name  = clean($_POST['name']  ?? '');
$phone = clean($_POST['phone'] ?? '');
$email = clean($_POST['email'] ?? '');

if ($name === '' || $phone === '' || empty($_POST['consent'])) { finish(400, false); }
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { finish(400, false); }

$host = preg_replace('/[^a-z0-9.\-]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
$host = preg_replace('/^www\./i', '', $host);

$subject = '=?UTF-8?B?' . base64_encode('Новая заявка с сайта Diana Darso') . '?=';
$body = "Имя: $name\nТелефон: $phone\nПочта: " . ($email !== '' ? $email : 'не указана') . "\nДата: " . date('d.m.Y H:i') . "\n";

$headers = ["From: no-reply@$host", 'Content-Type: text/plain; charset=UTF-8'];
if ($email !== '') { $headers[] = "Reply-To: $email"; }

$sent = @mail($to, $subject, $body, implode("\r\n", $headers));
finish($sent ? 200 : 500, (bool)$sent);
