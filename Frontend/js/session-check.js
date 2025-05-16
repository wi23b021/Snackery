document.addEventListener("DOMContentLoaded", () => {
    const basePath = "/Snackery/Frontend/sites/";
    const excludedPages = ["login.html", "register.html", "hilfe.html", "impressum.html", "index.html"];
    const currentPage = window.location.pathname.split("/").pop();

    // ⚠️ Wenn nur der Ordner aufgerufen wurde (z. B. /sites/), abbrechen
    if (currentPage === "") {
        console.warn("📂 Nur Verzeichnis geöffnet – Session-Check übersprungen.");
        return;
    }

    // ==== SESSION STATUS PRÜFEN ====
    fetch("/Snackery/Backend/sessionStatus.php", {
            method: "GET",
            credentials: "include"
        })
        .then(response => response.json())
        .then(data => {
            const isLoggedIn = data.loggedIn;
            const role = data.role;

            if (!isLoggedIn) {
                // ❌ Nicht eingeloggt → Weiterleitung (außer auf erlaubten Seiten)
                if (!excludedPages.includes(currentPage)) {
                    console.warn("❌ Nicht eingeloggt – Weiterleitung zu Login.");
                    window.location.href = basePath + "login.html";
                    return;
                }
            } else {
                // ✅ Bereits eingeloggt → Login/Register blockieren
                if (["login.html", "register.html"].includes(currentPage)) {
                    window.location.href = role === "admin" ?
                        basePath + "admin.html" :
                        basePath + "profil.html";
                    return;
                }
            }

            // === Sichtbarkeit der Menüeinträge dynamisch setzen ===
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
            console.error("⚠️ Fehler beim Session-Check:", error);
            if (!["index.html", "hilfe.html", "impressum.html"].includes(currentPage)) {
                window.location.href = basePath + "login.html";
            }
        });

    // ==== LOGOUT-BUTTON HANDLING ====
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