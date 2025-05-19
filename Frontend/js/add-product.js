document.addEventListener("DOMContentLoaded", () => {
    // ===========================================
    // ⛔ Adminzugang prüfen (Zugriffsschutz)
    // ===========================================
    // Sobald die Seite geladen ist, prüfen wir per fetch,
    // ob der eingeloggte Nutzer ein Admin ist.
    // Falls nicht → automatische Weiterleitung zur Login-Seite.
    fetch("/Snackery/Backend/logic/requestHandler.php?action=getProfile", {
            credentials: "include" // wichtig, damit Cookies/Sessions mitgesendet werden
        })
        .then(res => res.json())
        .then(user => {
            if (!user || user.role !== "admin") {
                window.location.href = "login.html"; // Weiterleitung, wenn kein Admin
            }
        })
        .catch(() => window.location.href = "login.html"); // Fallback bei Serverfehler

    // ===========================================
    // 📦 Produktformular absenden (Produkt hinzufügen)
    // ===========================================
    const form = document.getElementById("productForm");
    const message = document.getElementById("message");

    if (form) {
        form.addEventListener("submit", function(e) {
            e.preventDefault(); // Verhindert klassisches Neuladen der Seite

            const formData = new FormData(form); // Daten aus Formular sammeln inkl. Datei

            fetch("/Snackery/Backend/logic/requestHandler.php?action=addProduct", {
                    method: "POST",
                    body: formData,
                    credentials: "include" // Session-Cookie mitsenden
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // ✅ Erfolgsmeldung anzeigen
                        message.textContent = "✅ Produkt erfolgreich gespeichert!";
                        message.style.color = "green";
                        form.reset(); // Formular nach erfolgreichem Speichern leeren
                    } else {
                        // ❌ Fehlermeldung ausgeben
                        message.textContent = "❌ Fehler: " + (data.message || "Produkt konnte nicht gespeichert werden.");
                        message.style.color = "red";
                    }
                })
                .catch(err => {
                    // Fehler beim Server oder Netzwerk
                    console.error("Fehler:", err);
                    message.textContent = "❌ Serverfehler – bitte später erneut versuchen.";
                    message.style.color = "red";
                });
        });
    }

    // ===========================================
    // Logout-Funktion aktivieren
    // ===========================================
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function(e) {
            e.preventDefault(); // Kein Seiten-Reload

            fetch("/Snackery/Backend/logout.php", {
                    credentials: "include"
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Nach erfolgreichem Logout zurück zur Startseite
                        window.location.href = "index.html";
                    } else {
                        alert("❌ Fehler beim Logout.");
                    }
                })
                .catch(() => {
                    alert("❌ Serverfehler beim Logout.");
                });
        });
    }
});