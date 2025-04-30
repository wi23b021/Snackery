// 
// Snackery – JavaScript zur Formularvalidierung bei der Registrierung
// Technologien: JavaScript (DOM-Handling, Fetch API)
//

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("registerForm");
    const usernameInput = form.querySelector("input[name='username']");
    const emailInput = form.querySelector("input[name='email']");
    const password = form.querySelector("input[name='password']");
    const passwordRepeat = form.querySelector("input[name='password_repeat']");

    const createError = (input, message) => {
        const oldError = input.nextElementSibling;
        if (oldError && oldError.classList.contains("error-message")) {
            oldError.remove();
        }

        const error = document.createElement("div");
        error.className = "error-message";
        error.textContent = message;
        input.insertAdjacentElement("afterend", error);
    };

    const clearError = (input) => {
        const oldError = input.nextElementSibling;
        if (oldError && oldError.classList.contains("error-message")) {
            oldError.remove();
        }
    };

    const checkAvailability = () => {
        const username = usernameInput.value.trim();
        const email = emailInput.value.trim();

        if (username && email) {
            fetch(`../../Backend/logic/requestHandler.php?action=checkUserExists&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`, {
                    credentials: "include"
                })
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
                })
                .catch(error => {
                    console.error("Fehler beim Verfügbarkeitscheck:", error);
                });
        }
    };

    usernameInput.addEventListener("input", checkAvailability);
    emailInput.addEventListener("input", checkAvailability);

    form.addEventListener("submit", (e) => {
        const errors = form.querySelectorAll(".error-message");
        if (errors.length > 0) {
            e.preventDefault();
            alert("Bitte behebe zuerst die rot markierten Fehler.");
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