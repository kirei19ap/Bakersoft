
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
            $('#editstockminimo').val(datos[2]);
            $('#editstockactual').val(datos[3]);
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

    $(document).ready(function() {
        $('.editbtnproveed').on('click', function() {
            $('#editarProveedor').modal('show');
            $tr = $(this).closest('tr');

            var datos = $tr.children("td").map(function() {
                return $(this).text();
            }).get();

            console.log(datos);

            $('#editidProve').val(datos[0]);
            $('#editnombreProve').val(datos[1]);
            $('#editdireccionProve').val(datos[2]);
            $('#editemailProve').val(datos[3]);
            $('#edittelefonoProve').val(datos[4]);
        })
    });

    $(document).ready(function() {
        $('.deletebtnProveed').on('click', function() {
            $('#borrarProveedor').modal('show');
            $tr = $(this).closest('tr');

            var datos = $tr.children("td").map(function() {
                return $(this).text();
            }).get();

            console.log(datos);
            $('#deleteidProve').text(datos[0]);
            $('#borrarProveedorId').val(datos[0]);
            $('#deleteNombreProve').text(datos[1]);
            $('#deletestemailProve').text(datos[3]);
            $('#deletetelefProve').text(datos[4]);
        })
    });