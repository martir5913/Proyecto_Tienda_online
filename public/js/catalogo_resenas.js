const CatalogoResenasModulo = {
  init() {
    this.raiz = document.getElementById('catalogo-resenas-app');
    if (!this.raiz) return;

    this.baseUrl = String(this.raiz.dataset.baseUrl || '').replace(/\/$/, '');
    this.autenticado = this.raiz.dataset.auth === '1';
    this.csrf = String(this.raiz.dataset.csrf || '');

    this.productoActual = 0;
    this.nombreProductoActual = '';

    /* Modal listado */
    this.modalListadoEl = document.getElementById('modalCatalogoResenas');
    this.modalListado = this.modalListadoEl ? bootstrap.Modal.getOrCreateInstance(this.modalListadoEl) : null;

    this.contenido = document.getElementById('catalogo-resenas-contenido');
    this.nombreProducto = document.getElementById('catalogo-resenas-producto');
    this.btnEscribir = document.getElementById('btn-catalogo-escribir-resena');

    /* Modal nueva reseña */
    this.modalNuevaEl = document.getElementById('modalCatalogoNuevaResena');
    this.modalNueva = this.modalNuevaEl ? bootstrap.Modal.getOrCreateInstance(this.modalNuevaEl) : null;

    this.form = document.getElementById('form-catalogo-resena');
    this.idProducto = document.getElementById('catalogo-resena-id-producto');
    this.calificacion = document.getElementById('catalogo-resena-calificacion');
    this.comentario = document.getElementById('catalogo-resena-comentario');
    this.contador = document.getElementById('catalogo-resena-contador');
    this.alerta = document.getElementById('catalogo-resena-alerta');
    this.btnGuardar = document.getElementById('btn-catalogo-guardar-resena');

    this.registrarEventos();

    /* Inserta automáticamente el acceso a reseñas en las tarjetas existentes. */
    this.instalarBotonesResenas();

    const grid = document.getElementById('grid-productos');
    if (grid) {
      const observer = new MutationObserver(() => {
        this.instalarBotonesResenas();
      });
      observer.observe(grid, { childList: true, subtree: true });
    }
  },

  registrarEventos() {
    /* Delegación para botones Ver reseñas. */
    document.addEventListener('click', (event) => {
      const boton = event.target.closest('.btn-catalogo-resenas');
      if (!boton) return;

      const id = Number.parseInt(boton.dataset.productoId || '0', 10);
      const nombre = String(boton.dataset.productoNombre || 'Producto');

      if (id > 0) {
        this.abrirResenas(id, nombre);
      }
    });

    /* Escribir reseña. */
    this.btnEscribir?.addEventListener('click', () => {
      this.prepararNuevaResena();
    });

    /* Estrellas. */
    document.querySelectorAll('.btn-catalogo-estrella').forEach((boton) => {
      boton.addEventListener('click', () => {
        const valor = Number.parseInt(boton.dataset.valor || '0', 10);
        this.seleccionarEstrellas(valor);
      });
    });

    /* Contador. */
    this.comentario?.addEventListener('input', () => {
      this.contador.textContent = `${this.comentario.value.length}/1000`;
    });

    /* Guardar. */
    this.form?.addEventListener('submit', (event) => {
      event.preventDefault();
      this.guardarResena();
    });
  },

  /* Agregar automáticamente el enlace de reseñas sin modificar catalogo.js. */
  instalarBotonesResenas() {
    document.querySelectorAll('.btn-detalle-producto').forEach((btnDetalle) => {
      const tarjeta = btnDetalle.closest('.product-card');
      if (!tarjeta) return;

      if (tarjeta.querySelector('.btn-catalogo-resenas')) return;

      const id = Number.parseInt(btnDetalle.dataset.productoId || '0', 10);
      if (id <= 0) return;

      const titulo = tarjeta.querySelector('.product-title');
      const nombre = titulo ? titulo.textContent.trim() : 'Producto';

      const boton = document.createElement('button');
      boton.type = 'button';
      boton.className = 'btn btn-link p-0 text-decoration-none btn-catalogo-resenas d-block mb-2';
      boton.dataset.productoId = String(id);
      boton.dataset.productoNombre = nombre;

      boton.innerHTML = `
        <span class="catalogo-resumen-resenas" data-producto-id="${id}">
          <span class="text-warning">
            <i class="bi bi-star"></i>
          </span>
          <span class="small text-muted ms-1">
            Cargando reseñas...
          </span>
        </span>
      `;

      const zonaInferior = tarjeta.querySelector('.mt-auto.pt-2.border-top');
      if (zonaInferior) {
        zonaInferior.before(boton);
      } else {
        tarjeta.querySelector('.product-card-body')?.appendChild(boton);
      }
      
      this.cargarResumenProducto(id, boton);
    });
  },
  
  async cargarResumenProducto(idProducto, boton) {
    if (!Number.isInteger(idProducto) || idProducto <= 0 || !boton) return;

    try {
      const resp = await fetch(`${this.baseUrl}/api/resenas.php?action=producto&id_producto=${encodeURIComponent(idProducto)}`, {
        headers: { 'Accept': 'application/json' }
      });
      const res = await resp.json();

      if (!resp.ok || !res.success) {
        throw new Error('No fue posible obtener las reseñas.');
      }

      const resumen = res.data?.resumen || {};
      const promedio = Number.parseFloat(resumen.promedio || 0) || 0;
      const total = Number.parseInt(resumen.total || 0, 10) || 0;

      if (total <= 0) {
        boton.innerHTML = `
          <span class="text-warning" aria-hidden="true">
            ${this.estrellasPromedio(0)}
          </span>
          <span class="small text-muted ms-1">
            Sin reseñas
          </span>
        `;
        return;
      }

      boton.innerHTML = `
        <span class="text-warning" aria-hidden="true">
          ${this.estrellasPromedio(promedio)}
        </span>
        <span class="small fw-semibold text-dark ms-1">
          ${promedio.toFixed(1)}
        </span>
        <span class="small text-muted ms-1">
          (${total} reseña${total === 1 ? '' : 's'})
        </span>
      `;

    } catch (error) {
      boton.innerHTML = `
        <span class="text-warning">
          <i class="bi bi-star"></i>
        </span>
        <span class="small text-muted ms-1">
          Ver reseñas
        </span>
      `;
    }
  },

  async abrirResenas(idProducto, nombre) {
    this.productoActual = idProducto;
    this.nombreProductoActual = nombre;
    this.nombreProducto.textContent = nombre;

    this.contenido.innerHTML = `
      <div class="text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
        Cargando reseñas...
      </div>
    `;

    this.modalListado.show();

    try {
      const resp = await fetch(`${this.baseUrl}/api/resenas.php?action=producto&id_producto=${encodeURIComponent(idProducto)}`, {
        headers: { 'Accept': 'application/json' }
      });
      const res = await resp.json();

      if (!resp.ok || !res.success) {
        throw new Error(res.message || 'No fue posible obtener las reseñas.');
      }

      const data = res.data || {};
      const resenas = Array.isArray(data.resenas) ? data.resenas : [];
      const resumen = data.resumen || {};

      this.renderizarResenas(resumen, resenas);
    } catch (error) {
      this.contenido.innerHTML = `
        <div class="alert alert-danger mb-0">
          <i class="bi bi-exclamation-triangle me-2"></i>
          ${this.escaparHtml(error.message)}
        </div>
      `;
    }
  },

  renderizarResenas(resumen, resenas) {
    if (resenas.length === 0) {
      this.contenido.innerHTML = `
        <div class="text-center py-4">
          <i class="bi bi-chat-square-text fs-1 text-muted"></i>
          <h6 class="mt-3">Sin reseñas todavía</h6>
          <p class="small text-muted mb-0">Sé el primero en compartir tu experiencia.</p>
        </div>
      `;
      return;
    }

    const promedio = Number.parseFloat(resumen.promedio || 0) || 0;
    const total = Number.parseInt(resumen.total || resenas.length, 10) || resenas.length;

    const listado = resenas.map((resena) => {
      const estrellas = Number.parseInt(resena.calificacion, 10) || 0;
      const usuario = this.escaparHtml(resena.nombre_usuario || resena.usuario || resena.nombre || 'Cliente');
      const comentario = this.escaparHtml(resena.comentario || '');
      const fecha = this.escaparHtml(resena.fecha_resena || resena.fecha || '');

      return `
        <div class="border rounded-3 p-3 mb-3">
          <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
            <div>
              <div class="fw-semibold">
                <i class="bi bi-person-circle me-1"></i> ${usuario}
              </div>
              <div class="small text-muted">${fecha}</div>
            </div>
            <div class="text-warning text-nowrap">
              ${this.estrellasHtml(estrellas)}
            </div>
          </div>
          <p class="small text-muted text-break mb-0">
            <i class="bi bi-chat-left-text me-1"></i> ${comentario}
          </p>
        </div>
      `;
    }).join('');

    this.contenido.innerHTML = `
      <div class="bg-light border rounded-3 p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-bold fs-4">${promedio.toFixed(1)}</div>
            <div class="text-warning">${this.estrellasPromedio(promedio)}</div>
          </div>
          <div class="text-end">
            <div class="fw-semibold">${total}</div>
            <div class="small text-muted">reseña${total === 1 ? '' : 's'}</div>
          </div>
        </div>
      </div>
      ${listado}
    `;
  },
  
    mostrarLoginRequerido() {

        if (!this.contenido) {
            return;
        }

        /*
        * Evita mostrar varias veces el mismo mensaje.
        */
        const alertaAnterior =
            this.contenido.querySelector(
                '.alert-login-resena'
            );

        if (alertaAnterior) {
            alertaAnterior.remove();
        }


        const alerta =
            document.createElement('div');


        alerta.className =
            'alert alert-warning alert-login-resena d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2';


        alerta.innerHTML = `
            <div class="small">

                <i class="bi bi-person-lock me-2"></i>

                Debes iniciar sesión para escribir una reseña.

            </div>

            <a
                href="${this.baseUrl}/index.php?ruta=login"
                class="btn btn-sm btn-outline-dark flex-shrink-0"
            >
                <i class="bi bi-box-arrow-in-right me-1"></i>
                Iniciar sesión
            </a>
        `;


        this.contenido.prepend(
            alerta
        );
    },

  async prepararNuevaResena() {
    /* Si no inició sesión, enviarlo al login. */
    if (!this.autenticado) {

    this.mostrarLoginRequerido();

    return;
}
    try {
      const resp = await fetch(`${this.baseUrl}/api/resenas.php?action=estado&id_producto=${encodeURIComponent(this.productoActual)}`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });
      const res = await resp.json();

      if (!resp.ok || !res.success) {
        throw new Error(res.message || 'No fue posible verificar la reseña.');
      }

      /* Si ya reseñó este producto, no permitimos duplicados. */
      if (res.data && res.data.ya_resenado) {
        this.mostrarMensajeListado('Ya calificaste este producto.', 'info');
        return;
      }
      this.abrirFormularioResena();

    } catch (error) {
      this.mostrarMensajeListado(error.message, 'danger');
    }
  },

  abrirFormularioResena() {
    this.form.reset();
    this.idProducto.value = String(this.productoActual);
    this.calificacion.value = '0';
    this.comentario.value = '';
    this.contador.textContent = '0/1000';

    document.querySelectorAll('.btn-catalogo-estrella i').forEach((icono) => {
      icono.className = 'bi bi-star';
    });

    document.getElementById('catalogo-nueva-resena-producto').textContent = this.nombreProductoActual;

    this.ocultarAlerta();
    this.modalListado.hide();

    window.setTimeout(() => {
      this.modalNueva.show();
    }, 180);
  },

  seleccionarEstrellas(valor) {
    if (valor < 1 || valor > 5) return;

    this.calificacion.value = String(valor);

    document.querySelectorAll('.btn-catalogo-estrella').forEach((boton) => {
      const numero = Number.parseInt(boton.dataset.valor, 10);
      const icono = boton.querySelector('i');
      icono.className = numero <= valor ? 'bi bi-star-fill' : 'bi bi-star';
    });
  },

  async guardarResena() {
    const idProducto = Number.parseInt(this.idProducto.value, 10);
    const calificacion = Number.parseInt(this.calificacion.value, 10);
    const comentario = this.comentario.value.trim();

    if (calificacion < 1 || calificacion > 5) {
      this.mostrarAlerta('Selecciona una calificación entre 1 y 5 estrellas.');
      return;
    }

    if (comentario === '') {
      this.mostrarAlerta('Escribe un comentario.');
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

      this.modalNueva.hide();

      window.setTimeout(() => {
        this.abrirResenas(this.productoActual, this.nombreProductoActual);
      }, 180);

    } catch (error) {
      this.mostrarAlerta(error.message);
    } finally {
      this.btnGuardar.disabled = false;
    }
  },

  mostrarMensajeListado(mensaje, tipo) {
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} small mb-3`;
    alerta.textContent = mensaje;
    this.contenido.prepend(alerta);
  },

  mostrarAlerta(mensaje) {
    this.alerta.className = 'alert alert-danger small';
    this.alerta.textContent = mensaje;
  },

  ocultarAlerta() {
    this.alerta.className = 'alert alert-danger d-none small';
    this.alerta.textContent = '';
  },

  estrellasHtml(cantidad) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
      html += i <= cantidad ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
    }
    return html;
  },

  estrellasPromedio(promedio) {
    let html = '';
    const valor = Math.round(promedio * 2) / 2;

    for (let i = 1; i <= 5; i++) {
      if (valor >= i) {
        html += '<i class="bi bi-star-fill"></i>';
      } else if (valor >= i - 0.5) {
        html += '<i class="bi bi-star-half"></i>';
      } else {
        html += '<i class="bi bi-star"></i>';
      }
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
  }
};

document.addEventListener('DOMContentLoaded', () => CatalogoResenasModulo.init());