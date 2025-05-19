document.addEventListener("DOMContentLoaded", () => {
    // >> Basis-Pfad für Weiterleitungen im Frontend (z. B. bei Logout oder Login-Redirects)
    const basePath = "/Snackery/Frontend/sites/";

    // >> Seiten, die auch für nicht eingeloggte Nutzer sichtbar sein dürfen
    const excludedPages = [
        "login.html",
        "register.html",
        "hilfe.html",
        "impressum.html",
        "index.html",
        "products.html",
        "product_detail.html",
        "cart.html"
    ];

    // >> Aktuelle Seite aus der URL extrahieren (z. B. "profil.html")
    const currentPage = window.location.pathname.split("/").pop();

    // >> Falls nur der Ordner aufgerufen wurde (z. B. .../sites/), Session-Check abbrechen
    if (currentPage === "") {
        console.warn("📂 Nur Verzeichnis geöffnet – Session-Check übersprungen.");
        return;
    }

    // >> Sessionstatus beim Backend abfragen (Login-Status & Rolle)
    fetch("/Snackery/Backend/sessionStatus.php", {
            method: "GET",
            credentials: "include"
        })
        .then(response => response.json())
        .then(data => {
            const isLoggedIn = data.loggedIn;
            const role = data.role;

            // >> Nicht eingeloggt → Weiterleitung zu Login, außer auf erlaubten Seiten
            if (!isLoggedIn) {
                if (!excludedPages.includes(currentPage)) {
                    console.warn("❌ Nicht eingeloggt – Weiterleitung zu Login.");
                    window.location.href = basePath + "login.html";
                    return;
                }
            } else {
                // >> Wenn bereits eingeloggt: Login- und Registrierungsseiten blockieren
                if (["login.html", "register.html"].includes(currentPage)) {
                    window.location.href = role === "admin" ?
                        basePath + "admin.html" :
                        basePath + "profil.html";
                    return;
                }
            }

            // >> Menü-Links im Header dynamisch je nach Login-Status anzeigen/verstecken
            const loginLink = document.getElementById("loginLink");
            const registerLink = document.getElementById("registerLink");
            const profileLink = document.getElementById("profileLink");
            const myOrdersLink = document.getElementById("myOrdersLink");
            const myOrdersBtn = document.getElementById("myOrdersButton");
            const adminLink = document.getElementById("adminLink");
            const logoutBtn = document.getElementById("logoutBtn");

            if (isLoggedIn) {
                if (loginLink) loginLink.style.display = "none";
                if (registerLink) registerLink.style.display = "none";
                if (profileLink) profileLink.style.display = "inline";
                if (myOrdersLink) myOrdersLink.style.display = "inline";
                if (myOrdersBtn) myOrdersBtn.style.display = "inline-block";
                if (logoutBtn) logoutBtn.style.display = "inline";
                if (adminLink && role === "admin") adminLink.style.display = "inline";
            } else {
                if (loginLink) loginLink.style.display = "inline";
                if (registerLink) registerLink.style.display = "inline";
                if (profileLink) profileLink.style.display = "none";
                if (myOrdersLink) myOrdersLink.style.display = "none";
                if (myOrdersBtn) myOrdersBtn.style.display = "none";
                if (logoutBtn) logoutBtn.style.display = "none";
                if (adminLink) adminLink.style.display = "none";
            }
        })
        .catch(error => {
            // >> Falls Sessionprüfung fehlschlägt: Weiterleitung zu Login (außer auf erlaubten Seiten)
            console.error("⚠️ Fehler beim Session-Check:", error);
            if (!["index.html", "hilfe.html", "impressum.html", "products.html", "product_detail.html", "cart.html"].includes(currentPage)) {
                window.location.href = basePath + "login.html";
            }
        });

    // >> Logout-Funktion: Bei Klick auf Logout-Link → fetch an Backend und Weiterleitung zur Startseite
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
                        window.location.href = basePath + "index.html";
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