<?php
// === login.php ===
// Diese Datei wird per fetch() angesprochen, um Nutzer einzuloggen (als API)

// === Session-Konfiguration setzen ===
session_set_cookie_params([
    'lifetime' => 0,               // Session läuft nur bis zum Schließen des Browsers
    'path' => '/Snackery',         // Gültig für den ganzen Projektpfad
    'domain' => 'localhost',       // Für lokale Entwicklung
    'secure' => false,             // false für localhost (kein HTTPS)
    'httponly' => true,            // JavaScript kann nicht auf die Session zugreifen
    'samesite' => 'Lax'            // Grundlegender Schutz gegen CSRF
]);
session_start(); // Session aktivieren

// === CORS- und Content-Type-Header setzen ===
header("Access-Control-Allow-Origin: http://localhost");  // Frontend-Zugriff erlauben
header("Access-Control-Allow-Credentials: true");         // Cookies (Session) erlauben
header("Content-Type: application/json");                 // Antwort im JSON-Format

// === Nur POST-Anfragen akzeptieren ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Nur POST erlaubt."]);
    exit;
}

// === JSON-Daten auslesen und prüfen ===
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Benutzername und Passwort erforderlich."]);
    exit;
}

// === Eingaben bereinigen ===
$usernameOrEmail = trim($input['username']);
$password = trim($input['password']);

// === DB-Verbindung herstellen ===
require_once __DIR__ . '/config/dbaccess.php';
$db = new DbAccess();
$conn = $db->connect();

// === Benutzer in der Datenbank suchen (per Username oder E-Mail) ===
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->execute([$usernameOrEmail, $usernameOrEmail]);
$user = $stmt->fetch();

// === Passwort prüfen und Inaktiv-Status berücksichtigen ===
if ($user && password_verify($password, $user['password'])) {
    // Wenn Benutzer auf inaktiv gesetzt wurde
    if ((int)$user['active'] === 0) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "❌ Dein Konto ist deaktiviert. Bitte wende dich an den Support."
        ]);
        exit;
    }

    // === Session-Daten setzen ===
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user'] = [
        "id" => $user['id'],
        "username" => $user['username'],
        "role" => $user['role']
    ];

    // === Erfolgreicher Login: Rolle bestimmt Weiterleitung ===
    echo json_encode([
        "success" => true,
        "message" => "Login erfolgreich!",
        "role" => $user['role'],
        "redirect" => $user['role'] === 'admin' ? "admin.html" : "profil.html"
    ]);
    exit;

} else {
    // === Fehlerhafter Login (Benutzer nicht gefunden oder Passwort falsch) ===
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "❌ Benutzername oder Passwort ist falsch."
    ]);
    exit;
}
?>
