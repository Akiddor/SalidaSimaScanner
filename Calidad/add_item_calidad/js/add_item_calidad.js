document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('addItemForm');
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        // Limpiar y convertir los valores escaneados a mayúsculas
        const nifcoNumeroInput = document.getElementById('nifco_numero');
        const serialNumberInput = document.getElementById('serial_number');
        const quantityInput = document.getElementById('quantity');

        // Eliminar caracteres no deseados y convertir a mayúsculas
        nifcoNumeroInput.value = nifcoNumeroInput.value.replace(/[\s'P-]/g, '').toUpperCase();
        serialNumberInput.value = serialNumberInput.value.replace(/[\s'P-]/g, '').toUpperCase();
        quantityInput.value = quantityInput.value.replace(/[\s-]/g, '').toUpperCase();

        // Eliminar las letras "S" o "1S" al principio del código de barras
        serialNumberInput.value = serialNumberInput.value.replace(/^(1S|S)/i, '');

        const formData = new FormData(this);

        fetch('back_add_item_calidad.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`${data.message} Número de NIFCO: ${data.nifco_numero}`, 'success');

                    // Limpiar el formulario
                    this.reset();

                    // Establecer el foco en el primer campo de entrada
                    document.getElementById('nifco_numero').focus();
                } else {
                    showNotification(data.message, 'error');
                }
                console.log(data.debug); // Mostrar mensajes de depuración en la consola
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al agregar el ítem.', 'error');
            });
    });

    const createDayForm = document.getElementById('createDayForm');
    createDayForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(this);

        fetch('back_add_item_calidad.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');

                    // Limpiar el formulario
                    this.reset();
                } else {
                    showNotification(data.message, 'error');
                }
                console.log(data.debug); // Mostrar mensajes de depuración en la consola
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al crear el día.', 'error');
            });
    });
});

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type} show`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.classList.remove('show');
        document.body.removeChild(notification);
    }, 5000);
}