// JavaScript para el Módulo de Carrito de Compras y Checkout
// Actualización reactiva y fluida sin recargas de página

const CarritoModulo = {
    // Formatea valores numéricos a moneda Quetzales
    formatearMoneda(valor) {
        const numero = parseFloat(valor) || 0;
        return 'Q ' + numero.toLocaleString('es-GT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    // Actualiza los totales en el DOM y la barra superior
    actualizarTotalesDOM(resumen) {
        if (!resumen) return;

        const subtotalEl = document.getElementById('resumen-subtotal');
        const impuestoEl = document.getElementById('resumen-impuesto');
        const totalEl = document.getElementById('resumen-total');

        if (subtotalEl) subtotalEl.textContent = this.formatearMoneda(resumen.subtotal);
        if (impuestoEl) impuestoEl.textContent = this.formatearMoneda(resumen.impuesto);
        if (totalEl) totalEl.textContent = this.formatearMoneda(resumen.total);

        if (typeof ElectroApp !== 'undefined' && ElectroApp.actualizarBadgeCarrito) {
            ElectroApp.actualizarBadgeCarrito(resumen.total_items ?? 0);
        }

        // Si el carrito quedó vacío, mostrar la vista vacía sin recargar
        if (!resumen.items || resumen.items.length === 0) {
            this.mostrarEstadoVacio();
        }
    },

    // Muestra el mensaje visual de carrito vacío
    mostrarEstadoVacio() {
        const contenedor = document.getElementById('contenedor-carrito');
        if (!contenedor) return;

        contenedor.innerHTML = `
            <h3 class="fw-bold mb-4"><i class="bi bi-cart3 me-2"></i>Carrito de Compras</h3>
            <div class="text-center py-5 bg-white rounded-3 shadow-sm" id="carrito-vacio">
                <i class="bi bi-cart-x fs-1 text-muted"></i>
                <h5 class="mt-3 text-muted">Tu carrito está vacío</h5>
                <p class="text-muted small">Explora nuestro catálogo para encontrar electrodomésticos para tu hogar.</p>
                <a href="index.php?ruta=catalogo" class="btn btn-primary-app">
                    <i class="bi bi-grid-fill me-1"></i> Ir al Catálogo
                </a>
            </div>
        `;
    },

    // Incrementa o decrementa la cantidad con los botones +/-
    cambiarCantidadRelativa(idProducto, delta) {
        const input = document.getElementById(`input-cant-${idProducto}`);
        if (!input) return;

        const cantActual = parseInt(input.value, 10) || 1;
        const nuevaCant = cantActual + delta;

        if (nuevaCant <= 0) {
            this.eliminarItem(idProducto);
            return;
        }

        input.value = nuevaCant;
        this.actualizarCantidad(idProducto, nuevaCant);
    },

    // Actualiza la cantidad de un artículo mediante la API
    async actualizarCantidad(idProducto, cantidad) {
        const cant = parseInt(cantidad, 10);
        const input = document.getElementById(`input-cant-${idProducto}`);

        if (isNaN(cant) || cant <= 0) {
            this.eliminarItem(idProducto);
            return;
        }

        try {
            const resp = await fetch('api/carrito.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_producto: idProducto, cantidad: cant })
            });
            const data = await resp.json();

            if (data.success && data.data) {
                // Actualizar subtotal de la fila específica
                const items = data.data.items || [];
                const itemActualizado = items.find(it => it.id_producto == idProducto);

                if (itemActualizado) {
                    const subtotalItemEl = document.getElementById(`subtotal-item-${idProducto}`);
                    if (subtotalItemEl) {
                        subtotalItemEl.textContent = this.formatearMoneda(itemActualizado.subtotal);
                    }
                }

                // Actualizar los totales del resumen
                this.actualizarTotalesDOM(data.data);
            } else {
                ElectroApp.mostrarToast(data.message || 'No se pudo actualizar la cantidad.', 'danger');
                // Revertir valor si la API devuelve error (ej. superó stock)
                if (input && data.data && data.data.items) {
                    const itemPrevio = data.data.items.find(it => it.id_producto == idProducto);
                    if (itemPrevio) input.value = itemPrevio.cantidad;
                }
            }
        } catch (e) {
            ElectroApp.mostrarToast('Error de conexión al actualizar cantidad', 'danger');
        }
    },

    // Elimina un artículo del carrito
    async eliminarItem(idProducto) {
        try {
            const resp = await fetch('api/carrito.php?action=remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_producto: idProducto })
            });
            const data = await resp.json();

            if (data.success) {
                const fila = document.getElementById(`fila-item-${idProducto}`);
                if (fila) {
                    fila.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                    fila.style.opacity = '0';
                    fila.style.transform = 'translateX(-10px)';
                    setTimeout(() => fila.remove(), 250);
                }

                this.actualizarTotalesDOM(data.data);
                ElectroApp.mostrarToast('Producto eliminado del carrito.', 'success');
            } else {
                ElectroApp.mostrarToast(data.message || 'Error al eliminar producto', 'danger');
            }
        } catch (e) {
            ElectroApp.mostrarToast('Error de conexión al eliminar producto', 'danger');
        }
    },

    // Cambia dinámicamente los campos según el método de pago seleccionado (RF13)
    cambiarMetodoPago(idMetodo) {
        const panelTarjeta = document.getElementById('panel-pago-tarjeta');
        const panelTransferencia = document.getElementById('panel-pago-transferencia');
        const panelEntrega = document.getElementById('panel-pago-entrega');

        if (panelTarjeta) panelTarjeta.classList.add('d-none');
        if (panelTransferencia) panelTransferencia.classList.add('d-none');
        if (panelEntrega) panelEntrega.classList.add('d-none');

        if (idMetodo === 1 && panelTarjeta) {
            panelTarjeta.classList.remove('d-none');
        } else if (idMetodo === 2 && panelTransferencia) {
            panelTransferencia.classList.remove('d-none');
        } else if (panelEntrega) {
            panelEntrega.classList.remove('d-none');
        }
    },

    // Procesa la orden de compra y el pago (RF13, RF20)
    async realizarCheckout(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const idMetodo = parseInt(data.id_metodo_pago, 10);

        // Validaciones específicas según el método de pago
        if (idMetodo === 1) { // Tarjeta
            const cardNum = (data.card_numero || '').replace(/\s+/g, '');
            if (cardNum.length < 15 || cardNum.length > 16) {
                if (typeof ElectroApp !== 'undefined') {
                    ElectroApp.mostrarToast('Por favor, ingresa un número de tarjeta válido de 16 dígitos.', 'warning');
                }
                const cardInput = document.getElementById('card_numero');
                if (cardInput) cardInput.focus();
                return;
            }
            if (!data.card_exp || data.card_exp.length < 4) {
                if (typeof ElectroApp !== 'undefined') {
                    ElectroApp.mostrarToast('Por favor, ingresa la fecha de vencimiento (MM/AA).', 'warning');
                }
                const expInput = document.getElementById('card_exp');
                if (expInput) expInput.focus();
                return;
            }
            if (!data.card_cvv || data.card_cvv.length < 3) {
                if (typeof ElectroApp !== 'undefined') {
                    ElectroApp.mostrarToast('Por favor, ingresa el código de seguridad CVV.', 'warning');
                }
                const cvvInput = document.getElementById('card_cvv');
                if (cvvInput) cvvInput.focus();
                return;
            }
            data.tarjeta_terminacion = cardNum.slice(-4);
            // No enviar número completo ni CVV por seguridad
            delete data.card_numero;
            delete data.card_cvv;
            delete data.card_exp;
        } else if (idMetodo === 2) { // Transferencia
            if (!data.numero_boleta || data.numero_boleta.trim() === '') {
                if (typeof ElectroApp !== 'undefined') {
                    ElectroApp.mostrarToast('Por favor, ingresa el número de boleta o transferencia bancaria.', 'warning');
                }
                const boletaInput = document.getElementById('numero_boleta');
                if (boletaInput) boletaInput.focus();
                return;
            }
        }

        const btnSubmit = document.getElementById('btn-confirmar-checkout') || form.querySelector('button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando pago y orden...';
        }

        try {
            const resp = await fetch('api/pedidos.php?action=checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const res = await resp.json();
            if (res.success) {
                if (typeof ElectroApp !== 'undefined') {
                    ElectroApp.mostrarToast('¡Orden y pago procesados con éxito!', 'success');
                }
                // Redirigir a la vista de Confirmación y Facturación (RF20)
                setTimeout(() => {
                    window.location.href = `index.php?ruta=pedido_confirmacion&id=${res.data.id_pedido}&exito=1`;
                }, 400);
            } else {
                if (typeof ElectroApp !== 'undefined') {
                    ElectroApp.mostrarToast(res.message || 'Error al procesar el pago.', 'danger');
                }
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="bi bi-shield-check me-2"></i> Confirmar y Pagar Orden';
                }
            }
        } catch (e) {
            if (typeof ElectroApp !== 'undefined') {
                ElectroApp.mostrarToast('Error de conexión durante el checkout.', 'danger');
            }
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-shield-check me-2"></i> Confirmar y Pagar Orden';
            }
        }
    }
};

// Formateadores interactivos para tarjeta de crédito
document.addEventListener('DOMContentLoaded', () => {
    const cardNumInput = document.getElementById('card_numero');
    if (cardNumInput) {
        cardNumInput.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '').substring(0, 16);
            let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
            e.target.value = formatted;
        });
    }

    const cardExpInput = document.getElementById('card_exp');
    if (cardExpInput) {
        cardExpInput.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '').substring(0, 4);
            if (val.length >= 2) {
                e.target.value = val.substring(0, 2) + '/' + val.substring(2);
            } else {
                e.target.value = val;
            }
        });
    }
});

