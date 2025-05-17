<?php
// ==============================================
// Snackery – Zentraler Request-Handler für AJAX-Anfragen
// ==============================================

session_start();

header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/dataHandler.php';
require_once __DIR__ . '/../config/dbaccess.php';

$db = new DbAccess();
$conn = $db->connect();

$action = $_GET['action'] ?? '';

// ========== PROFILDATEN HOLEN ==========
if ($action === 'getProfile') {
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Nicht eingeloggt."]);
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $stmt = $conn->prepare("SELECT id, username, email, firstname, lastname, street, housenumber, postalcode, city, role, iban, cardnumber, bankname FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    echo $user ? json_encode($user) : json_encode(["success" => false, "message" => "Benutzer nicht gefunden."]);
    exit;
}

// ========== PRODUKTE LADEN ==========
if ($action === 'getProducts') {
    echo json_encode(getAllProducts());
    exit;
}

// ========== EINZELNES PRODUKT ==========
if ($action === 'getProduct') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Produkt-ID fehlt.']);
        exit;
    }
    $product = getProductById($id);
    echo $product ? json_encode($product) : json_encode(['success' => false, 'message' => 'Produkt nicht gefunden.']);
    exit;
}

// ========== PRODUKT BEARBEITEN ==========
if ($action === 'updateProduct') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
        exit;
    }

    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Nur Admins dürfen Produkte bearbeiten.']);
        exit;
    }

    $id = $_GET['id'] ?? null;
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$id || !$data || !isset($data['name'], $data['price'], $data['category'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ungültige oder unvollständige Daten.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category = ? WHERE id = ?");
    $stmt->execute([$data['name'], $data['price'], $data['category'], $id]);

    echo json_encode(['success' => true, 'message' => 'Produkt aktualisiert.']);
    exit;
}

// ========== ALLE BENUTZER LADEN ==========
if ($action === 'getUsers') {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Nur Admins dürfen Benutzer sehen.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ========== BENUTZER AKTUALISIEREN ==========
if ($action === 'updateUser') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
        exit;
    }

    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Nur Admins dürfen Benutzer bearbeiten.']);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ungültige Benutzerdaten.']);
        exit;
    }

    $fields = ["username", "email", "password", "firstname", "lastname", "street", "housenumber", "postalcode", "city", "iban", "cardnumber", "bankname", "role"];
    $updates = [];
    $values = [];

    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $values[] = $field === "password" ? password_hash($data[$field], PASSWORD_DEFAULT) : $data[$field];
        }
    }

    if (count($updates) > 0) {
        $values[] = $data['id'];
        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);
    }

    echo json_encode(['success' => true, 'message' => 'Benutzerdaten aktualisiert.']);
    exit;
}
// ========== BENUTZER AKTIV / INAKTIV SETZEN ==========
if ($action === 'toggleUserActive') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
        exit;
    }

    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Keine Admin-Berechtigung.']);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id']) || !isset($data['active'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Benutzer-ID oder Aktiv-Status fehlt.']);
        exit;
    }

    $userId = $data['id'];
    $isActive = $data['active'] ? 1 : 0;

    try {
        $stmt = $conn->prepare("UPDATE users SET active = ? WHERE id = ?");
        $stmt->execute([$isActive, $userId]);
        echo json_encode(['success' => true, 'message' => 'Status erfolgreich geändert.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Fehler: ' . $e->getMessage()]);
    }
    exit;
}

// ========== BENUTZER LÖSCHEN ==========
if ($action === 'deleteUser') {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Nur DELETE erlaubt.']);
        exit;
    }

    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Keine Admin-Berechtigung.']);
        exit;
    }

    $userId = $_GET['id'] ?? null;
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Benutzer-ID fehlt.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true, 'message' => 'Benutzer erfolgreich gelöscht.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Fehler beim Löschen: ' . $e->getMessage()]);
    }
    exit;
}

// ========== BESTELLUNGEN LADEN (ADMIN) ==========
if ($action === 'getOrders') {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Nur Admins dürfen Bestellungen sehen.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM orders");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ========== BESTELLSTATUS BEARBEITEN ==========
if ($action === 'editOrder') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
        exit;
    }

    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Nur Admins dürfen Bestellungen bearbeiten.']);
        exit;
    }

    $orderId = $_POST['order_id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$orderId || !$status) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bestell-ID oder Status fehlt.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $orderId]);

    echo json_encode(['success' => true, 'message' => 'Status aktualisiert.']);
    exit;
}

// ========== BESTELLUNG LÖSCHEN ==========
if ($action === 'deleteOrder') {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Nur DELETE erlaubt.']);
        exit;
    }

    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Nur Admins dürfen Bestellungen löschen.']);
        exit;
    }

    $orderId = $_GET['id'] ?? null;
    if (!$orderId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bestell-ID fehlt.']);
        exit;
    }

    try {
        $conn->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
        $conn->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
        echo json_encode(['success' => true, 'message' => 'Bestellung gelöscht.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Fehler beim Löschen: ' . $e->getMessage()]);
    }
    exit;
}

// ========== BESTELLUNG ABSCHICKEN ==========
if ($action === 'placeOrder') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
        exit;
    }

    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
        exit;
    }

    $cart = json_decode($_POST['cart'] ?? '', true);
    if (!is_array($cart) || count($cart) === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ungültiger Warenkorb.']);
        exit;
    }

    $requiredFields = ['street', 'housenumber', 'postalcode', 'city', 'payment_method'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Feld '$field' fehlt."]);
            exit;
        }
    }

    $userId = $_SESSION['user']['id'];

    try {
        $stmt = $conn->prepare("INSERT INTO orders (user_id, street, housenumber, postalcode, city, payment_method, created_at, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'offen')");
        $stmt->execute([
            $userId,
            $_POST['street'],
            $_POST['housenumber'],
            $_POST['postalcode'],
            $_POST['city'],
            $_POST['payment_method']
        ]);

        $orderId = $conn->lastInsertId();

        foreach ($cart as $item) {
            $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)")
                ->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
        }

        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Bestellung erfolgreich gespeichert.', 'orderId' => $orderId]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Fehler: ' . $e->getMessage()]);
    }
    exit;
}

// ========== EIGENE BESTELLUNGEN LADEN ==========
if ($action === 'getMyOrders') {
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
        exit;
    }

    $userId = $_SESSION['user']['id'];

    $stmt = $conn->prepare("
        SELECT 
            o.id,
            o.created_at AS order_date,
            o.status,
            (
                SELECT SUM(oi.price * oi.quantity)
                FROM order_items oi
                WHERE oi.order_id = o.id
            ) AS total_price
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$userId]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
// ========== RECHNUNGSDATEN LADEN ==========
if ($action === 'getInvoiceData') {
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $orderId = $_GET['orderId'] ?? null;

    if (!$orderId || !is_numeric($orderId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ungültige Bestell-ID.']);
        exit;
    }

    try {
        // Nutzerinformationen
        $userStmt = $conn->prepare("SELECT firstname, lastname, street, housenumber, postalcode, city FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

        // Bestelldaten inkl. Besitzüberprüfung
        $orderStmt = $conn->prepare("SELECT created_at FROM orders WHERE id = ? AND user_id = ?");
        $orderStmt->execute([$orderId, $userId]);
        $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData || !$orderData) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Benutzer oder Bestellung nicht gefunden.']);
            exit;
        }

        // Bestellpositionen
        $itemsStmt = $conn->prepare("
            SELECT p.name, oi.quantity, oi.price
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Keine Bestellpositionen gefunden.']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'user' => $userData,
            'order' => $orderData,
            'items' => $items
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Serverfehler: ' . $e->getMessage()
        ]);
    }
    exit;
}

// ========== UNBEKANNTE AKTION ==========
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Ungültige Aktion.']);
exit;
