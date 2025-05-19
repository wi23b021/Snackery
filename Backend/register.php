<?php
// === register.php ===
// API-Endpunkt zur Benutzerregistrierung (wird von fetch() im Frontend aufgerufen)

// === Session-Konfiguration setzen ===
session_set_cookie_params([
    'lifetime' => 0,               // Session bleibt nur solange aktiv wie der Browser offen ist
    'path' => '/Snackery',         // Cookie ist für das ganze Projekt gültig
    'domain' => 'localhost',       // Nur für localhost – in Produktion anpassen
    'secure' => false,             // HTTPS nicht nötig bei localhost
    'httponly' => true,            // Kein Zugriff über JavaScript (Sicherheit)
    'samesite' => 'Lax'            // Schutz gegen CSRF bei externen Anfragen
]);
session_start(); // Session starten

// === Header für CORS und JSON-Antwort setzen ===
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// === Nur POST-Anfragen zulassen ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Methode nicht erlaubt
    echo json_encode(["success" => false, "message" => "Nur POST erlaubt."]);
    exit;
}

// === JSON-Daten auslesen ===
$data = json_decode(file_get_contents("php://input"), true);

// === Prüfen ob JSON gültig ist ===
if (!$data || !is_array($data)) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Ungültiges JSON-Format."]);
    exit;
}

// === Felder validieren (alle Pflichtfelder müssen vorhanden sein) ===
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

// === Passwort-Wiederholung prüfen ===
if ($data["password"] !== $data["password_repeat"]) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Passwörter stimmen nicht überein."]);
    exit;
}

// === BONUS: Passwortstärke validieren ===
$pw = $data["password"];
if (
    strlen($pw) < 8 ||
    !preg_match('/[A-Z]/', $pw) ||
    !preg_match('/[a-z]/', $pw) ||
    !preg_match('/[0-9]/', $pw) ||
    !preg_match('/[\W]/', $pw)
) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" =>
        "Passwort zu schwach. Es muss mindestens 8 Zeichen, eine Zahl, einen Großbuchstaben und ein Sonderzeichen enthalten."]);
    exit;
}

// === Passwort sicher hashen ===
$hashedPassword = password_hash($pw, PASSWORD_DEFAULT);

// === Verbindung zur Datenbank aufbauen ===
require_once __DIR__ . '/config/dbaccess.php';
$db = new DbAccess();
$conn = $db->connect();

// === Prüfen ob Benutzername oder E-Mail bereits existiert ===
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$data["username"], $data["email"]]);

if ($stmt->fetch()) {
    http_response_code(409); // Konflikt: Benutzer existiert schon
    echo json_encode(["success" => false, "message" => "Benutzername oder E-Mail existiert bereits."]);
    exit;
}

// === Neuen Benutzer in Datenbank einfügen ===
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

// === Erfolg oder Fehler zurückgeben ===
if ($success) {
    http_response_code(201); // Erfolgreich erstellt
    echo json_encode(["success" => true, "message" => "Registrierung erfolgreich."]);
} else {
    http_response_code(500); // Serverfehler
    echo json_encode(["success" => false, "message" => "Fehler beim Speichern des Benutzers."]);
}
?>
