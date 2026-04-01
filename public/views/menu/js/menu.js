$(function () {
    (function init() {
        //cargarMenu();
    })();

    /**
     * Consulta los usuarios
     */
    function cargarMenu() {
        $.ajax({
            type: 'GET',
            url: `/api/menu`,
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
});