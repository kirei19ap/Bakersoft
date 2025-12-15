<?php
// mignon/controlador/controladorMignon.php

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../pedidos/modelo/modeloPedidos.php';
require_once '../../materiaprima/modelo/modeloMP.php';



// Creamos una instancia para que Mignon pueda consultar pedidos
$pedidosModel = new modeloPedidos();
$mpModel = new modeloMP();


// ===============================
// Helpers generales
// ===============================

/**
 * Normaliza texto:
 * - Pasa a minúsculas
 * - Saca tildes
 * - Recorta espacios extra
 */
function mignon_normalizar_texto(string $texto): string
{
    $texto = trim($texto);
    $texto = mb_strtolower($texto, 'UTF-8');

    // Eliminar tildes
    $originales = ['á', 'é', 'í', 'ó', 'ú', 'ñ'];
    $reemplazos = ['a', 'e', 'i', 'o', 'u', 'n'];
    $texto = str_replace($originales, $reemplazos, $texto);

    return $texto;
}
/**
 * Obtiene el nombre corto desde $_SESSION['nomyapellido'].
 * Si el valor no existe o viene vacío, devuelve "panadero".
 */
function mignon_obtener_nombre_desde_sesion(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!empty($_SESSION['nomyapellido'])) {
        $nombreCompleto = trim($_SESSION['nomyapellido']);

        // Separar por espacios
        $partes = preg_split('/\s+/', $nombreCompleto);

        // Si hay al menos 1 palabra, devolvemos la primera (nombre)
        if (!empty($partes[0])) {
            return ucfirst(strtolower($partes[0]));
        }
    }

    // Fallback
    return 'panadero';
}


/**
 * Detecta un "intent" básico a partir del mensaje normalizado
 */
function mignon_detectar_intent(string $textoNorm): string
{
    // Consultas sobre pedidos de clientes
    if (strpos($textoNorm, 'pedido') !== false) {

        // 1) HOY + PREPARACIÓN
        if (
            strpos($textoNorm, 'hoy') !== false &&
            (strpos($textoNorm, 'preparacion') !== false || strpos($textoNorm, 'preparando') !== false)
        ) {
            return 'pedidos_en_preparacion_hoy';
        }

        // 2) HOY + PREPARADOS
        if (
            strpos($textoNorm, 'hoy') !== false &&
            (strpos($textoNorm, 'preparado') !== false || strpos($textoNorm, 'preparados') !== false)
        ) {
            return 'pedidos_preparados_hoy';
        }

        // 3) HOY + ENTREGADOS
        if (
            strpos($textoNorm, 'hoy') !== false &&
            (strpos($textoNorm, 'entregado') !== false || strpos($textoNorm, 'entregados') !== false)
        ) {
            return 'pedidos_entregados_hoy';
        }

        // 4) SOLO PREPARACIÓN
        if (strpos($textoNorm, 'preparacion') !== false || strpos($textoNorm, 'preparando') !== false) {
            return 'pedidos_en_preparacion';
        }

        // 5) SOLO PREPARADOS
        if (strpos($textoNorm, 'preparado') !== false || strpos($textoNorm, 'preparados') !== false) {
            return 'pedidos_preparados';
        }

        // 6) SOLO ENTREGADOS
        if (strpos($textoNorm, 'entregado') !== false || strpos($textoNorm, 'entregados') !== false) {
            return 'pedidos_entregados';
        }

        // 7) HOY SOLO (sin palabra clave)
        if (strpos($textoNorm, 'hoy') !== false) {
            return 'pedidos_hoy';
        }

        return 'pedidos_generico';
    }
    // Consultas sobre Materia Prima / Stock
    if (
        strpos($textoNorm, 'materia prima') !== false ||
        strpos($textoNorm, 'materiaprima') !== false ||
        strpos($textoNorm, 'mp') !== false ||
        strpos($textoNorm, 'stock') !== false
    ) {
        if (
            strpos($textoNorm, 'bajo') !== false ||
            strpos($textoNorm, 'critico') !== false ||
            strpos($textoNorm, 'reponer') !== false ||
            strpos($textoNorm, 'faltante') !== false ||
            strpos($textoNorm, 'falta') !== false
        ) {
            return 'mp_stock_bajo';
        }

        // Opcional: "en minimo" / "justo"
        if (
            strpos($textoNorm, 'minimo') !== false ||
            strpos($textoNorm, 'justo') !== false ||
            strpos($textoNorm, 'al limite') !== false
        ) {
            return 'mp_stock_minimo';
        }

        return 'mp_generico';
    }

    // Productos / Ranking ventas
    if (
        strpos($textoNorm, 'top') !== false ||
        strpos($textoNorm, 'mas vendido') !== false ||
        strpos($textoNorm, 'mas vendidos') !== false ||
        strpos($textoNorm, 'ranking') !== false
    ) {
        // Si además menciona producto(s) o ventas, lo tomamos como top vendidos
        if (
            strpos($textoNorm, 'producto') !== false ||
            strpos($textoNorm, 'productos') !== false ||
            strpos($textoNorm, 'vendido') !== false ||
            strpos($textoNorm, 'vendidos') !== false ||
            strpos($textoNorm, 'venta') !== false ||
            strpos($textoNorm, 'ventas') !== false
        ) {
            return 'prod_top_vendidos';
        }
    }
    if (strpos($textoNorm, 'producto') !== false || strpos($textoNorm, 'productos') !== false) {
        return 'prod_generico';
    }



    // Repartos
    if (strpos($textoNorm, 'reparto') !== false || strpos($textoNorm, 'repartos') !== false) {
        if (strpos($textoNorm, 'curso') !== false) {
            return 'repartos_en_curso';
        }
        if (strpos($textoNorm, 'hoy') !== false) {
            return 'repartos_hoy';
        }
        return 'repartos_generico';
    }

    // Licencias
    if (strpos($textoNorm, 'licencia') !== false || strpos($textoNorm, 'licencias') !== false) {
        if (strpos($textoNorm, 'pendiente') !== false || strpos($textoNorm, 'pendientes') !== false) {
            return 'licencias_pendientes';
        }
        return 'licencias_generico';
    }

    // Ayuda
    if (
        strpos($textoNorm, 'ayuda') !== false ||
        strpos($textoNorm, 'como ') !== false ||
        strpos($textoNorm, 'que puedo hacer') !== false
    ) {
        return 'ayuda_uso';
    }

    // Saludos
    if (
        strpos($textoNorm, 'hola') !== false ||
        strpos($textoNorm, 'buen dia') !== false ||
        strpos($textoNorm, 'buenos dias') !== false ||
        strpos($textoNorm, 'buenas') !== false
    ) {
        return 'saludo';
    }

    return 'desconocido';
}



/**
 * Construye la respuesta de Mignon para "pedidos en preparación hoy"
 * usando el modeloPedidos.
 */
function mignon_respuesta_pedidos_en_preparacion_hoy(string $prefijo, modeloPedidos $pedidosModel): string
{
    try {
        $pedidos = $pedidosModel->obtenerPedidosEnPreparacionHoy();
    } catch (Throwable $e) {
        return $prefijo .
            "quise consultar los pedidos en preparación de hoy, pero hubo un problema en la cocina de datos. " .
            "Decile al administrador que revise el log.";
    }

    if (empty($pedidos)) {
        return $prefijo .
            "hoy no encontré pedidos en preparación. " .
            "Si querés, puedo mostrarte pedidos generados, preparados o entregados.";
    }

    $total   = count($pedidos);
    $vista   = array_slice($pedidos, 0, 3);
    $lineas  = [];

    foreach ($vista as $p) {
        $id      = $p['idPedidoVenta'];
        $cliente = htmlspecialchars($p['cliente'], ENT_QUOTES, 'UTF-8');
        $lineas[] = "#$id – $cliente";
    }

    $lista = implode("<br>", $lineas);

    if ($total <= 3) {
        return $prefijo .
            "hoy tenés <strong>$total pedidos</strong> en preparación:<br>$lista";
    }

    $resto = $total - 3;

    return $prefijo .
        "hoy tenés <strong>$total pedidos</strong> en preparación.<br>" .
        "Te muestro algunos:<br>$lista<br>…y otros <strong>$resto</strong> más.";
}

function mignon_respuesta_pedidos_en_preparacion_general(string $prefijo, modeloPedidos $pedidosModel): string
{
    try {
        $pedidos = $pedidosModel->obtenerPedidosEnPreparacion();
    } catch (Throwable $e) {
        return $prefijo .
            "quise consultar todos los pedidos en preparación, pero hubo un problema en la cocina de datos. " .
            "Decile al administrador que revise el log.";
    }

    if (empty($pedidos)) {
        return $prefijo .
            "no encontré pedidos en preparación. " .
            "Si querés, puedo ayudarte con pedidos generados, preparados o entregados.";
    }

    $total   = count($pedidos);
    $vista   = array_slice($pedidos, 0, 3);
    $lineas  = [];

    foreach ($vista as $p) {
        $id      = $p['idPedidoVenta'];
        $cliente = htmlspecialchars($p['cliente'], ENT_QUOTES, 'UTF-8');
        $lineas[] = "#$id – $cliente";
    }

    $lista = implode("<br>", $lineas);

    if ($total <= 3) {
        return $prefijo .
            "tenés <strong>$total pedidos</strong> en preparación:<br>$lista";
    }

    $resto = $total - 3;

    return $prefijo .
        "tenés <strong>$total pedidos</strong> en preparación.<br>" .
        "Te muestro algunos:<br>$lista<br>…y otros <strong>$resto</strong> más.";
}

function mignon_respuesta_pedidos_preparados_hoy(string $prefijo, modeloPedidos $pedidosModel): string
{
    try {
        $pedidos = $pedidosModel->obtenerPedidosPreparadosHoy();
    } catch (Throwable $e) {
        return $prefijo .
            "quise consultar los pedidos preparados de hoy, pero hubo un problema en la cocina de datos.";
    }

    if (empty($pedidos)) {
        return $prefijo .
            "hoy no encontré pedidos preparados.";
    }

    $total  = count($pedidos);
    $vista  = array_slice($pedidos, 0, 3);

    $lineas = [];
    foreach ($vista as $p) {
        $lineas[] = "#" . $p['idPedidoVenta'] . " – " . htmlspecialchars($p['cliente'], ENT_QUOTES, 'UTF-8');
    }

    $lista = implode("<br>", $lineas);
    $resto = $total - 3;

    if ($total <= 3) {
        return $prefijo . "hoy tenés $total pedidos preparados:<br>$lista";
    }

    return $prefijo . "hoy tenés $total pedidos preparados:<br>$lista<br>…y otros $resto más.";
}

function mignon_respuesta_pedidos_preparados_general(string $prefijo, modeloPedidos $pedidosModel): string
{
    try {
        $pedidos = $pedidosModel->obtenerPedidosPreparados();
    } catch (Throwable $e) {
        return $prefijo .
            "quise consultar todos los pedidos preparados, pero hubo un problema en la cocina de datos.";
    }

    if (empty($pedidos)) {
        return $prefijo . "no encontré pedidos preparados.";
    }

    $total  = count($pedidos);
    $vista  = array_slice($pedidos, 0, 3);

    $lineas = [];
    foreach ($vista as $p) {
        $lineas[] = "#" . $p['idPedidoVenta'] . " – " . htmlspecialchars($p['cliente'], ENT_QUOTES, 'UTF-8');
    }

    $lista = implode("<br>", $lineas);
    $resto = $total - 3;

    if ($total <= 3) {
        return $prefijo . "tenés $total pedidos preparados:<br>$lista";
    }

    return $prefijo . "tenés $total pedidos preparados:<br>$lista<br>…y otros $resto más.";
}

function mignon_respuesta_pedidos_entregados_hoy(string $prefijo, modeloPedidos $pedidosModel): string
{
    try {
        $pedidos = $pedidosModel->obtenerPedidosEntregadosHoy();
    } catch (Throwable $e) {
        return $prefijo .
            "quise consultar los pedidos entregados de hoy, pero ocurrió un problema.";
    }

    if (empty($pedidos)) {
        return $prefijo . "hoy no encontré pedidos entregados.";
    }

    $total  = count($pedidos);
    $vista  = array_slice($pedidos, 0, 3);

    $lineas = [];
    foreach ($vista as $p) {
        $lineas[] = "#" . $p['idPedidoVenta'] . " – " . htmlspecialchars($p['cliente'], ENT_QUOTES, 'UTF-8');
    }

    $lista = implode("<br>", $lineas);
    $resto = $total - 3;

    if ($total <= 3) {
        return $prefijo . "hoy tenés $total pedidos entregados:<br>$lista";
    }

    return $prefijo . "hoy tenés $total pedidos entregados:<br>$lista<br>…y otros $resto más.";
}

function mignon_respuesta_pedidos_entregados_general(string $prefijo, modeloPedidos $pedidosModel): string
{
    try {
        $pedidos = $pedidosModel->obtenerPedidosEntregados();
    } catch (Throwable $e) {
        return $prefijo .
            "quise consultar todos los pedidos entregados, pero ocurrió un problema.";
    }

    if (empty($pedidos)) {
        return $prefijo . "no encontré pedidos entregados.";
    }

    $total  = count($pedidos);
    $vista  = array_slice($pedidos, 0, 3);

    $lineas = [];
    foreach ($vista as $p) {
        $lineas[] = "#" . $p['idPedidoVenta'] . " – " . htmlspecialchars($p['cliente'], ENT_QUOTES, 'UTF-8');
    }

    $lista = implode("<br>", $lineas);
    $resto = $total - 3;

    if ($total <= 3) {
        return $prefijo . "tenés $total pedidos entregados:<br>$lista";
    }

    return $prefijo . "tenés $total pedidos entregados:<br>$lista<br>…y otros $resto más.";
}

function mignon_respuesta_mp_stock_bajo(string $prefijo, modeloMP $mpModel): string
{
    try {
        $items = $mpModel->obtenerMPBajoStock();
    } catch (Throwable $e) {
        return $prefijo .
            "quise consultar el stock de materia prima, pero hubo un problema en la cocina de datos. " .
            "Decile al administrador que revise el log.";
    }

    if (empty($items)) {
        return $prefijo . "no encontré materias primas con stock bajo. Todo en orden.";
    }

    $total = count($items);

    // Listado enumerado (como pediste)
    $lineas = [];
    $i = 1;

    foreach ($items as $mp) {
        $nombre = htmlspecialchars($mp['nombre'], ENT_QUOTES, 'UTF-8');
        $lote   = htmlspecialchars($mp['lote'], ENT_QUOTES, 'UTF-8');
        $um     = htmlspecialchars($mp['unidad_medida'], ENT_QUOTES, 'UTF-8');

        $stockA = (int)$mp['stockactual'];
        $stockM = (int)$mp['stockminimo'];

        $lineas[] = $i . ". " . $nombre . " (Lote: " . $lote . ") – " .
            "stock " . $stockA . " " . $um . " (mín " . $stockM . " " . $um . ")";
        $i++;
    }

    return $prefijo .
        "tenés <strong>$total materias primas</strong> con stock bajo:<br>" .
        implode("<br>", $lineas);
}

function mignon_respuesta_mp_stock_minimo(string $prefijo, modeloMP $mpModel): string
{
    try {
        $items = $mpModel->obtenerMPEnMinimo();
    } catch (Throwable $e) {
        return $prefijo . "quise consultar las materias primas al mínimo, pero ocurrió un problema.";
    }

    if (empty($items)) {
        return $prefijo . "no encontré materias primas exactamente en el mínimo.";
    }

    $total = count($items);

    $lineas = [];
    $i = 1;

    foreach ($items as $mp) {
        $nombre = htmlspecialchars($mp['nombre'], ENT_QUOTES, 'UTF-8');
        $lote   = htmlspecialchars($mp['lote'], ENT_QUOTES, 'UTF-8');
        $um     = htmlspecialchars($mp['unidad_medida'], ENT_QUOTES, 'UTF-8');
        $stock  = (int)$mp['stockactual'];

        $lineas[] = $i . ". " . $nombre . " (Lote: " . $lote . ") – " . $stock . " " . $um;
        $i++;
    }

    return $prefijo .
        "tenés <strong>$total materias primas</strong> en el mínimo (amarillo):<br>" .
        implode("<br>", $lineas);
}

function mignon_respuesta_prod_top_vendidos(string $prefijo, modeloPedidos $pedidosModel): string
{
    // Reutilizamos la lógica del reporte: por defecto, últimos 30 días
    $hasta = date('Y-m-d') . ' 23:59:59';
    $desde = date('Y-m-d', strtotime('-30 days')) . ' 00:00:00';

    try {
        $top = $pedidosModel->topProductosVendidos($desde, $hasta, 10);
    } catch (Throwable $e) {
        return $prefijo .
            "quise calcular el top de productos vendidos, pero hubo un problema en la cocina de datos.";
    }

    if (empty($top)) {
        return $prefijo . "no encontré ventas para armar el top 10 en los últimos 30 días.";
    }

    $lineas = [];
    $i = 1;

    foreach ($top as $row) {
        $nombre = htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
        $um     = htmlspecialchars($row['unidad_medida'] ?? '', ENT_QUOTES, 'UTF-8');

        $cant = (float)($row['cantidad_total'] ?? 0);
        $fac  = (float)($row['facturacion_total'] ?? 0);

        // Formato AR para facturación
        $facFmt = number_format($fac, 2, ',', '.');

        $lineas[] = $i . ". " . $nombre . " — " . rtrim(rtrim((string)$cant, '0'), '.') . " " . $um . " ( $ " . $facFmt . " )";
        $i++;
    }

    return $prefijo .
        "este es el <strong>top 10 de productos más vendidos</strong> (últimos 30 días):<br>" .
        implode("<br>", $lineas);
}



/**
 * Genera la respuesta de Mignon en función del intent detectado
 */
function mignon_responder(
    string $intent,
    string $mensajeOriginal,
    string $nombre,
    string $rol,
    modeloPedidos $pedidosModel,
    modeloMP $mpModel
): string {
    $prefijo = "Mirá, $nombre, ";

    switch ($intent) {
        case 'saludo':
            return "¡Hola $nombre! Soy <strong>Mignon</strong>, tu amigo panadero. " .
                "Podés preguntarme cosas como: <em>\"pedidos en preparación hoy\"</em> o <em>\"repartos en curso\"</em>.";

        case 'pedidos_hoy':
            // FUTURO: acá vamos a enchufar la consulta real a BD
            return $prefijo .
                "en esta primera versión todavía no estoy conectado a los pedidos reales, " .
                "pero mi idea es poder decirte cuántos pedidos de clientes tenés hoy y en qué estado están.";

        case 'pedidos_en_preparacion_hoy':
            return mignon_respuesta_pedidos_en_preparacion_hoy($prefijo, $pedidosModel);

        case 'pedidos_en_preparacion':
            return mignon_respuesta_pedidos_en_preparacion_general($prefijo, $pedidosModel);

        case 'pedidos_preparados_hoy':
            return mignon_respuesta_pedidos_preparados_hoy($prefijo, $pedidosModel);

        case 'pedidos_preparados':
            return mignon_respuesta_pedidos_preparados_general($prefijo, $pedidosModel);

        case 'pedidos_entregados_hoy':
            return mignon_respuesta_pedidos_entregados_hoy($prefijo, $pedidosModel);

        case 'pedidos_entregados':
            return mignon_respuesta_pedidos_entregados_general($prefijo, $pedidosModel);

        case 'mp_stock_bajo':
            return mignon_respuesta_mp_stock_bajo($prefijo, $mpModel);

        case 'mp_stock_minimo': // opcional
            return mignon_respuesta_mp_stock_minimo($prefijo, $mpModel);

        case 'mp_generico':
            return $prefijo .
                "sobre materia prima puedo ayudarte con: <em>stock bajo</em> (para reponer) y <em>en mínimo</em> (amarillo). " .
                "Probá con: <em>\"materia prima bajo stock\"</em>.";

        case 'prod_top_vendidos':
            return mignon_respuesta_prod_top_vendidos($prefijo, $pedidosModel);

        case 'prod_generico':
            return $prefijo .
                "sobre productos, por ahora puedo ayudarte con el <em>top 10 de productos vendidos</em>. " .
                "Probá con: <em>\"top 10 productos vendidos\"</em> o <em>\"más vendidos\"</em>.";


        case 'repartos_en_curso':
            return $prefijo .
                "voy a listar los repartos que están en curso, con su zona, vehículo y chofer asignado. " .
                "Así no tenés que entrar al módulo de repartos para ver el estado general.";

        case 'repartos_hoy':
            return $prefijo .
                "voy a poder mostrarte todos los repartos de hoy y en qué estado está cada uno (pendiente, en curso, finalizado).";

        case 'repartos_generico':
            return $prefijo .
                "sobre repartos voy a poder ayudarte con cosas como: repartos en curso, repartos de hoy o por zona.";

        case 'licencias_pendientes':
            return $prefijo .
                "la idea es mostrarte cuántas licencias tenés pendientes de aprobación y de qué empleados, " .
                "para que el área de RRHH pueda priorizar rápido.";

        case 'licencias_generico':
            return $prefijo .
                "con licencias voy a ayudarte a ver solicitudes, estados y, más adelante, incluso alertar inconsistencias.";

        case 'ayuda_uso':
            return "Te doy una mano con el uso del sistema, $nombre. " .
                "Por ejemplo, podés preguntarme:<br>" .
                "- <em>\"¿Cómo registro un pedido?\"</em><br>" .
                "- <em>\"¿Cómo asigno un turno?\"</em><br>" .
                "- <em>\"¿Cómo veo los repartos en curso?\"</em>";

        case 'desconocido':
        default:
            return "Recibí tu mensaje: <em>\"$mensajeOriginal\"</em>, $nombre, " .
                "pero todavía no aprendí a responder bien a ese tipo de pregunta. " .
                "Probá con algo como: <em>\"pedidos en preparación hoy\"</em>, " .
                "<em>\"repartos en curso\"</em> o <em>\"licencias pendientes\"</em>.";
    }
}

// ===============================
// Lógica principal del endpoint
// ===============================

$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

if ($mensaje === '') {
    echo json_encode([
        'ok'        => false,
        'respuesta' => 'Decime algo y veo cómo puedo ayudarte. Por ejemplo: "pedidos en preparación hoy" o "repartos en curso".'
    ]);
    exit;
}

// Intentamos tomar nombre y rol de la sesión, ajustá las claves si en tu sistema son otras
//$nombre = 'panadero';
$nombre = mignon_obtener_nombre_desde_sesion();
$rol    = !empty($_SESSION['rol']) ? $_SESSION['rol'] : 'Usuario';


// Normalizamos texto e identificamos intent
$mensajeNorm = mignon_normalizar_texto($mensaje);
$intent      = mignon_detectar_intent($mensajeNorm);

// Generamos respuesta
$respuesta = mignon_responder($intent, $mensaje, $nombre, $rol, $pedidosModel, $mpModel);


// (Más adelante podemos loguear cada interacción en una tabla chatbot_logs)

echo json_encode([
    'ok'        => true,
    'respuesta' => $respuesta,
    'intent'    => $intent
]);
