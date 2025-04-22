// Sobald das DOM geladen ist, alle Events setzen
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("registerForm");
    const usernameInput = form.querySelector("input[name='username']");
    const emailInput = form.querySelector("input[name='email']");
    const password = form.querySelector("input[name='password']");
    const passwordRepeat = form.querySelector("input[name='password_repeat']");

    // Dynamisch Fehleranzeigen erzeugen
    const createError = (input, message) => {
        // Vorherige Meldungen löschen
        const oldError = input.nextElementSibling;
        if (oldError && oldError.classList.contains("error-message")) {
            oldError.remove();
        }

        // Neue Fehlermeldung einfügen
        const error = document.createElement("div");
        error.className = "error-message";
        error.textContent = message;
        input.insertAdjacentElement("afterend", error);
    };

    // Fehler entfernen
    const clearError = (input) => {
        const oldError = input.nextElementSibling;
        if (oldError && oldError.classList.contains("error-message")) {
            oldError.remove();
        }
    };

    // Benutzername oder E-Mail prüfen (AJAX)
    const checkAvailability = () => {
        const username = usernameInput.value.trim();
        const email = emailInput.value.trim();

        if (username && email) {
            fetch(`../../Backend/requestHandler.php?action=checkUserExists&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`)
                .then(res => res.json())
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
                });
        }
    };

    // Beim Verlassen des Feldes oder beim Tippen prüfen
    usernameInput.addEventListener("input", checkAvailability);
    emailInput.addEventListener("input", checkAvailability);

    // Formular absenden verhindern, wenn Fehler bestehen
    form.addEventListener("submit", (e) => {
        const errors = form.querySelectorAll(".error-message");
        if (errors.length > 0) {
            e.preventDefault(); // Senden stoppen
            alert("Bitte behebe zuerst die rot markierten Fehler.");
            return;
        }

        // Passwortvergleich
        if (password.value !== passwordRepeat.value) {
            e.preventDefault();
            createError(passwordRepeat, "❌ Die Passwörter stimmen nicht überein.");
        } else {
            clearError(passwordRepeat);
        }
    });
});