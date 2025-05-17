function attachInvoiceListeners() {
    document.querySelectorAll(".invoiceBtn").forEach(button => {
                button.addEventListener("click", () => {
                            const orderId = button.dataset.orderId;

                            fetch(`/Snackery/Backend/logic/requestHandler.php?action=getInvoiceData&orderId=${orderId}`, {
                                    credentials: "include"
                                })
                                .then(res => res.json())
                                .then(data => {
                                        if (!data.success) {
                                            alert("❌ Fehler beim Laden der Rechnungsdaten.");
                                            return;
                                        }

                                        const user = data.user;
                                        const order = data.order;
                                        const items = data.items;
                                        const invoiceNumber = "INV-" + Date.now();
                                        const total = items.reduce((sum, item) => sum + parseFloat(item.price) * item.quantity, 0);
                                        const invoiceWindow = window.open("", "Rechnung", "width=800,height=1000");

                                        invoiceWindow.document.write(`
                    <html>
                    <head>
                        <title>Rechnung ${invoiceNumber}</title>
                        <style>
                            body { font-family: Arial; padding: 20px; }
                            h1 { text-align: center; }
                            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                            th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
                            .footer { margin-top: 30px; font-size: 14px; }
                        </style>
                    </head>
                    <body>
                        <h1>Rechnung ${invoiceNumber}</h1>
                        <p><strong>Datum:</strong> ${new Date(order.created_at).toLocaleDateString()}</p>
                        <p><strong>Kunde:</strong><br>
                        ${user.firstname} ${user.lastname}<br>
                        ${user.street} ${user.housenumber}, ${user.postalcode} ${user.city}</p>

                        <table>
                            <thead>
                                <tr>
                                    <th>Produkt</th>
                                    <th>Menge</th>
                                    <th>Einzelpreis</th>
                                    <th>Gesamt</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${items.map(item => `
                                    <tr>
                                        <td>${item.name}</td>
                                        <td>${item.quantity}</td>
                                        <td>${parseFloat(item.price).toFixed(2)} €</td>
                                        <td>${(parseFloat(item.price) * item.quantity).toFixed(2)} €</td>
                                    </tr>
                                `).join("")}
                            </tbody>
                        </table>

                        <p class="footer"><strong>Gesamtsumme:</strong> ${total.toFixed(2)} €</p>

                        <hr style="margin: 30px 0;">

                        <h3>📄 Bankverbindung & Hinweise:</h3>
                        <p><strong>Empfänger:</strong> Snackery e.U.<br>
                        <strong>IBAN:</strong> AT12 3456 7890 1234 5678<br>
                        <strong>Betrag:</strong> siehe Bestellübersicht oben<br>
                        <strong>Lieferung:</strong> erfolgt nach Zahlungseingang (ca. 7 Tage)</p>

                    </body>
                    </html>
                `);

                invoiceWindow.document.close();
            })
            .catch(err => {
                console.error(err);
                alert("❌ Serverfehler beim Abrufen der Rechnung.");
            });
        });
    });
}