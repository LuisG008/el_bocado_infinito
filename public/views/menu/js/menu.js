$(function () {
    (function init() {
        cargarMenu();
    })();

    /**
     * Consulta todos los menus
     */
    function cargarMenu() {
        $.ajax({
            type: 'GET',
            url: `/api/menu`,
            dataType: 'json',
            data: {},
            beforeSend: function () {
                 $('.list-menu, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            mostrarMenu(response.data);
        }).always(() => {
            $('.list-menu, .spinner-border').toggleClass('d-none');
        });
    }

    function mostrarMenu(recordMenu) {
        let list = $('.list-menu');
        list.empty();

        recordMenu.forEach(item => {
            text = 'Publico';
            color = 'bg-success';
            if(item.estado != 'Activo'){
                color = 'bg-danger';
                text = 'No listado';
            }
            let descripcion = item.descripcion;
            if(item.descripcion.length > 150){
                descripcion = item.descripcion.substring(0, 150) + '<a href="#" data-descripcion="' + encodeURIComponent(JSON.stringify(item.descripcion)) + '" data-titulo="' + encodeURIComponent(JSON.stringify('#' + item.id + ' ' + item.nombre)) + '" class="text-decoration-none"  data-bs-toggle="modal" data-bs-target="#modalVerMas"> Ver más..</a>';
            }

            let menu = `<div class="menu">
                <div class="card">
                    <img src="/${item.rutaImagen}" class="card-img-top img-menu"
                        alt="Lo sentimos, no se pudo cargar la imagen">
                    <div class="card-body card-body-menu">
                        <p class="card-text">
                            <span class="title-card-body">${item.nombre}</span><br>
                            <p class="descrip-card-body">
                                ${descripcion}
                            </p>
                        </p>

                        <div class="acciones">
                            <div class="numero-menu">
                                <strong class="me-1">#${item.id}</strong>                       
                            </div>
                            <div class="estado-menu me-1">
                                <span class="badge ${color}">${text}</span>
                            </div>
                            <div class="accion-menu">
                                <i class="btn bi bi-pencil-square" data-bs-toggle="modal" data-bs-target="#editarMenu" data-info='${encodeURIComponent(JSON.stringify(item))}'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
            list.append(menu);
        }
        );
    }

    //CREAR -----------------------------------------------------------------------------------------------------------------------------------------------------
    $('#crearMenu').on('show.bs.modal', function (event) {
        // limpiar validaciones jQuery Validate
        $('#formAddMenu').validate().resetForm();
        $('#formAddMenu .form-control, #formAddMenu .form-select').removeClass('is-invalid is-valid');
    });

    $('#formAddMenu').validate({
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
            nombre: { 
                required: true, 
                minlength: 3 
            },
            descripcion: {
                required: true,
                minlength: 10
            },
            precio: {
                required: true
            },
            imagen: { 
                required: true
            },
        },
        messages: {
            nombre: "Ingrese el titulo del menú (mínimo 3 caracteres)",
            descripcion: "Ingrese descripción válida",
            precio: "Ingrese precio válido",
            imagen: "Cargue una imagen"
        },

        submitHandler: function (form) {
            guardarMenu();
        }
    });

    // Formatear precio con separadores de miles
    $('input[name="precio"]').on('input', function () {
        $(this).val(formatearMiles($(this).val()));
    });

    function formatearMiles(valor) {
        let limpio = valor.toString().replace(/\D/g, '');
        if (limpio) {
            return limpio.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        return '';
    }

    function guardarMenu() {
        let precioLimpio = $('input[name="precio"]').val().replace(/\D/g, '');
   
        let imagenInput = $('#imagen')[0];
   
        // FormData es TODO el contenido, no va dentro de un objeto
        let formData = new FormData();
        formData.append('nombre', $('#nombre').val());
        formData.append('descripcion', $('#descripcion').val());
        formData.append('precio', precioLimpio);
        formData.append('estado', $('input[name="publicar"]').is(':checked') ? 'Activo' : 'Inactivo');
        
        // Agregar archivo
        if (imagenInput.files.length > 0) {
            formData.append('imagen', imagenInput.files[0]);
        }

        $.ajax({
            type: 'POST',
            url: `/api/menu/create`,
            data: formData,
            contentType: false,  // Importante: dejar que FormData maneje el content-type
            processData: false,  // Importante: no procesar FormData
            beforeSend: function () {
                $('.list-menu, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            cargarMenu();

            flashy('¡Menu creado!', {
                type: 'success',
                animation: 'bounce',
            });

            // limpiar formulario
            $('#crearMenu input').val('');
            $('#imagen').val('');

            // cerrar modal
            $('#crearMenu').modal('hide');
            
        }).always(() => {
            $('.list-menu, .spinner-border').toggleClass('d-none');
        });
    }

    //EDITAR -----------------------------------------------------------------------------------------------------------------------------------------------------
    $('#formEditMenu').validate({
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
            nombreEdit: { 
                required: true, 
                minlength: 3 
            },
            descripcionEdit: {
                required: true,
                minlength: 10
            },
            precioEdit: {
                required: true
            },
            imagenEdit: { 
                required: false
            },
        },
        messages: {
            nombreEdit: "Ingrese el titulo del menú (mínimo 3 caracteres)",
            descripcionEdit: "Ingrese descripción válida",
            precioEdit: "Ingrese precio válido",
            imagenEdit: "Cargue una imagen"
        },

        submitHandler: function (form) {
            guardarEdicion();
        }
    });

     function guardarEdicion() {
        let idmenu = $('#idmenu').val();

        let precioLimpio = $('input[name="precioEdit"]').val().replace(/\D/g, '');
   
        // FormData es TODO el contenido, no va dentro de un objeto
        let formData = new FormData();
        formData.append('nombre', $('#nombreEdit').val());
        formData.append('descripcion', $('#descripcionEdit').val());
        formData.append('precio', precioLimpio);
        formData.append('estado', $('input[name="publicarEdit"]').is(':checked') ? 'Activo' : 'Inactivo');
        
        let imagenInput = $('#imagenEdit')[0];
        // Agregar archivo
        if (imagenInput.files.length > 0) {
            formData.append('imagen', imagenInput.files[0]);
        }
  

        $.ajax({
            type: 'POST',
            url: `/api/menu/edit/${idmenu}`,
            data: formData,
            contentType: false,  // Importante: dejar que FormData maneje el content-type
            processData: false,  // Importante: no procesar FormData
            beforeSend: function () {
                $('.list-menu, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            cargarMenu();

            flashy('¡Menu editado!', {
                type: 'success',
                animation: 'bounce',
            });

            // limpiar formulario
            $('#editarMenu input').val('');
            $('#imagen').val('');

            // cerrar modal
            $('#editarMenu').modal('hide');
            
        }).always(() => {
            $('.list-menu, .spinner-border').toggleClass('d-none');
        });
    }

    $('#editarMenu').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let info = JSON.parse(decodeURIComponent(button.data('info')));
        
        // limpiar validaciones jQuery Validate
        $('#formEditMenu').validate().resetForm();
        $('#formEditMenu .form-control, #formEditMenu .form-select').removeClass('is-invalid is-valid');

        $('#idmenu').val(info.id);
        $('#nombreEdit').val(info.nombre);
        $('#descripcionEdit').val(info.descripcion);
        $('#precioEdit').val(formatearMiles(info.precio));        
        $('#publicarEdit').prop('checked', info.estado === 'Activo');
    });
    //FIN EDITAR

    //Ver mas de la descripcion del menu -----------------------------------------------------------------------------------------------------------------------------------------------------
    $('#modalVerMas').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let info = JSON.parse(decodeURIComponent(button.data('descripcion')));
        let titulo = JSON.parse(decodeURIComponent(button.data('titulo')));
        
       $("#modalVerMas .descripcion-menu").append(info);
       $("#modalVerMas .titulo-menu").html(titulo);
    });

    //Validacion para solo permitir imagenes
    $('#imagen').on('change', function () {
        let archivo = this.files[0];

        if (archivo) {
            let tipo = archivo.type;

            // Validar que sea imagen
            if (!tipo.startsWith('image/')) {
                flashy.error("Solo se permiten imágenes");
                $(this).val(''); // limpiar input
                return;
            }
        }
    });

    //Buscador de menu  -----------------------------------------------------------------------------------------------------------------------------------------------------
    $('#buscador').on('input', function () {
        let texto = $(this).val().trim();
        if (texto == '') {
            cargarMenu();
        }
        
    });

    let xhrBusqueda = null;
    let timer;
    $('#buscador').on('keyup', function () {
        clearTimeout(timer);

        let texto = $(this).val();

        timer = setTimeout(function () {
        
            // evitar buscar si está vacío
            if (texto.length < 1) {
                return;
            }

            if (xhrBusqueda !== null) {
                xhrBusqueda.abort();
            }
            
            xhrBusqueda = $.ajax({
                type: 'GET',
                url: `/api/menu/buscar`,
                dataType: 'json',
                data: {
                    texto
                },
                beforeSend: function () {
                    $('.list-menu, .spinner-border').toggleClass('d-none');
                },
            }).fail((jqXHR) => {
                flashy.error(jqXHR.responseJSON.message);
            }).done(response => {

                mostrarMenu(response.data);

            }).always(() => {
                $('.list-menu, .spinner-border').toggleClass('d-none');
            });

        }, 400); // espera a que deje de escribir

    });

});