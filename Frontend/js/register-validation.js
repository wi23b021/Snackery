// ==============================================
// register-validation.js
// Snackery – Formularvalidierung für Registrierung
// ==============================================

document.addEventListener("DOMContentLoaded", () => {

    // Zugriff auf das Registrierungsformular
    const form = document.getElementById("registerForm");

    // Eingabefelder erfassen
    const usernameInput = form.querySelector("input[name='username']");
    const emailInput = form.querySelector("input[name='email']");
    const password = form.querySelector("input[name='password']");
    const passwordRepeat = form.querySelector("input[name='password_repeat']");

    // Funktion: Fehlermeldung unter einem Eingabefeld anzeigen
    const createError = (input, message) => {
        const existingError = input.nextElementSibling;
        if (existingError && existingError.classList.contains("error-message")) {
            existingError.remove();
        }

        const error = document.createElement("div");
        error.className = "error-message";
        error.style.color = "red";
        error.style.fontSize = "0.9em";
        error.textContent = message;
        input.insertAdjacentElement("afterend", error);
    };

    // Funktion: Fehlermeldung entfernen
    const clearError = (input) => {
        const existingError = input.nextElementSibling;
        if (existingError && existingError.classList.contains("error-message")) {
            existingError.remove();
        }
    };

    // Funktion: Live-Check ob Benutzername oder E-Mail bereits vergeben ist
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

    // Live-Überprüfung bei Eingabe
    usernameInput.addEventListener("input", checkAvailability);
    emailInput.addEventListener("input", checkAvailability);

    // Formularvalidierung beim Absenden
    form.addEventListener("submit", (e) => {
        const errors = form.querySelectorAll(".error-message");
        if (errors.length > 0) {
            e.preventDefault();
            alert("❗ Bitte behebe zuerst die markierten Fehler.");
            return;
        }

        if (password.value !== passwordRepeat.value) {
            e.preventDefault();
            createError(passwordRepeat, "❌ Die Passwörter stimmen nicht überein.");
        } else {
            clearError(passwordRepeat);
        }
    });
});