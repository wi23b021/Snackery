<?php
// Session starten, um eingeloggten Benutzer zu erkennen
session_start();

// Wenn der Benutzer nicht eingeloggt ist, wird er zur Loginseite weitergeleitet
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Benutzerinformationen aus der Session abrufen
$user = $_SESSION['user'];

// Datenbankverbindung einbinden
require_once '../../Backend/config/dbaccess.php';

// Leere Nachricht für Feedback vorbereiten
$message = "";

// Wenn das Formular abgeschickt wurde (per POST-Methode)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Persönliche Nutzerdaten auslesen
    $firstname = $_POST['firstname'];             // Vorname
    $lastname = $_POST['lastname'];               // Nachname
    $email = $_POST['email'];                     // E-Mail-Adresse
    $street = $_POST['street'];                   // Straße
    $housenumber = $_POST['housenumber'];         // Hausnummer
    $postalcode = $_POST['postalcode'];           // Postleitzahl
    $city = $_POST['city'];                       // Stadt / Wohnort

    // Zahlungsdaten auslesen
    $iban = $_POST['iban'];                       // IBAN
    $cardnumber = $_POST['cardnumber'];           // Kartennummer
    $bankname = $_POST['bankname'];               // Name der Bank

    // Aktuelles Passwort zum Schutz der Änderung
    $currentPassword = $_POST['current_password'];

    // Neue Passwörter für Änderung (optional)
    $newPassword = $_POST['new_password'];
    $newPasswordRepeat = $_POST['new_password_repeat'];

    // Verbindung zur Datenbank herstellen
    $db = new DbAccess();
    $conn = $db->connect();

    // Aktuelles Passwort aus der Datenbank abrufen
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $dbUser = $stmt->fetch();

    // Überprüfung des eingegebenen aktuellen Passworts
    if (!password_verify($currentPassword, $dbUser['password'])) {
        $message = "Das eingegebene aktuelle Passwort ist falsch.";
    } else {
        // Prüfen ob neue Passwörter eingegeben wurden und ob sie übereinstimmen
        if (!empty($newPassword) || !empty($newPasswordRepeat)) {
            if ($newPassword !== $newPasswordRepeat) {
                $message = "Die neuen Passwörter stimmen nicht überein.";
            } else {
                // Neues Passwort verschlüsseln
                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update aller Daten inklusive neuem Passwort
                $sql = "UPDATE users SET firstname=?, lastname=?, email=?, street=?, housenumber=?, postalcode=?, city=?, iban=?, cardnumber=?, bankname=?, password=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$firstname, $lastname, $email, $street, $housenumber, $postalcode, $city, $iban, $cardnumber, $bankname, $newHashedPassword, $user['id']]);
            }
        } else {
            // Update ohne Passwortänderung
            $sql = "UPDATE users SET firstname=?, lastname=?, email=?, street=?, housenumber=?, postalcode=?, city=?, iban=?, cardnumber=?, bankname=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$firstname, $lastname, $email, $street, $housenumber, $postalcode, $city, $iban, $cardnumber, $bankname, $user['id']]);
        }

        // Erfolgsmeldung vorbereiten
        $message = "Deine Daten wurden erfolgreich aktualisiert.";

        // Aktualisierte Benutzerdaten neu in die Session schreiben
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $_SESSION['user'] = $stmt->fetch();
        $user = $_SESSION['user'];
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Profil bearbeiten – Snackery</title>
    <link rel="stylesheet" href="../res/css/style.css">
</head>
<body>

<!-- Kopfbereich mit Logo und Navigation -->
<header>
    <div class="logo">
        <a href="/Snackery/index.php">
            <img src="../res/img/snackery-logo.jpg" alt="Snackery Logo">
        </a>
    </div>
    <nav>
        <ul>
            <li><a href="/Snackery/index.php">Startseite</a></li>
            <li><a href="#">Produkte</a></li>
            <li><a href="cart.php">Warenkorb</a></li>
            <li><a href="profil.php">Mein Profil</a></li>
            <li><a href="../../Backend/logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<!-- Hauptbereich für das Profilformular -->
<main>
    <div class="profile-container">
        <h2>Profil bearbeiten</h2>

        <!-- Feedback-Meldung für Erfolg oder Fehler -->
        <?php if (!empty($message)) : ?>
            <p class="<?= str_starts_with($message, 'Deine') ? 'success-message' : 'error-message' ?>">
                <?= $message ?>
            </p>
        <?php endif; ?>

        <!-- Formular zur Bearbeitung der Profildaten -->
        <form method="POST">
            <input type="text" name="firstname" placeholder="Vorname" value="<?= htmlspecialchars($user['firstname']) ?>" required>
            <input type="text" name="lastname" placeholder="Nachname" value="<?= htmlspecialchars($user['lastname']) ?>" required>
            <input type="email" name="email" placeholder="E-Mail" value="<?= htmlspecialchars($user['email']) ?>" required>
            <input type="text" name="street" placeholder="Straße" value="<?= htmlspecialchars($user['street']) ?>" required>
            <input type="text" name="housenumber" placeholder="Hausnummer" value="<?= htmlspecialchars($user['housenumber']) ?>" required>
            <input type="text" name="postalcode" placeholder="PLZ" value="<?= htmlspecialchars($user['postalcode']) ?>" required>
            <input type="text" name="city" placeholder="Ort" value="<?= htmlspecialchars($user['city']) ?>" required>

            <!-- Zahlungsdaten -->
            <input type="text" name="iban" placeholder="IBAN" value="<?= htmlspecialchars($user['iban'] ?? '') ?>">
            <input type="text" name="cardnumber" placeholder="Kartennummer" value="<?= htmlspecialchars($user['cardnumber'] ?? '') ?>">
            <input type="text" name="bankname" placeholder="Bankname" value="<?= htmlspecialchars($user['bankname'] ?? '') ?>">

            <!-- Passwortfelder -->
            <input type="password" name="current_password" placeholder="Aktuelles Passwort eingeben" required>
            <input type="password" name="new_password" placeholder="Neues Passwort (optional)">
            <input type="password" name="new_password_repeat" placeholder="Neues Passwort wiederholen">

            <button type="submit">Profil aktualisieren</button>
        </form>
    </div>
</main>

<!-- Fußbereich mit Links -->
<footer>
    <div class="footer-content">
        <p>&copy; 2025 Snackery</p>
        <div class="footer-links">
            <a href="/Snackery/Frontend/sites/impressum.html">Impressum</a>
            <a href="/Snackery/Frontend/sites/hilfe.html">Hilfe</a>
        </div>
    </div>
</footer>

</body>
</html>