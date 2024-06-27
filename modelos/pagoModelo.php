<?php

require_once __DIR__ . '/../config/SERVER.php';
require_once __DIR__.'/../modelos/mainModel.php'; // Asegúrate de incluir mainModel.php

class pagoModelo extends mainModel {

    public function __construct() {
        parent::__construct(SGBD, USER, PASS);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Función para actualizar los detalles de un pago
    public static function editar_detalles_pago_modelo($datos_pago) {
        try {
            $pdo = self::conectar();
            $query = "UPDATE pago SET numero_operacion = :numero_operacion, banco = :banco WHERE pago_id = :pago_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":numero_operacion", $datos_pago['NumeroOperacion']);
            $stmt->bindParam(":banco", $datos_pago['Banco']);
            $stmt->bindParam(":pago_id", $datos_pago['ID']); // Ajusta según el nombre correcto de la columna
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}

?>
