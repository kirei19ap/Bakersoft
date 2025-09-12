
$(document).on('click', '#Provedores-lista tbody .editbtnproveed', function () {
    $('#editarProveedor').modal('show');

    // Si la fila es "child" por responsive, subir a la anterior
    let $tr = $(this).closest('tr');
    if ($tr.hasClass('child')) $tr = $tr.prev();

    var datos = $tr.children("td").map(function() {
        return $(this).text();
    }).get();

    console.log(datos);

    $('#editidProve').val(datos[0]);
    $('#editnombreProve').val(datos[1]);

    // Dirección: "Calle Número"
    let direccion = (datos[2] || '').trim();
    let ultimoEspacio = direccion.lastIndexOf(" ");
    if (ultimoEspacio !== -1) {
        let calle  = direccion.substring(0, ultimoEspacio).trim();
        let numero = direccion.substring(ultimoEspacio + 1).trim();
        $('#editcalleprove').val(calle);
        $('#editalturaprove').val(numero);
    } else {
        // FIX: el id correcto es #editcalleprove (todo minúsculas)
        $('#editcalleprove').val(direccion); // <--- ARREGLO de ID
        $('#editalturaprove').val('');
    }

    $('#editemailProve').val(datos[3]);
    $('#edittelefonoProve').val(datos[4]);

    // Provincia (col 6) e inicializar localidades vía AJAX
    let idProvincia  = (datos[6] || '').toString().trim();
    let idLocalidad  = (datos[5] || '').toString().trim();
    $('#editprovProve').val(idProvincia);

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let ownCities = JSON.parse(this.responseText);
            let cityDropdown = document.getElementById('editlocprove');
            cityDropdown.innerText = ''; // limpiar

            ownCities.forEach(function (c) {
                var option = document.createElement('option');
                option.text  = c.localidad;
                option.value = c.id_localidad;
                if (String(c.id_localidad) == String(idLocalidad)) {
                    option.selected = true;
                }
                cityDropdown.appendChild(option);
            });
        }
    };
    xhttp.open("GET", "../controlador/traerLocalidades.php?id_provincia=" + idProvincia, true);
    xhttp.send();
});

// Delegación para Ver
$(document).on('click', '#Provedores-lista tbody .verbtnproveed', function () {
    $('#verProveedor').modal('show');

    let $tr = $(this).closest('tr');
    if ($tr.hasClass('child')) $tr = $tr.prev();

    var datos = $tr.children("td").map(function() {
        return $(this).text();
    }).get();

    console.log(datos);

    $('#veridProve').val(datos[0]);
    $('#vernombreProve').val(datos[1]);

    let direccion = (datos[2] || '').trim();
    let ultimoEspacio = direccion.lastIndexOf(" ");
    if (ultimoEspacio !== -1) {
        let calle  = direccion.substring(0, ultimoEspacio).trim();
        let numero = direccion.substring(ultimoEspacio + 1).trim();
        $('#vercalleprove').val(calle);
        $('#veralturaprove').val(numero);
    } else {
        $('#vercalleprove').val(direccion);
        $('#veralturaprove').val('');
    }

    $('#veremailProve').val(datos[3]);
    $('#vertelefonoProve').val(datos[4]);

    // Provincia / Localidad
    let idProvincia = (datos[6] || '').toString().trim();
    let idLocalidad = (datos[5] || '').toString().trim();
    $('#verprovProve').val(idProvincia);

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let ownCities = JSON.parse(this.responseText);
            let cityDropdown = document.getElementById('verlocprove');
            cityDropdown.innerText = ''; // limpiar

            ownCities.forEach(function (c) {
                var option = document.createElement('option');
                option.text  = c.localidad;
                option.value = c.id_localidad;
                if (String(c.id_localidad) == String(idLocalidad)) {
                    option.selected = true;
                }
                cityDropdown.appendChild(option);
            });
        }
    };
    xhttp.open("GET", "../controlador/traerLocalidades.php?id_provincia=" + idProvincia, true);
    xhttp.send();
});


//Esta funcion actualiza el select de localidades dentro del modal

document.getElementById('editprovProve').addEventListener('change', function(e) {
    var xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let ownCities = JSON.parse(this.responseText);

            let cityDropdown = document.getElementById('editlocprove');
            cityDropdown.innerText = null;

            ownCities.forEach(function (c) {
                var option = document.createElement('option');
                option.text = c.localidad;
                option.value = c.id_localidad;
                cityDropdown.appendChild(option);
            });
        }
    };
    xhttp.open("GET", "../controlador/traerLocalidades.php?id_provincia=" + e.target.value, true);
    xhttp.send();
});

// Delegación para Eliminar
$(document).on('click', '#Provedores-lista tbody .deletebtnProveed', function () {
    $('#borrarProveedor').modal('show');

    let $tr = $(this).closest('tr');
    if ($tr.hasClass('child')) $tr = $tr.prev();

    var datos = $tr.children("td").map(function() {
        return $(this).text();
    }).get();

    console.log(datos);
    $('#deleteidProve').text(datos[0]);
    $('#borrarProveedorId').val(datos[0]);
    $('#deleteNombreProve').text(datos[1]);
    $('#deletestemailProve').text(datos[3]);
    $('#deletetelefProve').text(datos[4]);
});


var table = new DataTable('#Provedores-lista', {
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
    "search": "Buscador:",
    "searchPlaceholder": "Buscar...",
    "emptyTable": "No hay registros para mostrar en la tabla",
},
    
    "lengthMenu": [5, 10, 25, 50],
    "pageLength": 10,
    "order": [[0, "asc"]],
    "searching": true,
    "paging": true,
    "info": true,
    "autoWidth": false,
    "responsive": true
});

document.getElementById('provincia').addEventListener('change', function(e) {
    var xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let ownCities = JSON.parse(this.responseText);

            let cityDropdown = document.getElementById('localidad');
            cityDropdown.innerText = null;

            ownCities.forEach(function (c) {
                var option = document.createElement('option');
                option.text = c.localidad;
                option.value = c.id_localidad;
                cityDropdown.appendChild(option);
            });
        }
    };
    xhttp.open("GET", "../controlador/traerLocalidades.php?id_provincia=" + e.target.value, true);
    xhttp.send();
});