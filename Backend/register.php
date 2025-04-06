<?php
//  Verbindung zur Datenbank aufbauen (dbaccess.php enthält die Klasse DbAccess)
require_once __DIR__ . '/config/dbaccess.php'; // Absolute Pfadangabe macht unabhängig von Ausführungsort

// 🧾 Formularwerte per POST abholen (müssen mit den "name"-Attributen im HTML übereinstimmen!)
$firstname    = $_POST['firstname'];       // Vorname
$lastname     = $_POST['lastname'];        // Nachname
$username     = $_POST['username'];        // Benutzername
$email        = $_POST['email'];           // E-Mail-Adresse
$street       = $_POST['street'];          // Straße
$housenumber  = $_POST['housenumber'];     // Hausnummer
$postalcode   = $_POST['postalcode'];      // PLZ
$city         = $_POST['city'];            // Ort
$password     = $_POST['password'];        // Passwort
$password2    = $_POST['password_repeat']; // Passwort-Wiederholung

//  Prüfen, ob beide Passwörter übereinstimmen
if ($password !== $password2) {
    // ❌ Fehlermeldung, wenn die Passwörter nicht identisch sind
    die("❌ Die Passwörter stimmen nicht überein.");
}

//  Passwort sicher verschlüsseln mit BCRYPT (empfohlene Methode)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

//  Neue Datenbankverbindung aufbauen
$db = new DbAccess();
$conn = $db->connect();

//  SQL-Anweisung zum Einfügen eines neuen Users (Prepared Statement verhindert SQL-Injection)
$sql = "INSERT INTO users (firstname, lastname, username, email, street, housenumber, postalcode, city, password, role)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

//  Statement vorbereiten
$stmt = $conn->prepare($sql);

//  Standardrolle für neue Benutzer: 'user'
$role = "user";

// ✅ Ausführung des SQL-Statements mit den übergebenen Werten
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
    //  Erfolgreiche Registrierung – Weiterleitung zu index.php im Hauptverzeichnis
    echo "
    <!DOCTYPE html>
    <html lang='de'>
    <head>
        <meta charset='UTF-8'>
        <meta http-equiv='refresh' content='3;url=../index.php'>
        <title>Registrierung erfolgreich</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #fffaf4;
                padding: 100px;
                text-align: center;
            }
            .message {
                font-size: 1.5em;
                color: green;
            }
        </style>
    </head>
    <body>
        <p class='message'>✅ Du hast dich erfolgreich registriert!<br>Du wirst in wenigen Sekunden zur Startseite weitergeleitet...</p>
    </body>
    </html>";
} else {
    // ❌ Fehler beim Registrieren (z. B. doppelter Username oder leeres Feld)
    echo "❌ Fehler bei der Registrierung.";
}
?>
