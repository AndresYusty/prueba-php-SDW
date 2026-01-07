/**
 * Script principal del formulario
 * Maneja validaciones, Ajax y geolocalización
 */

$(document).ready(function() {
    // Inicializar Select2 para los select con búsqueda
    $('#genero').select2({
        placeholder: 'Seleccione un género',
        allowClear: true,
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            }
        }
    });

    $('#preferencias').select2({
        placeholder: 'Seleccione sus preferencias',
        allowClear: true,
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            }
        }
    });

    /**
     * Validación del campo documento
     * Verifica que solo contenga números
     */
    $('#documento').on('input', function() {
        const documento = $(this).val();
        const tipoDocumento = $('#tipo_documento').val();
        const errorElement = $('#error_documento');
        const infoElement = $('#info_documento');
        
        // Limpiar mensajes anteriores
        errorElement.text('');
        infoElement.text('');

        // Validar que solo contenga números
        if (documento && !/^\d+$/.test(documento)) {
            errorElement.text('El documento solo debe contener números');
            return;
        }

        // Verificar si el usuario existe (solo si hay documento y tipo)
        if (documento && tipoDocumento && documento.length >= 5) {
            verificarUsuarioExistente(tipoDocumento, documento);
        }
    });

    /**
     * Validación del campo nombre
     * Solo permite letras, números y espacios
     */
    $('#nombre').on('input', function() {
        const nombre = $(this).val();
        const errorElement = $('#error_nombre');
        
        errorElement.text('');

        // Validar que solo contenga letras, números y espacios
        if (nombre && !/^[A-Za-z0-9\s]+$/.test(nombre)) {
            errorElement.text('El nombre solo puede contener letras, números y espacios');
            $(this).val(nombre.replace(/[^A-Za-z0-9\s]/g, ''));
        }
    });

    /**
     * Validación del campo edad
     * Verifica que sea mayor de edad (18 años)
     */
    $('#edad').on('input', function() {
        const edad = parseInt($(this).val());
        const errorElement = $('#error_edad');
        
        errorElement.text('');

        if (edad && edad < 18) {
            errorElement.text('Debe ser mayor de edad (mínimo 18 años)');
        } else if (edad && edad > 120) {
            errorElement.text('La edad ingresada no es válida');
        }
    });

    /**
     * Botón para obtener coordenadas del dispositivo
     */
    $('#btnObtenerCoordenadas').on('click', function() {
        obtenerCoordenadas();
    });

    /**
     * Limpiar formulario
     */
    $('#btnLimpiar').on('click', function() {
        limpiarFormulario();
    });

    /**
     * Envío del formulario con Ajax
     */
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validar formulario antes de enviar
        if (validarFormulario()) {
            enviarDatos();
        }
    });

    /**
     * Obtener coordenadas del dispositivo usando la API de Geolocalización
     */
    function obtenerCoordenadas() {
        const errorElement = $('#error_coordenadas');
        const btnObtener = $('#btnObtenerCoordenadas');
        
        errorElement.text('');
        btnObtener.prop('disabled', true).text('Obteniendo coordenadas...');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const latitud = position.coords.latitude;
                    const longitud = position.coords.longitude;
                    
                    $('#latitud').val(latitud.toFixed(6));
                    $('#longitud').val(longitud.toFixed(6));
                    
                    btnObtener.prop('disabled', false).text('Obtener Coordenadas');
                    errorElement.text('Coordenadas obtenidas correctamente').css('color', '#27ae60');
                },
                function(error) {
                    let mensajeError = 'Error al obtener coordenadas: ';
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            mensajeError += 'Permiso denegado por el usuario';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            mensajeError += 'Información de ubicación no disponible';
                            break;
                        case error.TIMEOUT:
                            mensajeError += 'Tiempo de espera agotado';
                            break;
                        default:
                            mensajeError += 'Error desconocido';
                            break;
                    }
                    
                    errorElement.text(mensajeError);
                    btnObtener.prop('disabled', false).text('Obtener Coordenadas');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            errorElement.text('Su navegador no soporta geolocalización');
            btnObtener.prop('disabled', false).text('Obtener Coordenadas');
        }
    }

    /**
     * Verificar si el usuario ya existe en la base de datos
     * @param {string} tipoDocumento - Tipo de documento
     * @param {string} documento - Número de documento
     */
    function verificarUsuarioExistente(tipoDocumento, documento) {
        const userId = $('#user_id').val();
        
        // Si estamos en modo edición, no verificar
        if (userId) {
            return;
        }

        $.ajax({
            url: 'php/verificar_usuario.php',
            method: 'POST',
            data: {
                tipo_documento: tipoDocumento,
                documento: documento
            },
            dataType: 'json',
            success: function(response) {
                const errorElement = $('#error_documento');
                const infoElement = $('#info_documento');
                
                if (response.existe) {
                    errorElement.text('Este usuario ya está registrado');
                    $('#documento').addClass('error-input');
                } else {
                    errorElement.text('');
                    infoElement.text('Documento disponible');
                    $('#documento').removeClass('error-input');
                }
            },
            error: function() {
                console.error('Error al verificar usuario');
            }
        });
    }

    /**
     * Validar todos los campos del formulario
     * @returns {boolean} - true si el formulario es válido
     */
    function validarFormulario() {
        let esValido = true;

        // Validar tipo de documento
        if (!$('#tipo_documento').val()) {
            $('#error_tipo_documento').text('Debe seleccionar un tipo de documento');
            esValido = false;
        } else {
            $('#error_tipo_documento').text('');
        }

        // Validar documento
        const documento = $('#documento').val();
        if (!documento || !/^\d+$/.test(documento)) {
            $('#error_documento').text('El documento es requerido y solo debe contener números');
            esValido = false;
        }

        // Validar nombre
        const nombre = $('#nombre').val();
        if (!nombre || !/^[A-Za-z0-9\s]+$/.test(nombre)) {
            $('#error_nombre').text('El nombre es requerido y solo puede contener letras, números y espacios');
            esValido = false;
        }

        // Validar edad
        const edad = parseInt($('#edad').val());
        if (!edad || edad < 18 || edad > 120) {
            $('#error_edad').text('La edad es requerida y debe ser mayor de 18 años');
            esValido = false;
        }

        // Validar género
        if (!$('#genero').val()) {
            $('#error_genero').text('Debe seleccionar un género');
            esValido = false;
        } else {
            $('#error_genero').text('');
        }

        // Validar preferencias
        const preferencias = $('#preferencias').val();
        if (!preferencias || preferencias.length === 0) {
            $('#error_preferencias').text('Debe seleccionar al menos una preferencia');
            esValido = false;
        } else {
            $('#error_preferencias').text('');
        }

        return esValido;
    }

    /**
     * Enviar datos del formulario mediante Ajax
     */
    function enviarDatos() {
        const btnSubmit = $('#btnSubmit');
        const btnText = $('#btnText');
        const mensajeRespuesta = $('#mensajeRespuesta');
        
        // Deshabilitar botón durante el envío
        btnSubmit.prop('disabled', true);
        btnText.text('Enviando...');
        mensajeRespuesta.removeClass('success error').hide();

        // Obtener datos del formulario
        const formData = {
            tipo_documento: $('#tipo_documento').val(),
            documento: $('#documento').val(),
            nombre: $('#nombre').val(),
            edad: $('#edad').val(),
            genero: $('#genero').val(),
            preferencias: $('#preferencias').val(), // Array de preferencias
            latitud: $('#latitud').val(),
            longitud: $('#longitud').val(),
            user_id: $('#user_id').val()
        };

        // Enviar datos mediante Ajax
        $.ajax({
            url: 'php/controlador.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    mensajeRespuesta
                        .addClass('success')
                        .text(response.message)
                        .show();
                    
                    // Si es creación exitosa, limpiar formulario
                    if (!formData.user_id) {
                        setTimeout(function() {
                            limpiarFormulario();
                        }, 2000);
                    }
                } else {
                    mensajeRespuesta
                        .addClass('error')
                        .text(response.message)
                        .show();
                }
            },
            error: function(xhr, status, error) {
                let mensaje = 'Error al procesar la solicitud';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                
                mensajeRespuesta
                    .addClass('error')
                    .text(mensaje)
                    .show();
            },
            complete: function() {
                // Rehabilitar botón
                btnSubmit.prop('disabled', false);
                btnText.text($('#user_id').val() ? 'Actualizar Usuario' : 'Registrar Usuario');
                
                // Scroll al mensaje
                $('html, body').animate({
                    scrollTop: mensajeRespuesta.offset().top - 100
                }, 500);
            }
        });
    }

    /**
     * Limpiar todos los campos del formulario
     */
    function limpiarFormulario() {
        $('#userForm')[0].reset();
        $('#user_id').val('');
        $('#genero').val(null).trigger('change');
        $('#preferencias').val(null).trigger('change');
        
        // Limpiar mensajes de error
        $('.error-message').text('');
        $('.info-message').text('');
        $('#mensajeRespuesta').removeClass('success error').hide();
        
        // Actualizar texto del botón
        $('#btnText').text('Registrar Usuario');
    }
});

