<?php
// 
// Snackery – Benutzer-Login (login.php)
// Dieses Skript verarbeitet die Anmeldung und startet eine Session.
// 

// Session starten für Benutzerverwaltung
session_start();

// CORS-Header setzen (für Cookies bei localhost)
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Verbindung zur Datenbank
require_once __DIR__ . '/config/dbaccess.php';

// POST-Daten prüfen
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Benutzername und Passwort erforderlich."]);
    exit;
}

$usernameOrEmail = $_POST['username'];
$password = $_POST['password'];

// Datenbankverbindung aufbauen
$db = new DbAccess();
$conn = $db->connect();

$sql = "SELECT * FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$usernameOrEmail, $usernameOrEmail]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // ✅ Passwort korrekt → Sessiondaten setzen
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user'] = $user;

    // Sicherstellen, dass Session-Cookie nicht als "secure" markiert ist (für localhost!)
    setcookie("PHPSESSID", session_id(), [
        'path' => '/',
        'httponly' => true,
        'secure' => false, // ⚠️ Bei HTTPS auf true setzen
        'samesite' => 'Lax'
    ]);

    // Erfolgreiche Antwort zurücksenden
    echo json_encode([
        "success" => true,
        "message" => "Login erfolgreich!",
        "role" => $user['role']
    ]);
    exit;
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "❌ Benutzername oder Passwort ist falsch."]);
    exit;
}
?>
