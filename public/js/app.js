// JavaScript Global de la Aplicación: ElectroHogar
// Notificaciones Toast, utilidades de carrito y eventos compartidos
// Prueba PR

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
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar tooltips y popovers de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
});
