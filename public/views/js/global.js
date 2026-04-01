
//Redirige cuando la sesion ha finalizado 
let redirigiendo = false;

$(document).ajaxError(function (event, jqXHR) {
    if (jqXHR.status === 401 && !redirigiendo) {
        redirigiendo = true;
        flashy.error(jqXHR.responseJSON.message);
        setTimeout(() => {
            window.top.location.href = '/views/login/login.html';
        }, 1500);
    }
});