// DataTable
var tablaUsuarios = new DataTable('#Usuarios-lista', {
  language: {
    decimal: ",", thousands: ".",
    info: "Mostrando _END_ registros de un total de _TOTAL_",
    infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
    infoFiltered: "(filtrado de un total de _MAX_ registros)",
    loadingRecords: "Cargando...",
    lengthMenu: "Mostrar _MENU_",
    paginate: { first: "<<", last: ">>", next: ">", previous: "<" },
    search: "Buscador:", searchPlaceholder: "Buscar...",
    emptyTable: "No hay registros para mostrar en la tabla",
  },
  lengthMenu: [5,10,25,50],
  pageLength: 10,
  order: [[1,'asc']], // por Usuario
  searching: true, paging: true, info: true, autoWidth: false, responsive: true
});

// EDITAR (delegado)
$(document).on('click', '#Usuarios-lista tbody .editUsuario, #Usuarios-lista tbody .editbtnusuario', function(){
  let $tr = $(this).closest('tr'); if ($tr.hasClass('child')) $tr = $tr.prev();
  const d = $tr.children('td').map(function(){ return $(this).text().trim(); }).get();
  console.log(d);
  $('#edit_id').val(d[0]);
  $('#edit_usuario').val(d[1]);
  $('#edit_nomyapellido').val(d[2]);
  $('#edit_rol').val(d[3] || '');
  
  // Estado desde badge/texto
  const estadoTxt = (d[4] || '').includes('Activo') ? 'Activo' : 'Inactivo';
  $('#edit_estado').val(estadoTxt);
  
  // Passwords en blanco
  $('#edit_contrasena').val('');
  $('#edit_contrasena2').val('');
  
  $('#editarUsuario').modal('show');
  
});

// ELIMINAR (si ya lo tenés, dejalo; si no, este patrón)
$(document).on('click', '#Usuarios-lista tbody .deleteUsuario, #Usuarios-lista tbody .deletebtnusuario', function(){
  let $tr = $(this).closest('tr'); if ($tr.hasClass('child')) $tr = $tr.prev();
  const d = $tr.children('td').map(function(){ return $(this).text().trim(); }).get();
  $('#del_id').val(d[0]);      // hidden del form de borrar
  $('#del_usuario').text(d[1]);
  $('#borrarUsuario').modal('show');
});

// VER (delegado): funciona al paginar/redibujar DataTables
$(document).on('click', '#Usuarios-lista tbody .verUsuario', function(){
  let $tr = $(this).closest('tr');
  if ($tr.hasClass('child')) $tr = $tr.prev(); // por si DataTables usa filas "child"

  const d = $tr.children('td').map(function(){ return $(this).text().trim(); }).get();
  // Orden según tu <thead>:
  // d[0]=ID (hidden), d[1]=Usuario, d[2]=Nombre y Apellido, d[3]=Rol (texto),
  // d[4]=Fecha creación, d[5]=rol_id (hidden)

  $('#ver_usuario').val(d[1]);
  $('#ver_nomyapellido').val(d[2]);
  $('#ver_rol').val(d[3] || '—');
  $('#ver_estado').val( (d[4] || '').includes('Activo') ? 'Activo' : 'Inactivo' );
  $('#ver_fecha').val(d[5]);

  $('#verUsuario').modal('show');
});

// Validación de contraseñas en EDITAR (opcional pero consistente con Registrar)
(function () {
  const editModal = document.getElementById('editarUsuario');
  if (!editModal) return;

  const form   = editModal.querySelector('form');
  const pass   = document.getElementById('edit_contrasena');
  const pass2  = document.getElementById('edit_contrasena2');
  const btnSub = form.querySelector('button[type="submit"]');
  const minLen = 6;

  function validateEdit() {
    const p  = (pass.value || '').trim();
    const p2 = (pass2.value || '').trim();

    // Caso 1: ambas vacías => válido (no se cambia la contraseña)
    if (p === '' && p2 === '') {
      pass.setCustomValidity('');
      pass2.setCustomValidity('');
      document.getElementById('passLenFeedbackEdit').textContent = 'Si vas a cambiarla, mínimo 6 caracteres.';
      document.getElementById('passMismatchFeedbackEdit').textContent = 'Las contraseñas deben coincidir.';
      pass.classList.remove('is-invalid', 'is-valid');
      pass2.classList.remove('is-invalid', 'is-valid');
      btnSub.disabled = false;
      return;
    }

    // Caso 2: se desea cambiar => validar largo y coincidencia
    if (p.length < minLen) {
      pass.setCustomValidity('La contraseña debe tener al menos ' + minLen + ' caracteres.');
    } else {
      pass.setCustomValidity('');
    }
    document.getElementById('passLenFeedbackEdit').textContent =
      pass.validationMessage || 'Si vas a cambiarla, mínimo 6 caracteres.';

    if (p !== p2) {
      pass2.setCustomValidity('Las contraseñas deben coincidir.');
    } else {
      pass2.setCustomValidity('');
    }
    document.getElementById('passMismatchFeedbackEdit').textContent =
      pass2.validationMessage || 'Las contraseñas deben coincidir.';

    // Estilos Bootstrap
    [pass, pass2].forEach(el => {
      el.classList.toggle('is-invalid', !el.checkValidity());
      el.classList.toggle('is-valid', el.checkValidity());
    });

    // Habilitar/deshabilitar submit según validez global del form
    btnSub.disabled = !form.checkValidity();
  }

  // Validación en vivo
  ['input', 'blur'].forEach(evt => {
    pass.addEventListener(evt, validateEdit);
    pass2.addEventListener(evt, validateEdit);
  });

  // Reset cada vez que se abre el modal
  editModal.addEventListener('shown.bs.modal', function () {
    form.classList.remove('was-validated');
    pass.value = '';
    pass2.value = '';
    pass.classList.remove('is-valid', 'is-invalid');
    pass2.classList.remove('is-valid', 'is-invalid');
    btnSub.disabled = false; // arranca habilitado si no se cambia contraseña
    validateEdit();
  });

  // Validar al enviar
  form.addEventListener('submit', function (e) {
    validateEdit();
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  });
})();
