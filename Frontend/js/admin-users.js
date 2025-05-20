document.addEventListener("DOMContentLoaded", function() {

    // ============================================
    // 🔐 Zugriffskontrolle: Nur Admins erlaubt
    // Wenn kein Admin eingeloggt ist → Weiterleitung zum Login
    // ============================================
    fetch("/Snackery/Backend/logic/requestHandler.php?action=getProfile", {
            credentials: "include"
        })
        .then(response => response.json())
        .then(user => {
            if (!user || user.role !== "admin") {
                window.location.href = "login.html";
            }
        })
        .catch(() => window.location.href = "login.html");

    // ============================================
    // 📥 Benutzerdaten vom Server holen (Admin)
    // ============================================
    fetch("/Snackery/Backend/logic/requestHandler.php?action=getUsers", {
            credentials: "include"
        })
        .then(response => response.json())
        .then(users => {
            const container = document.getElementById("userTableContainer");

            // Wenn keine Benutzer existieren
            if (!Array.isArray(users) || users.length === 0) {
                container.innerHTML = "<p>❌ Keine Benutzer gefunden.</p>";
                return;
            }

            // Tabelle mit Eingabefeldern für jeden User
            const rows = users.map(user => `
            <tr data-user-id="${user.id}">
                <td>${user.id}</td>
                <td><input class="input-username" value="${user.username}" /></td>
                <td><input class="input-email" value="${user.email}" /></td>
                <td><input class="input-firstname" value="${user.firstname}" /></td>
                <td><input class="input-lastname" value="${user.lastname}" /></td>
                <td><input class="input-street" value="${user.street}" /></td>
                <td><input class="input-housenumber" value="${user.housenumber}" /></td>
                <td><input class="input-postalcode" value="${user.postalcode}" /></td>
                <td><input class="input-city" value="${user.city}" /></td>
                <td><input class="input-iban" value="${user.iban || ''}" /></td>
                <td><input class="input-cardnumber" value="${user.cardnumber || ''}" /></td>
                <td><input class="input-bankname" value="${user.bankname || ''}" /></td>
                <td>
                    <select class="input-role">
                        <option value="user" ${user.role === "user" ? "selected" : ""}>User</option>
                        <option value="admin" ${user.role === "admin" ? "selected" : ""}>Admin</option>
                    </select>
                </td>
                <td>
                    <select class="input-active">
                        <option value="1" ${user.active == 1 ? "selected" : ""}>aktiv</option>
                        <option value="0" ${user.active == 0 ? "selected" : ""}>inaktiv</option>
                    </select>
                </td>
                <td>
                    <div class="action-btns">
                        <button class="saveUserBtn" data-user-id="${user.id}">💾</button>
                        <button class="deleteUserBtn" data-user-id="${user.id}">🗑️</button>
                    </div>
                </td>
            </tr>
        `).join("");

            container.innerHTML = `
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Benutzername</th>
                        <th>E-Mail</th>
                        <th>Vorname</th>
                        <th>Nachname</th>
                        <th>Straße</th>
                        <th>Hausnr.</th>
                        <th>PLZ</th>
                        <th>Ort</th>
                        <th>IBAN</th>
                        <th>Kartennr.</th>
                        <th>Bank</th>
                        <th>Rolle</th>
                        <th>Status</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        `;

            // ============================================
            // 💾 Benutzer speichern (Daten + Status)
            // ============================================
            document.querySelectorAll(".saveUserBtn").forEach(button => {
                button.addEventListener("click", () => {
                    const userId = button.dataset.userId;
                    const row = document.querySelector(`tr[data-user-id='${userId}']`);

                    const userData = {
                        id: userId,
                        username: row.querySelector(".input-username").value,
                        email: row.querySelector(".input-email").value,
                        firstname: row.querySelector(".input-firstname").value,
                        lastname: row.querySelector(".input-lastname").value,
                        street: row.querySelector(".input-street").value,
                        housenumber: row.querySelector(".input-housenumber").value,
                        postalcode: row.querySelector(".input-postalcode").value,
                        city: row.querySelector(".input-city").value,
                        iban: row.querySelector(".input-iban").value,
                        cardnumber: row.querySelector(".input-cardnumber").value,
                        bankname: row.querySelector(".input-bankname").value,
                        role: row.querySelector(".input-role").value,
                        active: parseInt(row.querySelector(".input-active").value)
                    };

                    // Benutzer updaten
                    fetch("/Snackery/Backend/logic/requestHandler.php?action=updateUser", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            credentials: "include",
                            body: JSON.stringify(userData)
                        })
                        .then(res => res.json())
                        .then(data => {
                            alert(data.success ? "✅ Benutzer erfolgreich aktualisiert!" : "❌ Fehler: " + data.message);

                            // Aktivitätsstatus separat speichern
                            fetch("/Snackery/Backend/logic/requestHandler.php?action=toggleUserActive", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json"
                                },
                                credentials: "include",
                                body: JSON.stringify({
                                    id: userId,
                                    active: userData.active
                                })
                            });
                        })
                        .catch(err => {
                            console.error(err);
                            alert("❌ Serverfehler beim Speichern.");
                        });
                });
            });

            // ============================================
            // 🗑️ Benutzer löschen (mit Bestätigung)
            // ============================================
            document.querySelectorAll(".deleteUserBtn").forEach(button => {
                button.addEventListener("click", () => {
                    const userId = button.dataset.userId;

                    if (confirm("❗ Willst du diesen Benutzer wirklich löschen?")) {
                        fetch(`/Snackery/Backend/logic/requestHandler.php?action=deleteUser&id=${userId}`, {
                                method: "DELETE",
                                credentials: "include"
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    alert("✅ Benutzer gelöscht.");
                                    window.location.reload();
                                } else {
                                    alert("❌ Fehler: " + data.message);
                                }
                            })
                            .catch(() => alert("❌ Serverfehler beim Löschen."));
                    }
                });
            });
        })
        .catch(() => {
            document.getElementById("userTableContainer").innerHTML = "<p>❌ Fehler beim Laden der Benutzerliste.</p>";
        });

    // ============================================
    // 🔓 Logout-Funktion
    // ============================================
    document.getElementById("logoutBtn").addEventListener("click", function(e) {
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
            .catch(() => alert("❌ Serverfehler beim Logout."));
    });
});