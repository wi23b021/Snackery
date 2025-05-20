<?php
// === userController.php ===
// 📂 Verantwortlich für alle Benutzeraktionen im System (Verteilerstelle zum Modell)

require_once __DIR__ . '/../model/userModel.php'; // 🔄 Modell mit DB-Funktionen einbinden

class UserController {
    private $model;

    public function __construct() {
        // 📌 Modell-Instanz erzeugen – Zugriff auf DB-Operationen für Benutzer
        $this->model = new UserModel();
    }

    public function handle($action) {
        // 🧭 Schaltet anhand der "action" (z. B. ?action=getUsers) in den richtigen Fall

        switch ($action) {

            case 'getProfile':
                // 🧾 Holt das Profil des aktuell eingeloggten Users
                if (session_status() === PHP_SESSION_NONE) session_start(); // Session starten
                if (!isset($_SESSION['user'])) {
                    http_response_code(401); // Nicht eingeloggt
                    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
                    return;
                }

                $userId = $_SESSION['user']['id']; // ID aus der Session holen
                echo json_encode($this->model->getProfile($userId)); // Antwort aus Modell
                break;

            case 'getUsers':
                // 🛡️ Adminfunktion: Gibt alle Benutzer zurück
                if (session_status() === PHP_SESSION_NONE) session_start();
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    http_response_code(403); // Kein Zugriff
                    echo json_encode(['success' => false, 'message' => 'Nur Admins dürfen Benutzer sehen.']);
                    return;
                }

                echo json_encode($this->model->getAllUsers());
                break;

            case 'updateUser':
                // 🛠️ Adminfunktion zum Bearbeiten eines beliebigen Benutzers
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    http_response_code(405); // Nur POST erlaubt
                    echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
                    return;
                }

                if (session_status() === PHP_SESSION_NONE) session_start();
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    http_response_code(403); // Kein Admin
                    echo json_encode(['success' => false, 'message' => 'Nur Admins dürfen Benutzer bearbeiten.']);
                    return;
                }

                $data = json_decode(file_get_contents("php://input"), true); // JSON-Daten empfangen
                echo json_encode($this->model->updateUser($data)); // An Modell übergeben
                break;

            case 'updateProfile':
                // ✏️ Userfunktion: Eigenes Profil bearbeiten (nur mit Passwortprüfung!)
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    http_response_code(405); // Nur POST erlaubt
                    echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
                    return;
                }

                if (session_status() === PHP_SESSION_NONE) session_start();
                if (!isset($_SESSION['user'])) {
                    http_response_code(401); // Nicht eingeloggt
                    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
                    return;
                }

                $currentUser = $_SESSION['user']; // Aktueller User aus Session
                $data = json_decode(file_get_contents("php://input"), true); // JSON-Daten holen

                // 🔐 Sicherheitsprüfung: Passwort muss korrekt eingegeben sein
                $db = new DbAccess();
                $conn = $db->connect();
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$currentUser['id']]);
                $userRecord = $stmt->fetch();

                if (!$userRecord || !password_verify($data['current_password'], $userRecord['password'])) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => '❌ Falsches aktuelles Passwort.']);
                    return;
                }

                // 🔄 Falls neues Passwort eingegeben → prüfen & übernehmen
                if (!empty($data['new_password'])) {
                    if ($data['new_password'] !== $data['new_password_repeat']) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => '❌ Neue Passwörter stimmen nicht überein.']);
                        return;
                    }
                    $data['password'] = $data['new_password']; // neues Passwort ins Feld schreiben
                }

                // 🧷 Sicherheitswerte setzen – User darf ID/Rolle nicht selbst bestimmen
                $data['id'] = $currentUser['id'];
                $data['role'] = $currentUser['role'];

                echo json_encode($this->model->updateUser($data)); // ✅ An Modell übergeben
                break;

            case 'toggleUserActive':
                // 🔄 Adminfunktion: Benutzer aktiv/inaktiv schalten
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    http_response_code(405);
                    echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
                    return;
                }

                if (session_status() === PHP_SESSION_NONE) session_start();
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Keine Admin-Berechtigung.']);
                    return;
                }

                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($this->model->toggleUserActive($data));
                break;

            case 'deleteUser':
                // 🗑️ Adminfunktion: Benutzer vollständig löschen
                if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                    http_response_code(405);
                    echo json_encode(['success' => false, 'message' => 'Nur DELETE erlaubt.']);
                    return;
                }

                if (session_status() === PHP_SESSION_NONE) session_start();
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Keine Admin-Berechtigung.']);
                    return;
                }

                $userId = $_GET['id'] ?? null;
                echo json_encode($this->model->deleteUser($userId));
                break;

            default:
                // 🛑 Fallback: Ungültige Aktion erkannt
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Ungültige Benutzer-Aktion.']);
        }
    }
}
