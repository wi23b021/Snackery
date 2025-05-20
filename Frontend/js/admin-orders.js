document.addEventListener("DOMContentLoaded", () => {

    // =======================================
    // 🔐 Admin-Zugriffsprüfung beim Laden der Seite
    // =======================================
    // Wenn der Nutzer kein Admin ist → Weiterleitung zur Login-Seite
    fetch("/Snackery/Backend/logic/requestHandler.php?action=getProfile", {
            credentials: "include"
        })
        .then(res => res.json())
        .then(user => {
            if (!user || user.role !== "admin") {
                window.location.href = "login.html";
            }
        })
        .catch(() => {
            // Bei Fehler (z. B. keine Session) → auch zur Login-Seite weiterleiten
            window.location.href = "login.html";
        });

    // =======================================
    // 📦 Bestellungen vom Server laden (nur für Admin)
    // =======================================
    fetch("/Snackery/Backend/logic/requestHandler.php?action=getOrders", {
            credentials: "include"
        })
        .then(res => res.json())
        .then(orders => {
            const container = document.getElementById("orderTableContainer");

            // Wenn keine Bestellungen vorhanden sind
            if (!Array.isArray(orders) || orders.length === 0) {
                container.innerHTML = "<p>❌ Es wurden noch keine Bestellungen aufgegeben.</p>";
                return;
            }

            // HTML-Tabelle mit allen Bestellungen dynamisch erzeugen
            const rows = orders.map(order => `
            <tr>
                <td>${order.id}</td>
                <td>${order.user_id}</td>
                <td>${order.order_date}</td>
                <td>
                    <select class="statusSelect" data-id="${order.id}">
                        <option value="offen" ${order.status === "offen" ? "selected" : ""}>offen</option>
                        <option value="in Bearbeitung" ${order.status === "in Bearbeitung" ? "selected" : ""}>in Bearbeitung</option>
                        <option value="auf dem Weg" ${order.status === "auf dem Weg" ? "selected" : ""}>auf dem Weg</option>
                        <option value="geliefert" ${order.status === "geliefert" ? "selected" : ""}>geliefert</option>
                    </select>
                </td>
                <td>
                    <button class="btn deleteOrderBtn" data-id="${order.id}">🗑️ Löschen</button>
                </td>
            </tr>
        `).join("");

            // Tabelle anzeigen
            container.innerHTML = `
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Bestell-ID</th>
                        <th>Benutzer-ID</th>
                        <th>Datum</th>
                        <th>Status</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        `;

            // =======================================
            // 🔄 Status einer Bestellung ändern
            // =======================================
            document.querySelectorAll(".statusSelect").forEach(select => {
                select.addEventListener("change", function() {
                    const orderId = this.dataset.id;
                    const newStatus = this.value;

                    fetch("/Snackery/Backend/logic/requestHandler.php?action=editOrder", {
                            method: "POST",
                            credentials: "include",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `order_id=${orderId}&status=${encodeURIComponent(newStatus)}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            alert(data.success ? "✅ Status aktualisiert." : "❌ Fehler beim Aktualisieren.");
                        })
                        .catch(() => alert("❌ Fehler beim Senden der Statusänderung."));
                });
            });

            // =======================================
            // 🗑️ Bestellung löschen (Admin)
            // =======================================
            document.querySelectorAll(".deleteOrderBtn").forEach(btn => {
                btn.addEventListener("click", function() {
                    const orderId = this.dataset.id;
                    if (confirm("❗ Bestellung wirklich löschen?")) {
                        fetch(`/Snackery/Backend/logic/requestHandler.php?action=deleteOrder&id=${orderId}`, {
                                method: "DELETE",
                                credentials: "include"
                            })
                            .then(res => res.json())
                            .then(data => {
                                alert(data.success ? "✅ Bestellung gelöscht." : "❌ Fehler beim Löschen.");
                                if (data.success) location.reload(); // Seite neu laden, um Liste zu aktualisieren
                            })
                            .catch(() => alert("❌ Fehler beim Löschen der Bestellung."));
                    }
                });
            });
        })
        .catch(error => {
            // Wenn etwas schiefläuft beim Laden der Bestellungen
            console.error("❌ Fehler beim Laden der Bestellungen:", error);
            document.getElementById("orderTableContainer").innerHTML =
                "<p>❌ Fehler beim Laden der Daten.</p>";
        });

    // =======================================
    // 🔐 Logout-Funktion
    // =======================================
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function(e) {
            e.preventDefault();
            fetch("/Snackery/Backend/logout.php", {
                    credentials: "include"
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "index.html"; // Zurück zur Startseite nach Logout
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