console.log('alterei');
// Mask label usuario

const campoPersonalizadoLabel_1 = document.getElementById('campo-personalizado-label-1');
const campoPersonalizadoInput_1 = document.getElementById('campo-personalizado-input-1');
const campoPersonalizadoLabel_2 = document.getElementById('campo-personalizado-label-2');
const campoPersonalizadoInput_2 = document.getElementById('campo-personalizado-input-2');

if (campoPersonalizadoLabel_1) {
  campoPersonalizadoLabel_1.addEventListener('input', () => {
    campoPersonalizadoInput_1.value = campoPersonalizadoLabel_1.innerText;
  });
}

if (campoPersonalizadoLabel_2) {
  campoPersonalizadoLabel_2.addEventListener('input', () => {
    campoPersonalizadoInput_2.value = campoPersonalizadoLabel_2.innerText;
  });
}

// Validar destinatario

const select = document.querySelector('#destinatarios');
const submitBtn = document.querySelector('#submit-btn');
const alertMsg = document.querySelector('#alert-form');

if (submitBtn) {
  submitBtn.addEventListener('click', function (event) {
    const selectedOption = select.options[select.selectedIndex].value;
    if (selectedOption === 'Escolha um destinatário') {
      event.preventDefault();
      alertMsg.classList.remove('d-none');
      alertMsg.classList.add('d-block');
    } else {
      alertMsg.classList.remove('d-block');
      alertMsg.classList.add('d-none');
      const loadingElem = document.querySelector('#loading');
      loadingElem.style.display = 'block';
    }
  });
}


// Date range picker

$(function () {
  $('input[name="periodo"]').daterangepicker({
    opens: 'left'
  });

  $('input[name="periodo"]').on('apply.daterangepicker', function (ev, picker) {
    var periodo = picker.startDate.format('DD/MM/YYYY') + ' a ' + picker.endDate.format('DD/MM/YYYY');
    $(this).val(periodo);
  });
});

// Maskmoney

$(function () {
  $('#custo').maskMoney({
    prefix: 'R$ ',
    thousands: '.',
    decimal: ',',
    allowZero: true
  });
});
