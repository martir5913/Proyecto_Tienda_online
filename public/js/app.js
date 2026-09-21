// JavaScript Global de la Aplicación: ElectroHogar
// Notificaciones Toast, utilidades de carrito y eventos compartidos

const ElectroApp = {
    // Muestra notificaciones flotantes con Bootstrap Toast
    mostrarToast(mensaje, tipo = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container-custom position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(container);
        }

        const iconClass = tipo === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger';
        const toastId = 'toast-' + Date.now();

        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center">
                        <i class="bi ${iconClass} fs-5 me-2"></i>
                        <span>${mensaje}</span>
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    },

    // Actualiza el badge numérico del carrito en el navbar
    actualizarBadgeCarrito(total) {
        const badges = document.querySelectorAll('.cart-count-badge');
        badges.forEach(b => {
            b.textContent = total;
            b.style.display = total > 0 ? 'inline-block' : 'none';
        });
    },

    // Agregar producto al carrito mediante API
    async agregarAlCarrito(idProducto, cantidad = 1) {
        try {
            const resp = await fetch('api/carrito.php?action=add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_producto: idProducto, cantidad })
            });
            const data = await resp.json();
            if (data.success) {
                this.mostrarToast(data.message, 'success');
                if (data.data && data.data.total_items !== undefined) {
                    this.actualizarBadgeCarrito(data.data.total_items);
                }
            } else {
                this.mostrarToast(data.message, 'danger');
            }
        } catch (err) {
            this.mostrarToast('Error al conectar con el servidor', 'danger');
        }
    },

    // Agregar producto a la Lista de Deseos
    async agregarAWishlist(idProducto, boton = null) {
        try {
            const resp = await fetch('api/wishlist.php?action=add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_producto: idProducto })
            });
            const data = await resp.json();
            if (data.success) {
                this.mostrarToast(data.message, 'success');
                if (boton) {
                    boton.classList.add('text-danger');
                    const icon = boton.querySelector('i');
                    if (icon) {
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                    }
                }
            } else {
                this.mostrarToast(data.message, 'danger');
            }
        } catch (err) {
            this.mostrarToast('Error al procesar la lista de deseos.', 'danger');
        }
    },

    // Eliminar producto de la Lista de Deseos con animación
    async eliminarDeWishlist(idProducto, elementoCardId = null) {
        try {
            const resp = await fetch('api/wishlist.php?action=remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_producto: idProducto })
            });
            const data = await resp.json();
            if (data.success) {
                this.mostrarToast(data.message, 'success');

                if (elementoCardId) {
                    const el = document.getElementById(elementoCardId);
                    if (el) {
                        el.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                        el.style.opacity = '0';
                        el.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            el.remove();
                            // Si ya no quedan productos en la vista de favoritos
                            const contenedor = document.getElementById('grid-favoritos');
                            if (contenedor && contenedor.querySelectorAll('.col-favorito').length === 0) {
                                const vacioEl = document.getElementById('wishlist-vacia');
                                if (vacioEl) vacioEl.classList.remove('d-none');
                            }
                        }, 250);
                    }
                }
            } else {
                this.mostrarToast(data.message, 'danger');
            }
        } catch (err) {
            this.mostrarToast('Error al conectar con el servidor', 'danger');
        }
    },

    // Alternar (toggle) producto en Wishlist
    async toggleWishlist(idProducto, boton = null) {
        const estaActivo = boton && boton.querySelector('.bi-heart-fill');
        if (estaActivo) {
            await this.eliminarDeWishlist(idProducto);
            if (boton) {
                boton.classList.remove('text-danger');
                const icon = boton.querySelector('i');
                if (icon) {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                }
            }
        } else {
            await this.agregarAWishlist(idProducto, boton);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar tooltips y popovers de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
});
