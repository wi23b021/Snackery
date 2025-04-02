// Wartet, bis das gesamte HTML geladen ist
document.addEventListener("DOMContentLoaded", () => {

    // Holt sich das Formular mit der ID "registerForm"
    const form = document.getElementById("registerForm");

    // Wenn das Formular abgeschickt wird ...
    form.addEventListener("submit", function(event) {
        // Holt sich die Eingaben aus den Formularfeldern
        const name = form.name.value.trim();
        const email = form.email.value.trim();
        const address = form.address.value.trim();
        const password = form.password.value;

        // Hinweis: trim() entfernt Leerzeichen am Anfang/Ende

        // Prüft, ob das Passwort mindestens 6 Zeichen lang ist
        if (password.length < 6) {
            alert("⚠️ Das Passwort muss mindestens 6 Zeichen lang sein.");
            event.preventDefault(); // Stoppt das Abschicken des Formulars
            return;
        }

        // Optional: weitere Validierungen wären möglich
        // z. B. Email-Format, Sonderzeichen im Passwort, etc.

        // Wenn alles passt, wird das Formular ganz normal abgeschickt
    });
});