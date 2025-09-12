<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
    integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
</script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>   
    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../rsc/script/scriptUSU.js"></script>
    <script>
(function () {
  const modal = document.getElementById('registrarUsuario');
  const form  = document.getElementById('formUsu');
  if (!modal || !form) return;

  const usuario = document.getElementById('usuario');
  const pass    = document.getElementById('contrasena');
  const pass2   = document.getElementById('contrasena_conf');
  const btnSub  = form.querySelector('button[type="submit"]');
  const minLen  = 6;

  function validateAll() {
    // Usuario requerido
    if (!usuario.value.trim()) {
      usuario.setCustomValidity('El usuario es obligatorio.');
    } else {
      usuario.setCustomValidity('');
    }

    // Largo de contraseña
    if ((pass.value || '').length < minLen) {
      pass.setCustomValidity('La contraseña debe tener al menos ' + minLen + ' caracteres.');
      document.getElementById('passLenFeedback').textContent = pass.validationMessage;
    } else {
      pass.setCustomValidity('');
      document.getElementById('passLenFeedback').textContent =
        'La contraseña es obligatoria y debe tener al menos ' + minLen + ' caracteres.';
    }

    // Coincidencia
    if (pass.value !== pass2.value) {
      pass2.setCustomValidity('Las contraseñas deben coincidir.');
      document.getElementById('passMismatchFeedback').textContent = pass2.validationMessage;
    } else {
      pass2.setCustomValidity('');
      document.getElementById('passMismatchFeedback').textContent = 'Las contraseñas deben coincidir.';
    }

    // Estilos Bootstrap
    [usuario, pass, pass2].forEach(el => {
      el.classList.toggle('is-invalid', !el.checkValidity());
      el.classList.toggle('is-valid',    el.checkValidity());
    });

    // Habilitar/deshabilitar submit
    btnSub.disabled = !form.checkValidity();
  }

  // Validación en vivo
  ['input', 'blur'].forEach(evt => {
    usuario.addEventListener(evt, validateAll);
    pass.addEventListener(evt, validateAll);
    pass2.addEventListener(evt, validateAll);
  });

  // Al enviar, re-validar y bloquear si algo falla
  form.addEventListener('submit', function (e) {
    validateAll();
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  });

  // Al abrir el modal: reset limpio
  modal.addEventListener('shown.bs.modal', function () {
    form.reset();
    form.classList.remove('was-validated');
    [usuario, pass, pass2].forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    btnSub.disabled = true; // arranca deshabilitado hasta que sea válido
  });
})();
</script>


</body>

</html>