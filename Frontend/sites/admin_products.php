<?php
// Diese Datei dient als Einstiegspunkt zum Verwalten von Produkten im Adminbereich
// Sie zeigt eine Liste aller Produkte mit Optionen zum Hinzufügen, Bearbeiten und Löschen

session_start();

// Nur eingeloggte Admins dürfen auf diese Seite zugreifen
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: /Snackery/index.php");
    exit;
}

// Verbindung zur Datenbank herstellen
require_once '../../Backend/config/dbaccess.php';
$db = new DbAccess();
$conn = $db->connect();

// Alle Produkte aus der Datenbank abrufen
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Produkte verwalten</title>
    <link rel="stylesheet" href="../res/css/style.css">
</head>
<body>

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
            <li><a href="../../Backend/logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="profile-container">
        <h2>Produkte verwalten</h2>

        <a href="#" class="btn">+ Neues Produkt hinzufügen</a>

        <table style="width:100%; margin-top: 30px; border-collapse: collapse;">
            <tr style="background-color: #f5f5f5;">
                <th>ID</th>
                <th>Name</th>
                <th>Preis</th>
                <th>Kategorie</th>
                <th>Aktionen</th>
            </tr>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= $product['price'] ?> €</td>
                    <td><?= htmlspecialchars($product['category']) ?></td>
                    <td>
                        <a href="#" class="btn">Bearbeiten</a>
                        <a href="#" class="btn">Löschen</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>

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
