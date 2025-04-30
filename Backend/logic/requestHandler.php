<?php
// Snackery – Verbesserter zentraler Request-Handler für Frontend-AJAX

// Startet die PHP-Session (wichtig für Login-Status, Benutzerrollen etc.)
session_start();

// === CORS-Header (für Zugriff vom Frontend bei Entwicklung auf localhost) ===
header("Access-Control-Allow-Origin: http://localhost"); // Nur localhost erlaubt
header("Access-Control-Allow-Credentials: true");        // Cookies/Sessions mitübertragen
header("Content-Type: application/json");                // Antwort im JSON-Format
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS"); // Erlaubte Methoden
header("Access-Control-Allow-Headers: Content-Type");    // Erlaubter Header

// Falls ein Preflight-Request kommt (z.B. bei POST mit Headern), sofort antworten
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// === Datenbank- und Produktfunktionen einbinden ===
require_once __DIR__ . '/../config/dataHandler.php';
require_once __DIR__ . '/../config/dbaccess.php';

// Datenbankverbindung aufbauen
$db = new DbAccess();
$conn = $db->connect();

// Aktion aus der URL holen (z.B. ?action=getProducts)
$action = $_GET['action'] ?? '';

// ======== 1) Alle Produkte abrufen ==========
if ($action === 'getProducts') {
    $products = getAllProducts(); // Holt alle Produkte über dataHandler.php
    echo json_encode($products);  // Gibt sie im JSON-Format zurück
    exit;
}

// ======== 2) Einzelnes Produkt abrufen ==========
if ($action === 'getProduct') {
    $productId = $_GET['id'] ?? null;
    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Produkt-ID fehlt.']);
        exit;
    }

    // Hole das Produkt aus der Datenbank
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    echo $product ? json_encode($product) : json_encode(['success' => false, 'message' => 'Produkt nicht gefunden.']);
    exit;
}

// ======== 3) Produkt aktualisieren (nur Admins) ==========
if ($action === 'updateProduct') {
    // Admin-Check
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Zugriff verweigert']);
        exit;
    }

    // Daten aus der URL und dem Request-Body holen
    $productId = $_GET['id'] ?? null;
    $data = json_decode(file_get_contents("php://input"), true);

    // Prüfen ob alle Pflichtdaten vorhanden sind
    if (!$productId || !$data || !isset($data['name'], $data['price'], $data['category'])) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Daten.']);
        exit;
    }

    // Update-Query ausführen
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category = ? WHERE id = ?");
    $stmt->execute([$data['name'], $data['price'], $data['category'], $productId]);

    echo json_encode(['success' => true, 'message' => 'Produkt aktualisiert.']);
    exit;
}

// ======== 4) Produkt hinzufügen (nur Admins) ==========
if ($action === 'addProduct') {
    // Nur Admins dürfen Produkte hinzufügen
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Nur Admins dürfen Produkte hinzufügen']);
        exit;
    }

    // Nur POST-Anfragen erlaubt
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
        exit;
    }

    // Pflichtfelder prüfen
    $required = ['name', 'description', 'price', 'category', 'origin_country', 'stock'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Pflichtfeld '$field' fehlt."]);
            exit;
        }
    }

    // Bild prüfen
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Bild-Upload fehlgeschlagen.']);
        exit;
    }

    // Bild speichern
    $imageName = basename($_FILES['image']['name']);
    $uploadDir = '../productpictures/';
    $uploadPath = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        echo json_encode(['success' => false, 'message' => 'Bild konnte nicht gespeichert werden.']);
        exit;
    }

    // Produkt in die Datenbank einfügen
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

// ======== 5) Benutzerprofil abrufen ==========
if ($action === 'getProfile') {
    if (!isset($_SESSION['user'])) {
        http_response_code(401); // Nicht eingeloggt
        echo json_encode(['error' => 'Nicht eingeloggt']);
        exit;
    }

    // Gibt gespeicherte Nutzerdaten aus der Session zurück
    $user = $_SESSION['user'];
    echo json_encode([
        'id' => $user['id'],
        'username' => $user['username'],
        'firstname' => $user['firstname'],
        'lastname' => $user['lastname'],
        'email' => $user['email'],
        'street' => $user['street'],
        'housenumber' => $user['housenumber'],
        'postalcode' => $user['postalcode'],
        'city' => $user['city'],
        'iban' => $user['iban'] ?? '',
        'cardnumber' => $user['cardnumber'] ?? '',
        'bankname' => $user['bankname'] ?? '',
        'role' => $user['role']
    ]);
    exit;
}

// ======== 6) Existenz von Benutzern prüfen (für Registrierung) ==========
if ($action === 'checkUserExists') {
    $username = $_GET['username'] ?? '';
    $email = $_GET['email'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $user = $stmt->fetch();

    echo json_encode([
        'usernameTaken' => $user && $user['username'] === $username,
        'emailTaken' => $user && $user['email'] === $email
    ]);
    exit;
}

// ======== 7) Benutzerliste abrufen (nur Admins) ==========
if ($action === 'getUsers') {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Nicht erlaubt']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT id, username, email, firstname, lastname, role FROM users");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Fehler beim Laden der Benutzer: ' . $e->getMessage()]);
    }
    exit;
}

// ======== 8) Bestellung absenden ==========
if ($action === 'placeOrder') {
    // Nur POST erlaubt
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
        exit;
    }

    // Nur eingeloggte Nutzer dürfen bestellen
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
        exit;
    }

    // Daten aus dem POST-Request holen
    $street = $_POST['street'] ?? '';
    $housenumber = $_POST['housenumber'] ?? '';
    $postalcode = $_POST['postalcode'] ?? '';
    $city = $_POST['city'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? '';
    $cartJson = $_POST['cart'] ?? '';

    // Pflichtfelder prüfen
    if (!$street || !$housenumber || !$postalcode || !$city || !$paymentMethod || !$cartJson) {
        echo json_encode(['success' => false, 'message' => 'Fehlende Bestelldaten.']);
        exit;
    }

    // Warenkorb-Daten in Array umwandeln
    $cart = json_decode($cartJson, true);
    if (!is_array($cart) || count($cart) === 0) {
        echo json_encode(['success' => false, 'message' => 'Warenkorb ist leer oder ungültig.']);
        exit;
    }

    $userId = $_SESSION['user']['id'];

    try {
        // Bestellung speichern
        $stmt = $conn->prepare("INSERT INTO orders (user_id, street, housenumber, postalcode, city, payment_method, order_date, status)
                                VALUES (?, ?, ?, ?, ?, ?, NOW(), 'offen')");
        $stmt->execute([$userId, $street, $housenumber, $postalcode, $city, $paymentMethod]);

        $orderId = $conn->lastInsertId(); // ID der Bestellung

        // Bestellpositionen speichern
        foreach ($cart as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price)
                                    VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
        }

        echo json_encode(['success' => true, 'message' => 'Bestellung gespeichert.', 'orderId' => $orderId]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Fehler beim Speichern: ' . $e->getMessage()]);
    }
    exit;
}

// ======== Wenn keine bekannte Aktion erkannt wurde ==========
http_response_code(400);
echo json_encode(["error" => "Ungültige Anfrage – keine passende Aktion gefunden."]);
exit;
