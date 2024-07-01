<script>
    function print_ticket(url){
        window.open(url,'Imprimir ticket','width=400,height=720,top=0,left=100,menubar=NO,toolbar=YES');
    }

   

    function print_invoice(url){
        window.open(url,'Imprimir factura','width=820,height=720,top=0,left=100,menubar=NO,toolbar=YES');
    }
    
    function actualizarEstadoFactura(selectElement, ventaId) {
    const nuevoEstadoFactura = selectElement.value;
    const estadoVentaSpan = document.getElementById("estadoVenta_" + ventaId);
    const estadoFacturaSpan = document.getElementById("estadoFactura_" + ventaId);

    // Enviar solicitud AJAX para actualizar la base de datos
    fetch('<?php echo SERVERURL; ?>ajax/ventaAjax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `venta_id=${ventaId}&nuevo_estado_factura=${nuevoEstadoFactura}&modulo_venta=actualizar_estado_venta_y_factura`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Error al actualizar:', data.message);

            // Revertir el cambio en el desplegable (opcional)
            if (estadoVentaSpan) {
                selectElement.value = estadoVentaSpan.textContent === 'Impreso' ? 'Facturado' : 'No Facturado';
            }

        } else {
            // Actualizar el estado visualmente en la tabla
            if (estadoVentaSpan) {
                if (nuevoEstadoFactura === 'Facturado') {
                    estadoVentaSpan.textContent = "Impreso";
                    estadoVentaSpan.classList.remove("badge-secondary", "badge-warning");
                    estadoVentaSpan.classList.add("badge-success");
                } else { // Si se selecciona "No Facturado"
                    if (estadoVentaSpan.textContent === 'Impreso') {
                        estadoVentaSpan.textContent = "Cancelado"; // Volver a "Cancelado"
                        estadoVentaSpan.classList.remove("badge-success");
                        estadoVentaSpan.classList.add("badge-secondary"); // Volver a gris
                    }
                }
                estadoVentaSpan.style.color = "white"; // Asegurar que el texto sea blanco
            }

            // Actualizar el estado de facturación visualmente
            if (estadoFacturaSpan) {
                estadoFacturaSpan.textContent = nuevoEstadoFactura;
                estadoFacturaSpan.classList.remove("badge-danger", "badge-success");
                estadoFacturaSpan.classList.add(nuevoEstadoFactura === 'Facturado' ? 'badge-success' : 'badge-danger');
            }
        }
    })
    .catch(error => {
        console.error('Error en la solicitud AJAX:', error);

        // Revertir el cambio en el desplegable (opcional)
        if (estadoVentaSpan) {
            selectElement.value = estadoVentaSpan.textContent === 'Impreso' ? 'Facturado' : 'No Facturado';
        }
    });
    
}


    
</script>