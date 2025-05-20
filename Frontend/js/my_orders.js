document.addEventListener("DOMContentLoaded", () => {
    // ================================
    // 🔐 Prüfen, ob der User eingeloggt ist
    // Wenn nicht, automatische Weiterleitung zum Login
    // ================================
    fetch("/Snackery/Backend/logic/requestHandler.php?action=getProfile", {
            credentials: "include"
        })
        .then(res => res.json())
        .then(user => {
            // Wenn kein Benutzerobjekt zurückkommt → umleiten
            if (!user || !user.id) {
                window.location.href = "login.html";
            } else {
                // Wenn gültig → eigene Bestellungen laden
                loadOrders();
            }
        })
        .catch(() => {
            // Bei Fehler in der Anfrage auch umleiten
            window.location.href = "login.html";
        });

    // ================================
    // 🔓 Logout-Funktion mit fetch
    // ================================
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function(e) {
            e.preventDefault(); // Linkverhalten verhindern

            // Logout-Fetch-Request ans Backend
            fetch("/Snackery/Backend/logout.php", {
                    credentials: "include"
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Nach erfolgreichem Logout zur Startseite
                        window.location.href = "index.html";
                    } else {
                        alert("❌ Logout fehlgeschlagen.");
                    }
                })
                .catch(() => {
                    alert("❌ Serverfehler beim Logout.");
                });
        });
    }
});

// ================================
// 📦 Funktion zum Laden der eigenen Bestellungen
// ================================
function loadOrders() {
    fetch("/Snackery/Backend/logic/requestHandler.php?action=getMyOrders", {
            credentials: "include"
        })
        .then(async res => {
            // 🔍 Debug-Ausgabe für rohe Serverantwort (z. B. bei Syntaxfehlern hilfreich)
            const text = await res.text();
            console.log("🔍 Serverantwort (roh):", text);

            let data;
            try {
                // JSON parsen (manuell für bessere Fehlersuche)
                data = JSON.parse(text);
            } catch (e) {
                throw new Error("❌ Fehler beim Parsen der Antwort: " + e.message);
            }

            const container = document.getElementById("orderList");

            // Wenn keine Bestellungen vorhanden sind
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = "<p>❌ Du hast noch keine Bestellungen aufgegeben.</p>";
                return;
            }

            // HTML-Tabellenzeilen für alle Bestellungen erstellen
            const rows = data.map(order => `
                <tr>
                    <td>${order.id}</td>
                    <td>${order.order_date}</td>
                    <td>${order.status}</td>
                    <td>${parseFloat(order.total_price).toFixed(2)} €</td>
                    <td><button class="invoiceBtn" data-order-id="${order.id}">🧾 Rechnung</button></td>
                </tr>
            `).join("");

            // Tabelle in DOM einsetzen
            container.innerHTML = `
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Bestellnummer</th>
                            <th>Datum</th>
                            <th>Status</th>
                            <th>Gesamtsumme</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            `;

            // Nachträglich Event-Listener für alle Rechnungsbuttons setzen
            attachInvoiceListeners();
        })
        .catch(error => {
            // Fehlerbehandlung bei Problemen mit der API
            console.error("❌ Fehler beim Laden der Bestellungen:", error);
            document.getElementById("orderList").innerHTML =
                "<p>❌ Fehler beim Laden der Bestellungen.</p>";
        });
}