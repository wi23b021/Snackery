document.addEventListener("DOMContentLoaded", () => {
    // == Referenz auf das Registrierungsformular holen ==
    const form = document.getElementById("registerForm");

    // == Eingabefelder im Formular referenzieren ==
    const usernameInput = form.querySelector("input[name='username']");
    const emailInput = form.querySelector("input[name='email']");
    const password = form.querySelector("input[name='password']");
    const passwordRepeat = form.querySelector("input[name='password_repeat']");

    // == Zeigt eine rote Fehlermeldung direkt unter einem Input-Feld an ==
    const createError = (input, message) => {
        const existingError = input.nextElementSibling;
        if (existingError && existingError.classList.contains("error-message")) {
            existingError.remove(); // alte Fehlermeldung entfernen
        }

        const error = document.createElement("div");
        error.className = "error-message";
        error.style.color = "red";
        error.style.fontSize = "0.9em";
        error.textContent = message;
        input.insertAdjacentElement("afterend", error); // Fehlermeldung anzeigen
    };

    // == Entfernt eine bestehende Fehlermeldung unter einem Input-Feld ==
    const clearError = (input) => {
        const existingError = input.nextElementSibling;
        if (existingError && existingError.classList.contains("error-message")) {
            existingError.remove(); // Fehlermeldung löschen
        }
    };

    // == AJAX-Check, ob Username oder Email bereits vergeben ist ==
    const checkAvailability = () => {
        const username = usernameInput.value.trim();
        const email = emailInput.value.trim();

        if (username && email) {
            fetch(`/Snackery/Backend/logic/requestHandler.php?action=checkUserExists&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`, {
                    credentials: "include"
                })
                .then(res => {
                    if (!res.ok) throw new Error("Netzwerkfehler");
                    return res.json();
                })
                .then(data => {
                    if (data.usernameTaken) {
                        createError(usernameInput, "❌ Benutzername ist bereits vergeben.");
                    } else {
                        clearError(usernameInput);
                    }

                    if (data.emailTaken) {
                        createError(emailInput, "❌ E-Mail ist bereits registriert.");
                    } else {
                        clearError(emailInput);
                    }
                })
                .catch(err => {
                    console.error("❌ Fehler beim Verfügbarkeitscheck:", err);
                });
        }
    };

    // == Führt den Verfügbarkeitscheck bei jeder Eingabeänderung aus ==
    usernameInput.addEventListener("input", checkAvailability);
    emailInput.addEventListener("input", checkAvailability);

    // == Validierung beim Formular-Absenden ==
    form.addEventListener("submit", (e) => {
        const errors = form.querySelectorAll(".error-message");
        if (errors.length > 0) {
            e.preventDefault(); // Absenden verhindern
            alert("❗ Bitte behebe zuerst die markierten Fehler.");
            return;
        }

        // Passwortgleichheit prüfen
        if (password.value !== passwordRepeat.value) {
            e.preventDefault();
            createError(passwordRepeat, "❌ Die Passwörter stimmen nicht überein.");
        } else {
            clearError(passwordRepeat);
        }
    });
});