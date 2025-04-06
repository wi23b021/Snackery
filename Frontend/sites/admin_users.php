<?php
// Session starten, um zu überprüfen, ob ein Admin eingeloggt ist
session_start();

// Prüfen, ob der Benutzer eingeloggt ist und die Rolle "admin" hat
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Wenn kein Admin, weiterleiten zur Login-Seite
    header("Location: ../sites/login.php");
    exit;
}

// Datenbankverbindung einbinden
require_once '../Backend/config/dbaccess.php';
$db = new DbAccess();
$conn = $db->connect();

// Wenn ein Benutzer gelöscht werden soll (per GET-Parameter)
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];

    // Sicherheitsabfrage: Admin darf sich selbst nicht löschen
    if ($_SESSION['user']['id'] != $deleteId) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$deleteId]);
        header("Location: admin_users.php"); // Seite neu laden nach dem Löschen
        exit;
    }
}

// Wenn die Rolle eines Benutzers geändert werden soll (per POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$_POST['role'], $_POST['user_id']]);
    header("Location: admin_users.php"); // Seite neu laden nach Update
    exit;
}

// Alle Benutzer aus der Datenbank abrufen
$stmt = $conn->prepare("SELECT * FROM users ORDER BY lastname ASC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Admin – Benutzerverwaltung</title>
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
        <nav>
    <ul>
        <li><a href="/Snackery/index.php">Startseite</a></li>
        <li><a href="#">Produkte</a></li>
        <li><a href="/Snackery/Frontend/sites/cart.php">Warenkorb</a></li>

        <?php if (isset($_SESSION['user'])): ?>
            <li><a href="/Snackery/Frontend/sites/profil.php">Mein Profil</a></li>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <li><a href="/Snackery/Frontend/admin/admin_dashboard.php">Adminbereich</a></li>
            <?php endif; ?>
            <li><a href="/Snackery/Backend/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="/Snackery/Frontend/sites/login.php">Login</a></li>
            <li><a href="/Snackery/Frontend/sites/register.php">Registrieren</a></li>
        <?php endif; ?>
    </ul>
</nav>

        </ul>
    </nav>
</header>

<!-- Hauptinhalt: Benutzerübersicht -->
<main style="padding: 40px;">
    <h2>Benutzer verwalten</h2>

    <?php if (count($users) === 0): ?>
        <p>Es sind keine Benutzer vorhanden.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width:100%; background-color: white;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Benutzername</th>
                    <th>E-Mail</th>
                    <th>Vorname</th>
                    <th>Nachname</th>
                    <th>Rolle</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['firstname']) ?></td>
                        <td><?= htmlspecialchars($user['lastname']) ?></td>
                        <td>
                            <!-- Formular zur Rollenänderung -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <select name="role">
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <button type="submit">Speichern</button>
                            </form>
                        </td>
                        <td>
                            <!-- Benutzer löschen, aber nicht sich selbst -->
                            <?php if ($_SESSION['user']['id'] != $user['id']): ?>
                                <a href="admin_users.php?delete=<?= $user['id'] ?>" onclick="return confirm('Diesen Benutzer wirklich löschen?')">Löschen</a>
                            <?php else: ?>
                                Eigener Account
                            <?php endif; ?>
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
