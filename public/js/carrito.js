/**
 * JavaScript para el Módulo de Carrito de Compras y Checkout
 */

const CarritoModulo = {
    async actualizarCantidad(idProducto, cantidad) {
        try {
            const resp = await fetch('api/carrito.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_producto: idProducto, cantidad: parseInt(cantidad) })
            });
            const data = await resp.json();
            if (data.success) {
                location.reload();
            } else {
                ElectroApp.mostrarToast(data.message, 'danger');
            }
        } catch (e) {
            ElectroApp.mostrarToast('Error al actualizar cantidad', 'danger');
        }
    },

    async eliminarItem(idProducto) {
        try {
            const resp = await fetch('api/carrito.php?action=remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_producto: idProducto })
            });
            const data = await resp.json();
            if (data.success) {
                location.reload();
            }
        } catch (e) {
            ElectroApp.mostrarToast('Error al eliminar producto', 'danger');
        }
    },

    async realizarCheckout(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const btnSubmit = form.querySelector('button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando orden...';
        }

        try {
            const resp = await fetch('api/pedidos.php?action=checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const res = await resp.json();
            if (res.success) {
                window.location.href = `index.php?ruta=mis_pedidos&orden=${res.data.numero_pedido}&exito=1`;
            } else {
                ElectroApp.mostrarToast(res.message, 'danger');
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="bi bi-shield-check me-2"></i>Confirmar Pedido Transaccional';
                }
            }
        } catch (e) {
            ElectroApp.mostrarToast('Error de conexión durante el checkout.', 'danger');
            if (btnSubmit) {
                btnSubmit.disabled = false;
            }
        }
    }
};
