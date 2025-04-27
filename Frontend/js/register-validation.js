// 
// Snackery – JavaScript zur Formularvalidierung bei der Registrierung
// Dieses Script prüft automatisch Benutzername, E-Mail und Passwortübereinstimmung.
// Technologien: JavaScript (DOM-Handling, Fetch API)
// 

// Wenn das DOM vollständig geladen ist
document.addEventListener("DOMContentLoaded", () => {

    // Formular-Elemente abrufen
    const form = document.getElementById("registerForm");
    const usernameInput = form.querySelector("input[name='username']");
    const emailInput = form.querySelector("input[name='email']");
    const password = form.querySelector("input[name='password']");
    const passwordRepeat = form.querySelector("input[name='password_repeat']");

    // Funktion zum Anzeigen einer Fehlermeldung unter einem Eingabefeld
    const createError = (input, message) => {
        // Alte Fehlermeldung entfernen, falls vorhanden
        const oldError = input.nextElementSibling;
        if (oldError && oldError.classList.contains("error-message")) {
            oldError.remove();
        }

        // Neue Fehlermeldung erzeugen
        const error = document.createElement("div");
        error.className = "error-message";
        error.textContent = message;
        input.insertAdjacentElement("afterend", error);
    };

    // Funktion zum Entfernen von Fehlern
    const clearError = (input) => {
        const oldError = input.nextElementSibling;
        if (oldError && oldError.classList.contains("error-message")) {
            oldError.remove();
        }
    };

    // Funktion: Verfügbarkeit von Benutzername und E-Mail prüfen (AJAX)
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

    // Events: Live-Check bei Eingabe
    usernameInput.addEventListener("input", checkAvailability);
    emailInput.addEventListener("input", checkAvailability);

    // Formular absenden verhindern, falls Fehler existieren
    form.addEventListener("submit", (e) => {
        const errors = form.querySelectorAll(".error-message");
        if (errors.length > 0) {
            e.preventDefault(); // Absenden blockieren
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