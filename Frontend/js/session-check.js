// Snackery – Session-Check für geschützte Seiten
// Prüft beim Laden der Seite, ob ein Benutzer eingeloggt ist

document.addEventListener("DOMContentLoaded", () => {
    fetch("../../Backend/logic/sessionStatus.php", {
            credentials: "include" // 🔒 Cookie mit Session-ID mitsenden
        })
        .then(response => response.json())
        .then(data => {
            if (!data.loggedIn) {
                console.log("❌ Nicht eingeloggt – Weiterleitung zum Login.");
                window.location.href = "../sites/login.html"; // 🔁 Weiterleitung bei fehlender Session
            } else {
                console.log("✅ Benutzer ist eingeloggt.");
            }
        })
        .catch(error => {
            console.error("⚠️ Fehler beim Session-Check:", error);
            window.location.href = "../sites/login.html"; // 🔁 Vorsichtshalber weiterleiten
        });
});