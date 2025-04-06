<?php
// Session starten, um Benutzerstatus zu prüfen
session_start();

// Wenn kein Benutzer eingeloggt ist oder Rolle nicht 'admin' ist, Zugriff verweigern
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: /Snackery/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Adminbereich – Snackery</title>
    <link rel="stylesheet" href="../res/css/style.css">
</head>
<body>

<!-- Kopfbereich mit Navigation -->
<header>
    <div class="logo">
        <a href="/Snackery/index.php">
            <img src="../res/img/snackery-logo.jpg" alt="Snackery Logo">
        </a>
    </div>

    <nav>
        <ul>
            <li><a href="/Snackery/index.php">Startseite</a></li>
            <li><a href="admin.php">Adminbereich</a></li>
            <li><a href="profil.php">Mein Profil</a></li>
            <li><a href="../../Backend/logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<!-- Hauptinhalt mit Links zu Admin-Funktionen -->
<main>
    <div class="profile-container">
        <h2>Willkommen im Adminbereich</h2>
        <p>Hier kannst du deinen Shop verwalten:</p>

        <!-- Verlinkung zu den Admin-Funktionen -->
        <ul style="list-style: none; padding: 0; text-align: center;">
            <li style="margin-bottom: 20px;">
                <a class="btn" href="admin_products.php">Produkte verwalten</a>
            </li>
            <li style="margin-bottom: 20px;">
                <a class="btn" href="admin_orders.php">Bestellungen verwalten</a>
            </li>
            <li style="margin-bottom: 20px;">
                <a class="btn" href="admin_users.php">Benutzer verwalten</a>
            </li>
        </ul>
    </div>
</main>

<!-- Fußzeile -->
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
