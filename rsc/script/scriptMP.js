
$(document).on('click', '#MP-lista tbody button.editbtn', function () { 
    $('#editarMP').modal('show');

    // Tomamos la fila "real" (si está en child row por modo responsive, subimos a la anterior)
    let $tr = $(this).closest('tr');                                      
    if ($tr.hasClass('child')) $tr = $tr.prev();                           

    var datos = $tr.children("td").map(function () {
        return $(this).text();
    }).get();

    console.log(datos);

    $('#editid').val(datos[0]);
    $('#editnombre').val(datos[1]);

    // ----- Stock mínimo: "200 kg" -----
    let stockmin = datos[2].trim();
    const idCat = datos[6];
    const categoria = categorias.find(cat => cat.idCatMP == idCat);
    $('#editcategoria').val(categoria ? categoria.nombreCatMP : 'Sin categoría');

    let Espacio1 = stockmin.lastIndexOf(" ");
    if (Espacio1 !== -1) {
        let num = stockmin.substring(0, Espacio1).trim();
        let med = stockmin.substring(Espacio1 + 1).trim();
        console.log(med)
        setTimeout(function () {
            $(`input[name="unidad_medida"][value="${med}"]`).prop("checked", true);
        }, 150);
        $('#editstockminimo').val(num);
    }

    // ----- Stock actual: "200 kg"  -----
    let stockact = datos[3].trim();
    let Espacio2 = stockact.lastIndexOf(" ");
    if (Espacio2 !== -1) {
        let numAct = stockact.substring(0, Espacio2).trim();             
        let medAct = stockact.substring(Espacio2 + 1).trim();             
        $('#editstockactual').val(numAct);
        
    }

    setTimeout(function () {
        $('#editMPproveedor').val(datos[5].trim());
    }, 100);

    // ----- Perecedero / vencimiento -----
   
    const rawNp = (datos[7] ?? '').toString().trim();
    const fv = (datos[8] ?? '').toString().trim();

    // El switch visible es "Perecedero": ON cuando no_perecedero == 1
    const esPerecedero = (rawNp === '1');                                 
    $('#edit_perecedero').prop('checked', esPerecedero);

    // Seteamos la fecha si viene y luego sincronizamos todo
    $('#edit_fecha_vencimiento').val(fv || '');
    syncPerecederoEdit();                                                  // <-- Mueve el estado de disabled/required/hidden

    
    $('#edit_perecedero').off('change').on('change', syncPerecederoEdit);  

    function syncPerecederoEdit() {
        const esPere = $('#edit_perecedero').is(':checked'); // ON => perecedero
        
        $('#edit_no_perecedero_hidden').val(esPere ? '0' : '1');

        // Fecha: obligatoria y habilitada sólo si es perecedero
        $('#edit_fecha_vencimiento')
            .prop('disabled', !esPere)
            .prop('required', esPere);

        if (!esPere) {
            $('#edit_fecha_vencimiento').val('');                       
        }
    }
});



$(document).on('click', '#MP-lista tbody button.verMPbtn', function () {   
    $('#verMP').modal('show');

    let $tr = $(this).closest('tr');                                       
    if ($tr.hasClass('child')) $tr = $tr.prev();                            

    var datos = $tr.children("td").map(function () {
        return $(this).text();
    }).get();

    console.log(datos);
    console.log(categorias);

    $('#verid').val(datos[0]);
    $('#vernombre').val(datos[1]);

    const idCat = datos[6];
    const categoria = categorias.find(cat => cat.idCatMP == idCat);
    $('#vercategoria').val(categoria ? categoria.nombreCatMP : 'Sin categoría');

    // ----- Stock mínimo -----
    let unimedstmin = datos[2].trim(); // Ej: "200 kg"
    let Espacio1 = unimedstmin.lastIndexOf(" ");
    if (Espacio1 !== -1) {
        let numstockmin = unimedstmin.substring(0, Espacio1).trim();
        let medmin = unimedstmin.substring(Espacio1 + 1).trim();
        console.log(numstockmin + " " + medmin)
        $('#verstockminimo').val(numstockmin + " " + medmin);
    }

    // ----- Stock actual  -----
    let unimedstact = datos[3].trim(); // Ej: "200 kg"
    let Espacio2 = unimedstact.lastIndexOf(" ");
    if (Espacio2 !== -1) {
        let numstockact = unimedstact.substring(0, Espacio2).trim();
        let medact = unimedstact.substring(Espacio2 + 1).trim();          
        $('#verstockactual').val(numstockact + " " + medact);
    }

    setTimeout(function () {
        $('#verMPproveedor').val(datos[5].trim());
    }, 100);

    // datos[7] y datos[8] vienen de las columnas ocultas
    const np2 = (datos[7] || '').trim(); 
    $('#ver_condicion').val(
        (np2 === '0') ? 'No perecedero' : 'Perecedero'
    );

    const fv2 = (datos[8] || '').trim();
    $('#ver_fecha_venc').val(fv2 ? fv2.split('-').reverse().join('/') : '—');
});



$(document).on('click', '#MP-lista tbody button.deletebtn', function () {  
    $('#borrarMP').modal('show');

    let $tr = $(this).closest('tr');                                      
    if ($tr.hasClass('child')) $tr = $tr.prev();                         

    var datos = $tr.children("td").map(function () {
        return $(this).text();
    }).get();

    console.log(datos);
    $('#deleteid').text(datos[0]);
    $('#borrarID').val(datos[0]);
    $('#deleteNombre').text(datos[1]);
    $('#deletestmin').text(datos[2]);
    $('#deletestact').text(datos[3]);
});


// === DataTable (sin cambios estructurales) ===
var table = new DataTable('#MP-lista', {
    dom: `
    <'row mb-2 align-items-end'
      <'col-md-4 text-start'l>
      <'col-md-4 text-start categoria-placeholder'>
      <'col-md-4 text-start'f>
    >
    rt
    <'row mt-2'
      <'col-md-6'i>
      <'col-md-6 text-end'p>
    >
  `,
    language: {
        "decimal": ",",
        "thousands": ".",
        "info": "Mostrando _END_ registros de un total de _TOTAL_",
        "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "infoPostFix": "",
        "infoFiltered": "(filtrado de un total de _MAX_ registros)",
        "loadingRecords": "Cargando...",
        "lengthMenu": "Mostrar _MENU_",
        "paginate": {
            "first": "<<",
            "last": ">>",
            "next": ">",
            "previous": "<"
        },
        "search": "Buscador:",
        "searchPlaceholder": "Buscar...",
        "emptyTable": "No hay registros para mostrar en la tabla",
    },
    "columnDefs": [
        { targets: [0, 2, 3], className: 'fw-bold text-center' },  // Bootstrap
        {
            targets: [2, 3], // La columna 2 (tercera columna)
            createdCell: function (td, cellData, rowData, row, col) {
                $(td).css('max-width', '100px'); // Define el ancho máximo
            }
        },
    ],
    "lengthMenu": [5, 10, 25, 50],
    "pageLength": 10,
    "order": [[0, "asc"]],
    "searching": true,
    "paging": true,
    "info": true,
    "autoWidth": false,
    "responsive": false
});

// Ubicamos el contenedor de categorías
$('.categoria-placeholder').html($('#contenedorCategoria'));

// Filtro por categoría (columna 6)
$('#filtroCategoria').on('change', function () {
    var selectedCategoria = $(this).val();

    if (selectedCategoria) {
        table.column(6).search('^' + selectedCategoria + '$', true, false).draw();
    } else {
        table.column(6).search('').draw();
    }
});
