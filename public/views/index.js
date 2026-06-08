$(function () {
    (function init() {
        cargarMenu();
    })();
    
    /**
     * Consulta todos los menus disponibles
     */
    function cargarMenu() {
        $.ajax({
            type: 'GET',
            url: `/api/public/menu/allAvailable`,
            dataType: 'json',
            data: {},
            beforeSend: function () {
                 $('.list-menu, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            mostrarMenuAvailable(response.data);
        }).always(() => {
            $('.list-menu, .spinner-border').toggleClass('d-none');
        });
    }

    function mostrarMenuAvailable(recordMenu) {
        let list = $('.list-menu');
        list.empty();

        recordMenu.forEach(item => {
            
            let descripcion = item.descripcion;
            if(item.descripcion.length > 150){
                descripcion = item.descripcion.substring(0, 150) + '<a href="#" data-descripcion="' + encodeURIComponent(JSON.stringify(item.descripcion)) + '" data-titulo="' + encodeURIComponent(JSON.stringify('#' + item.id + ' ' + item.nombre)) + '" class="text-decoration-none"  data-bs-toggle="modal" data-bs-target="#modalVerMas"> Ver más..</a>';
            }

            let precio = formatearMiles(item.precio);

            let menu = `<div class="menu">
                    <div class="card" >
                        <img src="/${item.rutaImagen}" class="card-img-top img-menu"
                            alt="Lo sentimos, no se pudo cargar la imagen">
                        <div class="card-body card-body-menu">
                            <p class="card-text">
                                <span class="title-card-body">${item.nombre}</span><br>
                                <p class="descrip-card-body">${descripcion}</p>
                            </p>
                            <div class="form-check ">
                                <input class="form-check-input" type="checkbox" value="${item.id}" id="menu${item.id}">
                                <label class="form-check-label d-flex justify-content-end" for="menu${item.id}">$ ${precio}</label>
                            </div>
                        </div>
                    </div>
                </div>`;
            list.append(menu);
        });

    }

    function formatearMiles(valor) {
        let limpio = valor.toString().replace(/\D/g, '');
        if (limpio) {
            return limpio.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        return '';
    }

    //Generar pedido --------------------------------------------------------------------------------------------------------------------------------------------------------
    $(document).on('click', '#generarPedido', function () {
        let pedidos = [];

        $('.form-check-input:checked').each(function () {
            pedidos.push($(this).val()); // aquí obtiene el value (id del menú)
        });

        console.log(pedidos);

        if (pedidos.length === 0) {
            flashy.error('Debes seleccionar al menos un menú');
            return;
        }

        $.ajax({
            type: 'POST',
            url: `/api/public/prepedido`,
            dataType: 'json',
            data: {
                pedido: pedidos
            },
            beforeSend: function () {
                $('.list-menu, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            window.location.href = '/views/terminar_pedido/terminar_pedido.html';
        }).always(() => {
            $('.list-menu, .spinner-border').toggleClass('d-none');
        });

    });
});