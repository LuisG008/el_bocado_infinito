$(function () {
    (function init() {
        cargarUsuarios();
    })();

    /**
     * Consulta los usuarios
     */
    function cargarUsuarios() {
        $.ajax({
            type: 'GET',
            url: `/api/usuario`,
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

    //CREAR
    $('#crearUsuario').on('show.bs.modal', function (event) {
        // limpiar validaciones jQuery Validate
        $('#formAddUsuario').validate().resetForm();
        $('#formAddUsuario .form-control, #formAddUsuario .form-select').removeClass('is-invalid is-valid');
    });

    $('#formAddUsuario').validate({
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
            identificacion: {
                required: true,
                digits: true
            },
            telefono: {
                required: true,
                digits: true
            },
            cargo: { 
                required: true
            },
            clave: { 
                required: true, 
                minlength: 3 
            }
        },
        messages: {
            nombre: "Ingrese el nombre",
            identificacion: "Ingrese identificación válida",
            telefono: "Ingrese teléfono válido",
            cargo: "Seleccione un cargo",
            clave: "Ingrese la clave"
        },

        submitHandler: function (form) {
            guardarUsuario();
        }

    });

    function guardarUsuario() {
        $.ajax({
            type: 'POST',
            url: `/api/usuario/create`,
            dataType: 'json',
            data: {
                nombres: $('#nombre').val(),
                identificacion: $('#identificacion').val(),
                telefono: $('#telefono').val(),
                idcargo: $('#cargo').val(),
                clave: $('#clave').val(),
                estado: 'Activo'
            },
            beforeSend: function () {
                $('.tabla, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            cargarUsuarios();
            flashy('¡Usuario creado!', {
                type: 'success',
                animation: 'bounce',
            });

            // limpiar formulario
            $('#crearUsuario input').val('');
            $('#cargo').val('');

            // cerrar modal
            $('#crearUsuario').modal('hide');
            
        }).always(() => {
            $('.tabla, .spinner-border').toggleClass('d-none');
        });
    }
    //Fin CREAR
    

    

    //EDITAR
    $('#editarUsuario').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let info = JSON.parse(decodeURIComponent(button.data('info')));
        
        // limpiar validaciones jQuery Validate
        $('#formEditUsuario').validate().resetForm();
        $('#formEditUsuario .form-control, #formEditUsuario .form-select').removeClass('is-invalid is-valid');

        $('#idusuario').val(info.idusuario);
        $('#nombreEdit').val(info.nombres);
        $('#identificacionEdit').val(info.identificacion);
        $('#telefonoEdit').val(info.telefono);
        $('#cargoEdit').val(info.idcargo);
        $('#claveEdit').val(info.clave);
    });

    $('#formEditUsuario').validate({
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
            identificacionEdit: {
                required: true,
                digits: true
            },
            telefonoEdit: {
                required: true,
                digits: true
            },
            cargoEdit: { 
                required: true
            },
            claveEdit: { 
                required: true, 
                minlength: 3 
            }
        },
        messages: {
            nombreEdit: "Ingrese el nombre",
            identificacionEdit: "Ingrese identificación válida",
            telefonoEdit: "Ingrese teléfono válido",
            cargoEdit: "Seleccione un cargo",
            claveEdit: "Ingrese la clave"
        },

        submitHandler: function (form) {
            guardarEdicion();
        }

    });

    function guardarEdicion() {
        let idusuario = $('#idusuario').val();

        $.ajax({
            type: 'PUT',
            url: `/api/usuario/edit/${idusuario}`,
            dataType: 'json',
            data: {
                nombres: $('#nombreEdit').val(),
                identificacion: $('#identificacionEdit').val(),
                telefono: $('#telefonoEdit').val(),
                idcargo: $('#cargoEdit').val(),
                clave: $('#claveEdit').val()
            },
            beforeSend: function () {
                $('.tabla, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            cargarUsuarios();
            flashy('¡Usuario editado!', {
                type: 'success',
                animation: 'bounce',
                icon: ' '
            });

            // cerrar modal
            $('#editarUsuario').modal('hide');
        }).always(() => {
            $('.tabla, .spinner-border').toggleClass('d-none');
        });
    }
    //Fin EDITAR

    //Inactivar Usuario
    $(document).on('click', '#inactivar', function () {
        let idusuario = $(this).data('id');
        let estado = $(this).data('estado');
        let accion = estado === 'Activo' ? 'inactivar' : 'activar';
        
        iziToast.question({
            timeout: 20000,
            close: true,
            progressBar: false,
            overlay: true,
            displayMode: 'once',
            id: 'question',
            zindex: 999,
            title: 'Hey',
            message: '¿Esta seguro de ' + accion + ' el usuario?',
            position: 'center',
            buttons: [
                ['<button><b>YES</b></button>', function (instance, toast) {
                    activarInactivarUsuario(idusuario, accion);

                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
        
                }, true],
                ['<button>NO</button>', function (instance, toast) {
        
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
    });

    function activarInactivarUsuario(idusuario, accion) {
        $.ajax({
            type: 'PUT',
            url: `/api/usuario/${idusuario}`,
            dataType: 'json',
            data: {
                accion
            },
            beforeSend: function () {
                $('.tabla, .spinner-border').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            cargarUsuarios();

            flashy('¡' + response.message + '!', {
                type: 'success',
                animation: 'bounce',
                icon: ' '
            });
            
        }).always(() => {
            $('.tabla, .spinner-border').toggleClass('d-none');
        });
    }


    //Buscador
    $('#buscador').on('input', function () {
        let texto = $(this).val().trim();
        if (texto == '') {
            cargarUsuarios();
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
                url: `/api/usuario/buscar`,
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

    /**
     * Llena la tabla con los usuarios
     */
    function llenarTabla(data){

        let tabla = $('#tabla-usuarios');
        tabla.empty();
        if(data){
            data.forEach(usuario => {
                let color = 'bg-success';
                let iconColor = 'bi-dash-circle-fill text-danger';
                text = 'Inactivar';
                if(usuario.estado_usuario != 'Activo'){
                    text = 'Activar';
                    color = 'bg-danger';
                    iconColor = 'bi-speedometer text-success';
                }

                let row = `
                    <tr>
                        <th>${usuario.identificacion}</th>
                        <td>${usuario.nombres}</td>
                        <td>${usuario.telefono}</td>
                        <td>${usuario.nombre_cargo}</td>
                        <td>...</td>
                        <td><span class="badge ${color}">${usuario.estado_usuario}</span></td>
                        <td><i class="bi bi-pencil-square me-2" title="Editar" data-bs-toggle="modal" data-info='${encodeURIComponent(JSON.stringify(usuario))}'
                                data-bs-target="#editarUsuario" style="cursor: pointer;"></i>
                            <i class="bi ${iconColor}" id="inactivar" title="${text}" data-estado="${usuario.estado_usuario}" data-id="${usuario.idusuario}" style="cursor: pointer;"></i>
                        </td>
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


});