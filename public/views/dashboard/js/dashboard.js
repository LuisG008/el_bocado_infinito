$(function () {
     $('#cerrar_session').on('click', function (event) {
        $.ajax({
            type: 'GET',
            url: `/logout`,
            dataType: 'json',
            beforeSend: function () {
               
            },
        }).fail((jqXHR) => {
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
        }).always(() => {
            
        });
    });

    
});