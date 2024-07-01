<?php
require_once "./controladores/ventaControlador.php";
$ins_venta = new ventaControlador();

// Verificar si se ha enviado el formulario de búsqueda
if(isset($_POST['busqueda_inicial'])) {
    // Obtener el usuario seleccionado del formulario
    $usuario_id = $_POST['usuario_id']; // Asegúrate de sanitizar este valor

    // Mostrar resultados filtrados por usuario
    echo $ins_venta->paginador_venta_usuario_controlador(1, 15, SERVERURL, $usuario_id);
}
?>

<div class="full-box page-header">
    <h3 class="text-left text-uppercase">
        <i class="fas fa-user fa-fw"></i> &nbsp; Buscar venta (Usuario)
    </h3>
    <p class="text-justify">
        En el módulo VENTAS podrá realizar ventas de productos y buscar ventas filtrando por usuario.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs text-uppercase">
        <!-- Aquí puedes dejar los enlaces de navegación existentes o añadir según necesites -->
        <li>
            <a href="<?php echo SERVERURL; ?>sale-new/">
                <i class="fas fa-cart-plus fa-fw"></i> &nbsp; Nueva venta
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>sale-list/">
                <i class="fas fa-coins fa-fw"></i> &nbsp; Ventas realizadas
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>sale-pending/">
                <i class="fab fa-creative-commons-nc fa-fw"></i> &nbsp; Ventas pendientes
            </a>
        </li>
        <!-- Enlace activo para búsqueda por usuario -->
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>sale-search-user/">
                <i class="fas fa-user fa-fw"></i> &nbsp; Buscar venta (Usuario)
            </a>
        </li>
    </ul>
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>sale-search-user/" data-form="default" method="POST" autocomplete="off">
        <input type="hidden" name="modulo" value="venta">
        <div class="container-fluid">
            <div class="row justify-content-md-center">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="inputUser" class="bmd-label-floating">Seleccionar usuario</label>
                        <!-- Aquí puedes usar un select o autocomplete para seleccionar el usuario -->
                        <!-- Ejemplo de un select básico -->
                        <select class="form-control" name="usuario_id" id="inputUser">
                            <!-- Llena este select con opciones de usuarios -->
                            <option value="1">Usuario 1</option>
                            <option value="2">Usuario 2</option>
                            <!-- Asegúrate de llenar esto dinámicamente desde la base de datos si es necesario -->
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <p class="text-center" style="margin-top: 40px;">
                        <button type="submit" class="btn btn-raised btn-info"><i class="fas fa-search"></i> &nbsp; BUSCAR</button>
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Aquí se mostrarán los resultados de la búsqueda -->
<div class="container-fluid">
    <?php
        require_once "./controladores/ventaControlador.php";
        $ins_venta = new ventaControlador();

        echo $ins_venta->paginador_venta_controlador($pagina[1],15,$pagina[0],$_SESSION['busqueda_venta'],"");
    ?>
</div>
<?php
		include "./vistas/inc/print_invoice_script.php";
?>
