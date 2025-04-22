<?php
// ==============================================
// Request-Handler für das Frontend (AJAX-Zugriffe)
// ==============================================

session_start(); // Session starten

// Header für CORS & JSON-Ausgabe
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

// DB-Zugriff laden
require_once __DIR__ . '/../config/dataHandler.php';
require_once __DIR__ . '/../config/dbaccess.php';

// DB-Verbindung
$db = new DbAccess();
$conn = $db->connect();

// Aktion auslesen
$action = $_GET['action'] ?? '';

// ==========================
// ALLE PRODUKTE LADEN
// ==========================
if ($action === 'getProducts') {
    $products = getAllProducts();
    echo json_encode($products);
    exit;
}

// ==========================
// EINZELNES PRODUKT LADEN
// ==========================
if ($action === 'getProduct') {
    $productId = $_GET['id'] ?? null;

    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Produkt-ID fehlt.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode($product);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produkt nicht gefunden.']);
    }
    exit;
}

// ==========================
// PRODUKT AKTUALISIEREN
// ==========================
if ($action === 'updateProduct') {
    $productId = $_GET['id'] ?? null;

    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Produkt-ID fehlt.']);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['name'], $data['price'], $data['category'])) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Daten.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category = ? WHERE id = ?");
    $stmt->execute([
        $data['name'],
        $data['price'],
        $data['category'],
        $productId
    ]);

    echo json_encode(['success' => true, 'message' => 'Produkt aktualisiert.']);
    exit;
}

// ==========================
// NEUES PRODUKT HINZUFÜGEN
// ==========================
if ($action === 'addProduct') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
        exit;
    }

    $required = ['name', 'description', 'price', 'category', 'origin_country', 'stock'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Pflichtfeld '$field' fehlt."]);
            exit;
        }
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Bild-Upload fehlgeschlagen.']);
        exit;
    }

    $imageName = basename($_FILES['image']['name']);
    $uploadDir = '../productpictures/';
    $uploadPath = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        echo json_encode(['success' => false, 'message' => 'Bild konnte nicht gespeichert werden.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, category, origin_country, stock, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $imageName,
        $_POST['category'],
        $_POST['origin_country'],
        $_POST['stock']
    ]);

    echo json_encode(['success' => true, 'message' => 'Produkt gespeichert.']);
    exit;
}

// ==========================
// PROFIL LADEN
// ==========================
if ($action === 'getProfile') {
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Nicht eingeloggt']);
        exit;
    }

    $user = $_SESSION['user'];

    echo json_encode([
        'id'           => $user['id'],
        'username'     => $user['username'],
        'firstname'    => $user['firstname'],
        'lastname'     => $user['lastname'],
        'email'        => $user['email'],
        'street'       => $user['street'],
        'housenumber'  => $user['housenumber'],
        'postalcode'   => $user['postalcode'],
        'city'         => $user['city'],
        'iban'         => $user['iban'] ?? '',
        'cardnumber'   => $user['cardnumber'] ?? '',
        'bankname'     => $user['bankname'] ?? '',
        'role'         => $user['role']
    ]);
    exit;
}

// ==========================
// USER EXISTENZ PRÜFEN
// ==========================
if ($action === 'checkUserExists') {
    $username = $_GET['username'] ?? '';
    $email = $_GET['email'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'usernameTaken' => $user && $user['username'] === $username,
        'emailTaken'    => $user && $user['email'] === $email
    ]);
    exit;
}

// ==========================
// BENUTZER VERWALTEN (KORRIGIERT!)
// ==========================
if ($action === 'getUsers') {
    try {
        $stmt = $conn->prepare("SELECT id, username, email, firstname, lastname, role FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ⚠️ Kein success-Wrapper! Frontend erwartet direkt ein Array!
        echo json_encode($users);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Fehler beim Laden der Benutzer: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================
// UNBEKANNTE AKTION
// ==========================
http_response_code(400);
echo json_encode(["error" => "Ungültige Anfrage – keine passende Aktion gefunden."]);
exit;
