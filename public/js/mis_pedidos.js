const ResenasPedidosModulo = {
  init() {
    this.raiz = document.getElementById('mis-pedidos-app');
    if (!this.raiz) return;

    this.baseUrl = String(this.raiz.dataset.baseUrl || '').replace(/\/$/, '');
    this.csrf = String(this.raiz.dataset.csrfResena || '');

    /* Modal para seleccionar producto */
    this.modalProductosEl = document.getElementById('modal-productos-resena');
    this.contenidoProductos = document.getElementById('resena-productos-contenido');
    this.numeroPedido = document.getElementById('resena-pedido-numero');
    this.modalProductos = this.modalProductosEl ? bootstrap.Modal.getOrCreateInstance(this.modalProductosEl) : null;

    /* Modal para escribir la reseña */
    this.modalResenaEl = document.getElementById('modal-resena');
    this.modalResena = this.modalResenaEl ? bootstrap.Modal.getOrCreateInstance(this.modalResenaEl) : null;

    this.form = document.getElementById('form-resena');
    this.idProducto = document.getElementById('resena-id-producto');
    this.nombreProducto = document.getElementById('resena-producto-nombre');
    this.calificacion = document.getElementById('resena-calificacion');
    this.comentario = document.getElementById('resena-comentario');
    this.contador = document.getElementById('resena-contador');
    this.alerta = document.getElementById('resena-alerta');
    this.btnGuardar = document.getElementById('btn-guardar-resena');

    this.pedidoActual = null;

    this.registrarEventos();
  },

  registrarEventos() {
    /* Botón Reseñar en la columna Acciones */
    document.querySelectorAll('.btn-resenar-pedido').forEach((boton) => {
      boton.addEventListener('click', () => {
        const idPedido = Number.parseInt(boton.dataset.pedidoId || '', 10);
        const numeroPedido = String(boton.dataset.pedidoNumero || '');

        if (Number.isInteger(idPedido) && idPedido > 0) {
          this.abrirProductosPedido(idPedido, numeroPedido);
        }
      });
    });

    /* Botón Calificar dentro del modal */
    this.contenidoProductos?.addEventListener('click', (event) => {
      const boton = event.target.closest('.btn-calificar-producto');
      if (!boton) return;

      const idProducto = Number.parseInt(boton.dataset.productoId || '', 10);
      const nombre = String(boton.dataset.productoNombre || 'Producto');

      if (Number.isInteger(idProducto) && idProducto > 0) {
        this.abrirModalResena(idProducto, nombre);
      }
    });

    /* Estrellas */
    document.querySelectorAll('.btn-estrella-resena').forEach((boton) => {
      boton.addEventListener('click', () => {
        const valor = Number.parseInt(boton.dataset.valor || '0', 10);
        this.seleccionarCalificacion(valor);
      });
    });

    /* Contador comentario */
    this.comentario?.addEventListener('input', () => {
      if (this.contador) {
        this.contador.textContent = `${this.comentario.value.length}/1000`;
      }
    });

    /* Guardar reseña */
    this.form?.addEventListener('submit', (event) => {
      event.preventDefault();
      this.guardarResena();
    });
  },

  /* Obtener productos del pedido. */
  async abrirProductosPedido(idPedido, numeroPedido = '') {
    if (!this.modalProductos || !this.contenidoProductos) return;

    this.pedidoActual = idPedido;

    if (this.numeroPedido) {
      this.numeroPedido.textContent = numeroPedido !== '' 
        ? `Pedido ${numeroPedido}` 
        : 'Selecciona un producto para calificar.';
    }

    this.contenidoProductos.innerHTML = `
      <div class="text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
        Cargando productos...
      </div>
    `;

    this.modalProductos.show();

    try {
      /* Utilizamos la API actual de pedidos*/
      const resp = await fetch(`${this.baseUrl}/api/pedidos.php?action=detalle&id=${encodeURIComponent(idPedido)}`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });
      const res = await resp.json();

      if (!resp.ok || !res.success || !res.data) {
        throw new Error(res.message || 'No se pudo obtener el pedido.');
      }

      const pedido = res.data;

      /* Seguridad visual adicional. La seguridad real también está en la API. */
      if (Number.parseInt(pedido.id_estado_pedido, 10) !== 4) {
        throw new Error('Solo puedes reseñar productos de pedidos entregados.');
      }

      const productos = Array.isArray(pedido.items) ? pedido.items : [];
      if (productos.length === 0) {
        throw new Error('Este pedido no contiene productos para reseñar.');
      }

      /* Consultar estado de reseña de cada producto. */
      const productosConEstado = await Promise.all(
        productos.map(async (producto) => {
          const estado = await this.consultarEstadoProducto(Number.parseInt(producto.id_producto, 10));
          return { ...producto, estado_resena: estado };
        })
      );

      this.renderizarProductos(productosConEstado);
    } catch (error) {
      this.contenidoProductos.innerHTML = `
        <div class="alert alert-danger mb-0">
          <i class="bi bi-exclamation-triangle me-2"></i>
          ${this.escaparHtml(error.message || 'No fue posible cargar los productos.')}
        </div>
      `;
    }
  },

  /* Saber si ya reseñó el producto. */
  async consultarEstadoProducto(idProducto) {
    if (!Number.isInteger(idProducto) || idProducto <= 0) {
      return { puede_resenar: false, ya_resenado: false, resena: null };
    }

    try {
      const resp = await fetch(`${this.baseUrl}/api/resenas.php?action=estado&id_producto=${encodeURIComponent(idProducto)}`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });
      const res = await resp.json();

      if (!resp.ok || !res.success || !res.data) {
        return { puede_resenar: false, ya_resenado: false, resena: null };
      }
      return res.data;
    } catch {
      return { puede_resenar: false, ya_resenado: false, resena: null };
    }
  },

  renderizarProductos(productos) {
    const html = productos.map((producto) => {
      const idProducto = Number.parseInt(producto.id_producto, 10) || 0;
      const estado = producto.estado_resena || {};
      let accion = '';

      /* Ya escribió reseña. */
        if (estado.ya_resenado) {

            const estrellas =
                Number.parseInt(
                    estado.resena?.calificacion,
                    10
                ) || 0;

            const comentario =
                this.escaparHtml(
                    estado.resena?.comentario ||
                    'Sin comentario.'
                );

            accion = `
                <div class="w-100">
                    <div class="text-md-end">
                        <div class="text-warning small mb-1">
                            ${this.estrellasHtml(estrellas)}
                        </div>

                        <span class="badge text-bg-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Reseñado
                        </span>

                    </div>
                    <div
                        class="
                            small
                            text-muted
                            mt-2
                            text-break
                        "
                        style="
                            overflow-wrap: anywhere;
                            word-break: break-word;
                            white-space: normal;
                        "
                    >
                        <i class="bi bi-chat-left-text me-1"></i>
                        ${comentario}
                    </div>
                </div>
            `;
        }
        
      /* Puede escribir reseña. */
      else if (estado.puede_resenar) {
        accion = `
          <button
            type="button"
            class="btn btn-sm btn-outline-primary btn-calificar-producto"
            data-producto-id="${idProducto}"
            data-producto-nombre="${this.escaparAtributo(producto.nombre_producto || 'Producto')}"
          >
            <i class="bi bi-star me-1"></i> Calificar
          </button>
        `;
      } else {
        accion = `
          <span class="small text-muted">No disponible</span>
        `;
      }

   return `
    <div class="border rounded-3 p-3 mb-2">
        <div class="row g-3 align-items-start">
            <!-- INFORMACIÓN DEL PRODUCTO -->
            <div class="col-12 col-md-7">

                <div class="fw-semibold">
                    ${this.escaparHtml(
                        producto.nombre_producto ||
                        'Producto'
                    )}
                </div>
                <div class="small text-muted mt-1">
                    ${this.escaparHtml(
                        producto.nombre_marca || ''
                    )}
                    ${
                        producto.codigo_modelo
                            ? ` · Mod: ${this.escaparHtml(
                                producto.codigo_modelo
                            )}`
                            : ''
                    }
                </div>
                <div class="small text-muted mt-1">
                    Cantidad comprada:
                    ${
                        Number.parseInt(
                            producto.cantidad,
                            10
                        ) || 0
                    }
                </div>
            </div>
            <!-- RESEÑA / ACCIÓN -->
            <div class="col-12 col-md-5">
                <div class="w-100">
                    ${accion}
                </div>
            </div>
        </div>
    </div>
`;
    }).join('');

    this.contenidoProductos.innerHTML = html;
  },

  abrirModalResena(idProducto, nombre) {
    if (!this.modalResena || !this.form) return;

    this.form.reset();
    this.idProducto.value = String(idProducto);
    this.calificacion.value = '0';
    this.nombreProducto.textContent = nombre;
    this.contador.textContent = '0/1000';

    this.ocultarAlerta();
    this.seleccionarCalificacion(0);

    /* Cerramos selección de productos y después mostramos reseña. */
    this.modalProductos?.hide();

    window.setTimeout(() => {
      this.modalResena.show();
    }, 180);
  },

  seleccionarCalificacion(valor) {
    const valorSeguro = Number.isInteger(valor) && valor >= 1 && valor <= 5 ? valor : 0;
    this.calificacion.value = String(valorSeguro);

    document.querySelectorAll('.btn-estrella-resena').forEach((boton) => {
      const numero = Number.parseInt(boton.dataset.valor || '0', 10);
      const icono = boton.querySelector('i');
      
      if (!icono) return;

      icono.className = numero <= valorSeguro ? 'bi bi-star-fill' : 'bi bi-star';
    });
  },

  async guardarResena() {
    const idProducto = Number.parseInt(this.idProducto.value || '0', 10);
    const calificacion = Number.parseInt(this.calificacion.value || '0', 10);
    const comentario = String(this.comentario.value || '').trim();

    if (!Number.isInteger(calificacion) || calificacion < 1 || calificacion > 5) {
      this.mostrarAlerta('Selecciona una calificación entre 1 y 5 estrellas.');
      return;
    }

    if (comentario === '') {
      this.mostrarAlerta('Escribe un comentario sobre el producto.');
      return;
    }

    this.btnGuardar.disabled = true;

    try {
      const resp = await fetch(`${this.baseUrl}/api/resenas.php?action=crear`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id_producto: idProducto,
          calificacion,
          comentario,
          csrf_token: this.csrf
        })
      });

      const res = await resp.json();

      if (!resp.ok || !res.success) {
        throw new Error(res.message || 'No fue posible publicar la reseña.');
      }

      this.mostrarAlerta('Reseña publicada correctamente.', 'success');

      window.setTimeout(() => {
        this.modalResena.hide();
        if (this.pedidoActual) {
          this.abrirProductosPedido(this.pedidoActual);
        }
      }, 650);

    } catch (error) {
      this.mostrarAlerta(error.message || 'No fue posible publicar la reseña.');
    } finally {
      this.btnGuardar.disabled = false;
    }
  },

  mostrarAlerta(mensaje, tipo = 'danger') {
    if (!this.alerta) return;
    this.alerta.className = `alert alert-${tipo} py-2 small`;
    this.alerta.textContent = mensaje;
  },

  ocultarAlerta() {
    if (!this.alerta) return;
    this.alerta.className = 'alert alert-danger py-2 small d-none';
    this.alerta.textContent = '';
  },

  estrellasHtml(cantidad) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
      html += i <= cantidad ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
    }
    return html;
  },

  escaparHtml(valor) {
    return String(valor ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  },

  escaparAtributo(valor) {
    return this.escaparHtml(valor);
  }
};

document.addEventListener('DOMContentLoaded', () => ResenasPedidosModulo.init());