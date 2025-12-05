var table = new DataTable('#MP-lista', {
    language: {
    "decimal": ",",
    "thousands": ".",
    "info": "Mostrando _END_ registros de un total de _TOTAL_",
    "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
    "infoPostFix": "",
    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
    "loadingRecords": "Cargando...",
    "lengthMenu": "Mostrar _MENU_ registros",
    "paginate": {
        "first": "<<",
        "last": ">>",
        "next": ">",
        "previous": "<"
    },
    "emptyTable": "No hay registros para mostrar en la tabla",
},       
    "lengthMenu": [5, 10, 25, 50],
    "pageLength": 10,
    "order": [[0, "asc"]],
    "searching": false,
    "paging": true,
    "info": true,
    "autoWidth": false,
    "responsive": true
});

document.addEventListener('DOMContentLoaded', () => {
    const tipoRadios = document.querySelectorAll('input[name="tipoPedido"]');
    const filtrosMP = document.getElementById('filtrosMP');
    const filtrosClientes = document.getElementById('filtrosClientes');

    if (!tipoRadios.length) return;

    tipoRadios.forEach(r => {
        r.addEventListener('change', () => {
            if (r.value === 'mp') {
                filtrosMP.classList.remove('d-none');
                filtrosClientes.classList.add('d-none');
            } else {
                filtrosClientes.classList.remove('d-none');
                filtrosMP.classList.add('d-none');
            }
        });
    });
});
