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