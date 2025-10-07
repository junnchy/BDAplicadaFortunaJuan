<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error en la Aplicación
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h5>Se ha producido un error:</h5>
                        <p class="mb-0"><?= esc($message ?? 'Error desconocido') ?></p>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Posibles soluciones:</h6>
                        <ul>
                            <li>Verifica que la base de datos esté accesible</li>
                            <li>Revisa que los datos estén disponibles</li>
                            <li>Actualiza la página</li>
                            <li>Contacta al administrador si el problema persiste</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="/dashboard" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>
                            Volver al Dashboard
                        </a>
                        <button type="button" class="btn btn-secondary" onclick="history.back()">
                            <i class="fas fa-arrow-left me-2"></i>
                            Volver Atrás
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-2"></i>
                            Recargar Página
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>