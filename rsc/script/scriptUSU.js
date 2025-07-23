var table = new DataTable('#Usuarios-lista', {
    language: {
    "decimal": ",",
    "thousands": ".",
    "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
    "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
    "infoPostFix": "",
    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
    "loadingRecords": "Cargando...",
    "lengthMenu": "Mostrar _MENU_ registros",
    "paginate": {
        "first": "<<",
        "last": ">>",
        "next": ">",
        "previous": ">"
    },
    "search": "Buscador:",
    "searchPlaceholder": "Buscar...",
    "emptyTable": "No hay registros para mostrar en la tabla",
},
    "columnDefs": [
            { targets: [0, 1], className: 'text-center' },  // Bootstrap
        ],        
    
    "lengthMenu": [5, 10, 25, 50],
    "pageLength": 10,
    "order": [[0, "asc"]],
    "searching": true,
    "paging": true,
    "info": false,
    "autoWidth": false,
    "responsive": true
});

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formUsu');
    const pass1 = document.getElementById('contrasena');
    const pass2 = document.getElementById('contrasena_conf');
    const mismatchFeedback = document.getElementById('passMismatchFeedback');
  
    form.addEventListener('submit', function (event) {
      // Verificación estándar de Bootstrap
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
  
      // Verificar que las contraseñas coincidan
      if (pass1.value !== pass2.value) {
        pass2.classList.add('is-invalid');
        mismatchFeedback.style.display = 'block';
        event.preventDefault();
        event.stopPropagation();
      } else {
        pass2.classList.remove('is-invalid');
        mismatchFeedback.style.display = 'none';
      }
    });
  });


  $(document).ready(function() {
    $('.deleteUsuario').on('click', function() {
        $('#borrarUsuario').modal('show');
        $tr = $(this).closest('tr');

        var datos = $tr.children("td").map(function() {
            return $(this).text();
        }).get();

        console.log(datos);
        $('#borrarUsuarioId').val(datos[0]);
        $('#deleteidUsu').text(datos[1]);
        $('#deleteNombreUsu').text(datos[2]);
    })
});