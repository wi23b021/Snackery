let cart = [];
let isLoggedIn = false; // Wird später durch Session-Status gesetzt

// ===================================================
// 🧾 Lädt die Produkte aus dem localStorage und zeigt sie im Bestellformular an
// ===================================================
function loadCart() {
    cart = JSON.parse(localStorage.getItem("cart")) || [];
    const summary = document.getElementById("orderSummary");
    const totalPriceElement = document.getElementById("totalPrice");

    summary.innerHTML = "";

    if (cart.length === 0) {
        summary.innerHTML = "<p>🧺 Dein Warenkorb ist leer.</p>";
        totalPriceElement.textContent = "Gesamt: € 0.00";
        return;
    }

    let total = 0;
    cart.forEach(item => {
        const div = document.createElement("div");
        div.innerHTML = `<p><strong>${item.name}</strong> – € ${parseFloat(item.price).toFixed(2)} x ${item.quantity}</p>`;
        summary.appendChild(div);
        total += item.price * item.quantity;
    });

    totalPriceElement.textContent = `Gesamt: € ${total.toFixed(2)}`;
}

// ===================================================
// ⏳ Alles wird ausgeführt, sobald die Seite vollständig geladen ist
// ===================================================
document.addEventListener("DOMContentLoaded", function() {
    loadCart(); // Warenkorb beim Laden anzeigen

    // ===================================================
    // 🔐 Login-Status abfragen, um Zugriff auf Checkout zu steuern
    // ===================================================
    fetch("/Snackery/Backend/sessionStatus.php", {
            credentials: "include"
        })
        .then(res => res.json())
        .then(data => {
            isLoggedIn = data.loggedIn || false;

            // Wenn eingeloggt → "Meine Bestellungen"-Link sichtbar machen
            if (isLoggedIn) {
                document.getElementById("myOrdersLink").style.display = "inline";
            }
        })
        .catch(() => {
            isLoggedIn = false;
        });

    // ===================================================
    // 💳 Vorschau für Erlagschein nur anzeigen, wenn Option gewählt
    // ===================================================
    document.getElementById("paymentMethod").addEventListener("change", (e) => {
        document.getElementById("erlagscheinPreview").style.display =
            e.target.value === "erlagschein" ? "block" : "none";
    });

    // ===================================================
    // ✅ Formular zum Absenden der Bestellung
    // Nur wenn der User eingeloggt ist und der Warenkorb nicht leer ist
    // ===================================================
    document.getElementById("checkoutForm").addEventListener("submit", function(e) {
        e.preventDefault();

        if (!isLoggedIn) {
            alert("⚠️ Bitte logge dich ein, um zur Kasse zu gehen.");
            window.location.href = "login.html";
            return;
        }

        if (cart.length === 0) {
            alert("❌ Dein Warenkorb ist leer!");
            return;
        }

        const formData = new FormData(this);
        formData.append("cart", JSON.stringify(cart)); // Cart als JSON mitschicken

        fetch("/Snackery/Backend/logic/requestHandler.php?action=placeOrder", {
                method: "POST",
                body: formData,
                credentials: "include"
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || "Unbekannter Fehler");
                return data;
            })
            .then(() => {
                localStorage.removeItem("cart"); // Warenkorb leeren
                window.location.href = "checkout_success.html"; // Weiterleitung
            })
            .catch(err => {
                console.error("❌ Fehler:", err);
                document.getElementById("feedbackMessage").textContent = "❌ " + err.message;
            });
    });

    // ===================================================
    // 🔓 Logout-Funktion per Fetch
    // ===================================================
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
});