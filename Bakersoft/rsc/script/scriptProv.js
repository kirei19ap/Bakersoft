
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
        let direccion = datos[2].trim(); // Ej: "Av. Siempre Viva 742"

        let ultimoEspacio = direccion.lastIndexOf(" ");
        if (ultimoEspacio !== -1) {
        let calle = direccion.substring(0, ultimoEspacio).trim();
        let numero = direccion.substring(ultimoEspacio + 1).trim();
        $('#editcalleprove').val(calle);
        $('#editalturaprove').val(numero);
        } else {
        // Si no hay espacio, asumimos que no hay número
        $('#editcalleProve').val(direccion);
        $('#editalturaprove').val('');
        }
        $('#editemailProve').val(datos[3]);
        $('#edittelefonoProve').val(datos[4]);
        $('#editprovProve').val(datos[6].toString().trim());
        
        let idLocalidad = datos[5].toString().trim();
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
                if (c.id_localidad == idLocalidad) {
                    option.selected = true;
                  }
                cityDropdown.appendChild(option);
            });
        }
    }; 
    xhttp.open("GET", "../controlador/traerLocalidades.php?id_provincia=" + datos[6].toString(), true);
    xhttp.send();
    })
});

$(document).ready(function() {
    $('.verbtnproveed').on('click', function() {
        $('#verProveedor').modal('show');
        $tr = $(this).closest('tr');

        var datos = $tr.children("td").map(function() {
            return $(this).text();
        }).get();

        console.log(datos);

        $('#veridProve').val(datos[0]);
        $('#vernombreProve').val(datos[1]);
        let direccion = datos[2].trim(); // Ej: "Av. Siempre Viva 742"

        let ultimoEspacio = direccion.lastIndexOf(" ");
        if (ultimoEspacio !== -1) {
        let calle = direccion.substring(0, ultimoEspacio).trim();
        let numero = direccion.substring(ultimoEspacio + 1).trim();
        $('#vercalleprove').val(calle);
        $('#veralturaprove').val(numero);
        } else {
        // Si no hay espacio, asumimos que no hay número
        $('#vercalleprove').val(direccion);
        $('#veralturaprove').val('');
        }
        $('#veremailProve').val(datos[3]);
        $('#vertelefonoProve').val(datos[4]);
        $('#verprovProve').val(datos[6].toString().trim());
        
        let idLocalidad = datos[5].toString().trim();
        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let ownCities = JSON.parse(this.responseText);

            let cityDropdown = document.getElementById('verlocprove');
            cityDropdown.innerText = null;

            ownCities.forEach(function (c) {
                var option = document.createElement('option');
                option.text = c.localidad;
                option.value = c.id_localidad;
                if (c.id_localidad == idLocalidad) {
                    option.selected = true;
                  }
                cityDropdown.appendChild(option);
            });
        }
    }; 
    xhttp.open("GET", "../controlador/traerLocalidades.php?id_provincia=" + datos[6].toString(), true);
    xhttp.send();
    })
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

var table = new DataTable('#Provedores-lista', {
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
    
    "lengthMenu": [5, 10, 25, 50],
    "pageLength": 10,
    "order": [[0, "asc"]],
    "searching": true,
    "paging": true,
    "info": false,
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