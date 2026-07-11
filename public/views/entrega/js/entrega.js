$(function () {
    var colorEstado;
    var clasesColor;

    (function init() {
        cargarPedidos();
    })();

    /**
     * Consulta los pedidos que se deben entregar
     */
    function cargarPedidos() {
        $.ajax({
            type: 'GET',
            url: `/api/pedido/entrega`,
            dataType: 'json',
            data: {},
            beforeSend: function () {
                $('.tabla, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            colorEstado = response.estados;
            clasesColor = Object.values(colorEstado)
                .map(estado => estado.color)
                .filter((valor, indice, array) => array.indexOf(valor) === indice)
                .join(' ');

            llenarTabla(response.data);
        }).always(() => {
            $('.tabla, .spinner-border').toggleClass('d-none');
        });
    }
    
    /**
     * Llena la tabla con todos los pedidos del dia
     */
    function llenarTabla(data){

        let tabla = $('#tabla-pedidos');
        tabla.empty();
        
        if(data.length > 0){
            data.forEach(pedido => {

                let row = `
                    <tr>
                        <th>${pedido.idpedido}</th>
                        <td>${pedido.cliente}</td>
                        <td>${pedido.identificacion}</td>
                        <td><button class="btn btn-outline-warning" type="button" data-bs-toggle="modal" data-bs-target="#modalVerMas" data-items="${encodeURIComponent(JSON.stringify(pedido.items))}" data-titulo="${encodeURIComponent(JSON.stringify('#' + pedido.idpedido))}"><i class="bi bi-list-ol"></i></button></td>

                        <td>
                            <div class="input-group mb-3" data-idpedido="${pedido.idpedido}">
                                <button class="border-0 btn btn-outline-secondary dropdown-toggle badge ${pedido.color_estado}" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">${pedido.estado}</button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item cambiar-estado" href="#">Entregado</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>`;

                tabla.append(row);
            });
        }else{
            let row = `
                <tr>
                    <td colspan="5" align="center">Sin registros</td>
                </tr>`;

            tabla.append(row);
        }
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
     * click cuando cambian el estado
     */
    $(document).on('click', '.cambiar-estado', function(e) {
        e.preventDefault(); // Evitamos que el enlace realice su acción predeterminada de navegación

        let nuevoEstado = $(this).text().trim(); // Obtenemos el nombre del nuevo estado del enlace clickeado y eliminamos espacios

        let grupo = $(this).closest('.input-group'); //Obtenemos el grupo padre del botón clickeado para acceder al id del pedido y al botón para cambiar su color
        let idPedido = grupo.data('idpedido'); //Obtenemos el id del pedido desde el atributo data-idpedido del grupo

        confirmarCambioEstado(idPedido, nuevoEstado, grupo);
    });

    function confirmarCambioEstado(idPedido, nuevoEstado, grupo) {
        iziToast.question({
            timeout: 20000,
            close: true,
            progressBar: false,
            overlay: true,
            displayMode: 'once',
            id: 'question',
            zindex: 999,
            message: '¿Esta seguro de cambiar al estado <strong>' + nuevoEstado + '</strong> del pedido <strong>#' + idPedido + '</strong>?',
            position: 'center',
            buttons: [
                ['<button><b>Sí</b></button>', function (instance, toast) {
                    cambiarEstadoPedido(idPedido, nuevoEstado, grupo);

                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
        
                }, true],
                ['<button>No</button>', function (instance, toast) {
        
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
        
                }],
            ],
            onClosing: function(instance, toast, closedBy){
                //console.info('Closing | closedBy: ' + closedBy);
            },
            onClosed: function(instance, toast, closedBy){
                //console.info('Closed | closedBy: ' + closedBy);
            }
        });
    }

    function cambiarEstadoPedido(idPedido, nuevoEstado, grupo) {
        $.ajax({
            type: 'POST',
            url: `/api/pedido/updateEstado`,
            data: { 
                id: idPedido,
                estado: nuevoEstado
            },
            beforeSend: function () {
                $('.tabla, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            cargarPedidos();

            flashy('¡Estado Actualizado!', {
                type: 'success',
                animation: 'bounce',
            });

            grupo.find('.dropdown-toggle').text(nuevoEstado); //Cambiamos el texto del botón al nuevo estado
            let boton = grupo.find('.dropdown-toggle'); // Obtenemos el botón para cambiar su color
            boton.removeClass(clasesColor); // Removemos todas las clases de color posibles
            boton.addClass(colorEstado[nuevoEstado].color); // Agregamos la clase de color correspondiente al nuevo estado usando el objeto colorEstado
            
        }).always(() => {
            $('.tabla, .spinner-border').toggleClass('d-none');
        });
        
    }

    //Ver items pedido -----------------------------------------------------------------------------------------------------------------------------------------------------
    $('#modalVerMas').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let info = JSON.parse(decodeURIComponent(button.data('items')));
        let titulo = JSON.parse(decodeURIComponent(button.data('titulo')));
        
        let list = $(this).find('.descripcion-menu');
        list.empty();
        info.forEach(item => {
            listitem = `<li class="list-group-item d-flex justify-content-between align-items-start" data-precio="${item.precio}">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">${item.nombre_item_pedido}</div>
                                ${item.descripcion_pedido}
                            </div>
                            <span class="badge bg-primary rounded-pill">${item.cantidad}</span>
                        </li><hr>`;
            list.append(listitem);
        });
       $("#modalVerMas .descripcion-menu").append(list);
       $("#modalVerMas .titulo-menu").html(titulo);
    });

});