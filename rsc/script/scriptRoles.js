// DataTable
var tablaRoles = new DataTable('#Roles-lista', {
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
    order: [[1,'asc']],
    searching: true, paging: true, info: true, autoWidth: false, responsive: true
  });
  
  // EDITAR (delegación)
  $(document).on('click', '#Roles-lista tbody .editRol', function(){
    let $tr = $(this).closest('tr'); if ($tr.hasClass('child')) $tr = $tr.prev();
    const d = $tr.children('td').map(function(){ return $(this).text().trim(); }).get();
    // d[0]=id(hidden), d[1]=nombre
    $('#edit_idrol').val(d[0]);
    $('#edit_nombrerol').val(d[1]);
    $('#editarRol').modal('show');
  });
  
  // VER (delegación)
  $(document).on('click', '#Roles-lista tbody .verRol', function(){
    let $tr = $(this).closest('tr'); if ($tr.hasClass('child')) $tr = $tr.prev();
    const d = $tr.children('td').map(function(){ return $(this).text().trim(); }).get();
    $('#ver_nombrerol').val(d[1]);
    $('#verRol').modal('show');
  });
  
  // ELIMINAR (delegación)
  $(document).on('click', '#Roles-lista tbody .deleteRol', function(){
    let $tr = $(this).closest('tr'); if ($tr.hasClass('child')) $tr = $tr.prev();
    const d = $tr.children('td').map(function(){ return $(this).text().trim(); }).get();
    $('#del_idrol').val(d[0]);
    $('#del_nombrerol').text(d[1]);
    $('#borrarRol').modal('show');
  });
  