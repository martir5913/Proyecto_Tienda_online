const AdminPedidosModulo = {
    init() {
        this.modulo = document.getElementById('admin-pedidos-modulo');
        if (!this.modulo) return;

        this.apiUrl = this.modulo.dataset.apiUrl || 'api/admin_pedidos.php';
        this.csrfToken = this.modulo.dataset.csrfToken || '';
        this.formFiltros = document.getElementById('form-filtros-pedidos');
        this.inputBusqueda = document.getElementById('pedido-busqueda');
        this.tabla = document.getElementById('tabla-admin-pedidos');
        this.contador = document.getElementById('contador-admin-pedidos');
        this.alerta = document.getElementById('alerta-admin-pedidos');
        this.modalDetalle = document.getElementById('modalDetallePedidoAdmin');
        this.modalDetalleTitulo = document.getElementById('modalDetallePedidoAdminTitulo');
        this.modalDetalleContenido = document.getElementById('modalDetallePedidoAdminContenido');
        this.modalConfirmar = document.getElementById('modalConfirmarEstadoPedido');
        this.textoConfirmar = document.getElementById('texto-confirmar-estado-pedido');
        this.btnConfirmar = document.getElementById('btn-confirmar-estado-pedido');
        this.accionPendiente = null;

        if (this.formFiltros) {
            this.formFiltros.addEventListener('change', () => this.cargarPedidos());
        }

        if (this.inputBusqueda) {
            let temporizador = null;
            this.inputBusqueda.addEventListener('input', () => {
                clearTimeout(temporizador);
                temporizador = setTimeout(() => this.cargarPedidos(), 350);
            });
        }

        document.getElementById('btn-limpiar-pedidos')?.addEventListener('click', () => {
            this.formFiltros?.reset();
            this.cargarPedidos();
        });

        this.tabla?.addEventListener('click', (event) => this.manejarAccionTabla(event));
        this.btnConfirmar?.addEventListener('click', () => this.ejecutarAccionConfirmada());

        this.modalDetalleContenido?.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-modal-reenviar-correo');
            if (btn) {
                const idPedido = Number.parseInt(btn.dataset.id, 10);
                this.abrirConfirmacion({
                    tipo: 'reenviar_correo',
                    idPedido,
                    texto: '¿Deseas reenviar el correo de confirmación de compra al cliente para este pedido?'
                });
            }
        });

        this.cargarPedidos();
    },

    async cargarPedidos() {
        const params = new URLSearchParams(
            new FormData(this.formFiltros || document.createElement('form'))
        );
        params.set('action', 'listar');

        this.mostrarCargaTabla();

        try {
            const resp = await fetch(`${this.apiUrl}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });

            const res = await resp.json();

            if (!resp.ok || !res.success) {
                throw new Error(res.message || 'No fue posible cargar los pedidos.');
            }

            const pedidos = Array.isArray(res.data?.pedidos) ? res.data.pedidos : [];
            this.renderizarPedidos(pedidos);
            this.actualizarResumen(res.data?.resumen || {});
            this.contador.textContent = `Mostrando ${pedidos.length} ${pedidos.length === 1 ? 'pedido' : 'pedidos'}`;
        } catch (error) {
            this.tabla.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-5 text-danger">
                        No fue posible cargar los pedidos.
                    </td>
                </tr>
            `;
            this.mostrarAlerta(error.message, 'danger');
        }
    },

    renderizarPedidos(pedidos) {
        if (!this.tabla) return;

        if (pedidos.length === 0) {
            this.tabla.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        No se encontraron pedidos con los filtros seleccionados.
                    </td>
                </tr>
            `;
            return;
        }

        this.tabla.innerHTML = pedidos.map((pedido) => {
            const id = Number.parseInt(pedido.id_pedido, 10) || 0;
            const estado = Number.parseInt(pedido.id_estado_pedido, 10) || 0;
            const total = Number.parseFloat(pedido.total) || 0;
            const articulos = Number.parseInt(pedido.total_articulos, 10) || 0;
            const cliente = `${pedido.nombre || ''} ${pedido.apellido || ''}`.trim();

            return `
                <tr>
                    <td>
                        <div class="fw-semibold text-primary">${this.escaparHtml(pedido.numero_pedido || '')}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">${this.escaparHtml(cliente)}</div>
                        <div class="small text-muted">${this.escaparHtml(pedido.correo || '')}</div>
                    </td>
                    <td class="small">${this.formatearFecha(pedido.fecha_pedido)}</td>
                    <td>${articulos}</td>
                    <td class="fw-semibold">Q ${this.formatearDinero(total)}</td>
                    <td class="small">${this.escaparHtml(pedido.nombre_metodo || '')}</td>
                    <td>${this.crearBadgeEstado(estado, pedido.nombre_estado || '')}</td>
                    <td class="text-end text-nowrap">
                        ${this.crearAcciones(id, estado)}
                    </td>
                </tr>
            `;
        }).join('');
    },

    crearAcciones(idPedido, estado) {
        const botones = [
            `<button type="button" class="btn btn-sm btn-outline-primary me-1 btn-ver-pedido" data-id="${idPedido}" title="Ver detalle">
                <i class="bi bi-eye"></i>
            </button>`,
            `<button type="button" class="btn btn-sm btn-outline-info me-1 btn-reenviar-correo" data-id="${idPedido}" title="Reenviar correo de confirmación">
                <i class="bi bi-envelope-at"></i>
            </button>`
        ];

        if (estado === 1) {
            botones.push(this.botonEstado(idPedido, 2, 'Aceptar / Procesar', 'bi-check2-circle', 'btn-outline-success'));
            botones.push(this.botonEstado(idPedido, 5, 'Cancelar', 'bi-x-circle', 'btn-outline-danger'));
        } else if (estado === 2) {
            botones.push(this.botonEstado(idPedido, 3, 'Marcar enviado', 'bi-truck', 'btn-outline-primary'));
            botones.push(this.botonEstado(idPedido, 5, 'Cancelar', 'bi-x-circle', 'btn-outline-danger'));
        } else if (estado === 3) {
            botones.push(this.botonEstado(idPedido, 4, 'Marcar entregado', 'bi-box2-heart', 'btn-outline-success'));
        } else if (estado === 5) {
            botones.push(`
                <button type="button"
                        class="btn btn-sm btn-outline-danger btn-eliminar-pedido"
                        data-id="${idPedido}"
                        title="Eliminar pedido cancelado">
                    <i class="bi bi-trash"></i>
                </button>
            `);
        }

        return botones.join('');
    },

    botonEstado(idPedido, nuevoEstado, titulo, icono, clase) {
        return `
            <button type="button"
                    class="btn btn-sm ${clase} me-1 btn-cambiar-estado"
                    data-id="${idPedido}"
                    data-estado="${nuevoEstado}"
                    data-titulo="${this.escaparHtml(titulo)}"
                    title="${this.escaparHtml(titulo)}">
                <i class="bi ${icono}"></i>
            </button>
        `;
    },

    manejarAccionTabla(event) {
        const btnVer = event.target.closest('.btn-ver-pedido');
        if (btnVer) {
            this.mostrarDetalle(Number.parseInt(btnVer.dataset.id, 10));
            return;
        }

        const btnReenviar = event.target.closest('.btn-reenviar-correo');
        if (btnReenviar) {
            const idPedido = Number.parseInt(btnReenviar.dataset.id, 10);
            this.abrirConfirmacion({
                tipo: 'reenviar_correo',
                idPedido,
                texto: '¿Deseas reenviar el correo de confirmación de compra al cliente para este pedido?'
            });
            return;
        }

        const btnEstado = event.target.closest('.btn-cambiar-estado');
        if (btnEstado) {
            const idPedido = Number.parseInt(btnEstado.dataset.id, 10);
            const estado = Number.parseInt(btnEstado.dataset.estado, 10);
            const titulo = btnEstado.dataset.titulo || 'Cambiar estado';

            this.abrirConfirmacion({
                tipo: 'estado',
                idPedido,
                estado,
                texto: `¿Deseas ${titulo.toLowerCase()} este pedido?`
            });
            return;
        }

        const btnEliminar = event.target.closest('.btn-eliminar-pedido');
        if (btnEliminar) {
            const idPedido = Number.parseInt(btnEliminar.dataset.id, 10);
            this.abrirConfirmacion({
                tipo: 'eliminar',
                idPedido,
                texto: '¿Deseas eliminar definitivamente este pedido cancelado? Esta acción no se puede deshacer.'
            });
        }
    },

    abrirConfirmacion(accion) {
        this.accionPendiente = accion;
        this.textoConfirmar.textContent = accion.texto;
        bootstrap.Modal.getOrCreateInstance(this.modalConfirmar).show();
    },

    async ejecutarAccionConfirmada() {
        if (!this.accionPendiente) return;

        const accion = this.accionPendiente;
        this.btnConfirmar.disabled = true;

        try {
            let body = {};
            if (accion.tipo === 'estado') {
                body = {
                    action: 'cambiar_estado',
                    id_pedido: accion.idPedido,
                    id_estado_pedido: accion.estado
                };
            } else if (accion.tipo === 'eliminar') {
                body = {
                    action: 'eliminar',
                    id_pedido: accion.idPedido
                };
            } else if (accion.tipo === 'reenviar_correo') {
                body = {
                    action: 'reenviar_correo',
                    id_pedido: accion.idPedido
                };
            }

            const resp = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                body: JSON.stringify(body)
            });

            const res = await resp.json();

            if (!resp.ok || !res.success) {
                throw new Error(res.message || 'No fue posible completar la operación.');
            }

            bootstrap.Modal.getInstance(this.modalConfirmar)?.hide();
            this.mostrarAlerta(res.message, 'success');
            if (accion.tipo !== 'reenviar_correo') {
                await this.cargarPedidos();
            }
        } catch (error) {
            this.mostrarAlerta(error.message, 'danger');
        } finally {
            this.btnConfirmar.disabled = false;
            this.accionPendiente = null;
        }
    },

    async mostrarDetalle(idPedido) {
        if (!Number.isInteger(idPedido) || idPedido <= 0) return;

        this.modalDetalleTitulo.textContent = 'Detalle del pedido';
        this.modalDetalleContenido.innerHTML = `
            <div class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                Cargando detalle...
            </div>
        `;

        bootstrap.Modal.getOrCreateInstance(this.modalDetalle).show();

        try {
            const resp = await fetch(
                `${this.apiUrl}?action=detalle&id=${encodeURIComponent(idPedido)}`,
                { headers: { 'Accept': 'application/json' } }
            );
            const res = await resp.json();

            if (!resp.ok || !res.success || !res.data) {
                throw new Error(res.message || 'No fue posible obtener el pedido.');
            }

            this.renderizarDetalle(res.data);
        } catch (error) {
            this.modalDetalleContenido.innerHTML = `
                <div class="alert alert-danger mb-0">${this.escaparHtml(error.message)}</div>
            `;
        }
    },

    renderizarDetalle(pedido) {
        const items = Array.isArray(pedido.items) ? pedido.items : [];
        this.modalDetalleTitulo.textContent = pedido.numero_pedido || 'Detalle del pedido';

        const filas = items.map((item) => `
            <tr>
                <td>
                    <div class="fw-semibold">${this.escaparHtml(item.nombre_producto || '')}</div>
                    <div class="small text-muted">${this.escaparHtml(item.codigo_modelo || '')}</div>
                </td>
                <td class="text-center">${Number.parseInt(item.cantidad, 10) || 0}</td>
                <td class="text-end">Q ${this.formatearDinero(item.precio_unitario)}</td>
                <td class="text-end fw-semibold">Q ${this.formatearDinero(item.subtotal)}</td>
            </tr>
        `).join('');

        this.modalDetalleContenido.innerHTML = `
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="small text-muted">Cliente</div>
                    <div class="fw-semibold">${this.escaparHtml(`${pedido.nombre || ''} ${pedido.apellido || ''}`.trim())}</div>
                    <div class="small">${this.escaparHtml(pedido.correo || '')}</div>
                    <div class="small">${this.escaparHtml(pedido.telefono || '')}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Estado</div>
                    <div class="mb-2">${this.crearBadgeEstado(Number.parseInt(pedido.id_estado_pedido, 10), pedido.nombre_estado || '')}</div>
                    <div class="small text-muted">Método de pago</div>
                    <div>${this.escaparHtml(pedido.nombre_metodo || '')}</div>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Dirección de envío</div>
                    <div>${this.escaparHtml(pedido.direccion_envio || '')}</div>
                </div>
                ${pedido.notas ? `
                    <div class="col-12">
                        <div class="small text-muted">Notas</div>
                        <div>${this.escaparHtml(pedido.notas)}</div>
                    </div>
                ` : ''}
            </div>

            <div class="table-responsive mb-3">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>${filas}</tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-5">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Subtotal</span><strong>Q ${this.formatearDinero(pedido.subtotal)}</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Impuesto</span><strong>Q ${this.formatearDinero(pedido.impuesto)}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2 mb-3">
                        <span class="fw-bold">Total</span><strong>Q ${this.formatearDinero(pedido.total)}</strong>
                    </div>
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-info btn-sm btn-modal-reenviar-correo" data-id="${pedido.id_pedido}">
                            <i class="bi bi-envelope-at me-1"></i> Reenviar Correo
                        </button>
                        <a href="index.php?ruta=factura&id=${pedido.id_pedido}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-printer me-1"></i> Ver / Imprimir Factura Electrónica
                        </a>
                    </div>
                </div>
            </div>
        `;
    },

    crearBadgeEstado(estado, nombre) {
        const clases = {
            1: 'bg-warning text-dark',
            2: 'bg-info text-dark',
            3: 'bg-primary',
            4: 'bg-success',
            5: 'bg-secondary'
        };

        return `<span class="badge ${clases[estado] || 'bg-secondary'}">${this.escaparHtml(nombre || 'Sin estado')}</span>`;
    },

    actualizarResumen(resumen) {
        const mapa = {
            'resumen-pendientes': resumen.pendientes,
            'resumen-procesando': resumen.procesando,
            'resumen-enviados': resumen.enviados,
            'resumen-entregados': resumen.entregados,
            'resumen-cancelados': resumen.cancelados
        };

        Object.entries(mapa).forEach(([id, valor]) => {
            const elemento = document.getElementById(id);
            if (elemento) elemento.textContent = Number.parseInt(valor, 10) || 0;
        });
    },

    mostrarCargaTabla() {
        if (!this.tabla) return;
        this.tabla.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                    Cargando pedidos...
                </td>
            </tr>
        `;
    },

    mostrarAlerta(mensaje, tipo = 'success') {
        if (!this.alerta) return;

        this.alerta.className = `alert alert-${tipo} mb-4`;
        this.alerta.textContent = mensaje;

        window.setTimeout(() => {
            this.alerta.classList.add('d-none');
        }, 4500);
    },

    formatearDinero(valor) {
        const numero = Number.parseFloat(valor) || 0;
        return numero.toLocaleString('es-GT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    formatearFecha(valor) {
        if (!valor) return '';
        const fecha = new Date(String(valor).replace(' ', 'T'));
        if (Number.isNaN(fecha.getTime())) return this.escaparHtml(valor);

        return fecha.toLocaleString('es-GT', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    escaparHtml(valor) {
        return String(valor ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
};

document.addEventListener('DOMContentLoaded', () => AdminPedidosModulo.init());
