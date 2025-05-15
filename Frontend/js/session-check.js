document.addEventListener("DOMContentLoaded", () => {
    const excludedPages = ["login.html", "register.html"];
    const currentPage = window.location.pathname.split("/").pop();

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
                // ❌ Nicht eingeloggt → Weiterleitung (außer Login/Registrierung)
                if (!excludedPages.includes(currentPage)) {
                    console.warn("❌ Nicht eingeloggt – Weiterleitung erfolgt.");
                    window.location.href = "/Frontend/sites/login.html";
                }
            } else {
                console.log("✅ Eingeloggt als:", role);

                // ✅ Bereits eingeloggt → Login/Registrieren blockieren
                if (excludedPages.includes(currentPage)) {
                    if (role === "admin") {
                        window.location.href = "/Frontend/sites/admin.html";
                    } else {
                        window.location.href = "/Frontend/sites/profil.html";
                    }
                }

                // ✅ Sichtbarkeit dynamischer Menüeinträge anpassen
                const myOrdersLink = document.getElementById("myOrdersLink");
                const myOrdersBtn = document.getElementById("myOrdersButton");
                const adminLink = document.getElementById("adminLink");

                if (myOrdersLink) myOrdersLink.style.display = "inline";
                if (myOrdersBtn) myOrdersBtn.style.display = "inline-block";
                if (adminLink && role === "admin") adminLink.style.display = "inline";
            }
        })
        .catch(error => {
            console.error("⚠️ Fehler beim Session-Check:", error);
            window.location.href = "login.html";
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
                        window.location.href = "index.html"; // ✅ Zurück zur Startseite
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