document.addEventListener("DOMContentLoaded", () => {
    let userId = null; // ✅ Nutzer-ID global speichern

    // === Beim Laden: Nutzerprofil vom Server holen ===
    fetch("/Snackery/Backend/logic/requestHandler.php?action=getProfile", {
            credentials: "include"
        })
        .then(res => res.json())
        .then(user => {
            // Wenn kein Nutzer eingeloggt ist → zur Loginseite
            if (!user || !user.id) {
                window.location.href = "login.html";
                return;
            }

            userId = user.id; // ✅ ID speichern für späteres Absenden

            // Eingabefelder automatisch mit Profilwerten befüllen
            for (const field in user) {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) input.value = user[field];
            }

            // Admin-Link einblenden, falls Rolle "admin"
            if (user.role === "admin") {
                document.getElementById("adminLink").style.display = "inline";
            }

            // Link zu "Meine Bestellungen" immer anzeigen, wenn eingeloggt
            document.getElementById("myOrdersLink").style.display = "inline";
        })
        .catch(() => {
            alert("Fehler beim Laden des Profils");
            window.location.href = "login.html";
        });

    // === Formular für Profildaten absenden ===
    document.getElementById("profileForm").addEventListener("submit", (e) => {
        e.preventDefault(); // Standard-Formularverhalten unterbinden

        const form = e.target;
        const formData = new FormData(form);
        const data = {};

        // Formulardaten in ein JSON-Objekt umwandeln
        formData.forEach((value, key) => {
            data[key] = value.trim();
        });

        // Nutzer-ID hinzufügen (zur Sicherheit auf Serverseite)
        data["id"] = userId;

        // Profildaten an Backend senden (korrekte Route für eingeloggte User)
        fetch("/Snackery/Backend/logic/requestHandler.php?action=updateProfile", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data),
                credentials: "include"
            })
            .then(response =>
                response.json().then(json => ({
                    status: response.status,
                    body: json
                }))
            )
            .then(result => {
                const msg = result.body.message || "Unbekannter Fehler.";
                if (result.status === 200) {
                    alert("✅ Profil erfolgreich gespeichert: " + msg);
                } else {
                    alert("❌ Fehler: " + msg);
                }
            })
            .catch(() => {
                alert("❌ Serverfehler beim Speichern.");
            });
    });

    // === Logout-Logik ===
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", (e) => {
            e.preventDefault();

            fetch("/Snackery/Backend/logout.php", {
                    credentials: "include"
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "index.html";
                    } else {
                        alert("Fehler beim Logout.");
                    }
                })
                .catch(() => {
                    alert("❌ Serverfehler beim Logout.");
                });
        });
    }
});