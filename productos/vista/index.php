<?php
$currentPage = 'productos';
include_once("../../includes/head_app.php");
require_once("../controlador/controladorProductos.php");
$ctrl = new ControladorProducto();
#$ctrl->index();
?>
<style>
  /* desactiva el scroll horizontal SOLO en Productos */
  #wrapProductos.table-responsive {
    overflow-x: visible;
    /* o 'unset'/'clip' según tu preferencia */
  }

  /* ===== Botones de icono ONLY para Productos ===== */

  /* Área clickeable uniforme */
  #modProductos .btn-icon,
  #modalCrearProducto .btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    /* ancho fijo */
    height: 42px;
    /* alto fijo */
    padding: 0;
    /* sin paddings que deformen */
    border-radius: .50rem;
    /* suave */
  }

  /* Tamaño del icono */
  #modProductos .btn-icon ion-icon,
  #modalCrearProducto .btn-icon ion-icon {
    font-size: 1.45rem;
    /* + grande para mejor lectura */
    line-height: 1;
  }

  /* Opcional - que no encoja en la tabla */
  #tablaComposicion .btn-icon {
    min-width: 42px;
  }

  /* Responsive pequeño */
  @media (max-width: 576px) {

    #modProductos .btn-icon,
    #modalCrearProducto .btn-icon {
      width: 38px;
      height: 38px;
    }

    #modProductos .btn-icon ion-icon,
    #modalCrearProducto .btn-icon ion-icon {
      font-size: 1.45rem;
    }
  }

  /* ===== Scope Productos ===== */
  #wrapProductos.table-responsive {
    overflow-x: visible;
  }

  /* sin scroll */

  #tablaProductos {
    width: 100% !important;
    font-size: .95rem;
    /* misma escala que Proveedores */
  }

  #tablaProductos thead th {
    font-weight: 600;
    text-transform: none;
    /* evita mayúsculas forzadas en algún theme */
  }

  #tablaProductos th,
  #tablaProductos td {
    padding: .5rem .75rem;
    /* compacta tipo 'table-sm' de Proveedores */
    vertical-align: middle;
    white-space: normal;
    word-break: break-word;
  }

  #tablaProductos tbody tr:hover {
    background-color: rgba(0, 0, 0, .03);
    /* hover suave como en Proveedores */
  }

  /* Si alguna columna se supercompacta, evita saltos raros en Unidad/Estado */
  #tablaProductos .text-nowrap {
    white-space: nowrap;
  }

  /* Controles: alineación y tamaños "tipo proveedores" */
  #tablaProductosWrap .dataTables_length label,
  #tablaProductosWrap .dataTables_filter label {
    margin-bottom: 0;
    font-size: .95rem;
  }

  #tablaProductosWrap .dataTables_length select,
  #tablaProductosWrap .dataTables_filter input {
    font-size: .95rem;
    padding: .25rem .5rem;
    height: calc(1.5em + .5rem + 2px);
  }

  /* Tabla: densidad y comportamiento de texto como en Proveedores */
  #wrapProductos {
    overflow-x: visible;
  }

  /* sin scroll horizontal */
  #tablaProductos {
    width: 100% !important;
    font-size: .95rem;
  }

  #tablaProductos th,
  #tablaProductos td {
    padding: .5rem .75rem;
    vertical-align: middle;
    white-space: normal;
    word-break: break-word;
  }

  #tablaProductos tbody tr:hover {
    background-color: rgba(0, 0, 0, .03);
  }

  /* Si en Proveedores el header resalta un poco más: */
  #tablaProductos thead th {
    font-weight: 600;
  }

  /* Wrapper consistente, sin “card” */
  #wrapProductos {
    overflow-x: visible;
  }

  /* nada de scroll horizontal */

  /* Densidad y legibilidad como Proveedores */
  #tablaProductos {
    width: 100% !important;
    font-size: .95rem;
  }

  #tablaProductos th,
  #tablaProductos td {
    padding: .5rem .75rem;
    vertical-align: middle;
    white-space: normal;
    word-break: break-word;
  }

  #tablaProductos thead th {
    font-weight: 600;
  }

  #tablaProductos tbody tr:hover {
    background-color: rgba(0, 0, 0, .03);
  }

  /* Controles: tamaños y alineación */
  #prod-controls-left .dataTables_length label,
  #prod-controls-right .dataTables_filter label {
    margin-bottom: 0;
    font-size: .95rem;
  }

  #prod-controls-left .dataTables_length select {
    font-size: .875rem;
  }

  #prod-controls-right .dataTables_filter input {
    font-size: .875rem;
  }

  /* Contenedor derecho: no envolver a otra línea */
  #prod-controls-right {
    display: flex;
    justify-content: end;
    align-items: center;
    flex-wrap: nowrap;
  }

  /* El contenedor que DataTables genera al mover el filtro */
  #prod-controls-right .dataTables_filter {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    width: 100%;
  }

  /* El label del filtro: en línea, sin margen inferior */
  #prod-controls-right .dataTables_filter label {
    display: flex !important;
    align-items: center;
    gap: .5rem;
    margin-bottom: 0 !important;
  }

  /* Input group del buscador (el que rearmamos en JS) */
  #prod-controls-right .dt-search-group {
    max-width: 340px;
    width: 100%;
  }

  /* El input: que crezca dentro del grupo y no “salte” */
  #prod-controls-right .dt-search-group .form-control {
    flex: 1 1 auto;
    min-width: 0;
    /* evita que el flex rompa a otra línea */
  }

  /* Selector de cantidad a la izquierda, compactito */
  #prod-controls-left .dataTables_length label {
    margin-bottom: 0;
  }

  #prod-controls-left .dataTables_length select {
    font-size: .875rem;
    padding: .25rem .5rem;
    height: calc(1.5em + .5rem + 2px);
  }

  .ion-fuente ion-icon {
    font-size: 20px;
  }

  .ion-fuente button {
    width: 35px;
    height: 35px;
    padding: 5px;
  }

  /* Distribución y compactación generales */
  #tablaProductos {
    table-layout: fixed;
    /* reparte mejor el ancho */
    width: 100%;
  }

  #tablaProductos th,
  #tablaProductos td {
    vertical-align: middle;
    padding: 10px 12px;
    white-space: nowrap;
    /* evita saltos feos */
    overflow: hidden;
    text-overflow: ellipsis;
    /* … para textos largos */
  }

  /* Anchos equilibrados por columna (Nombre, Categoría, Unidad, Estado, Acciones) */
  #tablaProductos th:nth-child(1),
  #tablaProductos td:nth-child(1) {
    width: 38%;
    text-align: left;
  }

  /* Nombre */
  #tablaProductos th:nth-child(2),
  #tablaProductos td:nth-child(2) {
    width: 27%;
    text-align: left;
  }

  /* Categoría */
  #tablaProductos th:nth-child(3),
  #tablaProductos td:nth-child(3) {
    width: 12%;
    text-align: center;
  }

  /* Unidad */
  #tablaProductos th:nth-child(4),
  #tablaProductos td:nth-child(4) {
    width: 11%;
    text-align: center;
  }

  /* Estado */
  #tablaProductos th:nth-child(5),
  #tablaProductos td:nth-child(5) {
    width: 12%;
    text-align: right;
  }

  /* Acciones */

  /* Botonera de acciones más prolija */
  #tablaProductos td:nth-child(5) .btn,
  #tablaProductos td:nth-child(5) button {
    margin-left: 6px;
  }

  #tablaProductos td:nth-child(5) .btn:first-child {
    margin-left: 0;
  }

  /* Ajuste fino en pantallas chicas */
  @media (max-width: 576px) {

    #tablaProductos th:nth-child(1),
    #tablaProductos td:nth-child(1) {
      width: 42%;
    }

    #tablaProductos th:nth-child(2),
    #tablaProductos td:nth-child(2) {
      width: 26%;
    }

    #tablaProductos th:nth-child(3),
    #tablaProductos td:nth-child(3),
    #tablaProductos th:nth-child(4),
    #tablaProductos td:nth-child(4) {
      width: 10%;
    }

    #tablaProductos th:nth-child(5),
    #tablaProductos td:nth-child(5) {
      width: 12%;
    }
  }

  /* Contenedor y tabla del modal Ver */
  #tablaComposicionVer {
    table-layout: fixed;
    width: 100%;
    font-size: 0.92rem;
  }

  #tablaComposicionVer th,
  #tablaComposicionVer td {
    padding: 8px 10px;
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Materia prima / Cantidad */
  #tablaComposicionVer th:nth-child(1),
  #tablaComposicionVer td:nth-child(1) {
    width: 64%;
    /* más espacio al nombre */
    text-align: left;
  }

  #tablaComposicionVer th:nth-child(2),
  #tablaComposicionVer td:nth-child(2) {
    width: 36%;
    text-align: right;
    /* números alineados a la derecha */
  }

  /* Línea separadora suave */
  #tablaComposicionVer tbody tr {
    border-bottom: 1px solid #ececec;
  }

  .badge.bg-success {
    background-color: #28a745 !important;
    margin-left: 20px;
  }

  .badge.bg-secondary {
    background-color: #6c757d !important;
    margin-left: 20px;
  }
</style>
<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Productos</h1>
</div>
<div class="contenido-principal">

  <div class="encabezado-tabla">
    <div class="mb-3 buscador">
            <h3 class="mb-0 text-muted">Listado de productos para la venta.</h3>
        </div>
    <div>
      <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->
      <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearProducto">
        Registrar Producto
      </button>
    </div>
  </div>
  <div class=""><?php
                if (!empty($_SESSION['error_valida_proveedor'])):
                  $mensaje = $_SESSION['error_valida_proveedor'];
                  unset($_SESSION['error_valida_proveedor']);
                ?>
      <script>
        console.log("<?php echo $mensaje; ?>");
        Swal.fire({
          icon: 'warning',
          title: 'Error al crear',
          text: '<?php echo $mensaje; ?>',
          confirmButtonText: 'Aceptar'
        });
      </script>
    <?php endif; ?>
  </div>
  <div class=""><?php
                if (!empty($_SESSION['error_borra_proveedor'])):
                  $mensaje = $_SESSION['error_borra_proveedor'];
                  unset($_SESSION['error_borra_proveedor']);
                ?>
      <script>
        console.log("<?php echo $mensaje; ?>");
        Swal.fire({
          icon: 'error',
          title: 'Error al eliminar',
          text: '<?php echo $mensaje; ?>',
          confirmButtonText: 'Aceptar'
        });
      </script>
    <?php endif; ?>
  </div>


  <div class="contenido">
    <div class="card">
      <div class="card-body">
        <div class="tabla-empleados">

          <!-- ======= Productos (contenido principal) ======= -->
          <div class="container-fluid py-3">
            <?php if (!empty($_SESSION['flash_success'])): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8');
                unset($_SESSION['flash_success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8');
                unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <section id="tablaProductosWrap" class="tabla-wrap">
              <!-- Controles: selector (izq) + buscador (der) -->
              <div class="tabla-controls row g-2 align-items-center mb-2">
                <div class="col-12 col-md-6" id="prod-controls-left"></div>
                <div class="col-12 col-md-6 d-flex justify-content-md-end" id="prod-controls-right"></div>
              </div>

              <div class="table-responsive" id="wrapProductos">
                <table class="table table-striped table-sm table-hover table-bordered align-middle" id="tablaProductos">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Nombre</th>
                      <th>Categoría</th>
                      <th>Unidad</th>
                      <th>Estado</th>
                      <th class="text-center">Acciones</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- ======= /Productos (contenido principal) ======= -->

  <!-- ======= Modal Registrar Producto ======= -->
  <div class="modal fade" id="modalCrearProducto" tabindex="-1" aria-labelledby="lblCrearProducto" aria-hidden="true">
    <div class="modal-dialog modal-lg"><!-- ancho mayor por la tabla de composición -->
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="lblCrearProducto">Registrar Producto</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <form action="./crear.php" method="post" id="formCrearProducto" novalidate>
            <div class="mb-3">
              <label for="nombreProducto" class="form-label">Nombre del producto</label>
              <input type="text" class="form-control" id="nombreProducto" name="nombre" required>
              <div class="invalid-feedback">Ingresá un nombre válido.</div>
              <div class="form-text" id="textoNombreDuplicado" style="display:none;color:#dc3545;">
                Ya existe un producto con ese nombre.
              </div>
            </div>

            <div class="row g-3">
              <div class="col-md-4">
                <label for="categoriaProd" class="form-label">Categoría / Tipo</label>
                <select class="form-select" id="categoriaProd" name="categoriaProd" required>
                  <option value=""></option>
                  <!-- se carga por AJAX: productos/categorias.php -->
                </select>
                <div class="invalid-feedback">Seleccioná una categoría.</div>
              </div>
              <div class="col-md-5">
                <label for="unidadMedida" class="form-label">Unidad de medida</label>
                <select class="form-select" id="unidadMedida" name="unidad_medida" required>
                  <option value=""></option>
                  <option value="unid">Unidad</option>
                  <option value="kg">Kg</option>
                  <option value="g">g</option>
                  <option value="docena">Docena</option>
                </select>
                <div class="invalid-feedback">Seleccioná una unidad.</div>
              </div>
              <div class="col-md-3">
                <label for="precioVenta" class="form-label">Precio de venta</label>
                <input type="number" class="form-control" id="precioVenta" name="precio_venta" min="0" step="0.01" required>
                <div class="invalid-feedback">Ingresá un precio válido.</div>
              </div>

            </div>

            <div class="mt-3">
              <label for="descripcion" class="form-label">Descripción (opcional)</label>
              <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
            </div>

            <hr class="my-4">

            <h6 class="mb-3">Composición (materias primas)</h6>
            <div class="row g-2 align-items-end">
              <div class="col-md-6">
                <label class="form-label" for="mpSelect">Materia prima</label>
                <select class="form-select" id="mpSelect">
                  <option value=""></option>
                  <!-- se carga por AJAX: productos/materias.php -->
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="mpCantidad">Cantidad</label>
                <input type="number" step="1" min="1" class="form-control" id="mpCantidad" placeholder="0">
              </div>

              <div class="col-md-1 d-grid">
                <button type="button" class="btn btn-success btn-icon" id="btnAgregarMP" title="Agregar">
                  <ion-icon name="add-circle-outline"></ion-icon>
                </button>
              </div>
            </div>

            <div class="table-responsive mt-3">
              <table class="table table-sm table-striped align-middle" id="tablaComposicion">
                <thead class="table-light">
                  <tr>
                    <th>Materia prima</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody><!-- filas dinámicas --></tbody>
              </table>
            </div>

            <input type="hidden" name="componentes" id="componentesJson" value="[]">
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button class="btn btn-primary" id="btnGuardarProducto">Guardar</button>
        </div>
      </div>
    </div>
  </div>
  <!-- ======= /Modal Registrar Producto ======= -->

  <!-- Modal Ver Producto (solo lectura) -->
  <div class="modal fade" id="modalVerProducto" tabindex="-1" aria-labelledby="lblVerProducto" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="lblVerProducto">Detalle del producto<span id="badgeEstadoVer" class="badge"></span></h1>
          <button class="btn btn-icon btn-secondary" data-bs-dismiss="modal" aria-label="Close">
            <ion-icon name="close"></ion-icon>
          </button>
        </div>
        <div class="modal-body">
          <form id="formVerProducto">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" id="ver_nombre" disabled>
              </div>
              <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <input type="text" class="form-control" id="ver_categoria" disabled>
              </div>
              <div class="col-md-3">
                <label class="form-label">Unidad</label>
                <input type="text" class="form-control" id="ver_unidad" disabled>
              </div>
              <div class="col-md-2">
                <label class="form-label">Precio</label>
                <input type="text" class="form-control" id="ver_precio" disabled>
              </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Composición</h6>
            <div class="table-responsive mt-1">
              <table class="table table-sm table-striped align-middle" id="tablaComposicionVer">
                <thead class="table-light">
                  <tr>
                    <th>Materia prima</th>
                    <th class="text-center">Cantidad</th>
                  </tr>
                </thead>
                <tbody><!-- filas dinámicas --></tbody>
              </table>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Editar Producto -->
  <div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="lblEditarProducto" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="lblEditarProducto">Editar producto<span id="badgeEstadoEdit" class="badge"></span></h1>
          <button class="btn btn-icon btn-secondary" data-bs-dismiss="modal" aria-label="Close">
            <ion-icon name="close"></ion-icon>
          </button>
        </div>
        <div class="modal-body">
          <form action="/productos/editar.php" method="post" id="formEditarProducto" novalidate>
            <input type="hidden" name="idProducto" id="edit_idProducto">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label" for="edit_nombre">Nombre</label>
                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                <div class="form-text" id="edit_nombre_dup" style="display:none">Ya existe un producto con ese nombre.</div>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="edit_categoria">Categoría</label>
                <select class="form-select" id="edit_categoria" name="categoria" required>
                  <option value=""></option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="edit_unidad">Unidad</label>
                <select class="form-select" id="edit_unidad" name="unidad" required>
                  <option value=""></option>
                  <option value="unidad">Unidad</option>
                  <option value="kg">Kg</option>
                  <option value="docena">Docena</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="edit_precio">Precio</label>
                <input type="number" class="form-control" id="edit_precio" name="precio_venta" step="0.01" min="0" required>
              </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Composición</h6>
            <div class="row g-2 align-items-end">
              <div class="col-md-7">
                <label class="form-label" for="edit_mpSelect">Materia prima</label>
                <select class="form-select" id="edit_mpSelect">
                  <option value=""></option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label" for="edit_mpCantidad">Cantidad</label>
                <input type="number" step="1" min="1" class="form-control" id="edit_mpCantidad" placeholder="1">
              </div>
              <div class="col-md-1 d-grid">
                <button type="button" class="btn btn-primary btn-icon" id="btnAgregarMPEdit" title="Agregar">
                  <ion-icon name="add-circle-outline"></ion-icon>
                </button>
              </div>
            </div>

            <div class="table-responsive mt-2">
              <table class="table table-sm table-striped align-middle" id="tablaComposicionEdit">
                <thead class="table-light">
                  <tr>
                    <th>Materia prima</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody><!-- dinámico --></tbody>
              </table>
            </div>

            <input type="hidden" name="componentes" id="componentesJsonEdit" value="[]">
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button class="btn btn-primary" id="btnGuardarEdit">Guardar cambios</button>
        </div>
      </div>
    </div>
  </div>


  <!-- Modal Confirmación Genérico -->
  <div class="modal fade" id="modalConfirmar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 id="confirmTitulo" class="modal-title">Confirmar acción</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p id="confirmMensaje">¿Estás seguro de continuar?</p>
          <input type="hidden" id="confirmIdProducto">
          <input type="hidden" id="confirmAccion"> <!-- 'activar' | 'desactivar' -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="btnConfirmarAccion" class="btn btn-primary">Confirmar</button>
        </div>
      </div>
    </div>
  </div>


</div>

</div>


<?php
require_once("foot/foot.php")
?>