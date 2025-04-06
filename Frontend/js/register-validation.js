// Warten, bis das DOM vollständig geladen ist
document.addEventListener("DOMContentLoaded", () => {

    // Das Registrierungsformular über die ID holen
    const form = document.getElementById("registerForm");

    // Wenn das Formular abgeschickt wird ...
    form.addEventListener("submit", function(event) {
        // Eingaben aus den Feldern holen
        const password = form.password.value;
        const passwordRepeat = form.password_repeat.value;

        // Prüfen, ob das Passwort mindestens 6 Zeichen hat
        if (password.length < 6) {
            alert("⚠️ Das Passwort muss mindestens 6 Zeichen lang sein.");
            event.preventDefault(); // Verhindert das Abschicken
            return;
        }

        // Prüfen, ob beide Passwörter übereinstimmen
        if (password !== passwordRepeat) {
            alert("⚠️ Die Passwörter stimmen nicht überein.");
            event.preventDefault(); // Formular wird nicht abgeschickt
            return;
        }

        // Optionale Prüfung: PLZ sollte nur Ziffern enthalten
        const plz = form.postalcode.value;
        if (!/^\d{4,5}$/.test(plz)) {
            alert("⚠️ Bitte gib eine gültige Postleitzahl ein.");
            event.preventDefault();
            return;
        }

        // Optional: Prüfung der Hausnummer (einfaches Format)
        const hausnummer = form.housenumber.value;
        if (!/^[\d]{1,4}[a-zA-Z]?$/.test(hausnummer)) {
            alert("⚠️ Bitte gib eine gültige Hausnummer ein.");
            event.preventDefault();
            return;
        }

        // Wenn alle Prüfungen bestanden sind, wird das Formular normal abgeschickt
    });
});