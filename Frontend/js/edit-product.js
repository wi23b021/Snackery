document.addEventListener("DOMContentLoaded", () => {

    // ============================================
    // 🔓 Logout-Link: Bei Klick wird Session gelöscht und zurück zur Startseite geleitet
    // ============================================
    const logoutLink = document.getElementById("logoutBtn");

    logoutLink.addEventListener("click", function(e) {
        e.preventDefault();

        fetch("/Snackery/Backend/logout.php", {
                credentials: "include"
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "index.html";
                } else {
                    alert("❌ Fehler beim Logout.");
                }
            })
            .catch(() => {
                alert("❌ Serverfehler beim Logout.");
            });
    });

    // ============================================
    // 🔍 Produkt-ID aus der URL holen
    // → z. B. edit_product.html?id=3
    // ============================================
    const productId = new URLSearchParams(window.location.search).get("id");

    if (!productId) {
        alert("❌ Keine Produkt-ID gefunden.");
        window.location.href = "admin_products.html";
    }

    // ============================================
    // 📦 Produktdaten vom Server laden
    // und Formularfelder mit den aktuellen Werten befüllen
    // ============================================
    fetch(`/Snackery/Backend/logic/requestHandler.php?action=getProduct&id=${productId}`, {
            credentials: "include"
        })
        .then(res => {
            if (!res.ok) throw new Error("Serverantwort nicht OK");
            return res.json();
        })
        .then(product => {
            if (!product || !product.id) {
                alert("❌ Produkt nicht gefunden.");
                window.location.href = "admin_products.html";
                return;
            }

            document.getElementById("name").value = product.name;
            document.getElementById("price").value = product.price;
            document.getElementById("category").value = product.category;
        })
        .catch(error => {
            console.error("Fehler beim Laden des Produkts:", error);
            alert("❌ Fehler beim Laden des Produkts.");
            window.location.href = "admin_products.html";
        });

    // ============================================
    // 💾 Formular-Submit: Änderungen werden gespeichert
    // und per fetch() an den Server gesendet
    // ============================================
    document.getElementById("editProductForm").addEventListener("submit", function(e) {
        e.preventDefault();

        const updatedProduct = {
            name: document.getElementById("name").value.trim(),
            price: parseFloat(document.getElementById("price").value),
            category: document.getElementById("category").value.trim()
        };

        fetch(`/Snackery/Backend/logic/requestHandler.php?action=updateProduct&id=${productId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(updatedProduct),
                credentials: "include"
            })
            .then(response => {
                if (!response.ok) throw new Error("Serverfehler bei updateProduct");
                return response.json();
            })
            .then(result => {
                const feedback = document.getElementById("feedback");
                feedback.textContent = result.success ?
                    "✅ Produkt erfolgreich gespeichert!" :
                    "❌ " + (result.message || "Fehler beim Speichern.");
                feedback.style.color = result.success ? "green" : "red";

                if (result.success) {
                    // Nach erfolgreichem Speichern automatische Weiterleitung
                    setTimeout(() => window.location.href = "admin_products.html", 2000);
                }
            })
            .catch(error => {
                console.error("❌ Serverfehler:", error);
                const feedback = document.getElementById("feedback");
                feedback.textContent = "❌ Fehler beim Senden.";
                feedback.style.color = "red";
            });
    });
});