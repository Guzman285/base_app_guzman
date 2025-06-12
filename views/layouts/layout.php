<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="build/js/app.js"></script>
    <link rel="shortcut icon" href="<?= asset('images/cit.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <title>DemoApp - Proyecto11</title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="/proyecto11/inicio">
            <img src="<?= asset('./images/cit.png') ?>" width="35px'" alt="cit" >
            Aplicaciones
        </a>
        <div class="collapse navbar-collapse" id="navbarToggler">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="margin: 0;">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/proyecto11/inicio"><i class="bi bi-house-fill me-2"></i>Inicio</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/proyecto11/login"><i class="bi bi-door-open me-2"></i>Login</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/proyecto11/registro"><i class="bi bi-person-add me-2"></i>Usuarios</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/proyecto11/aplicaciones"><i class="bi bi-app me-2"></i>Aplicaciones</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/proyecto11/permisos"><i class="bi bi-person-fill-gear me-2"></i>Permisos</a>
                </li>

                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-gear me-2"></i>Configuración
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" id="dropwdownRevision" style="margin: 0;">
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/proyecto11/aplicaciones/nueva"><i class="ms-lg-0 ms-2 bi bi-plus-circle me-2"></i>Nueva Aplicación</a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/proyecto11/configuracion"><i class="ms-lg-0 ms-2 bi bi-gear me-2"></i>Configuración</a>
                        </li>
                    </ul>
                </div> 
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
                        <li><h6 class="dropdown-header">Rol: <?= $_SESSION['rol'] ?? 'USER' ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="/proyecto11/perfil">
                                <i class="bi bi-person me-2"></i>Mi Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="/proyecto11/login/logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php else: ?>
            <div class="col-lg-1 d-grid mb-lg-0 mb-2">
                <a href="/proyecto11/login" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="progress fixed-bottom" style="height: 6px;">
    <div class="progress-bar progress-bar-animated bg-danger" id="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
</div>

<div class="container-fluid pt-5 mb-4" style="min-height: 85vh">
    <?php echo $contenido; ?>
</div>

<div class="container-fluid">
    <div class="row justify-content-center text-center">
        <div class="col-12">
            <p style="font-size:xx-small; font-weight: bold;">
                Comando de Informática y Tecnología, <?= date('Y') ?> &copy;
            </p>
        </div>
    </div>
</div>

<!-- Scripts adicionales para logout -->
<script>
// Función para cerrar sesión con confirmación
function cerrarSesion() {
    if (confirm('¿Está seguro que desea cerrar sesión?')) {
        window.location.href = '/proyecto11/login/logout';
    }
}

// Agregar event listeners a todos los enlaces de logout
document.addEventListener('DOMContentLoaded', function() {
    const logoutLinks = document.querySelectorAll('a[href="/proyecto11/login/logout"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            cerrarSesion();
        });
    });
});
</script>
</body>
</html>