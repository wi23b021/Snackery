let allProducts = [];

document.addEventListener("DOMContentLoaded", function() {
    // === Session-Status prüfen, um z. B. "Meine Bestellungen"-Link anzuzeigen ===
    fetch("/Snackery/Backend/sessionStatus.php", {
            credentials: "include"
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById("myOrdersLink").style.display = data.loggedIn ? "inline" : "none";
        });

    // === Produkte vom Backend laden ===
    fetch("/Snackery/Backend/logic/requestHandler.php?action=getProducts", {
            credentials: "include"
        })
        .then(response => response.json())
        .then(data => {
            allProducts = data; // globale Produktsammlung speichern
            renderProducts(allProducts); // Produkte in HTML anzeigen
        })
        .catch(() => {
            showError("❌ Produkte konnten nicht geladen werden.");
        });

    // === Filterfelder mit Events verbinden ===
    document.getElementById("searchInput").addEventListener("input", applyFilters);
    document.getElementById("categoryFilter").addEventListener("change", applyFilters);
    document.getElementById("sortFilter").addEventListener("change", applyFilters);

    // === Logout per Fetch ===
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

// === Funktion um Fehlermeldung im Produktbereich anzuzeigen ===
function showError(msg) {
    document.getElementById("productGrid").innerHTML = `<p>${msg}</p>`;
}

// === Funktion zur Darstellung aller Produkte im Grid ===
function renderProducts(products) {
    const grid = document.getElementById("productGrid");
    grid.innerHTML = "";

    if (!products.length) {
        grid.innerHTML = "<p>❌ Keine Produkte verfügbar.</p>";
        return;
    }

    // Für jedes Produkt eine Card bauen
    products.forEach(product => {
        const card = document.createElement("div");
        card.className = "product-card";
        const priceFormatted = parseFloat(product.price).toFixed(2).replace(".", ",");
        card.innerHTML = `
            <a href="product_detail.html?id=${product.id}">
                <img src="../../Backend/productpictures/${product.image}" 
                     alt="${product.name}" 
                     onerror="this.src='../res/img/fallback.jpg';">
                <h3>${product.name}</h3>
                <p>€ ${priceFormatted}</p>
            </a>
        `;
        grid.appendChild(card);
    });
}

// === Funktion zum Anwenden von Such-, Kategorie- und Sortierfilter ===
function applyFilters() {
    const search = document.getElementById("searchInput").value.toLowerCase();
    const category = document.getElementById("categoryFilter").value;
    const sort = document.getElementById("sortFilter").value;

    // Produkte nach Name + Kategorie filtern
    let filtered = allProducts.filter(p =>
        p.name.toLowerCase().includes(search) &&
        (category === "" || p.category === category)
    );

    // Optional sortieren nach Name oder Preis
    if (sort === "name-asc") {
        filtered.sort((a, b) => a.name.localeCompare(b.name));
    } else if (sort === "name-desc") {
        filtered.sort((a, b) => b.name.localeCompare(a.name));
    } else if (sort === "price-asc") {
        filtered.sort((a, b) => a.price - b.price);
    } else if (sort === "price-desc") {
        filtered.sort((a, b) => b.price - a.price);
    }

    // Gefilterte Produkte anzeigen
    renderProducts(filtered);
}