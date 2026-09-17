<!DOCTYPE html>
<html lang="es" data-color="dark-blue" data-navcolor="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="icon" href="<?php echo base_url(); ?>/public/img/icono-phuyu.ico">
    <title>phuyu System | Iniciar Sesión</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos revolucionarios -->
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap');

      :root {
        --primary: #6366f1;
        --primary-light: #38bdf8;
        --secondary: #f59e0b;
        --accent: #ec4899;
        --dark: #0f172a;
        --light: #f8fafc;
        --glass: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
      }

      body {
        min-height: 100vh;
        background: linear-gradient(-45deg, #0f172a, #1e293b, #334155, #475569);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
      }

      @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
      }

      /* Partículas de fondo */
      .particles {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
      }

      .particle {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        animation: float 6s infinite ease-in-out;
      }

      @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
      }

      .login-container {
        width: 100%;
        max-width: 440px;
        z-index: 10;
        position: relative;
        padding: 20px;
      }

      .login-card {
        background: var(--glass);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: 24px;
        border: 1px solid var(--glass-border);
        box-shadow: var(--shadow);
        overflow: hidden;
        position: relative;
        padding: 40px 30px;
        color: white;
        transform-style: preserve-3d;
        perspective: 1000px;
        transition: transform 0.5s ease;
      }

      .login-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: rotate(45deg);
        animation: shine 3s infinite;
      }

      @keyframes shine {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
      }

      .brand-section {
        text-align: center;
        margin-bottom: 30px;
        position: relative;
        z-index: 2;
      }

      .brand-logo {
        max-width: 240px;
        height: auto;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        margin-bottom: 15px;
      }

      .welcome-title {
        font-size: 2.2rem;
        font-weight: 800;
          background: #fff;
        /* background: linear-gradient(90deg, var(--primary-light), var(--accent)); */
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin-bottom: 8px;
        letter-spacing: 1px;
      }

      .welcome-subtitle {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 10px;
      }

      .form-group {
        margin-bottom: 24px;
        position: relative;
        z-index: 2;
      }

      .input-container {
        position: relative;
        display: flex;
        align-items: center;
      }

      .input-icon {
        position: absolute;
        left: 18px;
        color: var(--primary-light);
        font-size: 1.2rem;
        z-index: 3;
        transition: all 0.3s ease;
      }

      .form-control {
        height: 56px;
        width: 100%;
        padding: 0 20px 0 55px;
        border-radius: 14px;
        border: 2px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.2);
        color: white;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
      }

      .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
      }

      .form-control:focus {
        border-color: var(--primary-light);
        background: rgba(0, 0, 0, 0.3);
        box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.2);
        outline: none;
        transform: translateY(-2px);
      }

      .form-control:focus + .input-icon {
        color: var(--accent);
        transform: scale(1.1);
      }

      .password-toggle {
        position: absolute;
        right: 18px;
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 3;
      }

      .password-toggle:hover {
        color: var(--primary-light);
        transform: scale(1.1);
      }

      .actions-row {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        z-index: 2;
        position: relative;
      }

      .btn-reset {
        flex: 1;
        height: 56px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        color: white;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
      }

      .btn-reset:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
      }

      .btn-login {
        flex: 2;
        height: 56px;
        border-radius: 14px;
        background: linear-gradient(90deg, var(--primary), var(--primary-light));
        border: none;
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        position: relative;
        overflow: hidden;
      }

      .btn-login::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: 0.5s;
      }

      .btn-login:hover::before {
        left: 100%;
      }

      .btn-login:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.6);
      }

      .login-links {
        display: flex;
        justify-content: space-between;
        margin-top: 25px;
        font-size: 0.9rem;
        z-index: 2;
        position: relative;
      }

      .login-links a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        padding-bottom: 2px;
      }

      .login-links a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--accent);
        transition: width 0.3s ease;
      }

      .login-links a:hover {
        color: white;
      }

      .login-links a:hover::after {
        width: 100%;
      }

      /* Efectos de neón */
      .neon-text {
        text-shadow: 0 0 5px var(--primary-light), 0 0 10px var(--primary-light), 0 0 15px var(--primary-light);
      }

      /* Responsive */
      @media (max-width: 576px) {
        .login-container {
          padding: 15px;
        }
        
        .login-card {
          padding: 30px 20px;
        }
        
        .welcome-title {
          font-size: 1.8rem;
        }
        
        .actions-row {
          flex-direction: column;
        }
        
        .login-links {
          flex-direction: column;
          gap: 10px;
          text-align: center;
        }
      }

      /* Animación de entrada */
      @keyframes slideIn {
        from {
          opacity: 0;
          transform: translateY(30px) scale(0.95);
        }
        to {
          opacity: 1;
          transform: translateY(0) scale(1);
        }
      }

      .login-card {
        animation: slideIn 0.8s ease-out forwards;
      }





      /* Estilos para mensajes de alerta */
.alert-message {
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-weight: 500;
    display: none;
    animation: fadeIn 0.3s ease-out;
}

.alert-success {
    background: rgba(34, 197, 94, 0.15);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #22c55e;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

.alert-message i {
    margin-right: 8px;
}

/* Animación de shake para errores */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
    20%, 40%, 60%, 80% { transform: translateX(8px); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}





/* Estilos para mensajes de alerta */
.alert-message {
    padding: 14px 18px;
    border-radius: 12px;
    margin: 15px 0;
    font-weight: 500;
    font-size: 0.95rem;
    text-align: center;
    backdrop-filter: blur(10px);
    border: 1px solid;
    animation: fadeInUp 0.4s ease-out;
}

.alert-success {
    background: rgba(34, 197, 94, 0.15);
    border-color: rgba(34, 197, 94, 0.4);
    color: #22c55e;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.15);
    border-color: rgba(239, 68, 68, 0.4);
    color: #ef4444;
}

.alert-message i {
    margin-right: 8px;
    font-size: 1.1rem;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Animación de shake */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
    20%, 40%, 60%, 80% { transform: translateX(8px); }
}

/* Spinner para el botón */
.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}


/* Estilos para mensajes de alerta */
.alert-message {
    padding: 14px 18px;
    border-radius: 12px;
    margin: 15px 0;
    font-weight: 500;
    font-size: 0.95rem;
    text-align: center;
    backdrop-filter: blur(10px);
    border: 1px solid;
    animation: fadeInUp 0.4s ease-out;
}

.alert-success {
    background: rgba(34, 197, 94, 0.15);
    border-color: rgba(34, 197, 94, 0.4);
    color: #22c55e;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.15);
    border-color: rgba(239, 68, 68, 0.4);
    color: #ef4444;
}

.alert-message i {
    margin-right: 8px;
    font-size: 1.1rem;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Animación de shake */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
    20%, 40%, 60%, 80% { transform: translateX(8px); }
}

/* Spinner para el botón */
.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}





.welcome-title {
    font-size: 2.2rem;
    font-weight: 800;
    /* background: #fff; */ /* ❌ ESTO ESTÁ MAL */
    background: linear-gradient(90deg, var(--primary-light), var(--accent)); /* ✅ CORRECTO */
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent; /* ✅ IMPORTANTE */
    margin-bottom: 8px;
    letter-spacing: 1px;
}

/* Estilos para mensajes de alerta */
.alert-message {
    padding: 14px 18px;
    border-radius: 12px;
    margin: 15px 0;
    font-weight: 500;
    font-size: 0.95rem;
    text-align: center;
    backdrop-filter: blur(10px);
    border: 1px solid;
    animation: fadeInUp 0.4s ease-out;
    display: none; /* ✅ IMPORTANTE */
}

.alert-success {
    background: rgba(34, 197, 94, 0.15);
    border-color: rgba(34, 197, 94, 0.4);
    color: #22c55e;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.15);
    border-color: rgba(239, 68, 68, 0.4);
    color: #ef4444;
}

.alert-message i {
    margin-right: 8px;
    font-size: 1.1rem;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Animación de shake */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
    20%, 40%, 60%, 80% { transform: translateX(8px); }
}

/* Spinner para el botón */
.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}


    </style>
  </head>

  <body>
    <!-- Partículas de fondo -->
    <div class="particles" id="particles"></div>

    <div class="login-container">
      <div class="login-card">
        <div class="brand-section">
          <img src="<?php echo base_url();?>/public/img/logo_completo.png" alt="phuyu Soft" class="brand-logo" />
          <h1 class="welcome-title ">Bienvenido</h1>
          <p class="welcome-subtitle">Accede con tu usuario y clave</p>
        </div>



       <form id="form_login" onsubmit="return phuyu_login()" autocomplete="off">
    <!-- ⚠️ SOLO DEBE HABER UN ELEMENTO CON ID "mensaje" -->
    <div id="mensaje" class="alert-message" style="display: none;"></div>
    
    <div class="form-group">
        <div class="input-container">
            <i class="fas fa-user input-icon"></i>
            <input type="text" class="form-control" id="phuyu_usuario" placeholder="USUARIO" required autofocus>
        </div>
    </div>
    
    <div class="form-group">
        <div class="input-container">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" class="form-control" id="phuyu_clave" placeholder="CLAVE" required>
            <button type="button" class="password-toggle" id="btnTogglePass" aria-label="Mostrar/Ocultar contraseña">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
    
    <div class="actions-row">
        <button type="reset" class="btn-reset">Limpiar</button>
        <button type="submit" class="btn-login" id="iniciar_sesion">
            <i class="fas fa-arrow-right"></i>
            <span>INGRESAR</span>
        </button>
    </div>
    
    <div class="login-links">
        <a href="#">¿Olvidaste tu contraseña?</a>
        <a href="#">Soporte</a>
    </div>
</form>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <?php include("phuyu_js.php"); ?>
    <script> var url = "<?php echo base_url();?>"; </script>
    <script src="<?php echo base_url();?>phuyu/phuyu_login.js"></script>

    <script>
      // Toggle password con Font Awesome
      (function(){
        const input = document.getElementById('phuyu_clave');
        const btn = document.getElementById('btnTogglePass');
        if (input && btn) {
          btn.addEventListener('click', function(){
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
          });
        }
      })();

      // Crear partículas de fondo
      function createParticles() {
        const particlesContainer = document.getElementById('particles');
        const particleCount = 20;
        
        for (let i = 0; i < particleCount; i++) {
          const particle = document.createElement('div');
          particle.classList.add('particle');
          
          // Tamaño y posición aleatorios
          const size = Math.random() * 6 + 2;
          const posX = Math.random() * 100;
          const posY = Math.random() * 100;
          const delay = Math.random() * 5;
          const duration = Math.random() * 10 + 5;
          
          particle.style.width = `${size}px`;
          particle.style.height = `${size}px`;
          particle.style.left = `${posX}%`;
          particle.style.top = `${posY}%`;
          particle.style.animationDelay = `${delay}s`;
          particle.style.animationDuration = `${duration}s`;
          
          particlesContainer.appendChild(particle);
        }
      }
      
      // Inicializar partículas cuando el DOM esté listo
      document.addEventListener('DOMContentLoaded', createParticles);
    </script>
  </body>
</html>