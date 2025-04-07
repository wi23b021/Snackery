<?php
// Session starten, um später Benutzerinformationen zu speichern
session_start();

// Datenbankverbindung laden
require_once __DIR__ . '/config/dbaccess.php'; // absolute Pfadangabe für mehr Sicherheit

// Formularwerte abholen (Benutzername oder E-Mail und Passwort)
$usernameOrEmail = $_POST['username'];
$password = $_POST['password'];

// Verbindung zur Datenbank aufbauen
$db = new DbAccess();
$conn = $db->connect();

// Benutzer anhand von Benutzername ODER E-Mail finden
$sql = "SELECT * FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$usernameOrEmail, $usernameOrEmail]);

// Benutzerdaten aus der Datenbank holen
$user = $stmt->fetch();

// Prüfen, ob der Benutzer existiert und das Passwort korrekt ist
if ($user && password_verify($password, $user['password'])) {
    
    // Login erfolgreich – Benutzerdaten in Session speichern
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user'] = $user; // gesamte Benutzerdaten für spätere Nutzung
    
    // Login merken über Cookie, gültig für 30 Tage
    if (isset($_POST['remember'])) {
        setcookie("username", $user['username'], time() + (86400 * 30), "/"); // 86400 = 1 Tag
    }

    // Weiterleitung je nach Benutzerrolle
    if ($user['role'] === 'admin') {
        header("Location: /Snackery/Frontend/sites/admin.php"); // Admin weiterleiten
    } else {
        header("Location: /Snackery/Frontend/sites/profil.php"); // Normale Nutzer weiterleiten
    }
    exit;

} else {
    // Falsche Zugangsdaten – Fehlermeldung als Alert anzeigen und zurück zum Login
    echo "<script>
            alert('❌ Benutzername oder Passwort ist falsch!');
            window.location.href = '../sites/login.php';
          </script>";
}
?>
