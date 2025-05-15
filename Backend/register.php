<?php
// =============================================
// Snackery – Benutzerregistrierung via JSON-API
// =============================================

// 1. Session-Einstellungen
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/Snackery',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// 2. Header für CORS + JSON
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// 3. Nur POST-Anfragen zulassen
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Nur POST erlaubt."]);
    exit;
}

// 4. JSON-Daten einlesen
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Ungültiges JSON-Format."]);
    exit;
}

// 5. Pflichtfelder prüfen
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

// 6. Passwortvergleich
if ($data["password"] !== $data["password_repeat"]) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Passwörter stimmen nicht überein."]);
    exit;
}

// 7. Passwort hashen
$hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);

// 8. Datenbankverbindung
require_once __DIR__ . '/config/dbaccess.php';
$db = new DbAccess();
$conn = $db->connect();

// 9. Benutzername oder E-Mail prüfen
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$data["username"], $data["email"]]);

if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Benutzername oder E-Mail existiert bereits."]);
    exit;
}

// 10. Neuen Benutzer einfügen
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

// 11. Antwort an das Frontend
if ($success) {
    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Registrierung erfolgreich."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Fehler beim Speichern des Benutzers."]);
}
?>
