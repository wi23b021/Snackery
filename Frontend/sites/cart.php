<?php
// Session starten, um Login-Status zu erkennen
session_start();
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Warenkorb – Snackery</title>
    <!-- Einbindung des zentralen CSS-Stylesheets -->
    <link rel="stylesheet" href="../res/css/style.css">
</head>

<body>

<!-- ================= HEADER ================= -->
<header>
    <div class="logo">
        <!-- Klickbares Logo führt zur Startseite -->
        <a href="/Snackery/index.php">
            <img src="../res/img/snackery-logo.jpg" alt="Snackery Logo">
        </a>
    </div>

    <!-- Navigation, die sich je nach Login-Status ändert -->
    <nav>
        <ul>
            <li><a href="/Snackery/index.php">Startseite</a></li>
            <li><a href="#">Produkte</a></li>
            <li><a href="cart.php">Warenkorb</a></li>

            <?php if (isset($_SESSION['user'])): ?>
                <!-- Wenn Benutzer eingeloggt ist -->
                <li><a href="profil.php">Mein Profil</a></li>
                <li><a href="../../Backend/logout.php">Logout</a></li>
            <?php else: ?>
                <!-- Wenn niemand eingeloggt ist -->
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Registrieren</a></li>

            <?php endif; ?>
        </ul>
    </nav>
</header>

<!-- ================= MAIN-INHALT ================= -->
<main style="text-align: center; padding: 50px;">
    <h1>Dein Warenkorb</h1>
    <p>Hier erscheinen deine ausgewählten Snacks.</p>
</main>

<!-- ================= FOOTER ================= -->
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
