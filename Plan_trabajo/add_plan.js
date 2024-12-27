document.getElementById('add-nifco').addEventListener('click', function() {
    var container = document.getElementById('nifco-container');
    var nifcoInputs = container.querySelectorAll('input[name="nifco_numero[]"]');
    var nifcoValues = Array.from(nifcoInputs).map(input => input.value);

    var newNifco = document.createElement('div');
    newNifco.className = 'nifco-entry';
    newNifco.innerHTML = `
        <label for="nifco_numero[]">Número de NIFCO:</label>
        <input type="text" id="nifco_numero[]" name="nifco_numero[]" required>

        <label for="piezas[]">Piezas a trabajar:</label>
        <input type="number" id="piezas[]" name="piezas[]" required>

        <button type="button" class="remove-nifco-btn"><i class="fas fa-times"></i></button>
    `;

    var newNifcoInput = newNifco.querySelector('input[name="nifco_numero[]"]');
    newNifcoInput.addEventListener('input', function() {
        if (nifcoValues.includes(newNifcoInput.value)) {
            alert('El número de NIFCO ya ha sido agregado en este formulario.');
            newNifcoInput.value = '';
        }
    });

    newNifco.querySelector('.remove-nifco-btn').addEventListener('click', function() {
        container.removeChild(newNifco);
    });

    container.appendChild(newNifco);
});

document.querySelectorAll('.accordion-button').forEach(button => {
    button.addEventListener('click', () => {
        const content = button.nextElementSibling;
        button.classList.toggle('active');
        if (button.classList.contains('active')) {
            content.style.maxHeight = content.scrollHeight + 'px';
        } else {
            content.style.maxHeight = 0;
        }
    });
});

document.querySelectorAll('.plan-row').forEach(row => {
    const piezasPlan = parseInt(row.dataset.piezasPlan);
    const piezasRegistradas = parseInt(row.dataset.piezasRegistradas);
    const diferencia = piezasPlan - piezasRegistradas;

    if (piezasRegistradas >= piezasPlan) {
        row.style.backgroundColor = '#d4edda'; // Verde
    } else if (diferencia <= piezasPlan * 0.1) {
        row.style.backgroundColor = '#fff3cd'; // Amarillo
    } else {
        row.style.backgroundColor = '#f8d7da'; // Rojo
    }
});