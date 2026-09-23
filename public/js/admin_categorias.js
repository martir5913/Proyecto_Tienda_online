// RF17 - Administración de categorías

const AdminCategorias = {
    init() {
        this.raiz = document.getElementById('admin-categorias');
        this.form = document.getElementById('form-categoria');
        this.modalElemento = document.getElementById('modalCategoria');
        this.modalEliminarElemento = document.getElementById('modalEliminarCategoria');

        if (!this.raiz || !this.form || !this.modalElemento || !this.modalEliminarElemento) {
            return;
        }

        this.endpoint = this.raiz.dataset.endpoint;
        this.baseUrl = (this.raiz.dataset.baseUrl || '').replace(/\/$/, '');
        this.modal = bootstrap.Modal.getOrCreateInstance(this.modalElemento);
        this.modalEliminar = bootstrap.Modal.getOrCreateInstance(this.modalEliminarElemento);
        this.idEliminar = null;

        document.getElementById('btn-nueva-categoria')?.addEventListener('click', () => {
            this.prepararNueva();
        });

        document.querySelectorAll('.btn-editar-categoria').forEach((boton) => {
            boton.addEventListener('click', () => {
                this.cargarCategoria(Number.parseInt(boton.dataset.id, 10));
            });
        });

        document.querySelectorAll('.btn-estado-categoria').forEach((boton) => {
            boton.addEventListener('click', () => {
                this.cambiarEstado(
                    Number.parseInt(boton.dataset.id, 10),
                    Number.parseInt(boton.dataset.activo, 10)
                );
            });
        });

        document.querySelectorAll('.btn-eliminar-categoria').forEach((boton) => {
            boton.addEventListener('click', () => {
                if (boton.disabled) {
                    return;
                }

                this.prepararEliminacion(
                    Number.parseInt(boton.dataset.id, 10),
                    boton.dataset.nombre || 'esta categoría'
                );
            });
        });

        this.form.addEventListener('submit', (event) => {
            event.preventDefault();
            this.guardarCategoria();
        });

        document.getElementById('btn-confirmar-eliminar-categoria')?.addEventListener('click', () => {
            this.eliminarCategoria();
        });
    },

    prepararNueva() {
        this.form.reset();
        this.form.classList.remove('was-validated');
        document.getElementById('categoria-accion').value = 'crear';
        document.getElementById('categoria-id').value = '';
        document.getElementById('categoria-activo').value = '1';
        document.getElementById('modalCategoriaTitulo').textContent = 'Nueva categoría';
        document.getElementById('categoria-imagen-actual-contenedor').classList.add('d-none');
        document.getElementById('categoria-imagen-actual').removeAttribute('src');
        this.ocultarAlerta();
        this.modal.show();
    },

    async cargarCategoria(idCategoria) {
        if (!Number.isInteger(idCategoria) || idCategoria <= 0) {
            return;
        }

        this.ocultarAlerta();

        try {
            const respuesta = await fetch(
                `${this.endpoint}?accion=detalle&id=${encodeURIComponent(idCategoria)}`,
                {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' }
                }
            );

            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.success || !resultado.data) {
                throw new Error(resultado.message || 'No fue posible cargar la categoría.');
            }

            const categoria = resultado.data;

            this.form.reset();
            this.form.classList.remove('was-validated');
            document.getElementById('categoria-accion').value = 'actualizar';
            document.getElementById('categoria-id').value = categoria.id_categoria || '';
            document.getElementById('categoria-nombre').value = categoria.nombre_categoria || '';
            document.getElementById('categoria-descripcion').value = categoria.descripcion || '';
            document.getElementById('categoria-activo').value = Number(categoria.activo) === 1 ? '1' : '0';
            document.getElementById('modalCategoriaTitulo').textContent = 'Editar categoría';

            const contenedorImagen = document.getElementById('categoria-imagen-actual-contenedor');
            const imagenActual = document.getElementById('categoria-imagen-actual');

            if (categoria.imagen) {
                imagenActual.src = `${this.baseUrl}/public/img/categorias/${encodeURIComponent(categoria.imagen)}`;
                contenedorImagen.classList.remove('d-none');
            } else {
                imagenActual.removeAttribute('src');
                contenedorImagen.classList.add('d-none');
            }

            this.modal.show();
        } catch (error) {
            this.mostrarMensajePagina(
                error.message || 'No fue posible cargar la categoría.',
                'danger'
            );
        }
    },

    async guardarCategoria() {
        if (!this.form.checkValidity()) {
            this.form.classList.add('was-validated');
            return;
        }

        const boton = document.getElementById('btn-guardar-categoria');
        const textoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Guardando...';
        this.ocultarAlerta();

        try {
            const datos = new FormData(this.form);

            const respuesta = await fetch(this.endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                body: datos,
                headers: { Accept: 'application/json' }
            });

            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.success) {
                throw new Error(resultado.message || 'No fue posible guardar la categoría.');
            }

            const accion = document.getElementById('categoria-accion').value;
            const resultadoUrl = accion === 'crear' ? 'creada' : 'actualizada';

            window.location.href = `${this.baseUrl}/index.php?ruta=admin_categorias&resultado=${resultadoUrl}`;
        } catch (error) {
            this.mostrarAlerta(error.message || 'No fue posible guardar la categoría.');
        } finally {
            boton.disabled = false;
            boton.innerHTML = textoOriginal;
        }
    },

    async cambiarEstado(idCategoria, activo) {
        if (!Number.isInteger(idCategoria) || idCategoria <= 0 || ![0, 1].includes(activo)) {
            return;
        }

        try {
            const datos = new FormData();
            datos.set('accion', 'estado');
            datos.set('id_categoria', String(idCategoria));
            datos.set('activo', String(activo));
            datos.set('csrf_token', this.form.elements.csrf_token.value);

            const respuesta = await fetch(this.endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                body: datos,
                headers: { Accept: 'application/json' }
            });

            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.success) {
                throw new Error(resultado.message || 'No fue posible cambiar el estado de la categoría.');
            }

            const resultadoUrl = activo === 1 ? 'activada' : 'desactivada';
            window.location.href = `${this.baseUrl}/index.php?ruta=admin_categorias&resultado=${resultadoUrl}`;
        } catch (error) {
            this.mostrarMensajePagina(
                error.message || 'No fue posible cambiar el estado de la categoría.',
                'danger'
            );
        }
    },

    prepararEliminacion(idCategoria, nombre) {
        if (!Number.isInteger(idCategoria) || idCategoria <= 0) {
            return;
        }

        this.idEliminar = idCategoria;
        document.getElementById('eliminar-categoria-nombre').textContent = nombre;
        this.modalEliminar.show();
    },

    async eliminarCategoria() {
        if (!this.idEliminar) {
            return;
        }

        const boton = document.getElementById('btn-confirmar-eliminar-categoria');
        const textoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Eliminando...';

        try {
            const datos = new FormData();
            datos.set('accion', 'eliminar');
            datos.set('id_categoria', String(this.idEliminar));
            datos.set('csrf_token', this.form.elements.csrf_token.value);

            const respuesta = await fetch(this.endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                body: datos,
                headers: { Accept: 'application/json' }
            });

            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.success) {
                throw new Error(resultado.message || 'No fue posible eliminar la categoría.');
            }

            window.location.href = `${this.baseUrl}/index.php?ruta=admin_categorias&resultado=eliminada`;
        } catch (error) {
            this.modalEliminar.hide();
            this.mostrarMensajePagina(
                error.message || 'No fue posible eliminar la categoría.',
                'danger'
            );
        } finally {
            boton.disabled = false;
            boton.innerHTML = textoOriginal;
        }
    },

    mostrarAlerta(mensaje) {
        const alerta = document.getElementById('categoria-alerta');
        alerta.className = 'alert alert-danger';
        alerta.textContent = mensaje;
        alerta.classList.remove('d-none');
    },

    ocultarAlerta() {
        const alerta = document.getElementById('categoria-alerta');
        alerta.classList.add('d-none');
        alerta.textContent = '';
        this.form.classList.remove('was-validated');
    },

    mostrarMensajePagina(mensaje, tipo) {
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
        alerta.setAttribute('role', 'alert');
        alerta.innerHTML = `
            <span></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        `;
        alerta.querySelector('span').textContent = mensaje;
        this.raiz.prepend(alerta);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

document.addEventListener('DOMContentLoaded', () => AdminCategorias.init());
