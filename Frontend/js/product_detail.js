document.addEventListener("DOMContentLoaded", () => {
    // === URL-Parameter auslesen (Produkt-ID holen) ===
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get("id");

    // Wenn keine ID vorhanden ist → Fehlermeldung anzeigen
    if (!productId) {
        showError("❌ Kein Produkt ausgewählt.");
        return;
    }

    // === Produktdetails vom Backend abrufen ===
    fetch(`/Snackery/Backend/logic/requestHandler.php?action=getProduct&id=${productId}`, {
            credentials: "include"
        })
        .then(response => response.json())
        .then(product => {
            // Wenn kein gültiges Produkt zurückkommt → Fehler anzeigen
            if (!product || !product.id) {
                showError("❌ Produkt nicht gefunden.");
                return;
            }

            // Produktdaten im HTML darstellen
            const container = document.getElementById("productContainer");
            const priceFormatted = parseFloat(product.price).toFixed(2).replace(".", ",");

            container.innerHTML = `
                <img src="../../Backend/productpictures/${product.image}" 
                     alt="${product.name}" 
                     style="max-width: 300px;" 
                     onerror="this.src='../res/img/fallback.jpg';">
                <div class="product-info">
                    <h2>${product.name}</h2>
                    <p>${product.description}</p>
                    <p class="price">€ ${priceFormatted}</p>
                    <p><strong>Kategorie:</strong> ${product.category}</p>
                    <p><strong>Herkunftsland:</strong> ${product.origin_country}</p>
                    <p><strong>Verfügbar:</strong> ${product.stock} Stück</p>
                    <button id="addToCartBtn">🛒 In den Warenkorb</button>
                </div>
            `;

            // Event-Listener für den "In den Warenkorb"-Button
            document.getElementById("addToCartBtn").addEventListener("click", () => {
                addToCart(product); // im localStorage speichern
                sendToBackend(product); // auch im Backend ablegen (wenn eingeloggt)
            });
        })
        .catch(error => {
            console.error("❌ Fehler beim Laden des Produkts:", error);
            showError("⚠️ Serverfehler – Produkt konnte nicht geladen werden.");
        });

    // === Logout-Logik per Fetch ===
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

// === Funktion zum Anzeigen einer Fehlermeldung im Produkt-Container ===
function showError(message) {
    document.getElementById("productContainer").innerHTML = `<h2>${message}</h2>`;
}

// === Produkt lokal im localStorage speichern ===
function addToCart(product) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const existing = cart.find(item => item.id == product.id);

    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1
        });
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    alert("✅ Produkt wurde lokal zum Warenkorb hinzugefügt!");
}

// === Produkt auch ins Backend senden (nur für eingeloggte Nutzer relevant) ===
function sendToBackend(product) {
    fetch("/Snackery/Backend/addToCart.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "include",
            body: JSON.stringify({
                id: product.id,
                name: product.name,
                price: product.price,
                quantity: 1
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert("⚠️ Backend konnte das Produkt nicht speichern.");
            }
        })
        .catch(() => {
            alert("❌ Fehler beim Senden an das Backend.");
        });
}