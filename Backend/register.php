<?php
// =============================================
// Snackery – Benutzerregistrierung via JSON-API
// =============================================

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/Snackery',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Nur POST erlaubt."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Ungültiges JSON-Format."]);
    exit;
}

$requiredFields = [
    "firstname", "lastname", "username", "email",
    "street", "housenumber", "postalcode", "city",
    "password", "password_repeat"
];

foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Feld '$field' fehlt oder ist leer."]);
        exit;
    }
}

if ($data["password"] !== $data["password_repeat"]) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Passwörter stimmen nicht überein."]);
    exit;
}

// === BONUS: Passwortstärke prüfen ===
$pw = $data["password"];
if (
    strlen($pw) < 8 ||
    !preg_match('/[A-Z]/', $pw) ||
    !preg_match('/[a-z]/', $pw) ||
    !preg_match('/[0-9]/', $pw) ||
    !preg_match('/[\W]/', $pw)
) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Passwort zu schwach. Es muss mindestens 8 Zeichen, eine Zahl, einen Großbuchstaben und ein Sonderzeichen enthalten."]);
    exit;
}

$hashedPassword = password_hash($pw, PASSWORD_DEFAULT);

require_once __DIR__ . '/config/dbaccess.php';
$db = new DbAccess();
$conn = $db->connect();

// Check auf doppelte E-Mail oder Benutzernamen
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$data["username"], $data["email"]]);

if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Benutzername oder E-Mail existiert bereits."]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO users (
    firstname, lastname, username, email,
    street, housenumber, postalcode, city,
    password, role
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$success = $stmt->execute([
    $data["firstname"], $data["lastname"], $data["username"], $data["email"],
    $data["street"], $data["housenumber"], $data["postalcode"], $data["city"],
    $hashedPassword, "user"
]);

if ($success) {
    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Registrierung erfolgreich."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Fehler beim Speichern des Benutzers."]);
}
?>
