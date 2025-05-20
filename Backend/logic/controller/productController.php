<?php
// === productController.php ===
// Steuert alle Produkt-bezogenen Aktionen (Abrufen, Hinzufügen, Aktualisieren, Löschen)

// Deaktiviert Warnungen wie "undefined index", um saubere JSON-Antworten zu ermöglichen
error_reporting(E_ALL & ~E_NOTICE);

// Startet die Session, falls noch keine existiert
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lädt das Produktmodell, das mit der Datenbank kommuniziert
require_once __DIR__ . '/../model/productModel.php';

class ProductController {
    private $model;

    public function __construct() {
        // Erstellt eine Instanz des Modells für Produktzugriffe
        $this->model = new ProductModel();
    }

    public function handle($action) {
        switch ($action) {

            // Gibt alle Produkte als JSON zurück
            case 'getProducts':
                echo json_encode($this->model->getAllProducts());
                break;

            // Gibt ein bestimmtes Produkt anhand seiner ID zurück
            case 'getProduct':
                $id = $_GET['id'] ?? null;
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Produkt-ID fehlt.']);
                    return;
                }
                $product = $this->model->getProductById($id);
                echo $product ? json_encode($product) : json_encode(['success' => false, 'message' => 'Produkt nicht gefunden.']);
                break;

            // Fügt ein neues Produkt hinzu (nur Admins, nur via POST)
            case 'addProduct':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    http_response_code(405);
                    echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
                    return;
                }
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Keine Admin-Berechtigung.']);
                    return;
                }
                echo json_encode($this->model->addProduct($_POST, $_FILES));
                break;

            // Aktualisiert ein bestehendes Produkt (nur Admins, nur via POST)
            case 'updateProduct':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    http_response_code(405);
                    echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
                    return;
                }
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Keine Admin-Berechtigung.']);
                    return;
                }
                $id = $_GET['id'] ?? null;
                $data = json_decode(file_get_contents("php://input"), true);
                if (!$id || !$data) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Ungültige Daten.']);
                    return;
                }
                echo json_encode($this->model->updateProduct($id, $data));
                break;

            // Löscht ein Produkt anhand der ID (nur Admins, nur via DELETE)
            case 'deleteProduct':
                if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                    http_response_code(405);
                    echo json_encode(['success' => false, 'message' => 'Nur DELETE erlaubt.']);
                    return;
                }
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Keine Admin-Berechtigung.']);
                    return;
                }
                $id = $_GET['id'] ?? null;
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Produkt-ID fehlt.']);
                    return;
                }
                echo json_encode($this->model->deleteProduct($id));
                break;

            // Fehlerfall: Wenn keine gültige Aktion erkannt wird
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Ungültige Produkt-Aktion.']);
        }
    }
}
