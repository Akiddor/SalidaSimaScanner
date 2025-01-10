document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("addItemForm");
    if (!form) return;

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        // Limpiar y convertir los valores escaneados
        const nifcoNumeroInput = document.getElementById("nifco_numero");
        const serialNumberInput = document.getElementById("serial_number");
        const quantityInput = document.getElementById("quantity");

        // Expresión corregida
        nifcoNumeroInput.value = nifcoNumeroInput.value.replace(/[\s'P\-.]/g, '').toUpperCase();
        serialNumberInput.value = serialNumberInput.value.replace(/[\s'P\-.]/g, '').toUpperCase();
        quantityInput.value = quantityInput.value.replace(/[\s\-.EQ]/gi, '').toUpperCase();

        // Eliminar las letras "S" o "1S" al principio del código de barras
        serialNumberInput.value = serialNumberInput.value.replace(/^(1S|S)/i, '');

        // Validar que el número de serie no comience con "Q"
        if (/^Q/i.test(serialNumberInput.value)) {
            showNotification("El número de serie no puede comenzar con 'Q'.", "error");
            return;
        }

        // Validar que el número de serie no contenga "Q50"
        if (/Q50/i.test(serialNumberInput.value)) {
            showNotification("El número de serie no puede contener 'Q50'.", "error");
            return;
        }

        // Validar que la cantidad no contenga las letras "E" y "Q"
        if (/E|Q/i.test(quantityInput.value)) {
            showNotification("La cantidad no puede contener las letras 'E' o 'Q'.", "error");
            return;
        }

        const formData = new FormData(form);

        // Enviar datos al servidor usando fetch
        fetch("back_add_item_calidad.php", {
            method: "POST",
            body: formData,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                if (data.success) {
                    showNotification(`${data.message} Número de NIFCO: ${data.nifco_numero}`, "success");

                    // Limpiar el formulario
                    form.reset();
                    document.getElementById("nifco_numero").focus();
                } else {
                    showNotification(data.message, "error");
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                showNotification("Error al agregar el ítem.", "error");
            });
    });
});

function showNotification(message, type) {
    const notification = document.createElement("div");
    notification.className = `notification ${type} show`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.remove("show");
        document.body.removeChild(notification);
    }, 5000);
}