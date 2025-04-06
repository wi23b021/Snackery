<?php
// Session starten, um zu überprüfen, ob der Benutzer als Admin eingeloggt ist
session_start();

// Sicherstellen, dass ein Benutzer eingeloggt ist und die Rolle "admin" hat
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Wenn nicht, Weiterleitung zur Loginseite
    header("Location: ../sites/login.php");
    exit;
}

// Verbindung zur Datenbank aufbauen
require_once '../Backend/config/dbaccess.php';
$db = new DbAccess();
$conn = $db->connect();

// Alle Bestellungen aus der Datenbank abrufen (hier: Tabelle "orders")
$stmt = $conn->prepare("SELECT * FROM orders ORDER BY order_date DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Admin – Bestellungen verwalten</title>
    <link rel="stylesheet" href="../Frontend/res/css/style.css">
</head>
<body>

<!-- Kopfbereich mit Navigation -->
<header>
    <div class="logo">
        <a href="/Snackery/index.php">
            <img src="../Frontend/res/img/snackery-logo.jpg" alt="Snackery Logo">
        </a>
    </div>
    <nav>
        <ul>
            <li><a href="/Snackery/index.php">Startseite</a></li>
            <li><a href="admin_products.php">Produkte</a></li>
            <li><a href="admin_orders.php">Bestellungen</a></li>
            <li><a href="admin_users.php">Benutzer</a></li>
            <li><a href="../Backend/logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<!-- Hauptinhalt: Bestellübersicht -->
<main style="padding: 40px;">
    <h2>Bestellungen verwalten</h2>

    <?php if (count($orders) === 0): ?>
        <p>Es wurden noch keine Bestellungen aufgegeben.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width:100%; background-color: white;">
            <thead>
                <tr>
                    <th>Bestell-ID</th>
                    <th>Benutzer-ID</th>
                    <th>Datum</th>
                    <th>Status</th>
                    <th>Aktion</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['user_id']) ?></td>
                        <td><?= htmlspecialchars($order['order_date']) ?></td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td>
                            <!-- Button zur Bearbeitung oder z. B. Stornierung -->
                            <a href="edit_order.php?id=<?= $order['id'] ?>">Bearbeiten</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<!-- Fußbereich -->
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
