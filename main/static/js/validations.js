// /home/innovaol/girapp/main/static/js/validations.js

document.addEventListener("DOMContentLoaded", function () {
    // Agregar esta línea para definir "form" (se toma el primer formulario de la página)
    const form = document.querySelector("form");

    if (form) {
      const firstError = form.querySelector(".is-invalid");
      if (firstError) {
          // Detecta si está dentro de un contenedor especial (como .form-check)
          const scrollTarget = firstError.closest(".form-check") || firstError;
          
          scrollTarget.scrollIntoView({
              behavior: "smooth",
              block: "center",
              inline: "nearest"
          });
      
          // También hace focus para resaltarlo mejor
          if (firstError.focus) {
              setTimeout(() => firstError.focus(), 300);
          }
      }
    }

    function validateField(field, errorMessage) {
        const value = field.value.trim();
        const errorDiv = field.nextElementSibling;

        if (!value) {
            field.classList.add("is-invalid");
            errorDiv.textContent = errorMessage;
            return false;
        }
        field.classList.remove("is-invalid");
        errorDiv.textContent = "";
        return true;
    }

    function validatePasswords(password1, password2) {
        const errorDiv1 = password1.nextElementSibling;
        const errorDiv2 = password2.nextElementSibling;

        // Limpiar mensajes previos
        password1.classList.remove("is-invalid");
        password2.classList.remove("is-invalid");
        errorDiv1.textContent = "";
        errorDiv2.textContent = "";
        errorDiv1.style.display = "none";
        errorDiv2.style.display = "none";

        let valid = true;

        // Validar si están vacíos
        if (password1.value.trim() === "") {
            password1.classList.add("is-invalid");
            errorDiv1.textContent = "La contraseña es obligatoria.";
            errorDiv1.style.display = "block";
            valid = false;
        }
        if (password2.value.trim() === "") {
            password2.classList.add("is-invalid");
            errorDiv2.textContent = "Debes confirmar la contraseña.";
            errorDiv2.style.display = "block";
            valid = false;
        }

        // Si alguno está vacío, no continuar
        if (!valid) return false;

        // Validar que ambas contraseñas coincidan
        if (password1.value !== password2.value) {
            password2.classList.add("is-invalid");
            errorDiv2.textContent = "Las contraseñas no coinciden.";
            errorDiv2.style.display = "block";
            valid = false;
        }

        return valid;
    }

    function validateAjaxField(field, url, dataKey) {
        return new Promise((resolve) => {
            const value = field.value.trim();
            const errorDiv = field.nextElementSibling;

            if (!value) {
                field.classList.add("is-invalid");
                errorDiv.textContent = "Este campo es obligatorio.";
                resolve(false);
                return;
            }

            fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRFToken": document.querySelector("input[name='csrfmiddlewaretoken']").value
                },
                body: JSON.stringify({ [dataKey]: value })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.valid) {
                    field.classList.add("is-invalid");
                    errorDiv.textContent = data.error;
                    resolve(false);
                } else {
                    field.classList.remove("is-invalid");
                    errorDiv.textContent = "";
                    resolve(true);
                }
            })
            .catch(() => {
                field.classList.add("is-invalid");
                errorDiv.textContent = "Error al validar los datos. Intenta de nuevo.";
                resolve(false);
            });
        });
    }
    
    async function runFieldValidations(fieldInfo, form, originalValues) {
        let field = form.querySelector(`input[name='${fieldInfo.name}'], select[name='${fieldInfo.name}']`);
        if (!field) return true;

        console.log(`▶ Validando campo "${fieldInfo.name}" → valor actual: "${field.value.trim()}"`);

        let errors = [];

        // Si el campo está vacío, solo se ejecuta la validación síncrona.
        if (field.value.trim() === "") {
            let syncValid = validateField(field, fieldInfo.errorMessage);
            if (!syncValid) {
                errors.push(field.nextElementSibling.textContent);
            }
        } else {
            // Ejecutar validación AJAX si aplica y el valor ha cambiado (en edición)
            if (fieldInfo.ajaxValidation && field.value.trim() !== originalValues[fieldInfo.name]) {
                let ajaxValid = await fieldInfo.ajaxValidation(field);
                if (!ajaxValid) {
                    errors.push(field.nextElementSibling.textContent);
                }
            }

            // Ejecutar validación personalizada (por ejemplo, para contraseñas)
            if (fieldInfo.validate) {
                let customValid = fieldInfo.validate(
                    form.querySelector("input[name='password1']"),
                    form.querySelector("input[name='password2']")
                );
                if (!customValid) {
                    errors.push(field.nextElementSibling.textContent);
                }
            } else {
                // Ejecutar validación síncrona (para campos no vacíos)
                let syncValid = validateField(field, fieldInfo.errorMessage);
                if (!syncValid) {
                    errors.push(field.nextElementSibling.textContent);
                }
            }
        }

        if (errors.length > 0) {
            field.classList.add("is-invalid");
            field.nextElementSibling.textContent = errors.join(" ");
            field.nextElementSibling.style.display = "block";
            return false;
        } else {
            field.classList.remove("is-invalid");
            field.nextElementSibling.textContent = "";
            field.nextElementSibling.style.display = "none";
            return true;
        }
    }

    function setupFormValidation(formId, fields) {
        const form = document.getElementById(formId);
        if (!form) return;
        
        console.log(`🔍 Inicializando validación para formulario "${formId}"`);

        // Desactivar validación nativa HTML5
        form.noValidate = true;

        // Guardar valores originales
        let originalValues = {};
        fields.forEach(fieldInfo => {
            let field = form.querySelector(`input[name='${fieldInfo.name}'], select[name='${fieldInfo.name}']`);
            if (field) {
                originalValues[fieldInfo.name] = field.value.trim();
            }
        });

        form.addEventListener("submit", async function (event) {
            event.preventDefault();
            let isValid = true;
            let validationPromises = [];

            // Validaciones generales definidas en setupFormValidation
            for (let fieldInfo of fields) {
                validationPromises.push(runFieldValidations(fieldInfo, form, originalValues));
            }

            let results = await Promise.all(validationPromises);
            if (results.includes(false)) isValid = false;

            // VALIDACIÓN ADICIONAL: Archivos adjuntos sin tipo seleccionado
            let fileError = false;
            // Recorremos cada input de archivo
            $(".file-input").each(function () {
                let fileInput = $(this);
                let fileVal = fileInput.val().trim();
                if (fileVal !== "") { // Si hay un archivo seleccionado
                    // Buscamos el select asociado (por ejemplo, file_1_type)
                    let typeSelect = $("[name='" + fileInput.attr("name") + "_type']");
                    if (!typeSelect.val()) {
                        fileError = true;
                        fileInput.addClass("is-invalid");
                        typeSelect.addClass("is-invalid");
                    } else {
                        fileInput.removeClass("is-invalid");
                        typeSelect.removeClass("is-invalid");
                    }
                }
            });

            if (fileError) {
                isValid = false;
                $("#file-error-message").show();
            } else {
                $("#file-error-message").hide();
            }

            if (!isValid) {
                const firstErrorInput = form.querySelector(".is-invalid");
        
                if (firstErrorInput) {
                    const wrapper = firstErrorInput.closest(".form-group, .form-check, .col-md-6, .col") || firstErrorInput;
        
                    wrapper.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                        inline: "nearest"
                    });
        
                    setTimeout(() => {
                        try {
                            firstErrorInput.focus();
                        } catch (e) {}
                    }, 300);
                }
        
                return;
            }

            // Solo para el formulario de vuelos se aplica el spinner y deshabilitación
            if (form.id === "flightForm") {
                const submitButton = form.querySelector("#submit-btn");
                const submitText = form.querySelector("#submit-text");
                const submitSpinner = form.querySelector("#submit-spinner");

                if (submitButton && submitText && submitSpinner) {
                    submitButton.disabled = true;
                    submitText.textContent = "Guardando...";
                    submitSpinner.classList.remove("d-none");
                }
            }

            form.submit();
        });
    }

    // ---------------------------------------------------------
    // Sección de configuración de validaciones para cada formulario
    // ---------------------------------------------------------
    setupFormValidation("documentTypeForm", [
        {
            name: "name",
            errorMessage: "El nombre del tipo de documento es obligatorio.",
            ajaxValidation: (field) => validateAjaxField(field, "/check/document_type/", "document_type_name")
        }
    ]);

    setupFormValidation("userForm", [
        {
            name: "username",
            errorMessage: "El nombre de usuario es obligatorio.",
            ajaxValidation: (field) => validateAjaxField(field, "/check/username/", "username")
        },
        {
            name: "email",
            errorMessage: "El correo electrónico es obligatorio.",
            ajaxValidation: (field) => validateAjaxField(field, "/check/email/", "email")
        },
        { name: "first_name", errorMessage: "El nombre es obligatorio." },
        { name: "last_name", errorMessage: "El apellido es obligatorio." },
        { name: "password1", errorMessage: "La contraseña es obligatoria." },
        {
            name: "password2",
            errorMessage: "Debes confirmar la contraseña.",
            validate: (password1, password2) => validatePasswords(password1, password2)
        }
    ]);

    setupFormValidation("airlineForm", [
        {
            name: "name",
            errorMessage: "El nombre de la aerolínea es obligatorio.",
            ajaxValidation: (field) => validateAjaxField(field, "/check/airline/", "airline_name")
        }
    ]);

    setupFormValidation("aircraftForm", [
        {
            name: "aeronave",
            errorMessage: "El nombre de la aeronave es obligatorio.",
            ajaxValidation: (field) => validateAjaxField(field, "/check/aircraft/", "aircraft_name")
        },
        { name: "aerolinea", errorMessage: "Selecciona una aerolínea." }
    ]);

    setupFormValidation("groupForm", [
        {
            name: "name",
            errorMessage: "El nombre del grupo es obligatorio.",
            ajaxValidation: (field) => validateAjaxField(field, "/check/group/", "group_name")
        }
    ]);

    setupFormValidation("flightForm", [
        {
            name: "flight_number",
            errorMessage: "El número de vuelo es obligatorio.",
            ajaxValidation: (field) => validateAjaxField(field, "/check/flight_number/", "flight_number")
        },
        { name: "date", errorMessage: "La fecha del vuelo es obligatoria." },
        { name: "airline", errorMessage: "Selecciona una aerolínea." },
        { name: "aircraft", errorMessage: "Selecciona una aeronave." }
    ]);
    
    document.querySelectorAll('.invalid-feedback.d-block').forEach(function(error) {
      const parent = error.closest('.form-check');
      if (parent) {
        const input = parent.querySelector('input');
        if (input) input.classList.add('is-invalid');
      }
    });
});
