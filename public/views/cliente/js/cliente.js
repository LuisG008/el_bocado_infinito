$(function () {
    (function init() {
        cargarClientes();
    })();
    
    /**
     * Consulta los clientes
     */
    function cargarClientes() {
        $.ajax({
            type: 'GET',
            url: `/api/cliente`,
            dataType: 'json',
            data: {},
            beforeSend: function () {
                $('.tabla, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            llenarTabla(response.data);
        }).always(() => {
            $('.tabla, .spinner-border').toggleClass('d-none');
        });
    }

    /**
     * Llena la tabla con los clientes
     */
    function llenarTabla(data){

        let tabla = $('#tabla-clientes');
        tabla.empty();
        if(data){
            data.forEach(cliente => {

                let row = `
                    <tr>
                        <th>${cliente.identificacion}</th>
                        <td>${cliente.nombres}</td>
                        <td>${cliente.telefono}</td>
                    </tr>`;

                tabla.append(row);
            });
        }else{
            let row = `
                <tr>
                    Sin registros
                </tr>`;

            tabla.append(row);
        }
    }

    //Buscador
    $('#buscador').on('input', function () {
        let texto = $(this).val().trim();
        if (texto == '') {
            cargarClientes();
        }
        
    });
    
    let xhrBusqueda = null;
    let timer;
    $('#buscador').on('keyup', function () {
        clearTimeout(timer);

        let texto = $(this).val();

        timer = setTimeout(function () {
        
            // evitar buscar si está vacío
            if (texto.length < 3) {
                return;
            }

            if (xhrBusqueda !== null) {
                xhrBusqueda.abort();
            }
            
            xhrBusqueda = $.ajax({
                type: 'GET',
                url: `/api/cliente/buscar`,
                dataType: 'json',
                data: {
                    texto
                },
                beforeSend: function () {
                    $('.tabla, .spinner-border').toggleClass('d-none');
                },
            }).fail((jqXHR) => {
                flashy.error(jqXHR.responseJSON.message);
            }).done(response => {

                llenarTabla(response.data);

            }).always(() => {
                $('.tabla, .spinner-border').toggleClass('d-none');
            });

        }, 400); // espera a que deje de escribir

    });

});