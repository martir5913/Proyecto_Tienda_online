<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$usuarioAutenticado =
    isset($_SESSION['usuario']['id_usuario']);

if (
    $usuarioAutenticado &&
    (
        !isset($_SESSION['csrf_resena']) ||
        !is_string($_SESSION['csrf_resena'])
    )
) {
    $_SESSION['csrf_resena'] =
        bin2hex(random_bytes(32));
}

$csrfResena =
    $usuarioAutenticado
        ? (string)$_SESSION['csrf_resena']
        : '';
?>

<div
    id="catalogo-resenas-app"
    data-base-url="<?= htmlspecialchars(
        BASE_URL,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    data-auth="<?= $usuarioAutenticado ? '1' : '0' ?>"
    data-csrf="<?= htmlspecialchars(
        $csrfResena,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
></div>


<!-- =========================================================
     MODAL: LISTAR RESEÑAS
========================================================= -->
<div
    class="modal fade"
    id="modalCatalogoResenas"
    tabindex="-1"
    aria-labelledby="modalCatalogoResenasTitulo"
    aria-hidden="true"
>
    <div
        class="modal-dialog modal-dialog-centered modal-dialog-scrollable"
    >

        <div class="modal-content border-0 shadow">

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title fw-bold mb-1"
                        id="modalCatalogoResenasTitulo"
                    >
                        <i class="bi bi-chat-square-heart me-2 text-primary"></i>
                        Reseñas del producto
                    </h5>

                    <p
                        class="small text-muted mb-0"
                        id="catalogo-resenas-producto"
                    >
                        Producto
                    </p>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>


            <div
                class="modal-body"
                id="catalogo-resenas-contenido"
            >

                <div class="text-center py-4 text-muted">
                    Cargando reseñas...
                </div>

            </div>


            <div
                class="
                    modal-footer
                    d-flex
                    justify-content-between
                "
            >

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                >
                    <i class="bi bi-x-lg me-1"></i>
                    Cerrar
                </button>


                <button
                    type="button"
                    class="btn btn-primary-app"
                    id="btn-catalogo-escribir-resena"
                >
                    <i class="bi bi-star me-1"></i>
                    Escribir reseña
                </button>

            </div>

        </div>

    </div>
</div>


<!-- =========================================================
     MODAL: CREAR RESEÑA
========================================================= -->
<div
    class="modal fade"
    id="modalCatalogoNuevaResena"
    tabindex="-1"
    aria-labelledby="modalCatalogoNuevaResenaTitulo"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title fw-bold mb-1"
                        id="modalCatalogoNuevaResenaTitulo"
                    >
                        <i class="bi bi-star me-2 text-primary"></i>
                        Calificar producto
                    </h5>

                    <p
                        class="small text-muted mb-0"
                        id="catalogo-nueva-resena-producto"
                    >
                        Producto
                    </p>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>


            <form id="form-catalogo-resena">

                <div class="modal-body">

                    <input
                        type="hidden"
                        id="catalogo-resena-id-producto"
                    >

                    <input
                        type="hidden"
                        id="catalogo-resena-calificacion"
                        value="0"
                    >


                    <div
                        class="alert alert-danger d-none small"
                        id="catalogo-resena-alerta"
                    ></div>


                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Tu calificación
                        </label>


                        <div class="d-flex gap-1">

                            <?php for ($i = 1; $i <= 5; $i++): ?>

                                <button
                                    type="button"
                                    class="
                                        btn
                                        btn-link
                                        p-0
                                        fs-3
                                        text-warning
                                        btn-catalogo-estrella
                                    "
                                    data-valor="<?= $i ?>"
                                    title="<?= $i ?> estrella<?= $i === 1 ? '' : 's' ?>"
                                >
                                    <i class="bi bi-star"></i>
                                </button>

                            <?php endfor; ?>

                        </div>

                        <div class="form-text">
                            Selecciona de 1 a 5 estrellas.
                        </div>

                    </div>


                    <div>

                        <div
                            class="
                                d-flex
                                justify-content-between
                                mb-1
                            "
                        >

                            <label
                                for="catalogo-resena-comentario"
                                class="form-label fw-semibold mb-0"
                            >
                                Comentario
                            </label>

                            <small
                                class="text-muted"
                                id="catalogo-resena-contador"
                            >
                                0/1000
                            </small>

                        </div>


                        <textarea
                            class="form-control"
                            id="catalogo-resena-comentario"
                            rows="4"
                            maxlength="1000"
                            placeholder="Cuéntanos tu experiencia con este producto"
                            required
                        ></textarea>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-lg me-1"></i>
                        Cancelar
                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary-app"
                        id="btn-catalogo-guardar-resena"
                    >
                        <i class="bi bi-send-check me-1"></i>
                        Publicar reseña
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>