$(function () {

    $('#formLogin').validate({
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
                digits: true
            },
            password: { 
                required: true, 
                minlength: 3 
            }
        },
        messages: {
            identificacion: "Ingrese identificación válida",
            password: "Ingrese la Contraseña"
        },

        submitHandler: function (form) {
            validarLogin();
        }

    });

    function validarLogin() {
        $.ajax({
            type: 'POST',
            url: `/login`,
            dataType: 'json',
            data: {
                identificacion: $('#identificacion').val(),
                password: $('#password').val()
            },
            beforeSend: function () {
            },
        }).fail((jqXHR) => {
            console.log(jqXHR);
            let error = JSON.parse(jqXHR.responseText);
            if(error.detail == "The presented password is invalid."){
                flashy.error("La contraseña es incorrecta");
            }else{
                flashy.error(error.detail);
            }
        }).done(response => {
            window.location.href = '/views/dashboard/dashboard.html';
        }).always(() => {
        });
    }

});