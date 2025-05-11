
    $(document).ready(function() {
        $('.editbtn').on('click', function() {
            $('#editarMP').modal('show');
            $tr = $(this).closest('tr');

            var datos = $tr.children("td").map(function() {
                return $(this).text();
            }).get();

            console.log(datos);

            $('#editid').val(datos[0]);
            $('#editnombre').val(datos[1]);
            let stockmin = datos[2].trim(); // Ej: "200 kg"

            let Espacio1 = stockmin.lastIndexOf(" ");
            if (Espacio1 !== -1) {
            let num = stockmin.substring(0, Espacio1).trim();
            let med = stockmin.substring(Espacio1 + 1).trim();
                console.log(med)
                setTimeout(function() {
                $(`input[name="unidad_medida"][value="${med}"]`).prop("checked", true);
            }, 150);
                $('#editstockminimo').val(num);
            }
            let stockact = datos[3].trim(); // Ej: "200 kg"

            let Espacio2 = stockact.lastIndexOf(" ");
            if (Espacio2 !== -1) {
            let num = stockact.substring(0, Espacio1).trim();
            let med = stockact.substring(Espacio1 + 1).trim();
                $('#editstockactual').val(num);
            }

            setTimeout(function() {
                $('#editMPproveedor').val(datos[5].trim());
            }, 100);
        })
    });

    $(document).ready(function() {
        $('.verMPbtn').on('click', function() {
            $('#verMP').modal('show');
            $tr = $(this).closest('tr');

            var datos = $tr.children("td").map(function() {
                return $(this).text();
            }).get();

            console.log(datos);

            $('#verid').val(datos[0]);
            $('#vernombre').val(datos[1]);

            let unimedstmin = datos[2].trim(); // Ej: "200 kg"

            let Espacio1 = unimedstmin.lastIndexOf(" ");
            if (Espacio1 !== -1) {
            let numstockmin = unimedstmin.substring(0, Espacio1).trim();
            let medmin = unimedstmin.substring(Espacio1 + 1).trim();
                console.log(numstockmin + " " + medmin)
            $('#verstockminimo').val(numstockmin + " " + medmin);
            }
            let unimedstact = datos[3].trim(); // Ej: "200 kg"

            let Espacio2 = unimedstact.lastIndexOf(" ");
            if (Espacio2 !== -1) {
            let numstockact = unimedstact.substring(0, Espacio2).trim();
            let medact = unimedstmin.substring(Espacio1 + 1).trim();
            $('#verstockactual').val(numstockact + " " + medact);}
            setTimeout(function() {
                $('#verMPproveedor').val(datos[5].trim());
            }, 100);
        })
    });

    $(document).ready(function() {
        $('.deletebtn').on('click', function() {
            $('#borrarMP').modal('show');
            $tr = $(this).closest('tr');

            var datos = $tr.children("td").map(function() {
                return $(this).text();
            }).get();

            console.log(datos);
            $('#deleteid').text(datos[0]);
            $('#borrarID').val(datos[0]);
            $('#deleteNombre').text(datos[1]);
            $('#deletestmin').text(datos[2]);
            $('#deletestact').text(datos[3]);
        })
    });

    
    var table = new DataTable('#MP-lista', {
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
            { targets: [0, 2, 3], className: 'fw-bold text-center' },  // Bootstrap
            {targets: [2, 3], // La columna 2 (tercera columna)
                createdCell: function(td, cellData, rowData, row, col) {
                $(td).css('max-width', '100px'); // Define el ancho máximo
                }
            },
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

    
   