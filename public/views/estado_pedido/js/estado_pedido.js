$(function () {
    var Pedidos = [];
    var HistorialEstados = [];
    (function init() {
        estadosPedidosGenerados();
    })();

    /**
     * Consulta el pedido generado y muestra la factura en html
     */
    function estadosPedidosGenerados() {
        $.ajax({
            type: 'GET',
            url: `/api/public/estadosPedidosGenerados`,
            dataType: 'json',
            data: {},
            beforeSend: function () {
                 $('.spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            if(jqXHR.responseJSON.message){
                if(jqXHR.responseJSON.message === 'Falta el identificador cliente'){
                    $('#modal-identificador').modal('show');
                } else {
                    flashy.error(jqXHR.responseJSON.message);
                }
            } else {
                flashy.error('Ocurrio un error en la carga');
            }
        }).done(response => {
            Pedidos = response.data;
            HistorialEstados = response.historial;
            cargarEstadosPedidos(Pedidos);
        }).always(() => {
            $('.spinner-border').toggleClass('d-none');
            $("#btn-refresh").prop("disabled", false);
        });
    }

    function cargarEstadosPedidos(estadosPedidos) {
        const div = $('.div-principal-estado');
        div.empty();
        let list = '';

        estadosPedidos.forEach(estado => {
            items = '';
            let i = 1;
            for (const item of estado.items) {
                items += item.nombre_item_pedido;
                if(i == 2){
                     break;
                }
                items += '<br>';
                i++;
            }

            let btnCancelar = '';
            if(estado.estado == 'Pendiente de pago'){
                btnCancelar = `<i class="bi bi-x-circle btn-cancelar" data-idpedido="${estado.idpedido}"></i>`;
            } 

            list += `
                <div class="estado-pedido mt-2 infoPedido" data-idpedido="${estado.idpedido}">
                    <div class="d-flex justify-content-between">
                        <span>Pedido No ${estado.idpedido}</span>
                        ${btnCancelar}
                    </div>
                    <span class="mt-3">${items}...</span>
                    <div class="d-flex justify-content-between">
                        <span>${estado.fecha_hora_pedido}</span>
                        <div>
                            <div class="input-group mb-3">
                                <button class="btn btn-outline-secondary badge ${estado.color_estado} btn-estado" data-idpedido="${estado.idpedido}" type="button">${estado.estado}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        div.html(list);
    }

    $(document).on('click', '.infoPedido', function () {
        let idpedido = $(this).data('idpedido');
        let pedido = Pedidos.find(pedido => pedido.idpedido == idpedido);

        console.log(pedido);

        $("#titulo").text("Pedido No " + idpedido);
        $("#list-items").empty();
        pedido.items.forEach(item => {
            $("#list-items").append(`${item.nombre_item_pedido} <br>`);
            $("#list-items").append(`${item.descripcion_pedido} <br> <hr>`);
        });

        $('#showInfo').modal('show');
    });

    // CLICK EN CANCELAR
    $(document).on('click', '.btn-cancelar', function (event) {
        event.stopPropagation();

        let idpedido = $(this).data('idpedido');

        iziToast.question({
            timeout: 10000,
            color: 'yellow',
            icon: 'bi bi-cart-x-fill',
            position: 'center',
            message: `¿Desea cancelar el pedido <strong>#${idpedido}</strong>?`,
            progressBarColor: 'rgb(255, 202, 44)',
            buttons: [
                ['<button>Sí</button>', function (instance, toast) {

                    cancelarPedido(idpedido);
                    instance.hide({ transitionOut: 'fadeOutUp', }, toast, 'buttonName');
                }, true], // true to focus
                ['<button>No</button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOutUp', }, toast, 'buttonName');
                }]
            ],
        });

        console.log('Cancelar pedido', idpedido);

    });

    // CLICK EN ESTADO
    $(document).on('click', '.btn-estado', function (event) {
        event.stopPropagation();

        const div = $('.tracking');
        div.empty();

        $('#showTrazabilidad').modal('show');

        let idpedido = $(this).data('idpedido');
        let estados = HistorialEstados[idpedido];

        let html = `<div class="tracking-item completed">
                        <div class="tracking-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>

                        <div class="tracking-content">
                            <h6>Creado</h6>
                            <small>${estados[0].fecha_hora}</small>
                        </div>
                    </div>`;

        estados.forEach(estado => {
            console.log(estado);
            let fecha = `<small>Pendiente</small>`;
            let posicion = 'active';

            if(estado.estado == 'Inactivo'){
                fecha = `<small>${estado.fecha_hora}</small>`;
                posicion = 'completed';
            }

            switch (estado.nombre) {
                case 'Pendiente de pago':
                html += `<div class="tracking-item ${posicion}">
                                <div class="tracking-icon">
                                    <i class="bi bi-wallet2"></i>
                                </div>

                                <div class="tracking-content">
                                    <h6>Pendiente de pago</h6>
                                    ${fecha}
                                </div>
                            </div>`;
                    break;
                case 'Cancelado':
                fecha = `<small>${estado.fecha_hora}</small>`;
                html += `<div class="tracking-item ${posicion}">
                                <div class="tracking-icon">
                                    <i class="bi bi-x-circle-fill"></i>
                                </div>

                                <div class="tracking-content">
                                    <h6>Cancelado</h6>
                                    ${fecha}
                                </div>
                            </div>`;
                    break;
                case 'Pagado':
                html += `<div class="tracking-item  ${posicion}">
                                <div class="tracking-icon">
                                    <i class="bi bi-cash-coin"></i>
                                </div>

                                <div class="tracking-content">
                                    <h6>Pagado</h6>
                                    ${fecha}
                                </div>
                            </div>`;
                    break;
                case 'En preparación':
                html += `<div class="tracking-item  ${posicion}">
                                <div class="tracking-icon">
                                    <i class="bi bi-fork-knife"></i>
                                </div>

                                <div class="tracking-content">
                                    <h6>En preparación</h6>
                                    ${fecha}
                                </div>
                            </div>`;
                    break;
                case 'Listo para entrega':
                html += `<div class="tracking-item  ${posicion}">
                                <div class="tracking-icon">
                                    <i class="bi bi-bag-fill"></i>
                                </div>

                                <div class="tracking-content">
                                    <h6>Listo para entrega</h6>
                                    ${fecha}
                                </div>
                            </div>`;
                    break;
                case 'Entregado':
                html += `<div class="tracking-item  ${posicion}">
                                <div class="tracking-icon">
                                    <i class="bi bi-bag-check-fill"></i>
                                </div>

                                <div class="tracking-content">
                                    <h6>Entregado</h6>

                                </div>
                            </div>`;
                    break;
            }
        });

        div.append(html);
        
    });

    // CLICK EN refrescar
    $(document).on('click', '#btn-refresh', function (event) {
        $("#btn-refresh").prop("disabled", true);
        estadosPedidosGenerados();
    });


    /**
     * Realiza la cancelacion del pedido y recarga la vista
     */
    function cancelarPedido(idpedido) {

        $.ajax({
            type: 'POST',
            url: `/api/public/cancelarPedido`,
            dataType: 'json',
            data: {
                idpedido: idpedido
            },
            beforeSend: function () {
                 //$('.spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            if(jqXHR.responseJSON.message){
                flashy.error(jqXHR.responseJSON.message);
            } else {
                flashy.error('Ocurrio un error');
            }
        }).done(response => {
            flashy(`Pedido #${idpedido} Cancelado`, {
                type: 'success',
                animation: 'bounce',
                icon: ' '
            });
            estadosPedidosGenerados();
        }).always(() => {
            //$('.spinner-border').toggleClass('d-none');
        });
    }

    //Enviar identificador ---------------------------------------------------------------------------------------------------------------------------------------------------------
    $('#identificador').validate({
        errorClass: "text-danger",
        errorElement: "small",
        highlight: function (element) {
            $(element).addClass('is-invalid');
            $(element).removeClass('is-valid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
            $(element).addClass('is-valid');
        },

        rules: {
            identificacion: { 
                required: true, 
                minlength: 8
            }
        },
        messages: {
            identificacion: "Ingrese identificación válida"
        },

        submitHandler: function (form) {
            enviarIdentificador();
        }
    });

    function enviarIdentificador() {
        
        $(".btn-warning").prop("disabled", true);
        $.ajax({
            type: 'GET',
            url: `/api/public/estadosPedidosGenerados`,
            dataType: 'json',
            data: {
                identificacion: $('#identificacion').val()
            },
            beforeSend: function () {
                $('.spinner-border, .btn-danger, .btn-warning').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            $(".btn-warning").prop("disabled", false);
            if(jqXHR.responseJSON.message){
                flashy.error(jqXHR.responseJSON.message);
            } else {
                flashy.error('Ocurrio un error en la carga');
            }
        }).done(response => {
            Pedidos = response.data;
            cargarEstadosPedidos(Pedidos);
            $(".btn-warning").prop("disabled", false);
            // cerrar modal
            $('#modal-identificador').modal('hide');
        }).always(() => {
            $('.spinner-border, .btn-danger, .btn-warning').toggleClass('d-none');
        });
    }
});