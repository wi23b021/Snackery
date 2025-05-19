<?php
// === orderController.php ===
// Zuständig für die Steuerung aller Bestell-bezogenen Aktionen (z. B. anzeigen, speichern, löschen)

// Verhindert, dass unnötige Warnungen (z. B. undefined index) im JSON erscheinen
error_reporting(E_ALL & ~E_NOTICE);

// Session starten, falls noch keine aktive besteht
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Datenmodell für Bestellungen einbinden
require_once __DIR__ . '/../model/orderModel.php';

class OrderController {
    private $model;

    public function __construct() {
        // Initialisiert das Modell für Datenbankzugriffe
        $this->model = new OrderModel();
    }

    public function handle($action) {
        switch ($action) {

            // Alle Bestellungen abrufen (nur Admins erlaubt)
            case 'getOrders':
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Keine Admin-Berechtigung.']);
                    return;
                }
                echo json_encode($this->model->getAllOrders());
                break;

            // Bestellstatus aktualisieren (nur Admins erlaubt)
            case 'editOrder':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Keine Admin-Berechtigung.']);
                    return;
                }
                $orderId = $_POST['order_id'] ?? null;
                $status = $_POST['status'] ?? null;
                echo json_encode($this->model->updateOrderStatus($orderId, $status));
                break;

            // Bestellung löschen (nur Admins erlaubt)
            case 'deleteOrder':
                if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' || !isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Keine Admin-Berechtigung.']);
                    return;
                }
                $orderId = $_GET['id'] ?? null;
                echo json_encode($this->model->deleteOrder($orderId));
                break;

            // Neue Bestellung speichern (nur eingeloggte Benutzer)
            case 'placeOrder':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
                    return;
                }
                echo json_encode($this->model->placeOrder($_SESSION['user']['id'], $_POST));
                break;

            // Eigene Bestellungen abrufen (nur für eingeloggte Benutzer)
            case 'getMyOrders':
                if (!isset($_SESSION['user'])) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
                    return;
                }
                echo json_encode($this->model->getOrdersByUser($_SESSION['user']['id']));
                break;

            // Rechnungsdaten einer Bestellung abrufen (nur für eingeloggte Benutzer)
            case 'getInvoiceData':
                $orderId = $_GET['orderId'] ?? null;
                if (!isset($_SESSION['user'])) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
                    return;
                }
                echo json_encode($this->model->getInvoiceData($_SESSION['user']['id'], $orderId));
                break;

            // Fallback für ungültige Aktionen
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Ungültige Bestellaktion.']);
        }
    }
}
