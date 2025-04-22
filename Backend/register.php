<?php
// Verbindung zur Datenbank
require_once __DIR__ . '/config/dbaccess.php';

// ✅ POST-Daten aus dem Formular abrufen
$firstname    = $_POST['firstname'];
$lastname     = $_POST['lastname'];
$username     = $_POST['username'];
$email        = $_POST['email'];
$street       = $_POST['street'];
$housenumber  = $_POST['housenumber'];
$postalcode   = $_POST['postalcode'];
$city         = $_POST['city'];
$password     = $_POST['password'];
$password2    = $_POST['password_repeat'];

// ❌ Überprüfung: Stimmen die Passwörter überein?
if ($password !== $password2) {
    // Wenn nein, leite zurück mit Hinweis auf Passwortproblem
    header("Location: ../Frontend/sites/register.html?error=passwort");
    exit;
}

// ✅ Passwort verschlüsseln (BCRYPT)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// 🔗 Verbindung zur Datenbank herstellen
$db = new DbAccess();
$conn = $db->connect();

// 🔎 Prüfen, ob der Benutzername oder die E-Mail bereits vergeben sind
$stmtCheck = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmtCheck->execute([$username, $email]);
$existingUser = $stmtCheck->fetch();

if ($existingUser) {
    // ❌ Benutzername oder E-Mail existiert bereits → Zurück zur Registrierung
    header("Location: ../Frontend/sites/register.html?error=existiert");
    exit;
}

// ✅ Benutzer in die Datenbank einfügen
$sql = "INSERT INTO users (firstname, lastname, username, email, street, housenumber, postalcode, city, password, role)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$role = "user";

// Wenn erfolgreich gespeichert
if ($stmt->execute([
    $firstname,
    $lastname,
    $username,
    $email,
    $street,
    $housenumber,
    $postalcode,
    $city,
    $hashedPassword,
    $role
])) {
    // ✅ Erfolgreiche Registrierung → Weiterleitung zur Login-Seite mit Bestätigung
    header("Location: ../Frontend/sites/login.html?registered=true");
    exit;
} else {
    // ❌ Technischer Fehler beim Speichern
    header("Location: ../Frontend/sites/register.html?error=datenbank");
    exit;
}
?>
