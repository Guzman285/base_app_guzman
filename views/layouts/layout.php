<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="/proyecto011/">
            <img src="<?= asset('./images/cit.png') ?>" width="35px'" alt="cit" >
            Sistema de Gestión
        </a>
        <div class="collapse navbar-collapse" id="navbarToggler">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="margin: 0;">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/proyecto011/">
                        <i class="bi bi-house-fill me-2"></i>Inicio
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/proyecto011/registro">
                        <i class="bi bi-person-add me-2"></i>Usuarios
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/proyecto011/aplicaciones">
                        <i class="bi bi-app me-2"></i>Aplicaciones
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/proyecto011/permisos">
                        <i class="bi bi-person-fill-gear me-2"></i>Permisos
                    </a>
                </li>
            </ul> 
            
            <?php if(isset($_SESSION['auth_user']) && $_SESSION['auth_user']): ?>
            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= $_SESSION['usuario_nombre'] ?? 'Usuario' ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                        <li><h6 class="dropdown-header">Usuario: <?= $_SESSION['usuario_nombre'] ?? 'N/A' ?></h6></li>
                        <li><h6 class="dropdown-header">Rol: <?= $_SESSION['rol_nombre'] ?? 'USER' ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" onclick="cerrarSesion()">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php else: ?>
            <div class="col-lg-1 d-grid mb-lg-0 mb-2">
                <a href="/proyecto011/login" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Resto del layout permanece igual -->