const proveedor = document.getElementById('proveedor');
const materiaPrimaSelect = document.getElementById('materiaPrima');
const tablaBody = document.querySelector('#tablaPedido tbody');
const generarPedidoBTN = document.getElementById('generarPedidoBtn');
const cancelarBtn = document.getElementById('cancelarPedidoBtn');
const agregarBTN = document.getElementById('agregarBTN');

var table = new DataTable ('#Pedidos', {
      language: {
          decimal: ",",
          thousands: ".",
          info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
          infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
          infoPostFix: "",
          infoFiltered: "(filtrado de un total de _MAX_ registros)",
          loadingRecords: "Cargando...",
          lengthMenu: "Mostrar _MENU_ registros",
          paginate: {
              first: "<<",
              last: ">>",
              next: ">",
              previous: "<"
          },
          search: "Buscador:",
          searchPlaceholder: "Buscar...",
          emptyTable: "No hay registros para mostrar en la tabla"
      },
      lengthMenu: [5, 10, 25, 50],
      pageLength: 10,
      order: [[0, "asc"]],
      searching: true,
      paging: true,
      info: false,
      autoWidth: false,
      responsive: true
  });

  if (proveedor) {
    proveedor.addEventListener('change', function(e) {
        var xhttp = new XMLHttpRequest();
      console.log(e.target.value);
        xhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let mps = JSON.parse(this.responseText);

                let mpDropdown = document.getElementById('materiaPrima');
                mpDropdown.innerHTML = '<option value="" disabled selected hidden>Seleccione una materia prima</option>';

                mps.forEach(function(c) {
                    var option = document.createElement('option');
                    option.text = c.nombre;
                    option.value = c.id;
                    option.setAttribute('data-unidad', c.unidad_medida);
                    option.setAttribute('data-stock', c.stockactual);
                    mpDropdown.appendChild(option);
                });
            }
        };

        xhttp.open("GET", "../controlador/traerMP.php?id_proveedor=" + e.target.value, true);
        xhttp.send();

        document.getElementById('proveedor').disabled = true;
    });
}


if(materiaPrimaSelect){
    materiaPrimaSelect.addEventListener('change',function(e){
    let selectedOption = e.target.options[e.target.selectedIndex];
    let unidad = selectedOption.getAttribute('data-unidad');
    let stock = selectedOption.getAttribute('data-stock');
    
    document.getElementById('unidad').value = unidad;
    document.getElementById('stockactual').value = stock;
});

window.addEventListener('DOMContentLoaded', () => {
  if (proveedorEnSesion) {
    // Simular la selección y disparar la carga de materias primas
    const selectProveedor = document.getElementById('proveedor');
    selectProveedor.value = proveedorEnSesion;

    // Disparar manualmente el evento para que cargue las materias primas
    const eventoChange = new Event('change');
    selectProveedor.dispatchEvent(eventoChange);
  }
});
}


// Agregar al pedido
if(agregarBTN){
agregarBTN.addEventListener('click', (e) => {
  e.preventDefault();
  const id = materiaPrimaSelect.value;
  const idprove = proveedor.value;
  const nombre = materiaPrimaSelect.options[materiaPrimaSelect.selectedIndex]?.text;
  const cantidad = document.getElementById('cantidad').value;

 if (!idprove || idprove === "-1") {
    return Swal.fire({
      icon: 'warning',
      title: 'Atención',
      text: 'Debe seleccionar un proveedor'
    });
  }

  if (!id) {
    return Swal.fire({
      icon: 'warning',
      title: 'Atención',
      text: 'Debe seleccionar una materia prima'
    });
  }

  if (!cantidad || isNaN(cantidad) || cantidad <= 0) {
    return Swal.fire({
      icon: 'error',
      title: 'Cantidad inválida',
      text: 'Por favor ingrese una cantidad válida mayor a 0'
    });
  }

  fetch('agregar_a_pedido.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ id, idprove, nombre, cantidad })
  }).then(() => {
    document.getElementById('cantidad').value = ''; // Limpiar el input
    cargarPedido();
  });
});
}

// Cargar pedido actual
function cargarPedido() {
  fetch('obtener_pedido.php')
    .then(res => res.json())
    .then(data => {
      tablaBody.innerHTML = '';
      data.forEach(item => {
        const row = tablaBody.insertRow();
        row.insertCell().textContent = item.nombre;
        row.insertCell().textContent = item.cantidad;
      });
    });
}

if(generarPedidoBTN){
generarPedidoBTN.addEventListener('click', () => {
  fetch('generar_pedido.php')
    .then(res => res.text())
    .then(msg => {
      const esExitoso = msg.includes('exitosamente');

      Swal.fire({
        icon: esExitoso ? 'success' : 'error',
        title: esExitoso ? 'Exito!' : 'Error',
        text: msg
      }).then(() => {
        if (esExitoso) {
          window.location.href = 'index.php'; // redirige al listado de pedidos
        }
      });

      if (!esExitoso) {
        cargarPedido(); // En caso de error, mantiene el pedido visible
      }
    });
});
}

//Cancela el pedido

  if (cancelarBtn) {
    console.log('Botón encontrado');

    cancelarBtn.addEventListener('click', () => {
      console.log('Click detectado');

      Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción cancelará el pedido definitivamente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, volver'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(`cancelarPedido.php?id=${pedidoId}`, { method: 'POST' })
            .then(res => res.text())
            .then(msg => {
              Swal.fire({
                icon: 'success',
                title: 'Pedido cancelado',
                text: msg
              }).then(() => {
                window.location.href = 'index.php';
              });
            })
            .catch(() => {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cancelar el pedido. Intente nuevamente.'
              });
            });
        }
      });
    });
  } else {
    console.warn('Botón cancelarPedidoBtn no encontrado');
  }
// Cargar pedido al inicio
cargarPedido();
