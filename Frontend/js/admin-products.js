document.addEventListener("DOMContentLoaded", () => {

    // =========================================
    // 🔐 Admin-Zugriffsprüfung beim Seitenaufruf
    // =========================================
    // Holt das eingeloggte Nutzerprofil. Wenn keine Session vorhanden ist oder der User kein Admin ist,
    // wird automatisch zur Login-Seite weitergeleitet.
    fetch("/Snackery/Backend/logic/requestHandler.php?action=getProfile", {
            credentials: "include"
        })
        .then(res => res.json())
        .then(user => {
            if (!user || user.role !== "admin") {
                window.location.href = "login.html";
            }
        })
        .catch(() => window.location.href = "login.html");

    // =========================================
    // 📦 Produkte vom Backend laden (für Admin)
    // =========================================
    fetch("/Snackery/Backend/logic/requestHandler.php?action=getProducts", {
            credentials: "include"
        })
        .then(res => res.json())
        .then(products => {
            const container = document.getElementById("productTableContainer");

            // Wenn keine Produkte vorhanden sind, Fehlermeldung anzeigen
            if (!Array.isArray(products) || products.length === 0) {
                container.innerHTML = "<p>❌ Keine Produkte gefunden.</p>";
                return;
            }

            // Produkte werden in HTML-Tabellenzeilen umgewandelt
            const rows = products.map(product => `
            <tr>
                <td>${product.id}</td>
                <td>${product.name}</td>
                <td>${parseFloat(product.price).toFixed(2).replace(".", ",")} €</td>
                <td>${product.category}</td>
                <td>
                    <a class="btn" href="edit_product.html?id=${product.id}">Bearbeiten</a>
                    <button class="btn deleteBtn" data-id="${product.id}">🗑️ Löschen</button>
                </td>
            </tr>
        `).join("");

            // Tabelle im DOM einfügen
            container.innerHTML = `
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead style="background-color: #f5f5f5;">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Preis</th>
                        <th>Kategorie</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        `;

            // =========================================
            // 🗑️ Funktion zum Löschen eines Produkts
            // =========================================
            document.querySelectorAll(".deleteBtn").forEach(btn => {
                btn.addEventListener("click", () => {
                    const productId = btn.dataset.id;

                    // Bestätigung vor dem Löschen
                    if (confirm("❗ Willst du dieses Produkt wirklich löschen?")) {
                        fetch(`/Snackery/Backend/logic/requestHandler.php?action=deleteProduct&id=${productId}`, {
                                method: "DELETE",
                                credentials: "include"
                            })
                            .then(res => res.json())
                            .then(result => {
                                if (result.success) {
                                    alert("✅ Produkt gelöscht.");
                                    window.location.reload(); // Seite neu laden nach erfolgreichem Löschen
                                } else {
                                    alert("❌ Fehler beim Löschen: " + (result.message || "Unbekannter Fehler."));
                                }
                            })
                            .catch(err => {
                                console.error("❌ Serverfehler:", err);
                                alert("❌ Serverfehler beim Löschen.");
                            });
                    }
                });
            });
        })
        .catch(err => {
            // Fehler beim Abrufen der Produkte (z. B. Server nicht erreichbar)
            console.error("❌ Fehler beim Laden der Produkte:", err);
            document.getElementById("productTableContainer").innerHTML =
                "<p>❌ Fehler beim Laden der Produkte.</p>";
        });

    // =========================================
    // 🔓 Logout-Funktion mit fetch
    // =========================================
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
                        window.location.href = "index.html"; // Weiterleitung zur Startseite
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