// JavaScript para el Módulo de Catálogo y Filtros Asíncronos

const CatalogoModulo = {
    init() {
        this.formFiltros = document.getElementById('form-filtros');
        this.gridProductos = document.getElementById('grid-productos');
        this.inputBusqueda = document.getElementById('input-busqueda-catalogo');
        this.contador = document.getElementById('catalogo-contador');
        this.modalElemento = document.getElementById('modalDetalleProducto');
        this.modalContenido = document.getElementById('modalDetalleProductoContenido');
        this.modalTitulo = document.getElementById('modalDetalleProductoTitulo');

        if (!this.gridProductos) return;

        this.apiUrl = this.gridProductos.dataset.apiUrl || 'api/productos.php';
        this.baseUrl = (this.gridProductos.dataset.baseUrl || '').replace(/\/$/, '');

        if (this.formFiltros) {
            this.formFiltros.addEventListener('change', () => this.aplicarFiltros());
        }

        if (this.inputBusqueda) {
            let timeout = null;

            this.inputBusqueda.addEventListener('input', () => {
                clearTimeout(timeout);

                timeout = setTimeout(() => {
                    this.aplicarFiltros();
                }, 400);
            });
        }

        this.gridProductos.addEventListener('click', (event) => {
            const botonDetalle = event.target.closest('.btn-detalle-producto');

            if (!botonDetalle) return;

            const idProducto = Number.parseInt(
                botonDetalle.dataset.productoId,
                10
            );

            if (Number.isInteger(idProducto) && idProducto > 0) {
                this.mostrarDetalle(idProducto);
            }
        });
    },

    async aplicarFiltros() {
        if (!this.gridProductos) return;

        const formData = new FormData(
            this.formFiltros || document.createElement('form')
        );

        const params = new URLSearchParams(formData);

        if (
            this.inputBusqueda &&
            this.inputBusqueda.value.trim() !== ''
        ) {
            params.set(
                'q',
                this.inputBusqueda.value.trim()
            );
        }
        else {
        params.delete('q');
        }

        try {
            const resp = await fetch(
                `${this.apiUrl}?${params.toString()}`,
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            const res = await resp.json();

            console.log('Filtros enviados:', params.toString());
            console.log('Respuesta filtros:', res);

            if (res.success) {
                const productos = Array.isArray(res.data?.productos)
                    ? res.data.productos
                    : [];

                this.renderizarProductos(productos);

                this.actualizarContador(productos.length);
            }

        } catch (error) {
            console.error(
                'Error al filtrar productos:',
                error
            );
        }
    },

    inicializarTooltips() {

        if (typeof bootstrap === 'undefined') {
            return;
        }

        document
            .querySelectorAll(
                '[data-bs-toggle="tooltip"]'
            )
            .forEach((elemento) => {

                bootstrap.Tooltip.getOrCreateInstance(
                    elemento
                );

            });
    },

    renderizarProductos(productos) {
        if (!this.gridProductos) return;

        if (productos.length === 0) {
            this.gridProductos.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>

                    <p class="mt-2 text-muted">
                        No se encontraron productos con los criterios seleccionados.
                    </p>
                </div>
            `;

            return;
        }

        this.gridProductos.innerHTML = productos
            .map(
                (producto) =>
                    this.crearTarjetaProducto(producto)
            )
            .join('');
    },

    crearTarjetaProducto(producto) {
        const id =
            Number.parseInt(
                producto.id_producto,
                10
            ) || 0;

        const stock =
            Number.parseInt(
                producto.stock,
                10
            ) || 0;

        const estado = Number.parseInt(
            producto.id_estado_producto,
            10
        );

        const disponible =
            estado === 1 &&
            stock > 0;

        const precio =
            Number.parseFloat(
                producto.precio
            ) || 0;

        const nombre = this.escaparHtml(
            producto.nombre || 'Producto'
        );

        const marca = this.escaparHtml(
            producto.nombre_marca || ''
        );

        const categoria = this.escaparHtml(
            producto.nombre_categoria || ''
        );

        const especificaciones = this.escaparHtml(
            this.recortarTexto(
                producto.especificaciones || '',
                68
            )
        );

        const imagen = String(
            producto.imagen || ''
        ).trim();

        let imagenHtml = `
            <i class="bi bi-box-seam fs-1 text-muted"
               aria-hidden="true">
            </i>
        `;

        if (imagen !== '') {
            const rutaImagen =
                `${this.baseUrl}/public/img/productos/${encodeURIComponent(imagen)}`;

            imagenHtml = `
                <img
                    src="${rutaImagen}"
                    alt="${nombre}"
                    loading="lazy"
                    onerror="
                        this.classList.add('d-none');
                        this.nextElementSibling.classList.remove('d-none');
                    "
                >

                <i class="bi bi-box-seam fs-1 text-muted d-none"
                   aria-hidden="true">
                </i>
            `;
        }

        return `
            <div class="col-md-6 col-lg-4 mb-3">
                <article class="product-card position-relative">
                    <!-- Botón flotante rápido para Wishlist -->
                    <button type="button"
                            class="btn-wishlist-card"
                            title="Guardar en lista de deseos"
                            onclick="ElectroApp.toggleWishlist(${id}, this)">
                        <i class="bi bi-heart"></i>
                    </button>

                    <div class="product-img-wrapper">
                        ${imagenHtml}
                    </div>

                    <div class="product-card-body">

                        <span class="product-brand">
                            ${marca}
                        </span>

                        <h3 class="product-title">
                            ${nombre}
                        </h3>

                        <div class="small text-muted mb-2">
                            <i class="bi bi-grid me-1"></i>
                            ${categoria}
                        </div>

                        <p class="text-muted small mb-2">
                            ${especificaciones}
                        </p>

                        <div class="small mb-2 ${
                            disponible
                                ? 'text-success'
                                : 'text-danger'
                        }">

                            <i class="bi ${
                                disponible
                                    ? 'bi-check-circle'
                                    : 'bi-x-circle'
                            } me-1"></i>

                            ${
                                disponible
                                    ? `Disponible (${stock} en stock)`
                                    : 'Agotado'
                            }

                        </div>

                        <div class="mt-auto pt-2 border-top">

                            <div class="product-price mb-2">

                                Q ${precio.toLocaleString(
                                    'es-GT',
                                    {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }
                                )}

                            </div>

                            <div class="d-flex gap-2">

                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-detalle-producto px-3"
                                    data-producto-id="${id}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Ver detalles del producto"
                                    aria-label="Ver detalles del producto">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary-app"
                                    onclick="ElectroApp.agregarAlCarrito(${id})"
                                    ${
                                        disponible
                                            ? ''
                                            : 'disabled'
                                    }
                                >

                                    <i class="bi bi-cart-plus"></i>

                                </button>

                            </div>

                        </div>

                    </div>

                </article>

            </div>
        `;
    },

    async mostrarDetalle(idProducto) {
        if (
            !this.modalElemento ||
            !this.modalContenido ||
            !this.modalTitulo
        ) {
            return;
        }

        this.modalTitulo.textContent =
            'Detalle del producto';

        this.modalContenido.innerHTML = `
            <div class="text-center py-4 text-muted">

                <div
                    class="spinner-border spinner-border-sm"
                    role="status"
                    aria-hidden="true">
                </div>

                <span class="ms-2">
                    Cargando información...
                </span>

            </div>
        `;

        const modal =
            bootstrap.Modal.getOrCreateInstance(
                this.modalElemento
            );

        modal.show();

        try {
            const resp = await fetch(
                `${this.apiUrl}?id=${encodeURIComponent(idProducto)}`,
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            const res = await resp.json();

            if (!res.success || !res.data) {
                throw new Error(
                    res.message ||
                    'No fue posible obtener el producto.'
                );
            }

            const producto = res.data;

            const stock =
                Number.parseInt(
                    producto.stock,
                    10
                ) || 0;

            const estado = Number.parseInt(
            producto.id_estado_producto,
            10
            );

            const disponible =
                estado === 1 &&
                stock > 0;

            const precio =
                Number.parseFloat(
                    producto.precio
                ) || 0;

            const nombre = this.escaparHtml(
                producto.nombre || 'Producto'
            );

            const marca = this.escaparHtml(
                producto.nombre_marca || ''
            );

            const categoria = this.escaparHtml(
                producto.nombre_categoria || ''
            );

            const modelo = this.escaparHtml(
                producto.codigo_modelo || ''
            );

            const descripcion = this.escaparHtml(
                producto.descripcion ||
                'Sin descripción disponible.'
            );

            const especificaciones = this.escaparHtml(
                producto.especificaciones ||
                'Sin especificaciones disponibles.'
            );

            this.modalTitulo.textContent =
                producto.nombre ||
                'Detalle del producto';

            this.modalContenido.innerHTML = `
                <div class="row g-4">

                    <div class="col-md-5">

                        <div class="product-img-wrapper rounded-3 h-100">

                            ${this.crearImagenDetalle(producto)}

                        </div>

                    </div>

                    <div class="col-md-7">

                        <span class="product-brand">
                            ${marca}
                        </span>

                        <div class="small text-muted mb-2">
                            ${categoria}
                        </div>

                        <div class="small mb-2">
                            <strong>Modelo:</strong>
                            ${modelo}
                        </div>

                        <p class="small text-muted">
                            ${descripcion}
                        </p>

                        <p class="small mb-3">

                            <strong>
                                Especificaciones:
                            </strong>

                            ${especificaciones}

                        </p>

                        <div class="small mb-2 ${
                            disponible
                                ? 'text-success'
                                : 'text-danger'
                        }">

                            <i class="bi ${
                                disponible
                                    ? 'bi-check-circle'
                                    : 'bi-x-circle'
                            } me-1"></i>

                            ${
                                disponible
                                    ? `Disponible (${stock} en stock)`
                                    : 'Agotado'
                            }

                        </div>

                        <div class="product-price mb-3">

                            Q ${precio.toLocaleString(
                                'es-GT',
                                {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }
                            )}

                        </div>

                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button
                                type="button"
                                class="btn btn-outline-danger px-3"
                                onclick="ElectroApp.toggleWishlist(
                                    ${Number.parseInt(producto.id_producto, 10) || 0},
                                    this
                                )"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Agregar a favoritos"
                                aria-label="Agregar a favoritos"
                            >
                                <i class="bi bi-heart"></i>
                            </button>


                            <button
                                type="button"
                                class="btn btn-primary-app px-3"
                                onclick="
                                    ElectroApp.agregarAlCarrito(
                                        ${Number.parseInt(
                                            producto.id_producto,
                                            10
                                        ) || 0}
                                    )
                                "
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Añadir al carrito"
                                aria-label="Añadir al carrito"
                                ${disponible ? '' : 'disabled'}
                            >
                                <i class="bi bi-cart-plus"></i>
                            </button>

                        </div>
                    </div>
                </div>
            `;

        } catch (error) {

            this.modalContenido.innerHTML = `
                <div class="text-center py-4 text-muted">

                    <i class="bi bi-exclamation-circle fs-3"></i>

                    <p class="mt-2 mb-0">
                        No fue posible cargar el detalle del producto.
                    </p>

                </div>
            `;
        }
    },

    crearImagenDetalle(producto) {
        const imagen = String(
            producto.imagen || ''
        ).trim();

        const nombre = this.escaparHtml(
            producto.nombre || 'Producto'
        );

        if (imagen === '') {
            return `
                <i class="bi bi-box-seam fs-1 text-muted"
                   aria-hidden="true">
                </i>
            `;
        }

        const rutaImagen =
            `${this.baseUrl}/public/img/productos/${encodeURIComponent(imagen)}`;

        return `
            <img
                src="${rutaImagen}"
                alt="${nombre}"
                loading="lazy"
                onerror="
                    this.classList.add('d-none');
                    this.nextElementSibling.classList.remove('d-none');
                "
            >

            <i class="bi bi-box-seam fs-1 text-muted d-none"
               aria-hidden="true">
            </i>
        `;
    },

    actualizarContador(total) {
        if (!this.contador) return;

        this.contador.textContent =
            `Mostrando ${total} ${
                total === 1
                    ? 'artículo'
                    : 'artículos'
            }`;
    },

    recortarTexto(texto, longitudMaxima) {
        const valor = String(
            texto || ''
        ).trim();

        if (valor.length <= longitudMaxima) {
            return valor;
        }

        return `${valor
            .substring(0, longitudMaxima)
            .trim()}...`;
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


document.addEventListener(
    'DOMContentLoaded',
    () => CatalogoModulo.init()
);