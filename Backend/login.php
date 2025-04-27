<?php
// 
// Snackery – Benutzer-Login (login.php)
// Dieses Skript verarbeitet die Anmeldung und startet eine Session.
// 

session_start(); // Session starten für Benutzerverwaltung
header("Content-Type: application/json"); // Rückgabe im JSON-Format

// 1. Verbindung zur Datenbank
require_once __DIR__ . '/config/dbaccess.php';

// 2. POST-Daten prüfen
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    // ❌ Fehlende Felder → Fehlerantwort
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Benutzername und Passwort erforderlich."]);
    exit;
}

// 3. Eingaben abrufen
$usernameOrEmail = $_POST['username'];
$password = $_POST['password'];

// 4. Verbindung zur Datenbank aufbauen
$db = new DbAccess();
$conn = $db->connect();

// 5. Benutzer anhand von Benutzername oder E-Mail suchen
$sql = "SELECT * FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$usernameOrEmail, $usernameOrEmail]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // ✅ Passwort korrekt → Sessiondaten setzen
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user'] = $user;

    // Optional: Cookie setzen für "angemeldet bleiben"
    if (isset($_POST['remember'])) {
        setcookie("username", $user['username'], time() + (86400 * 30), "/"); // 30 Tage
    }

    // Erfolgreiche Antwort zurücksenden
    echo json_encode([
        "success" => true,
        "message" => "Login erfolgreich!",
        "role" => $user['role']
    ]);
    exit;
} else {
    // ❌ Benutzername oder Passwort falsch
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "❌ Benutzername oder Passwort ist falsch."]);
    exit;
}
?>
