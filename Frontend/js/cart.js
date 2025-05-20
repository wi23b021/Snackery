// ===================================================
// 🔄 Funktion zum Laden und Anzeigen der Warenkorbinhalte
// Wird direkt beim Seitenladen aufgerufen
// ===================================================
function loadCart() {
    const cart = JSON.parse(localStorage.getItem("cart")) || []; // Cart aus dem localStorage holen
    const container = document.getElementById("cartItems"); // Container für Produktanzeige
    const totalDisplay = document.getElementById("totalPrice"); // Element für Gesamtsumme

    container.innerHTML = "";

    if (cart.length === 0) {
        container.innerHTML = "<p>🧺 Dein Warenkorb ist leer.</p>";
        totalDisplay.textContent = "Gesamt: € 0.00";
        return;
    }

    let total = 0;

    // Für jedes Produkt im Warenkorb eine Zeile erstellen
    cart.forEach((item) => {
        const div = document.createElement("div");
        div.className = "cart-item";
        div.innerHTML = `
            <p><strong>${item.name}</strong> – € ${parseFloat(item.price).toFixed(2)} 
            <button onclick="updateQuantity(${item.id}, -1)">-</button>
            <strong>${item.quantity}</strong>
            <button onclick="updateQuantity(${item.id}, 1)">+</button>
            </p>
            <button onclick="removeFromCart(${item.id})">🗑️ Entfernen</button>
        `;
        container.appendChild(div);
        total += item.price * item.quantity;
    });

    totalDisplay.textContent = `Gesamt: € ${total.toFixed(2)}`; // Gesamtpreis aktualisieren
}

// ===================================================
// ➕➖ Menge eines Produkts im Warenkorb ändern
// Parameter: Produkt-ID und Änderung der Menge (+1 oder -1)
// ===================================================
function updateQuantity(productId, change) {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const item = cart.find(p => p.id === productId);
    if (!item) return;

    item.quantity += change;

    // Wenn Menge 0 oder weniger → Produkt entfernen
    if (item.quantity <= 0) {
        const index = cart.findIndex(p => p.id === productId);
        cart.splice(index, 1);
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    loadCart(); // Anzeige aktualisieren
}

// ===================================================
// 🗑️ Produkt komplett aus dem Warenkorb entfernen
// ===================================================
function removeFromCart(productId) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart = cart.filter(item => item.id !== productId); // Produkt entfernen
    localStorage.setItem("cart", JSON.stringify(cart));
    loadCart(); // Anzeige neu laden
}

// ===================================================
// 🚀 Alles wird initial beim Laden der Seite ausgeführt
// ===================================================
document.addEventListener("DOMContentLoaded", () => {
    loadCart(); // Beim Start den Warenkorb anzeigen

    // =====================================
    // 🧹 Button zum Leeren des gesamten Warenkorbs
    // =====================================
    document.getElementById("clearCartBtn").addEventListener("click", () => {
        if (confirm("🧹 Möchtest du wirklich den ganzen Warenkorb leeren?")) {
            localStorage.removeItem("cart");
            loadCart();
            alert("✅ Warenkorb wurde geleert!");
        }
    });

    // =====================================
    // 🔓 Logout-Funktion über Fetch
    // =====================================
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
                        alert("❌ Logout fehlgeschlagen.");
                    }
                })
                .catch(() => {
                    alert("❌ Serverfehler beim Logout.");
                });
        });
    }

    // =====================================
    // 🧾 "Meine Bestellungen"-Link nur anzeigen, wenn eingeloggt
    // =====================================
    fetch("/Snackery/Backend/sessionStatus.php", {
            credentials: "include"
        })
        .then(res => res.json())
        .then(data => {
            if (data.loggedIn) {
                document.getElementById("myOrdersLink").style.display = "inline";
            } else {
                document.getElementById("myOrdersLink").style.display = "none";
            }
        })
        .catch(() => {
            document.getElementById("myOrdersLink").style.display = "none";
        });
});