document.addEventListener('DOMContentLoaded', function() {
    const NOTIFICATION_DURATION = 3000; // 3 segundos para mostrar la notificación
    const ANIMATION_DURATION = 500; // 0.5 segundos para la animación


      // Asociar eventos a los botones de mostrar formulario de agregar item
      document.querySelectorAll('.btn-show-add-item').forEach(button => {
        button.addEventListener('click', function () {
            const palletId = this.getAttribute('data-pallet-id');
            const folioId = this.getAttribute('data-folio-id');
            const url = `add_item_form.php?pallet_id=${palletId}&folio_id=${folioId}`;
            window.open(url, 'Agregar Item', 'width=600,height=400');
        });
    });


    function handleNotification() {
        const notification = document.getElementById('notification');
        
        if (!notification) return; // Si no hay notificación, salir

        // Asegurarse que la notificación sea visible
        notification.style.display = 'flex';
        
        // Timer para auto-cerrar
        const autoCloseTimer = setTimeout(() => {
            closeNotification(notification);
        }, NOTIFICATION_DURATION);

        // Configurar el botón de cerrar
        const closeButton = notification.querySelector('.notification-close');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                // Limpiar el timer automático si existe
                clearTimeout(autoCloseTimer);
                // Cerrar la notificación
                closeNotification(notification);
            });
        }
    }

    function closeNotification(notification) {
        notification.classList.add('fade-out');
        setTimeout(() => {
            notification.remove();
        }, ANIMATION_DURATION);
    }

    // Iniciar solo una vez
    handleNotification();
});
    


    // Manejo de acordeones
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', (e) => {
            if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                return;
            }
            header.classList.toggle('active');
            const body = header.nextElementSibling;
            body.style.display = body.style.display === 'block' ? 'none' : 'block';
        });
    });

    // Manejo de impresión de folios
    const printButtons = document.querySelectorAll('.btn-imprimir-folio');
    console.log('Botones de impresión encontrados:', printButtons.length);
    
    printButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const folioId = this.getAttribute('data-folio-id');
            console.log('Click en botón de imprimir, Folio ID:', folioId);
            imprimirFolioCompleto(folioId);
        });
    });

    // Manejo de selección de pallets
    document.querySelectorAll('.btn-select-pallet').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const palletId = this.getAttribute('data-pallet-id');
            togglePalletSelection(palletId);
        });
    });

    // Manejo de eliminación de pallets
    document.querySelectorAll('.btn-delete-pallet').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const palletId = this.getAttribute('data-pallet-id');
            deletePallet(palletId);
        });
    });

    // Manejo de eliminación de folios
    const deleteButtons = document.querySelectorAll('.btn-delete-folio');
    console.log('Botones de eliminar encontrados:', deleteButtons.length);

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const folioId = this.getAttribute('data-folio-id');
            console.log('Click en botón eliminar, Folio ID:', folioId);
            if (folioId) {
                deleteFolio(folioId);
            } else {
                console.error('No se encontró el ID del folio');
            }
        });
    });

// Función para imprimir folio completo
function imprimirFolioCompleto(folioId) {
    if (!folioId) {
        alert('Error: No se proporcionó ID del folio');
        return;
    }

    console.log('Solicitando pallets para folio:', folioId);

    fetch(`get_pallets_by_folio.php?folio_id=${folioId}`)
        .then(response => {
            console.log('Respuesta recibida:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);

            if (!data.success) {
                throw new Error(data.error || 'Error desconocido en el servidor');
            }

            if (!data.pallets || data.pallets.length === 0) {
                alert('No hay pallets disponibles para este folio');
                return;
            }

            const palletIds = data.pallets.map(pallet => pallet.id).join(',');
            const printUrl = `print_pallets.php?pallets=${palletIds}&folio=${data.folio_number}`;
            
            console.log('URL de impresión:', printUrl);

            const printWindow = window.open(printUrl, '_blank');
            
            if (!printWindow) {
                alert('Por favor, permite las ventanas emergentes para este sitio');
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            alert(`Error al obtener los pallets del folio: ${error.message}`);
        });
}

// Función para alternar la selección de pallets
function togglePalletSelection(palletId) {
    const index = selectedPallets.indexOf(palletId);
    if (index === -1) {
        selectedPallets.push(palletId);
        document.querySelector(`[data-pallet-id="${palletId}"]`).classList.add('selected');
    } else {
        selectedPallets.splice(index, 1);
        document.querySelector(`[data-pallet-id="${palletId}"]`).classList.remove('selected');
    }
    console.log('Pallets seleccionados:', selectedPallets);
}

// Función para eliminar un pallet
function deletePallet(palletId) {
    if (!palletId) {
        alert('Error: ID de pallet no válido');
        return;
    }

    // Solo una confirmación aquí
    if (!confirm('¿Está seguro de que desea eliminar este pallet?')) {
        return false; // Importante: retornar false si se cancela
    }

    console.log('Iniciando eliminación del pallet:', palletId);

    const formData = new FormData();
    formData.append('pallet_id', palletId);

    fetch('delete_pallet.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Respuesta recibida:', response.status);
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);

        if (data.success) {
            // Encontrar y eliminar el elemento del pallet
            const palletElement = document.querySelector(`.accordion-item[data-pallet-id="${palletId}"]`);
            if (palletElement) {
                palletElement.remove();
                console.log('Elemento del pallet eliminado del DOM');
            } else {
                console.log('No se encontró el elemento del pallet en el DOM');
            }
        } else {
            throw new Error(data.message || 'Error al eliminar el pallet');
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error al eliminar el pallet: ' + error.message);
    });
}

// Event listener para los botones de eliminar pallet
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete-pallet').forEach(button => {
        // Eliminar cualquier event listener existente
        button.replaceWith(button.cloneNode(true));
    });

    // Agregar los nuevos event listeners
    document.querySelectorAll('.btn-delete-pallet').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const palletId = this.getAttribute('data-pallet-id');
            console.log('Click en botón eliminar pallet, ID:', palletId);
            deletePallet(palletId);
        });
    });
});

// Función para eliminar folio
function deleteFolio(folioId) {
    console.log('Iniciando eliminación del folio:', folioId);

    if (!folioId) {
        alert('Error: ID de folio no válido');
        return;
    }

    if (!confirm('¿Está seguro de que desea eliminar este folio y todos sus pallets asociados?')) {
        return;
    }

    const formData = new FormData();
    formData.append('folio_id', folioId);

    console.log('Enviando petición al servidor...');

    fetch('delete_folio.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Respuesta recibida del servidor:', response.status);
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);

        if (data.success) {
            const folioElement = document.querySelector(`.accordion-item[data-folio-id="${folioId}"]`);
            if (folioElement) {
                folioElement.remove();
                console.log('Elemento del folio eliminado del DOM');
            } else {
                console.log('No se encontró el elemento del folio en el DOM');
            }
            
            alert('Folio eliminado exitosamente');
        } else {
            throw new Error(data.message || 'Error al eliminar el folio');
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error al eliminar el folio: ' + error.message);
    });
}

// Variable global para almacenar pallets seleccionados
let selectedPallets = [];
