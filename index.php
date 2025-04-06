<?php
// Session starten, um Login-Status zu erkennen
session_start();
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snackery – Internationale Snacks</title>

    <!-- Google Fonts für modernes Aussehen -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">

    <!-- Verknüpfung zum externen Stylesheet -->
    <link rel="stylesheet" href="Frontend/res/css/style.css">
</head>

<body>

    <!-- Header mit klickbarem Logo und Navigation -->
    <header>
        <div class="logo">
            <a href="index.php">
                <img src="Frontend/res/img/snackery-logo.jpg" alt="Snackery Logo">
            </a>
        </div>
        <nav>
            <ul>
                <!-- Navigationspunkte, angepasst je nach Login-Status -->
                <li><a href="index.php">Startseite</a></li>
                <li><a href="#">Produkte</a></li>
                <li><a href="Frontend/sites/cart.php">Warenkorb</a></li>

                <?php if (isset($_SESSION['user'])) : ?>
                    <!-- Wenn der Benutzer eingeloggt ist -->
                    <li><a href="Frontend/sites/profil.php">Mein Profil</a></li>
                    <li><a href="Backend/logout.php">Logout</a></li>
                <?php else : ?>
                    <!-- Wenn der Benutzer NICHT eingeloggt ist -->
                    <li><a href="Frontend/sites/login.php">Login</a></li>
                    <li><a href="Frontend/sites/register.php">Registrieren</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Willkommensbereich -->
    <section class="hero">
        <h1>Willkommen bei Snackery</h1>
        <p>Entdecke internationale Süßigkeiten & Snacks aus aller Welt.</p>
        <a href="#" class="btn">Jetzt entdecken</a>
    </section>

    <!-- Footer -->
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
