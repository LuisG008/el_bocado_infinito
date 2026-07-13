$(function () {
    (function init() {
        pedidoGenerado();
    })();

    // $('#descargar_factura').on('click', function () {
    //     window.open($('#descargar_factura').attr('href'), '_blank');
    // });

    /**
     * Consulta el pedido generado y muestra la factura en html
     */
    function pedidoGenerado() {
        $.ajax({
            type: 'GET',
            url: `/api/public/pedidoGenerado`,
            dataType: 'json',
            data: {},
            beforeSend: function () {
                 $('.spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            if(jqXHR.responseJSON.message){
                flashy.error(jqXHR.responseJSON.message);
            } else {
                flashy.error('Ocurrio un error en la carga');
            }
        }).done(response => {
            $('#descargar_factura').attr('href', '/' + response.data.ruta_factura);
            cargarFactura(response.data);
        }).always(() => {
            $('.spinner-border').toggleClass('d-none');
        });
    }

    function cargarFactura(pedido) {
        const div = $('.div-principal-factura');
        div.empty();

        let items = '';

        pedido.items.forEach(item => {
            let subtotal = item.cantidad * item.precio;
            items += `<tr>
                        <td>${item.cantidad}</td>
                        <td>${item.nombre}</td>
                        <td>${formatearMiles(item.precio)}</td>
                        <td>${formatearMiles(subtotal)}</td>
                    </tr>`;
        });

        let total = pedido.items.reduce((acc, item) => acc + (item.cantidad * item.precio), 0);

        let factura = `<div class="div-secundario-factura">
                    <div class="logo-factura">
                        <img src="/assets/img/logo.png" class="logo-fac">
                    </div>
                    <div class="info-basic-pedido">
                        Pedido No. ${pedido.idpedido} <br>
                        Fecha: ${pedido.fecha} <br>
                        Telefono: 3001234567 <br>
                        direccion: Calle 123 #45-67 <br>
                    </div>
                    <div class="info-items-pedido">
                        <table class="w-100">
                            <tr>
                                <td>Cant</td>
                                <td>Descripción</td>
                                <td>Precio</td>
                                <td>Total</td>
                            </tr>
                            ${items}
                            </table>
                    </div>
                    <div class="total-pedido">
                        <span class="d-flex justify-content-end">Total:</span>
                        <span class="d-flex justify-content-end">$ ${formatearMiles(total)}</span>
                    </div>
                    <div class="frase">
                        Por favor dirigirse a la caja para realizar el pago <br>
                        Gracias por su visita.
                    </div>
                </div>`;
                        
        div.append(factura);
    }

    /**
     * Formatea el precio en miles
     */
    function formatearMiles(valor) {
        let limpio = valor.toString().replace(/\D/g, '');
        if (limpio) {
            return limpio.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        return '';
    }

    /**
     * click para ver los estados de los pedidos generados
     */
    $(document).on('click', '#estados_pedidos', function () {
        window.location.href = '/views/estado_pedido/estado_pedido.html';
    });

});