// RF19 - Consola administrativa para consultar APIs internas.

const AdminApiModulo = {
    init() {
        this.contenedor = document.getElementById('admin-api-console');
        this.form = document.getElementById('form-api-test');
        this.method = document.getElementById('api-method');
        this.endpoint = document.getElementById('api-endpoint');
        this.body = document.getElementById('api-body');
        this.response = document.getElementById('api-response');
        this.status = document.getElementById('api-status');
        this.tiempo = document.getElementById('api-tiempo');
        this.urlFinal = document.getElementById('api-url-final');
        this.btnEnviar = document.getElementById('btn-enviar-api');
        this.btnLimpiar = document.getElementById('btn-limpiar-api');

        if (!this.contenedor || !this.form) return;

        this.baseUrl = String(this.contenedor.dataset.baseUrl || '').replace(/\/$/, '');

        document.querySelectorAll('.btn-probar-api').forEach((boton) => {
            boton.addEventListener('click', () => {
                this.method.value = boton.dataset.method || 'GET';
                this.endpoint.value = boton.dataset.endpoint || '';
                this.body.value = '';
                this.enviar();
            });
        });

        this.form.addEventListener('submit', (event) => {
            event.preventDefault();
            this.enviar();
        });

        this.btnLimpiar?.addEventListener('click', () => {
            this.response.textContent = 'Selecciona una API y pulsa Probar.';
            this.status.textContent = 'Sin ejecutar';
            this.status.className = 'badge text-bg-secondary';
            this.tiempo.textContent = '';
            this.urlFinal.textContent = '';
            this.body.value = '';
        });
    },

    construirUrl(endpoint) {
        const limpio = String(endpoint || '').trim().replace(/^\/+/, '');

        if (!limpio.startsWith('api/')) {
            throw new Error('Solo se permiten endpoints internos dentro de api/.');
        }

        return `${this.baseUrl}/${limpio}`;
    },

    /**
     * Modal asíncrono para reemplazar el window.confirm()
     */
    confirmarAccion(mensaje) {
        return new Promise((resolve) => {
            const modalEl = document.getElementById('modalConfirmacionApi');
            const mensajeEl = document.getElementById('mensajeConfirmacionApi');
            const btnAceptar = document.getElementById('btnConfirmarApiAceptar');

            if (!modalEl || !btnAceptar) {
                console.warn("No se encontró el modal de confirmación en el DOM.");
                resolve(true); 
                return;
            }

            if (mensaje) mensajeEl.textContent = mensaje;

            // Se asume que Bootstrap está cargado en el proyecto
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            let resuelto = false;

            const limpiarEventos = () => {
                btnAceptar.removeEventListener('click', onAceptar);
                modalEl.removeEventListener('hidden.bs.modal', onCancelar);
            };

            const onAceptar = () => {
                if (resuelto) return;
                resuelto = true;
                limpiarEventos();
                modalInstance.hide();
                resolve(true);
            };

            const onCancelar = () => {
                if (resuelto) return;
                resuelto = true;
                limpiarEventos();
                resolve(false);
            };

            btnAceptar.addEventListener('click', onAceptar);
            modalEl.addEventListener('hidden.bs.modal', onCancelar);

            modalInstance.show();
        });
    },

    async enviar() {
        const metodo = String(this.method.value || 'GET').toUpperCase();
        const metodosPermitidos = new Set(['GET', 'POST', 'PUT', 'PATCH', 'DELETE']);

        if (!metodosPermitidos.has(metodo)) {
            this.mostrarError('Método HTTP no permitido por la consola.');
            return;
        }

        let url;

        try {
            url = this.construirUrl(this.endpoint.value);
        } catch (error) {
            this.mostrarError(error.message);
            return;
        }

        const opciones = {
            method: metodo,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        };

        if (metodo !== 'GET') {
            const cuerpo = this.body.value.trim();

            if (cuerpo !== '') {
                try {
                    JSON.parse(cuerpo);
                } catch {
                    this.mostrarError('El Body no contiene JSON válido.');
                    return;
                }

                opciones.headers['Content-Type'] = 'application/json';
                opciones.body = cuerpo;
            }

            // Usamos la nueva promesa para mostrar el modal de Bootstrap
            const mensaje = `Se ejecutará una petición ${metodo} que puede modificar información del sistema. ¿Deseas continuar?`;
            const continuar = await this.confirmarAccion(mensaje);

            if (!continuar) return; // Se cancela la ejecución si el usuario dice que no
        }

        this.btnEnviar.disabled = true;
        this.status.textContent = 'Consultando';
        this.status.className = 'badge text-bg-warning';
        this.response.textContent = 'Esperando respuesta...';
        this.urlFinal.textContent = `${metodo} ${url}`;
        this.tiempo.textContent = '';

        const inicio = performance.now();

        try {
            const resp = await fetch(url, opciones);
            const duracion = Math.round(performance.now() - inicio);
            const contentType = resp.headers.get('content-type') || '';
            const texto = await resp.text();

            let salida = texto;

            if (contentType.includes('application/json') && texto.trim() !== '') {
                try {
                    salida = JSON.stringify(JSON.parse(texto), null, 2);
                } catch {
                    salida = texto;
                }
            }

            this.response.textContent = salida || '(respuesta vacía)';
            this.status.textContent = `${resp.status} ${resp.statusText}`;
            this.status.className = resp.ok
                ? 'badge text-bg-success'
                : 'badge text-bg-danger';
            this.tiempo.textContent = `${duracion} ms`;
        } catch (error) {
            this.mostrarError(`No fue posible completar la solicitud: ${error.message}`);
        } finally {
            this.btnEnviar.disabled = false;
        }
    },

    mostrarError(mensaje) {
        this.response.textContent = mensaje;
        this.status.textContent = 'Error';
        this.status.className = 'badge text-bg-danger';
        this.tiempo.textContent = '';
    }
};

// Iniciar el módulo
document.addEventListener('DOMContentLoaded', () => AdminApiModulo.init());