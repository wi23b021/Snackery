<?php
// ==============================================
// Snackery – Benutzer-Login (API für fetch())
// ==============================================

// 1. SESSION-KONFIGURATION
session_set_cookie_params([
    'lifetime' => 0,            // Session läuft nur bis zum Schließen des Browsers
    'path' => '/Snackery',       // Gültig für das gesamte Projektverzeichnis
    'domain' => 'localhost',     // Für Livebetrieb anpassen
    'secure' => false,           // true bei HTTPS (für localhost = false)
    'httponly' => true,          // Schutz: Kein Zugriff via JavaScript
    'samesite' => 'Lax'          // CSRF-Schutz für Fremdanfragen
]);
session_start();

// 2. HEADER FÜR FETCH() UND CORS
header("Access-Control-Allow-Origin: http://localhost"); // Genau anpassen
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// 3. Nur POST zulassen
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Methode nicht erlaubt
    echo json_encode(["success" => false, "message" => "Nur POST erlaubt."]);
    exit;
}

// 4. JSON-Daten auslesen
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input['username']) || !isset($input['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Benutzername und Passwort erforderlich."]);
    exit;
}

$usernameOrEmail = trim($input['username']);
$password = trim($input['password']);

// 5. DB-Verbindung
require_once __DIR__ . '/config/dbaccess.php';
$db = new DbAccess();
$conn = $db->connect();

// 6. Benutzer suchen
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->execute([$usernameOrEmail, $usernameOrEmail]);
$user = $stmt->fetch();

// 7. Passwort prüfen
if ($user && password_verify($password, $user['password'])) {
    // Session-Daten setzen (minimale Datenmenge empfohlen)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user'] = [
        "id" => $user['id'],
        "username" => $user['username'],
        "role" => $user['role']
    ];

    // Weiterleitung je nach Rolle
    if ($user['role'] === 'admin') {
        echo json_encode([
            "success" => true,
            "message" => "Login erfolgreich!",
            "role" => $user['role'],
            "redirect" => "admin.html"
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "message" => "Login erfolgreich!",
            "role" => $user['role'],
            "redirect" => "profil.html"
        ]);
    }
    exit;
} else {
    // Fehler bei Login
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "❌ Benutzername oder Passwort ist falsch."
    ]);
    exit;
}
?>
