// RF16 - Administración de productos

const AdminProductos = {
    init() {
        this.raiz = document.getElementById('admin-productos');
        this.form = document.getElementById('form-producto');
        this.modalElemento = document.getElementById('modalProducto');
        this.modalEliminarElemento = document.getElementById('modalEliminarProducto');

        if (!this.raiz || !this.form || !this.modalElemento) {
            return;
        }

        this.endpoint = this.raiz.dataset.endpoint;
        this.baseUrl = (this.raiz.dataset.baseUrl || '').replace(/\/$/, '');
        this.modal = bootstrap.Modal.getOrCreateInstance(this.modalElemento);
        this.modalEliminar = bootstrap.Modal.getOrCreateInstance(this.modalEliminarElemento);
        this.idEliminar = null;

        document.getElementById('btn-nuevo-producto')?.addEventListener('click', () => {
            this.prepararNuevo();
        });

        document.querySelectorAll('.btn-editar-producto').forEach((boton) => {
            boton.addEventListener('click', () => {
                this.cargarProducto(Number.parseInt(boton.dataset.id, 10));
            });
        });

        document.querySelectorAll('.btn-eliminar-producto').forEach((boton) => {
            boton.addEventListener('click', () => {
                this.prepararEliminacion(
                    Number.parseInt(boton.dataset.id, 10),
                    boton.dataset.nombre || 'este producto'
                );
            });
        });

        this.form.addEventListener('submit', (event) => {
            event.preventDefault();
            this.guardarProducto();
        });

        document.getElementById('btn-confirmar-eliminar-producto')?.addEventListener('click', () => {
            this.eliminarProducto();
        });
    },

    prepararNuevo() {
        this.form.reset();
        document.getElementById('producto-accion').value = 'crear';
        document.getElementById('producto-id').value = '';
        document.getElementById('producto-estado').value = '1';
        document.getElementById('modalProductoTitulo').textContent = 'Nuevo producto';
        document.getElementById('producto-imagen-actual-contenedor').classList.add('d-none');
        document.getElementById('producto-imagen-actual').removeAttribute('src');
        this.ocultarAlerta();
        this.modal.show();
    },

    async cargarProducto(idProducto) {
        if (!Number.isInteger(idProducto) || idProducto <= 0) {
            return;
        }

        this.ocultarAlerta();

        try {
            const respuesta = await fetch(
                `${this.endpoint}?accion=detalle&id=${encodeURIComponent(idProducto)}`,
                { headers: { Accept: 'application/json' } }
            );

            const resultado = await respuesta.json();
            if (!respuesta.ok || !resultado.success || !resultado.data) {
                throw new Error(resultado.message || 'No fue posible cargar el producto.');
            }

            const producto = resultado.data;

            this.form.reset();
            document.getElementById('producto-accion').value = 'actualizar';
            document.getElementById('producto-id').value = producto.id_producto || '';
            document.getElementById('producto-nombre').value = producto.nombre || '';
            document.getElementById('producto-modelo').value = producto.codigo_modelo || '';
            document.getElementById('producto-categoria').value = producto.id_categoria || '';
            document.getElementById('producto-marca').value = producto.id_marca || '';
            document.getElementById('producto-precio').value = producto.precio || '';
            document.getElementById('producto-stock').value = producto.stock ?? 0;
            document.getElementById('producto-estado').value = producto.id_estado_producto || 1;
            document.getElementById('producto-descripcion').value = producto.descripcion || '';
            document.getElementById('producto-especificaciones').value = producto.especificaciones || '';
            document.getElementById('producto-destacado').checked = Number(producto.destacado) === 1;
            document.getElementById('modalProductoTitulo').textContent = 'Editar producto';

            const contenedorImagen = document.getElementById('producto-imagen-actual-contenedor');
            const imagenActual = document.getElementById('producto-imagen-actual');

            if (producto.imagen) {
                imagenActual.src = `${this.baseUrl}/public/img/productos/${encodeURIComponent(producto.imagen)}`;
                contenedorImagen.classList.remove('d-none');
            } else {
                imagenActual.removeAttribute('src');
                contenedorImagen.classList.add('d-none');
            }

            this.modal.show();
        } catch (error) {
            this.mostrarMensajePagina(error.message || 'No fue posible cargar el producto.', 'danger');
        }
    },

    async guardarProducto() {
        if (!this.form.checkValidity()) {
            this.form.classList.add('was-validated');
            return;
        }

        const boton = document.getElementById('btn-guardar-producto');
        const textoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Guardando...';
        this.ocultarAlerta();

        try {
            const datos = new FormData(this.form);
            const respuesta = await fetch(this.endpoint, {
                method: 'POST',
                body: datos,
                headers: { Accept: 'application/json' }
            });

            const resultado = await respuesta.json();
            if (!respuesta.ok || !resultado.success) {
                throw new Error(resultado.message || 'No fue posible guardar el producto.');
            }

            const accion = document.getElementById('producto-accion').value;
            const resultadoUrl = accion === 'crear' ? 'creado' : 'actualizado';
            window.location.href = `${this.baseUrl}/index.php?ruta=admin_productos&resultado=${resultadoUrl}`;
        } catch (error) {
            this.mostrarAlerta(error.message || 'No fue posible guardar el producto.');
        } finally {
            boton.disabled = false;
            boton.innerHTML = textoOriginal;
        }
    },

    prepararEliminacion(idProducto, nombre) {
        if (!Number.isInteger(idProducto) || idProducto <= 0) {
            return;
        }

        this.idEliminar = idProducto;
        document.getElementById('eliminar-producto-nombre').textContent = nombre;
        this.modalEliminar.show();
    },

    async eliminarProducto() {
        if (!this.idEliminar) {
            return;
        }

        const boton = document.getElementById('btn-confirmar-eliminar-producto');
        const textoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Procesando...';

        try {
            const datos = new FormData();
            datos.set('accion', 'eliminar');
            datos.set('id_producto', String(this.idEliminar));
            datos.set('csrf_token', this.form.elements.csrf_token.value);

            const respuesta = await fetch(this.endpoint, {
                method: 'POST',
                body: datos,
                headers: { Accept: 'application/json' }
            });

            const resultado = await respuesta.json();
            if (!respuesta.ok || !resultado.success) {
                throw new Error(resultado.message || 'No fue posible eliminar el producto.');
            }

            window.location.href = `${this.baseUrl}/index.php?ruta=admin_productos&resultado=eliminado`;
        } catch (error) {
            this.modalEliminar.hide();
            this.mostrarMensajePagina(error.message || 'No fue posible eliminar el producto.', 'danger');
        } finally {
            boton.disabled = false;
            boton.innerHTML = textoOriginal;
        }
    },

    mostrarAlerta(mensaje) {
        const alerta = document.getElementById('producto-alerta');
        alerta.className = 'alert alert-danger';
        alerta.textContent = mensaje;
        alerta.classList.remove('d-none');
    },

    ocultarAlerta() {
        const alerta = document.getElementById('producto-alerta');
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

document.addEventListener('DOMContentLoaded', () => AdminProductos.init());
