document.addEventListener('DOMContentLoaded', function () {
    // Mostrar notificación si existe
    const notification = document.getElementById('notification');
    if (notification) {
        notification.classList.add('show');
        setTimeout(() => {
            notification.classList.remove('show');
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    // Asociar eventos a los encabezados de acordeón
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', () => {
            header.classList.toggle('active');
            const body = header.nextElementSibling;
            body.style.display = body.style.display === 'block' ? 'none' : 'block';
        });
    });

    // Asociar eventos a los botones de eliminar item
    document.querySelectorAll('.btn-delete-item').forEach(button => {
        button.addEventListener('click', async function (e) {
            e.preventDefault();
            const itemId = this.getAttribute('data-item-id');
            
            if (!confirm('¿Estás seguro de que deseas eliminar este registro?')) {
                return;
            }

            try {
                const response = await fetch(`./delete_item_calidad.php?id=${itemId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
                    if (row) {
                        row.remove();
                        showNotification('Registro eliminado exitosamente', 'success');
                    }
                } else {
                    showNotification(data.message || 'Error al eliminar el registro', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error al eliminar el registro', 'error');
            }
        });
    });

    // Asociar eventos a los botones de editar item
    document.querySelectorAll('.btn-edit-item').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const itemId = this.getAttribute('data-item-id');
            window.location.href = `./edit_item_calidad/edit_item_calidad.php?id=${itemId}`;
        });
    });
});

function showNotification(message, type) {
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    const notification = document.createElement('div');
    notification.className = `notification ${type} show`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.remove('show');
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}