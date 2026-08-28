$(function () {

    (function init() {
        loadDataUser();
        validPermission();
        cargarPrimerMenuDisponible();
    })();

     $('#cerrar_session').on('click', function (event) {
        
        $.ajax({
            type: 'GET',
            url: `/logout`,
            dataType: 'json',
            beforeSend: function () {
               $("#cerrar_session").empty();
                $("#cerrar_session").append(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span class="visually-hidden">Loading...</span>`);
            },
        }).fail((jqXHR) => {
            $("#cerrar_session").empty();
            $("#cerrar_session").append(`<i class="bi bi-box-arrow-left fs-4"></i>`);
            console.log(jqXHR);
            let error = JSON.parse(jqXHR.responseText);
            if(error.detail == "The presented password is invalid."){
                //flashy.error("La contraseña es incorrecta");
            }else{
                //flashy.error(error.detail);
            }
        }).done(response => {
            //window.location.href = '/views/login/login.html';
            console.log(response);
            $("#cerrar_session").empty();
            $("#cerrar_session").append(`<i class="bi bi-box-arrow-left fs-4"></i>`);
        }).always(() => {
            $("#cerrar_session").empty();
            $("#cerrar_session").append(`<i class="bi bi-box-arrow-left fs-4"></i>`);
        });
    });


    function loadDataUser() {
        $("#nombreUsuario").text(JSON.parse(localStorage.getItem('user')).nombre);
        $(".nombre").attr("title", JSON.parse(localStorage.getItem('user')).nombre);
    }
    
    function validPermission() {
        let user = JSON.parse(localStorage.getItem('user'));
        $('[data-role]').each(function () {

            let roles = $(this).data('role').split(',');

            let autorizado = roles.some(r => user.roles.includes(r.trim()));

            if (!autorizado) {
                $(this).hide();
            }

        });
    }

    function cargarPrimerMenuDisponible() {

        let primerMenu = $('.nav li:visible a').first();

        if (primerMenu.length === 0) {
            flashy.error("No tiene permisos para acceder a ningún módulo.");
            return;
        }

        primerMenu.addClass('active');
        primerMenu.removeClass('text-black');
        
        $('#pantalla').attr(
            'src',
            `/views/${primerMenu.data('id')}/${primerMenu.data('id')}.html`
        );

    }
});