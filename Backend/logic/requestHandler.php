<?php
//
// Snackery – Zentrale Datei für alle AJAX-Anfragen vom Frontend
// Hier werden z.B. Produkte geladen, Profile verwaltet, Bestellungen abgeschickt.
//

// === SESSION STARTEN ===
// Damit wir auf eingeloggte Benutzer zugreifen können
session_start();

// === HEADER FÜR ANFRAGEN EINSTELLEN ===
// Erlaubt Anfragen vom lokalen Frontend
header("Access-Control-Allow-Origin: http://localhost"); // Ursprungsseite erlauben
header("Access-Control-Allow-Credentials: true");         // Cookies erlauben (z.B. Session)
header("Content-Type: application/json");                  // Antwortformat ist JSON
header("Access-Control-Allow-Methods: POST, GET");         // Nur POST und GET Anfragen zulassen
header("Access-Control-Allow-Headers: Content-Type");      // Erlaubt bestimmte Header bei Anfragen

// === HELFERDATEIEN EINBINDEN ===
// Verbindung zur Datenbank und Hilfsfunktionen (z.B. Produkte abrufen)
require_once __DIR__ . '/../config/dataHandler.php';
require_once __DIR__ . '/../config/dbaccess.php';

// === VERBINDUNG ZUR DATENBANK HERSTELLEN ===
$db = new DbAccess(); // Neues Objekt der Klasse DbAccess
$conn = $db->connect(); // Verbindung aufbauen (PDO-Objekt)

// === AKTION AUSLESEN (getProducts, getProfile, placeOrder usw.) ===
$action = $_GET['action'] ?? ''; // Falls keine Aktion angegeben ist, leerer String

// ====================================
// 1) ALLE PRODUKTE LADEN
// ====================================
if ($action === 'getProducts') {
    // Produkte holen über dataHandler.php
    $products = getAllProducts(); 
    echo json_encode($products); // Als JSON an das Frontend schicken
    exit;
}

// ====================================
// 2) EINZELNES PRODUKT LADEN
// ====================================
if ($action === 'getProduct') {
    $productId = $_GET['id'] ?? null; // ID aus der URL holen

    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Produkt-ID fehlt.']);
        exit;
    }

    // Produkt aus der Datenbank abfragen
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode($product); // Produkt zurückgeben
    } else {
        echo json_encode(['success' => false, 'message' => 'Produkt nicht gefunden.']);
    }
    exit;
}

// ====================================
// 3) PRODUKT AKTUALISIEREN
// ====================================
if ($action === 'updateProduct') {
    $productId = $_GET['id'] ?? null;

    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Produkt-ID fehlt.']);
        exit;
    }

    // Neue Produktdaten auslesen (Body-Daten als JSON)
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['name'], $data['price'], $data['category'])) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Daten.']);
        exit;
    }

    // Update-Query ausführen
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

// ====================================
// 4) NEUES PRODUKT HINZUFÜGEN
// ====================================
if ($action === 'addProduct') {
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

    // Bild-Upload prüfen
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

    // Produkt speichern
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

// ====================================
// 5) BENUTZER-PROFIL LADEN
// ====================================
if ($action === 'getProfile') {
    // Nur wenn eingeloggt
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Nicht eingeloggt']);
        exit;
    }

    $user = $_SESSION['user']; // Session-Daten

    // Profildaten als JSON schicken
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

// ====================================
// 6) BENUTZER-EXISTENZ PRÜFEN (Register-Validierung)
// ====================================
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

// ====================================
// 7) ALLE BENUTZER LADEN (Adminbereich)
// ====================================
if ($action === 'getUsers') {
    try {
        $stmt = $conn->prepare("SELECT id, username, email, firstname, lastname, role FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($users); // Kein success-Wrapper nötig
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Fehler beim Laden der Benutzer: ' . $e->getMessage()]);
    }
    exit;
}

// ====================================
// 8) NEU: BESTELLUNG ABSCHICKEN (Checkout)
// ====================================
if ($action === 'placeOrder') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
        exit;
    }

    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
        exit;
    }

    // Adressdaten und Warenkorb auslesen
    $street = $_POST['street'] ?? '';
    $housenumber = $_POST['housenumber'] ?? '';
    $postalcode = $_POST['postalcode'] ?? '';
    $city = $_POST['city'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? '';
    $cartJson = $_POST['cart'] ?? '';

    if (!$street || !$housenumber || !$postalcode || !$city || !$paymentMethod || !$cartJson) {
        echo json_encode(['success' => false, 'message' => 'Fehlende Bestelldaten.']);
        exit;
    }

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

        $orderId = $conn->lastInsertId(); // ID der neuen Bestellung

        echo json_encode(['success' => true, 'message' => 'Bestellung gespeichert.', 'orderId' => $orderId]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Fehler beim Speichern: ' . $e->getMessage()]);
        exit;
    }
}

// ====================================
// 9) UNBEKANNTE AKTION
// ====================================
http_response_code(400);
echo json_encode(["error" => "Ungültige Anfrage – keine passende Aktion gefunden."]);
exit;
?>
