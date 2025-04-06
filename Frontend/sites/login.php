<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Login – Snackery</title>

    <!-- Einbindung des gemeinsamen CSS-Stylesheets für das Design -->
    <link rel="stylesheet" href="../res/css/style.css">
</head>

<body>

    <!-- Kopfbereich mit Logo und Navigation -->
    <header>
        <!-- Klickbares Logo führt zur Startseite -->
        <div class="logo">
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

    <!-- Hauptbereich für den Login -->
    <main>
        <!-- Container für das Loginformular, zentriert und mit Styling -->
        <div class="register-container">
            <h2>Login</h2>

            <!-- Formular zur Anmeldung des Benutzers -->
            <!-- Die Eingaben werden per POST an das PHP-Backend übermittelt -->
            <form action="/Snackery/Backend/login.php" method="POST">

                <!-- Eingabe für Benutzername oder E-Mail-Adresse -->
                <input type="text" name="username" placeholder="E-Mail oder Benutzername" required>

                <!-- Eingabe für das Passwort -->
                <input type="password" name="password" placeholder="Passwort" required>

                <!-- Optionale Checkbox zum Merken des Logins -->
                <label style="display: block; text-align: left; margin-top: 10px;">
                    <input type="checkbox" name="remember"> Eingeloggt bleiben
                </label>

                <!-- Button zum Absenden des Login-Formulars -->
                <button type="submit">Einloggen</button>
            </form>
        </div>
    </main>

    <!-- Fußbereich mit Urheberrecht -->
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
