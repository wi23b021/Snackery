<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung – Snackery</title>

    <!-- Globale CSS-Datei für das Layout und das visuelle Design -->
    <link rel="stylesheet" href="../res/css/style.css">

    <!-- JavaScript-Datei für die clientseitige Validierung des Formulars -->
    <script defer src="../js/register-validation.js"></script>
</head>

<body>

    <!-- ========== HEADERBEREICH ========== -->
    <header>
        <div class="logo">
            <!-- Klickbares Logo führt zurück zur Startseite (Hauptverzeichnis) -->
            <a href="/Snackery/index.php">
                <img src="../res/img/snackery-logo.jpg" alt="Snackery Logo">
            </a>
        </div>

        <!-- Navigationsleiste mit Links zu verschiedenen Seiten -->
        <nav>
            <ul>
                <li><a href="/Snackery/index.php">Startseite</a></li>
                <li><a href="#">Produkte</a></li>
                <li><a href="cart.php">Warenkorb</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Registrieren</a></li>

            </ul>
        </nav>
    </header>

    <!-- ========== HAUPTBEREICH MIT REGISTRIERUNG ========== -->
    <main>
        <div class="register-container">
            <h2>Registrieren</h2>

            <!-- Formular zur Benutzerregistrierung -->
            <!-- Daten werden per POST an das PHP-Backend geschickt -->
            <!-- Der Pfad wurde angepasst: Backend liegt eine Ebene höher -->
            <form action="../../Backend/register.php" method="POST" id="registerForm">

                <!-- Vorname (Pflichtfeld) -->
                <input type="text" name="firstname" placeholder="Vorname" required>

                <!-- Nachname (Pflichtfeld) -->
                <input type="text" name="lastname" placeholder="Nachname" required>

                <!-- Benutzername (Pflichtfeld) -->
                <input type="text" name="username" placeholder="Benutzername" required>

                <!-- E-Mail-Adresse (Pflichtfeld) -->
                <input type="email" name="email" placeholder="E-Mail-Adresse" required>

                <!-- Straße (Pflichtfeld) -->
                <input type="text" name="street" placeholder="Straße" required>

                <!-- Hausnummer (Pflichtfeld) -->
                <input type="text" name="housenumber" placeholder="Hausnummer" required>

                <!-- Postleitzahl (Pflichtfeld) -->
                <input type="text" name="postalcode" placeholder="PLZ" required>

                <!-- Stadt / Wohnort (Pflichtfeld) -->
                <input type="text" name="city" placeholder="Ort" required>

                <!-- Passwort (Pflichtfeld) -->
                <input type="password" name="password" placeholder="Passwort" required>

                <!-- Passwortwiederholung (Pflichtfeld) -->
                <input type="password" name="password_repeat" placeholder="Passwort wiederholen" required>

                <!-- Absende-Button für die Registrierung -->
                <button type="submit">Registrieren</button>
            </form>
        </div>
    </main>

    <!-- ========== FUSSZEILE ========== -->
    <footer>
    <div class="footer-content">
        <p>&copy; 2025 Snackery</p>
        <div class="footer-links">
            <li><a href="/Snackery/Frontend/sites/impressum.html">Impressum</a><li>
            <a href="/Snackery/Frontend/sites/hilfe.html">Hilfe</a>
        </div>
    </div>
</footer>


</body>

</html>
