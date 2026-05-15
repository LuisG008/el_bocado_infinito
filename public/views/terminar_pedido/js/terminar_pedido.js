$(function () {
    (function init() {
        prepedido();
    })();
    
    /**
     * Consulta el prepedido
     */
    function prepedido() {
        $.ajax({
            type: 'GET',
            url: `/api/public/prepedido`,
            dataType: 'json',
            data: {},
            beforeSend: function () {
                 $('.list-menu, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            cargarPrepedido(response.data);
            console.log(response.data);
        }).always(() => {
            $('.list-menu, .spinner-border').toggleClass('d-none');
        });
    }

    /**
     * Muestra el prepedido en la vista 
     */
    function cargarPrepedido(menus) {

        const list = $('.list-prepedido');
        list.empty();
        menus.forEach(menu => {
            let precio = formatearMiles(menu.precio);
            let item = `<li class="list-group-item" data-idmenu="${menu.idmenu}" data-precio="${menu.precio}">
                        <div class="d-flex justify-content-between">
                            <h6 class="card-title">${menu.nombre}</h6>
                            <select class="form-select tipo-consumo" aria-label="Default select example" style="width: 40%;">
                                <option value="" selected>Tipo Consumo</option>
                                <option value="1">Consumir en mesa</option>
                                <option value="2">Para llevar</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <label class="form-check-label d-flex justify-content-end">$ ${precio}</label>
                            <div class="input-group" style="width: 130px;">
                                <button class="btn btn-outline-secondary btn-minus" type="button">−</button>
                                <input type="text" class="form-control text-center quantity" value="1" readonly>
                                <button class="btn btn-outline-secondary btn-plus" type="button">+</button>
                            </div>
                        </div>
                    </li>`;
            list.append(item);
        });

        recalcularTotal();
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
     * Evento para aumentar la cantidad de un menú en el prepedido
     */
    $(document).on('click', '.btn-plus', function () {
        let input = $(this).siblings('.quantity');
        let valor = parseInt(input.val());

        if(valor >= 10){
            return;
        }

        input.val(valor + 1);
        recalcularTotal();
    });

    /**
     * Evento para disminuir la cantidad de un menú en el prepedido
     */
    $(document).on('click', '.btn-minus', function () {
        let input = $(this).siblings('.quantity');
        let valor = parseInt(input.val());
        if (valor > 1) {
            input.val(valor - 1);
        }
        recalcularTotal();
    });

    /**
     * Realiza el calculo total del prepedido y lo muestra en la vista
     */
    function recalcularTotal() {
        let total = 0;

        $('.list-prepedido li').each(function () {
            let precio = parseFloat($(this).data('precio'));
            let cantidad = parseInt($(this).find('.quantity').val());

            total += precio * cantidad;
        });

        $("#total").html(formatearMiles(total));
    }

    //PEDIR
    $('#addPedido').on('show.bs.modal', function (event) {
        $('.list-prepedido .tipo-consumo').removeClass('is-invalid is-valid');
        $('#terminarPedido .form-control').removeClass('is-invalid is-valid');

        let valido = true;

        $('.list-prepedido li').each(function () {
            let tipo = $(this).find('.tipo-consumo').val();

            if (!tipo) {
                valido = false;
                return false; // rompe el each
            }
        });

        if (!valido) {
            event.preventDefault(); // evita que la modal se abra
            $('.tipo-consumo').each(function () {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                }else{
                    $(this).addClass('is-valid');
                }
            });
        }
    });

    //Terminar pedido---------------------------------------------------------------------------------------------------------------------------------------------------------
    $('#terminarPedido').validate({
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
            },
            nombre: {
                required: true,
                minlength: 6 
            },
            telefono: {
                required: true,
                digits: true,
                minlength: 10 
            }
        },
        messages: {
            nombre: "Ingrese el nombre",
            identificacion: "Ingrese identificación válida",
            telefono: "Ingrese teléfono válido"
        },

        submitHandler: function (form) {
            terminarPedido();
        }
    });

    //Quede aqui falta recopilar todo para enviar en un ajax
    function terminarPedido() {
        console.log('terminar pedido');

        pedido = getPedido();

        console.log($('#identificacion').val());

        $.ajax({
            type: 'POST',
            url: `/api/public/finalizarPedido`,
            dataType: 'json',
            data: {
                identificacion: $('#identificacion').val(),
                nombre: $('#nombre').val(),
                telefono: $('#telefono').val(),
                pedido: pedido,
            },
            beforeSend: function () {
                $('.tabla, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            cargarUsuarios();
            flashy('¡Usuario editado!', {
                type: 'success',
                animation: 'bounce',
                icon: ' '
            });

            // cerrar modal
            $('#editarUsuario').modal('hide');
        }).always(() => {
            $('.tabla, .spinner-border').toggleClass('d-none');
        });
    }

    function getPedido() {
        let pedido = [];

        $('.list-prepedido .list-group-item').each(function () {

            let idmenu = $(this).data('idmenu');

            let tipoConsumo = $(this).find('.tipo-consumo').val();

            let cantidad = $(this).find('.quantity').val();

            //let precio = $(this).data('precio');

            pedido.push({
                idmenu: idmenu,
                tipo_consumo: tipoConsumo,
                cantidad: parseInt(cantidad),
                //precio: precio
            });

        });

        return pedido;
    }
});