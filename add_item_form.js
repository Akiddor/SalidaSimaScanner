document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('addItemForm');
    const messageDiv = document.getElementById('message');

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(form);

        fetch('add_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(errorText => {
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Limpiar el formulario
                form.reset();
                // Establecer el foco en el campo de número de serie
                document.getElementById('serial_number').focus();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error al procesar el formulario:', error);
            showNotification(`Error inesperado: ${error.message}`, 'error');
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
});
