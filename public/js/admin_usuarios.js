// JavaScript para el Módulo de Administración de Usuarios y Roles (RF18)

const AdminUsuariosModulo = {
    apiUrl: '',
    csrfToken: '',
    sesionId: 0,
    rolesCatalogo: [],
    estadosCatalogo: [],
    modalInstancia: null,
    debounceTimeout: null,

    init() {
        const moduloEl = document.getElementById('admin-usuarios-modulo');
        if (!moduloEl) return;

        this.apiUrl = moduloEl.dataset.apiUrl || 'api/admin_usuarios.php';
        this.csrfToken = moduloEl.dataset.csrfToken || '';
        this.sesionId = parseInt(moduloEl.dataset.sesionId, 10) || 0;

        const modalEl = document.getElementById('modal-usuario');
        if (modalEl && typeof bootstrap !== 'undefined') {
            this.modalInstancia = new bootstrap.Modal(modalEl);
        }

        this.vincularEventos();
        this.cargarUsuarios();
    },

    vincularEventos() {
        // Búsqueda con debounce (300ms)
        const inputBusqueda = document.getElementById('filtro-busqueda');
        if (inputBusqueda) {
            inputBusqueda.addEventListener('input', () => {
                clearTimeout(this.debounceTimeout);
                this.debounceTimeout = setTimeout(() => this.cargarUsuarios(), 300);
            });
        }

        // Filtros de selección
        ['filtro-rol', 'filtro-estado', 'filtro-orden'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => this.cargarUsuarios());
            }
        });

        // Botón limpiar filtros
        const btnLimpiar = document.getElementById('btn-limpiar-filtros');
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', () => {
                const form = document.getElementById('form-filtros-usuarios');
                if (form) form.reset();
                this.cargarUsuarios();
            });
        }

        // Formulario de edición
        const formEditar = document.getElementById('form-editar-usuario');
        if (formEditar) {
            formEditar.addEventListener('submit', (e) => this.guardarUsuario(e));
        }
    },

    async cargarUsuarios() {
        const tablaBody = document.getElementById('lista-usuarios');
        if (tablaBody) {
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                        Consultando usuarios...
                    </td>
                </tr>
            `;
        }

        const params = new URLSearchParams({
            action: 'listar',
            q: document.getElementById('filtro-busqueda')?.value || '',
            id_rol: document.getElementById('filtro-rol')?.value || '',
            id_estado: document.getElementById('filtro-estado')?.value || '',
            orden: document.getElementById('filtro-orden')?.value || 'recientes'
        });

        try {
            const resp = await fetch(`${this.apiUrl}?${params.toString()}`);
            const data = await resp.json();

            if (data.success && data.data) {
                this.rolesCatalogo = data.data.roles || [];
                this.estadosCatalogo = data.data.estados || [];

                this.actualizarMetricas(data.data.resumen);
                this.actualizarSelectoresFiltros();
                this.renderizarTabla(data.data.usuarios || []);
            } else {
                if (tablaBody) {
                    tablaBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-4 text-danger">
                                <i class="bi bi-exclamation-circle me-1"></i> ${data.message || 'Error al obtener usuarios.'}
                            </td>
                        </tr>
                    `;
                }
            }
        } catch (error) {
            if (tablaBody) {
                tablaBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4 text-danger">
                            <i class="bi bi-wifi-off me-1"></i> Error de conexión con el servidor.
                        </td>
                    </tr>
                `;
            }
        }
    },

    actualizarMetricas(resumen) {
        if (!resumen) return;
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val ?? 0;
        };

        setVal('kpi-total-usuarios', resumen.total_usuarios);
        setVal('kpi-total-clientes', resumen.total_clientes);
        setVal('kpi-total-admins', resumen.total_admins);
        setVal('kpi-total-activos', resumen.total_activos);
        setVal('kpi-total-bloqueados', (resumen.total_bloqueados || 0) + (resumen.total_inactivos || 0));
    },

    actualizarSelectoresFiltros() {
        const selectRol = document.getElementById('filtro-rol');
        if (selectRol && selectRol.options.length <= 1) {
            this.rolesCatalogo.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id_rol;
                opt.textContent = r.nombre_rol;
                selectRol.appendChild(opt);
            });
        }

        const selectEstado = document.getElementById('filtro-estado');
        if (selectEstado && selectEstado.options.length <= 1) {
            this.estadosCatalogo.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.id_estado_usuario;
                opt.textContent = e.nombre_estado;
                selectEstado.appendChild(opt);
            });
        }
    },

    renderizarTabla(usuarios) {
        const tablaBody = document.getElementById('lista-usuarios');
        if (!tablaBody) return;

        if (usuarios.length === 0) {
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-person-x fs-2 d-block mb-2 text-muted"></i>
                        No se encontraron usuarios con los criterios de búsqueda.
                    </td>
                </tr>
            `;
            return;
        }

        const html = usuarios.map(u => {
            const iniciales = (u.nombre.charAt(0) + (u.apellido ? u.apellido.charAt(0) : '')).toUpperCase();
            const esSesionActual = (parseInt(u.id_usuario, 10) === this.sesionId);

            // Badge de Rol
            const badgeRol = parseInt(u.id_rol, 10) === 1
                ? '<span class="badge bg-dark-subtle text-dark border"><i class="bi bi-shield-check text-primary me-1"></i>Administrador</span>'
                : '<span class="badge bg-light text-secondary border"><i class="bi bi-person me-1"></i>Cliente</span>';

            // Badge de Estado
            let badgeEstado = '';
            const idEstado = parseInt(u.id_estado_usuario, 10);
            if (idEstado === 1) {
                badgeEstado = '<span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i>Activo</span>';
            } else if (idEstado === 2) {
                badgeEstado = '<span class="badge bg-secondary-subtle text-secondary"><i class="bi bi-pause-circle me-1"></i>Inactivo</span>';
            } else {
                badgeEstado = '<span class="badge bg-danger-subtle text-danger"><i class="bi bi-slash-circle me-1"></i>Bloqueado</span>';
            }

            const fechaReg = u.fecha_registro ? u.fecha_registro.substring(0, 10) : '-';

            return `
                <tr id="usuario-fila-${u.id_usuario}">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center me-3" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                ${iniciales}
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">
                                    ${this.escaparHtml(u.nombre)} ${this.escaparHtml(u.apellido)}
                                    ${esSesionActual ? '<span class="badge bg-primary ms-1" style="font-size:0.65rem;">Tú</span>' : ''}
                                </div>
                                <small class="text-muted">${this.escaparHtml(u.correo)}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small">${this.escaparHtml(u.telefono || 'Sin teléfono')}</div>
                        <small class="text-muted text-truncate d-inline-block" style="max-width: 180px;" title="${this.escaparHtml(u.direccion || '')}">
                            ${this.escaparHtml(u.direccion || 'Sin dirección')}
                        </small>
                    </td>
                    <td>${badgeRol}</td>
                    <td>${badgeEstado}</td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-bag me-1 text-primary"></i> ${u.total_pedidos || 0} pedidos
                        </span>
                    </td>
                    <td class="text-muted small">${fechaReg}</td>
                    <td class="text-end pe-3">
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-sm btn-outline-primary" onclick="AdminUsuariosModulo.abrirModalUsuario(${u.id_usuario})" title="Ver y Editar">
                                <i class="bi bi-pencil-square me-1"></i> Gestionar
                            </button>
                            <button class="btn btn-sm btn-light border dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" ${esSesionActual ? 'disabled' : ''}>
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                <li><h6 class="dropdown-header">Cambiar Estado</h6></li>
                                ${idEstado !== 1 ? `<li><a class="dropdown-item text-success" href="javascript:void(0)" onclick="AdminUsuariosModulo.cambiarEstadoRapido(${u.id_usuario}, 1)"><i class="bi bi-check-circle me-2"></i>Marcar Activo</a></li>` : ''}
                                ${idEstado !== 2 ? `<li><a class="dropdown-item text-secondary" href="javascript:void(0)" onclick="AdminUsuariosModulo.cambiarEstadoRapido(${u.id_usuario}, 2)"><i class="bi bi-pause-circle me-2"></i>Marcar Inactivo</a></li>` : ''}
                                ${idEstado !== 3 ? `<li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="AdminUsuariosModulo.cambiarEstadoRapido(${u.id_usuario}, 3)"><i class="bi bi-slash-circle me-2"></i>Bloquear Usuario</a></li>` : ''}
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        tablaBody.innerHTML = html;
    },

    async abrirModalUsuario(idUsuario) {
        try {
            const resp = await fetch(`${this.apiUrl}?action=detalle&id=${idUsuario}`);
            const data = await resp.json();

            if (!data.success || !data.data) {
                if (typeof ElectroApp !== 'undefined') {
                    ElectroApp.mostrarToast(data.message || 'Error al obtener detalle del usuario.', 'danger');
                }
                return;
            }

            const u = data.data;
            const esSesionActual = (parseInt(u.id_usuario, 10) === this.sesionId);

            document.getElementById('edit-id-usuario').value = u.id_usuario;
            document.getElementById('edit-nombre').value = u.nombre || '';
            document.getElementById('edit-apellido').value = u.apellido || '';
            document.getElementById('edit-correo').value = u.correo || '';
            document.getElementById('edit-telefono').value = u.telefono || '';
            document.getElementById('edit-direccion').value = u.direccion || '';
            document.getElementById('edit-password').value = '';

            // Llenar selectores de rol y estado
            const selectRol = document.getElementById('edit-rol');
            selectRol.innerHTML = this.rolesCatalogo.map(r => `
                <option value="${r.id_rol}" ${parseInt(r.id_rol, 10) === parseInt(u.id_rol, 10) ? 'selected' : ''}>
                    ${r.nombre_rol} - ${r.descripcion || ''}
                </option>
            `).join('');

            const selectEstado = document.getElementById('edit-estado');
            selectEstado.innerHTML = this.estadosCatalogo.map(e => `
                <option value="${e.id_estado_usuario}" ${parseInt(e.id_estado_usuario, 10) === parseInt(u.id_estado_usuario, 10) ? 'selected' : ''}>
                    ${e.nombre_estado}
                </option>
            `).join('');

            // Restricciones para la propia sesión
            const avisoRol = document.getElementById('aviso-propio-rol');
            const avisoEstado = document.getElementById('aviso-propio-estado');

            if (esSesionActual) {
                selectRol.disabled = true;
                selectEstado.disabled = true;
                if (avisoRol) avisoRol.textContent = 'No puedes modificar tu propio rol de administrador.';
                if (avisoEstado) avisoEstado.textContent = 'No puedes cambiar el estado de tu cuenta actual.';
            } else {
                selectRol.disabled = false;
                selectEstado.disabled = false;
                if (avisoRol) avisoRol.textContent = '';
                if (avisoEstado) avisoEstado.textContent = '';
            }

            // Estadísticas de compra
            const pedidosEl = document.getElementById('info-pedidos-count');
            const montoEl = document.getElementById('info-pedidos-monto');
            if (pedidosEl) pedidosEl.textContent = u.total_pedidos || 0;
            if (montoEl) {
                const totalMonto = parseFloat(u.total_gastado || 0);
                montoEl.textContent = 'Q ' + totalMonto.toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            if (this.modalInstancia) {
                this.modalInstancia.show();
            }
        } catch (e) {
            if (typeof ElectroApp !== 'undefined') {
                ElectroApp.mostrarToast('Error de conexión al abrir modal.', 'danger');
            }
        }
    },

    async guardarUsuario(event) {
        event.preventDefault();
        const form = event.target;
        const btnSubmit = document.getElementById('btn-guardar-usuario');

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        data.csrf_token = this.csrfToken;

        // Si los selects estaban disabled, recuperamos sus valores
        const selectRol = document.getElementById('edit-rol');
        const selectEstado = document.getElementById('edit-estado');
        if (selectRol && selectRol.disabled) data.id_rol = selectRol.value;
        if (selectEstado && selectEstado.disabled) data.id_estado_usuario = selectEstado.value;

        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';
        }

        try {
            const resp = await fetch(`${this.apiUrl}?action=actualizar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const res = await resp.json();

            if (res.success) {
                if (typeof ElectroApp !== 'undefined') {
                    ElectroApp.mostrarToast(res.message, 'success');
                }
                if (this.modalInstancia) {
                    this.modalInstancia.hide();
                }
                this.cargarUsuarios();
            } else {
                if (typeof ElectroApp !== 'undefined') {
                    ElectroApp.mostrarToast(res.message || 'Error al guardar usuario.', 'danger');
                }
            }
        } catch (e) {
            if (typeof ElectroApp !== 'undefined') {
                ElectroApp.mostrarToast('Error de conexión al guardar cambios.', 'danger');
            }
        } finally {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-save me-1"></i> Guardar Cambios';
            }
        }
    },

    async cambiarEstadoRapido(idUsuario, nuevoEstado) {
        try {
            const resp = await fetch(`${this.apiUrl}?action=cambiar_estado`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_usuario: idUsuario,
                    id_estado_usuario: nuevoEstado,
                    csrf_token: this.csrfToken
                })
            });
            const res = await resp.json();

            if (res.success) {
                if (typeof ElectroApp !== 'undefined') {
                    ElectroApp.mostrarToast(res.message, 'success');
                }
                this.cargarUsuarios();
            } else {
                if (typeof ElectroApp !== 'undefined') {
                    ElectroApp.mostrarToast(res.message || 'No se pudo cambiar el estado.', 'danger');
                }
            }
        } catch (e) {
            if (typeof ElectroApp !== 'undefined') {
                ElectroApp.mostrarToast('Error al procesar el cambio de estado.', 'danger');
            }
        }
    },

    escaparHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    AdminUsuariosModulo.init();
});
