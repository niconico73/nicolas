<div class="full-box page-header">
    <h3 class="text-left text-uppercase">
        <i class="fas fa-coins fa-fw"></i> &nbsp; Ventas realizadas
    </h3>
    <p class="text-justify">
        En el módulo VENTAS podrá realizar ventas de productos, puede usar lector de código de barras o hacerlo de forma manual digitando el código del producto (debe de configurar estas opciones en ajustes de su cuenta). También puede ver las ventas realizadas y buscar ventas en el sistema.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs text-uppercase">
        <li>
            <a href="<?php echo SERVERURL; ?>sale-new/">
                <i class="fas fa-cart-plus fa-fw"></i> &nbsp; Nueva venta
            </a>
        </li>

        <li>
            <a class="active" href="<?php echo SERVERURL; ?>sale-list/">
                <i class="fas fa-coins fa-fw"></i> &nbsp; Ventas realizadas
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>sale-pending/">
                <i class="fab fa-creative-commons-nc fa-fw"></i> &nbsp; Ventas pendientes
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>sale-search-date/">
                <i class="fas fa-search-dollar fa-fw"></i> &nbsp; Buscar venta (Fecha)
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>sale-search-code/">
                <i class="fas fa-search-dollar fa-fw"></i> &nbsp; Buscar venta (Código o Cliente)
            </a>
        </li>
    </ul>

    <div class="container-fluid">
    <?php
    // Verifica si la sesión no está activa
    if (session_status() == PHP_SESSION_NONE) {
        // Si no está activa, inicia la sesión
        session_start();
    }

    // Verifica si el usuario está autenticado
    if (!isset($_SESSION['cargo_svi'])) {
        // Si el usuario no está autenticado, redirige a la página de inicio de sesión
        header("Location: login.php");
        exit();
    }

    // Obtén el rol del usuario actual desde la sesión
    $rol = $_SESSION['cargo_svi'];
    $usuario_id = $_SESSION['usuario_svi']; // Obtén el ID del usuario actual

    // Incluye el controlador de ventas
    require_once "./controladores/ventaControlador.php";
    $ins_venta = new ventaControlador();

    // Verifica si el rol del usuario es Administrador o Cajero
    if ($rol == "Administrador") {
        // Si el rol es Administrador, muestra todas las ventas
        echo $ins_venta->paginador_venta_controlador($pagina, 15, $pagina[0], "", "", null);
    } elseif ($rol == "Cajero") {
        // Si el rol es Cajero, muestra solo las ventas asociadas a este usuario
        echo $ins_venta->paginador_venta_controlador($pagina, 15, $pagina[0], "", "", $usuario_id);
    } else {
        // Si el rol no es ni Administrador ni Cajero, muestra un mensaje de acceso no autorizado
        echo "Acceso no autorizado";
    }
    ?>

    </div>

</div>

<?php
    include "./vistas/inc/print_invoice_script.php";
?>
