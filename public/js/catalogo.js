// JavaScript para el Módulo de Catálogo y Filtros Asíncronos

const CatalogoModulo = {
    init() {
        this.formFiltros = document.getElementById('form-filtros');
        this.gridProductos = document.getElementById('grid-productos');
        this.inputBusqueda = document.getElementById('input-busqueda-catalogo');

        if (this.formFiltros) {
            this.formFiltros.addEventListener('change', () => this.aplicarFiltros());
        }
        if (this.inputBusqueda) {
            let timeout = null;
            this.inputBusqueda.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => this.aplicarFiltros(), 400);
            });
        }
    },

    async aplicarFiltros() {
        if (!this.gridProductos) return;

        const formData = new FormData(this.formFiltros || document.createElement('form'));
        const params = new URLSearchParams(formData);

        if (this.inputBusqueda && this.inputBusqueda.value.trim() !== '') {
            params.set('q', this.inputBusqueda.value.trim());
        }

        try {
            const resp = await fetch(`api/productos.php?${params.toString()}`);
            const res = await resp.json();
            if (res.success) {
                this.renderizarProductos(res.data.productos);
            }
        } catch (e) {
            console.error('Error al filtrar productos:', e);
        }
    },

    renderizarProductos(productos) {
        if (!this.gridProductos) return;

        if (productos.length === 0) {
            this.gridProductos.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="mt-2 text-muted">No se encontraron electrodomésticos con los filtros seleccionados.</p>
                </div>
            `;
            return;
        }

        this.gridProductos.innerHTML = productos.map(p => `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="product-card">
                    <div class="product-img-wrapper">
                        <i class="bi bi-plug fs-1 text-muted"></i>
                    </div>
                    <div class="product-card-body">
                        <span class="product-brand">${p.nombre_marca}</span>
                        <h3 class="product-title">${p.nombre}</h3>
                        <p class="text-muted small mb-2">${p.especificaciones ? p.especificaciones.substring(0, 70) + '...' : ''}</p>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="product-price">Q ${parseFloat(p.precio).toLocaleString('es-GT', { minimumFractionDigits: 2 })}</span>
                            <button class="btn btn-sm btn-primary-app" onclick="ElectroApp.agregarAlCarrito(${p.id_producto})">
                                <i class="bi bi-cart-plus me-1"></i> Agregar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
};

document.addEventListener('DOMContentLoaded', () => CatalogoModulo.init());
