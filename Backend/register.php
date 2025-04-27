<?php
// 
// Snackery – Benutzerregistrierung (register.php)
// Dieses PHP-Skript verarbeitet Registrierungen und speichert neue Benutzer in der Datenbank.
// 

// 1. Verbindung zur Datenbank aufbauen
require_once __DIR__ . '/config/dbaccess.php';

// 2. POST-Daten aus Formular abholen
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

// 3. Passwörter vergleichen
if ($password !== $password2) {
    // ❌ Fehler: Passwörter stimmen nicht überein → Zurück zur Registrierung
    header("Location: ../Frontend/sites/register.html?error=passwort");
    exit;
}

// 4. Passwort sicher verschlüsseln
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// 5. Verbindung zur Datenbank aufbauen
$db = new DbAccess();
$conn = $db->connect();

// 6. Prüfen, ob Benutzername oder E-Mail bereits vergeben sind
$stmtCheck = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmtCheck->execute([$username, $email]);
$existingUser = $stmtCheck->fetch();

if ($existingUser) {
    // ❌ Benutzer existiert bereits → Zurück zur Registrierung
    header("Location: ../Frontend/sites/register.html?error=existiert");
    exit;
}

// 7. Neuen Benutzer in die Datenbank einfügen
$sql = "INSERT INTO users (firstname, lastname, username, email, street, housenumber, postalcode, city, password, role)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$role = "user"; // Standardrolle: user

// 8. Benutzer speichern
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
    // ✅ Erfolgreiche Registrierung → Weiterleitung zur Login-Seite mit Erfolgsmeldung
    header("Location: ../Frontend/sites/login.html?registered=true");
    exit;
} else {
    // ❌ Fehler beim Speichern → Zurück zur Registrierung
    header("Location: ../Frontend/sites/register.html?error=datenbank");
    exit;
}
?>
