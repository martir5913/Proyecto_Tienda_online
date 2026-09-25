<?php

declare(strict_types=1);

// Consola de pruebas disponible únicamente para administradores.

$tituloPagina = 'Pruebas de API | Domestik';
$scriptEspecifico = 'admin_apis.js';

require_once dirname(__DIR__, 2) . '/app/middlewares/AuthMiddleware.php';

use App\Middlewares\AuthMiddleware;

AuthMiddleware::verificarAdmin();

/*APIs disponibles*/

$apis = [
    [
        'nombre' => 'Catálogo de productos',
        'metodo' => 'GET',
        'endpoint' => 'api/productos.php',
        'descripcion' => 'Obtiene los productos visibles en el catálogo.',
        'tipo' => 'Pública',
        'badge' => 'text-bg-success',
    ],
    [
        'nombre' => 'Categorías',
        'metodo' => 'GET',
        'endpoint' => 'api/categorias.php',
        'descripcion' => 'Consulta las categorías registradas.',
        'tipo' => 'Pública',
        'badge' => 'text-bg-success',
    ],
    [
        'nombre' => 'Marcas',
        'metodo' => 'GET',
        'endpoint' => 'api/marcas.php',
        'descripcion' => 'Obtiene las marcas disponibles.',
        'tipo' => 'Pública',
        'badge' => 'text-bg-success',
    ],
    [
        'nombre' => 'Sesión actual',
        'metodo' => 'GET',
        'endpoint' => 'api/auth.php?action=check',
        'descripcion' => 'Comprueba qué usuario se encuentra autenticado.',
        'tipo' => 'Sesión',
        'badge' => 'text-bg-primary',
    ],
    [
        'nombre' => 'Carrito',
        'metodo' => 'GET',
        'endpoint' => 'api/carrito.php?action=get',
        'descripcion' => 'Consulta el contenido actual del carrito.',
        'tipo' => 'Sesión',
        'badge' => 'text-bg-primary',
    ],
    [
        'nombre' => 'Lista de deseos',
        'metodo' => 'GET',
        'endpoint' => 'api/wishlist.php?action=get',
        'descripcion' => 'Consulta los productos favoritos del usuario autenticado.',
        'tipo' => 'Autenticada',
        'badge' => 'text-bg-primary',
    ],
    [
        'nombre' => 'Historial de pedidos',
        'metodo' => 'GET',
        'endpoint' => 'api/pedidos.php?action=historial',
        'descripcion' => 'Obtiene los pedidos asociados al usuario autenticado.',
        'tipo' => 'Autenticada',
        'badge' => 'text-bg-primary',
    ],
    [
        'nombre' => 'Pedidos administrativos',
        'metodo' => 'GET',
        'endpoint' => 'api/admin_pedidos.php?action=listar',
        'descripcion' => 'Consulta todos los pedidos disponibles para administración.',
        'tipo' => 'Administrador',
        'badge' => 'text-bg-warning',
    ],
    [
        'nombre' => 'Usuarios administrativos',
        'metodo' => 'GET',
        'endpoint' => 'api/admin_usuarios.php?action=listar',
        'descripcion' => 'Consulta usuarios, roles, estados y métricas administrativas.',
        'tipo' => 'Administrador',
        'badge' => 'text-bg-warning',
    ],
    [
        'nombre' => 'Diagnóstico MySQL',
        'metodo' => 'GET',
        'endpoint' => 'api/test_db.php',
        'descripcion' => 'Verifica conexión, servidor MySQL, tablas y transacciones.',
        'tipo' => 'Administrador',
        'badge' => 'text-bg-warning',
    ],
];

require_once dirname(__DIR__) . '/layouts/header.php';
?>

<style>
    .caja-scroll-json textarea {
        min-height: 150px !important;
        height: auto !important;
    }
    #api-response {
        min-height: 150px;
        max-height: 400px; 
        overflow-y: auto;
    }

    @media (min-width: 992px) {
        .api-workspace {
            height: calc(100vh - 190px);
            min-height: 550px;
        }
        .api-columna {
            height: 100%;
        }
        .api-columna .card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        /* C1 y C3: Scroll interno para lista y respuesta */
        .caja-scroll, 
        .caja-respuesta {
            flex: 1 1 auto;
            min-height: 0;
            height: 0; 
            overflow-y: auto;
        }

        .caja-parametros, 
        .caja-parametros form {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
        
        /* C2: Contenedor del JSON que absorbe el espacio sobrante */
        .caja-scroll-json {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
        
        /* C2: El textarea interno, obligado a hacer scroll mágico */
        .caja-scroll-json textarea {
            flex: 1 1 auto;
            height: 0 !important; 
            resize: none;
            overflow-y: auto;
        }
        
        #api-response {
            height: 100%;
            max-height: none;
        }
    }
</style>

<div
    class="container-fluid py-3 px-3 px-lg-4"
    id="admin-api-console"
    data-base-url="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>"
>

    <!-- ENCABEZADO -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-braces-asterisk me-2 text-primary"></i>
                Pruebas de API
            </h3>
            <p class="text-muted small mb-0">
                Consulta y prueba los endpoints internos disponibles en Domestik.
            </p>
        </div>
        <a href="<?= BASE_URL ?>/index.php?ruta=admin_dashboard" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver al panel
        </a>
    </div>

    <!-- WORKSPACE -->
    <div class="row g-3 api-workspace">

        <!-- COLUMNA 1 - APIs DISPONIBLES -->
        <div class="col-12 col-lg-3 api-columna">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white border-0 pt-3 px-3 pb-2 flex-shrink-0">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="bi bi-diagram-3 me-2"></i>APIs disponibles</h5>
                            <p class="text-muted small mb-0">Selecciona un endpoint.</p>
                        </div>
                        <span class="badge text-bg-light"><?= count($apis) ?></span>
                    </div>
                </div>

                <div class="card-body p-3 caja-scroll">
                    <?php foreach ($apis as $api): ?>
                        <div class="border rounded-3 p-3 mb-2 bg-white">
                            <div class="mb-2">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="fw-semibold">
                                        <?= htmlspecialchars($api['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <span class="badge <?= htmlspecialchars($api['badge'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($api['tipo'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                                <div class="small font-monospace text-danger text-break">
                                    <?= htmlspecialchars($api['metodo'] . ' /' . $api['endpoint'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="small text-muted mt-2">
                                    <?= htmlspecialchars($api['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-probar-api"
                                    data-method="<?= htmlspecialchars($api['metodo'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-endpoint="<?= htmlspecialchars($api['endpoint'], ENT_QUOTES, 'UTF-8') ?>">
                                    Probar
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- COLUMNA 2 - PARÁMETROS -->
        <div class="col-12 col-lg-4 api-columna">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white border-0 pt-3 px-3 pb-2 flex-shrink-0">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <h5 class="fw-bold mb-0"><i class="bi bi-terminal me-2"></i>Parámetros</h5>
                        <span class="small text-muted">HTTP / JSON</span>
                    </div>
                </div>

                <div class="card-body p-3 caja-parametros">
                    <form id="form-api-test" class="mb-0">

                        <!-- Método HTTP -->
                        <div class="mb-2 flex-shrink-0">
                            <label for="api-method" class="form-label small fw-semibold mb-1">Método HTTP</label>
                            <select class="form-select form-select-sm w-50" id="api-method">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="PATCH">PATCH</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>

                        <!-- Endpoint -->
                        <div class="mb-3 flex-shrink-0">
                            <label for="api-endpoint" class="form-label small fw-semibold mb-1">Endpoint</label>
                            <input type="text" class="form-control form-control-sm font-monospace" id="api-endpoint" value="api/productos.php" autocomplete="off" spellcheck="false">
                            <div class="form-text text-break mt-1 mb-0" style="font-size: 0.75rem;">
                                Base: <?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/
                            </div>
                        </div>

                        <!-- BODY JSON -->
                        <div class="mb-3 caja-scroll-json">
                            <div class="d-flex justify-content-between align-items-center gap-2 flex-shrink-0 mb-1">
                                <label for="api-body" class="form-label small fw-semibold mb-0">Body JSON</label>
                                <small class="text-muted" id="api-body-ayuda" style="font-size: 0.75rem;">Opcional para GET</small>
                            </div>
                            <textarea class="form-control font-monospace" id="api-body" spellcheck="false" placeholder='{
    "id_producto": 1,
    "cantidad": 1
}'></textarea>
                        </div>

                        <!-- BOTONES FIJOS ABAJO -->
                        <div class="d-grid gap-2 flex-shrink-0 pt-3 border-top mt-auto">
                            <button type="submit" class="btn btn-primary-app btn-sm" id="btn-enviar-api">
                                <i class="bi bi-send me-1"></i> Enviar solicitud
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-limpiar-api">
                                <i class="bi bi-eraser me-1"></i> Limpiar
                            </button>
                            <div class="small text-muted mt-2 text-center" style="font-size: 0.75rem;">
                                Solo se permiten endpoints internos de <code>api/</code>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- COLUMNA 3 - RESPUESTA JSON -->
        <div class="col-12 col-lg-5 api-columna">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden" id="seccion-respuesta-api">
                
                <div class="card-header bg-white border-0 px-3 py-3 flex-shrink-0">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="overflow-hidden">
                            <h5 class="fw-bold mb-1"><i class="bi bi-code-square me-2"></i>Respuesta JSON</h5>
                            <div class="small text-muted font-monospace text-break" id="api-url-final"></div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <span class="badge text-bg-secondary" id="api-status">Sin ejecutar</span>
                            <span class="small text-muted" id="api-tiempo"></span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0 d-flex flex-column caja-respuesta">
                    <pre class="bg-dark text-light rounded-bottom-3 p-3 m-0 font-monospace" id="api-response">Selecciona una API y pulsa Probar.</pre>
                </div>
                
            </div>
        </div>

    </div>

</div>
<!-- Confirmación para solicitudes que modifican información -->
<div
    class="modal fade"
    id="modalConfirmarApi"
    tabindex="-1"
    aria-labelledby="modalConfirmarApiTitulo"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header border-bottom">
                <div>
                    <h5
                        class="modal-title fw-bold mb-1"
                        id="modalConfirmarApiTitulo"
                    >
                        Confirmar solicitud
                    </h5>

                    <p class="text-muted small mb-0">
                        Esta operación puede modificar información del sistema.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>
            </div>

            <div class="modal-body">

                <div class="border rounded-3 p-3 mb-3">

                    <div class="small text-muted mb-1">
                        Método
                    </div>

                    <div class="fw-semibold mb-3">
                        <span
                            class="badge text-bg-light border text-dark"
                            id="confirmar-api-metodo"
                        >
                            POST
                        </span>
                    </div>

                    <div class="small text-muted mb-1">
                        Endpoint
                    </div>

                    <div
                        class="font-monospace small text-break"
                        id="confirmar-api-endpoint"
                    >
                        api/productos.php
                    </div>

                </div>

                <div class="small text-muted">
                    Revisa el método, el endpoint y el Body JSON antes de continuar.
                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    class="btn btn-primary-app"
                    id="btn-confirmar-api"
                >
                    <i class="bi bi-send-check me-1"></i>
                    Confirmar y enviar
                </button>

            </div>

        </div>
    </div>
</div>
<?php
require_once dirname(__DIR__) . '/layouts/footer.php';
?>