<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema ETL Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .login-title {
            color: #2d3748;
            font-weight: 700;
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .login-subtitle {
            color: #718096;
            font-size: 14px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating > .form-control {
            background: rgba(247, 250, 252, 0.8);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            height: auto;
            transition: all 0.3s ease;
        }
        
        .form-floating > .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }
        
        .form-floating > label {
            color: #718096;
            font-weight: 500;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: rgba(254, 226, 226, 0.8);
            color: #c53030;
        }
        
        .alert-success {
            background: rgba(209, 250, 229, 0.8);
            color: #2f855a;
        }
        
        .form-check {
            margin: 20px 0;
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        
        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: #764ba2;
        }
        
        .footer-info {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(226, 232, 240, 0.5);
            color: #718096;
            font-size: 12px;
        }
        
        .input-group-text {
            background: transparent;
            border: none;
            color: #718096;
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-chart-line fa-2x text-white"></i>
                </div>
                <h1 class="login-title">Sistema ETL Dashboard</h1>
                <p class="login-subtitle">Ingresa tus credenciales para acceder</p>
            </div>

            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= session('error') ?>
                </div>
            <?php endif ?>

            <?php if (session()->has('message')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= session('message') ?>
                </div>
            <?php endif ?>

            <form action="/login" method="post">
                <?= csrf_field() ?>
                
                <div class="form-floating">
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="nombre@ejemplo.com"
                           value="<?= old('email', 'admin@example.com') ?>"
                           required>
                    <label for="email">
                        <i class="fas fa-envelope me-2"></i>
                        Correo Electrónico
                    </label>
                </div>

                <div class="form-floating">
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Contraseña"
                           value="secret"
                           required>
                    <label for="password">
                        <i class="fas fa-lock me-2"></i>
                        Contraseña
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           value="1" 
                           id="remember" 
                           name="remember">
                    <label class="form-check-label" for="remember">
                        Recordarme
                    </label>
                </div>

                <button class="btn btn-primary btn-login" type="submit">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Iniciar Sesión
                </button>
            </form>

            <div class="forgot-password">
                <a href="#" onclick="showForgotPassword()">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <div class="footer-info">
                <p>
                    <i class="fas fa-shield-alt me-1"></i>
                    Conexión segura • Sistema ETL v1.0
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showForgotPassword() {
            alert('Función de recuperación de contraseña no implementada en esta demo.\n\nUsuario demo:\nEmail: admin@example.com\nPassword: secret');
        }
        
        // Auto-focus en el primer campo
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
        
        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.login-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>