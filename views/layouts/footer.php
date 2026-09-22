    </main>

    <!-- Footer Corporativo -->
    <footer class="footer-custom">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="d-flex align-items-center">
                        <i class="bi bi-lightning-charge-fill text-warning me-2"></i> Doméstik
                    </h5>
                    <p class="small">Líderes en electrodomésticos y tecnología de línea blanca para el hogar. Garantía, respaldo técnico y eficiencia energética comprobada.</p>
                    <div class="d-flex gap-3 text-white fs-5">
                        <i class="bi bi-shield-check text-success" title="Compra 100% Segura"></i>
                        <i class="bi bi-truck text-info" title="Envíos Nacionales"></i>
                        <i class="bi bi-credit-card text-warning" title="Pagos Flexibles"></i>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Categorías</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?ruta=catalogo&categoria=1">Refrigeración</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?ruta=catalogo&categoria=2">Lavado y Secado</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?ruta=catalogo&categoria=3">Cocción y Estufas</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?ruta=catalogo&categoria=4">Pequeños Aparatos</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Marcas Aliadas</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?ruta=catalogo&marca=1">Samsung Inverter</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?ruta=catalogo&marca=2">LG Smart Living</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?ruta=catalogo&marca=3">Whirlpool Xpert</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?ruta=catalogo&marca=4">Mabe Hogar</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Atención y Soporte</h5>
                    <p class="small mb-1"><i class="bi bi-geo-alt me-2 text-primary"></i> Ciudad de Guatemala, Guatemala</p>
                    <p class="small mb-1"><i class="bi bi-telephone me-2 text-primary"></i> +(502) 2200-0000</p>
                    <p class="small"><i class="bi bi-envelope me-2 text-primary"></i> soporte@domestik.com</p>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center small">
                <span>&copy; 2026 Doméstik. Proyecto Académico de Comercio Electrónico.</span>
                <span class="badge bg-dark border border-secondary text-secondary">
                    <i class="bi bi-code-slash me-1"></i> MVC + MySQL InnoDB ACID
                </span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- App Main JS -->
    <script src="<?= BASE_URL ?>/public/js/app.js"></script>
    <?php if (isset($scriptEspecifico)): ?>
        <?php
        $rutaScript = PUBLIC_DIR . '/js/' . $scriptEspecifico;
        $versionScript = file_exists($rutaScript) ? filemtime($rutaScript) : time();
        ?>
        <script src="<?= BASE_URL ?>/public/js/<?= $scriptEspecifico ?>?v=<?= $versionScript ?>"></script>
    <?php endif; ?>
</body>
</html>
