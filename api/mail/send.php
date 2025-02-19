<?php
header('Content-Type: application/json; charset=utf-8');

function hasEmailContactUsRecently(string $email): bool
{
    if (!file_exists('db.db')) {
        return false;
    }
    $bd = new SQLite3('db.db');
    $result = $bd->query("SELECT * FROM contact WHERE email = '{$email}' AND date >= datetime('now', '-1 day')");
    if (!$result) {
        $bd->close();
        return false;
    }
    $result = $result->fetchArray();
    $bd->close();
    return gettype($result) === 'array' && count($result) > 0;
}

$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$messageForm = $data['message'] ?? '';

if (hasEmailContactUsRecently($email)) {
    http_response_code(400);
    echo json_encode(['message' => 'Ya has enviado un correo recientemente.']);
    exit;
}

if (empty($name) || empty($email) || empty($phone) || empty($messageForm)) {
    http_response_code(400);
    echo json_encode(['message' => 'Por favor, complete todos los campos.']);
    exit;
}

$to = 'ventas@sipgdl.com';
$subject = 'Contacto desde sipgdl.com';
$message = "Nombre: {$name}\nCorreo: {$email}\nTeléfono: {$phone}\nMensaje: {$messageForm}";
$headers = 'From: ' . $email;

if (mail($to, $subject, $message, $headers)) {
    $bd = new SQLite3('db.db');
    $bd->exec('CREATE TABLE IF NOT EXISTS contact (id INTEGER, name STRING, email STRING, phone STRING, message TEXT, date datetime, PRIMARY KEY (id AUTOINCREMENT))');
    $bd->exec("INSERT INTO contact (name, email, phone, message, date) VALUES ('{$name}', '{$email}', '{$phone}', '{$messageForm}', datetime())");
    $bd->close();
    http_response_code(200);
    echo json_encode(['message' => 'Correo enviado correctamente.']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Error interno en el servidor.']);
}
