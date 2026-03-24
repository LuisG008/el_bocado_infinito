$(function () {
    /**
     * Click en el menu lateral
     */
    $('.nav-link').on('click', function (e) {
         e.preventDefault();
        $('.nav-link').removeClass('active'); 
        $('.nav-link').addClass('text-black');
        $(this).addClass('active');
        $(this).removeClass('text-black');

        
        switch ($(this).data('id')) {
            case 'pedido':
                $('#pantalla').attr("src", "/views/pedidos/pedidos.html");
                break;
            case 'menu':
                $('#pantalla').attr("src", "/views/menu/menu.html");
                break;
            case 'usuario':
                $('#pantalla').attr("src", "/views/usuario/usuario.html");
                break;
            case 'cocina':
                $('#pantalla').attr("src", "/views/cocina/cocina.html");
                break;
            case 'entrega':
                $('#pantalla').attr("src", "/views/entrega/entrega.html");
                break;
            case 'caja':
                $('#pantalla').attr("src", "/views/caja/caja.html");
                break;
            case 'cliente':
                $('#pantalla').attr("src", "/views/cliente/cliente.html");
                break;
        }
    });
});