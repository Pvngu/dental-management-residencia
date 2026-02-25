<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compañía No Encontrada - 800Dent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 0;
        }
        
        .error-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 2rem;
            text-align: center;
            max-width: 900px;
            width: 100%;
            margin: 10px;
            max-height: 95vh;
            overflow-y: auto;
        }
        
        .error-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error-card {
            animation: fadeInUp 0.8s ease-out;
        }
        
        .error-title {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .error-subtitle {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-outline-secondary {
            border: 2px solid #7f8c8d;
            color: #7f8c8d;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-left: 10px;
            font-size: 0.9rem;
        }
        
        .btn-outline-secondary:hover {
            background: #7f8c8d;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .btn-outline-primary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .logo {
            max-width: 150px;
            margin-bottom: 2rem;
        }
        
        .features {
            margin-top: 2rem;
            text-align: left;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
            padding: 0.8rem;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .feature-icon {
            color: #667eea;
            margin-right: 0.8rem;
            font-size: 1.3rem;
            min-width: 30px;
        }
        
        .feature-text {
            flex: 1;
        }
        
        .feature-title {
            font-weight: bold;
            margin-bottom: 0.2rem;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        .feature-description {
            color: #7f8c8d;
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .error-card {
                padding: 1.5rem 1rem;
                margin: 5px;
            }
            
            .error-title {
                font-size: 1.8rem;
            }
            
            .error-subtitle {
                font-size: 0.9rem;
            }
            
            .btn-primary, .btn-outline-secondary, .btn-outline-primary {
                display: inline-block;
                width: auto;
                margin: 0.3rem 0.2rem;
                padding: 8px 20px;
                font-size: 0.8rem;
            }
            
            .features {
                margin-top: 1.5rem;
            }
            
            .feature-item {
                padding: 0.6rem;
                margin-bottom: 0.6rem;
            }
            
            .feature-icon {
                font-size: 1.1rem;
                margin-right: 0.6rem;
            }
            
            .feature-title {
                font-size: 0.9rem;
            }
            
            .feature-description {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <!-- Logo de 800Dent -->
            <div class="mb-3">
                <i class="fas fa-tooth error-icon"></i>
            </div>
            
            <h1 class="error-title">¡Oops!</h1>
            <p class="error-subtitle">
                @if(isset($reason) && $reason === 'disabled')
                    @if(isset($companyName))
                        La clínica dental "<strong>{{ $companyName }}</strong>" existe pero actualmente no tiene habilitada su página de aterrizaje.
                    @else
                        La clínica dental que buscas existe pero actualmente no tiene habilitada su página de aterrizaje.
                    @endif
                @elseif(isset($companySlug))
                    La clínica dental "<strong>{{ $companySlug }}</strong>" no fue encontrada en nuestro sistema.
                @else
                    La clínica dental que buscas no fue encontrada en nuestro sistema.
                @endif
                <br>
                Pero no te preocupes, tenemos algo mejor para ti.
            </p>
            
            <div class="mb-3">
                <a href="https://800dent.com" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>
                    Visitar 800Dent
                </a>
                <button onclick="history.back()" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Regresar
                </button>
                <div class="mt-2">
                    <a href="https://800dent.com/buscar-clinicas" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>
                        Buscar Clínicas Dentales
                    </a>
                </div>
            </div>
            
            @if(isset($reason) && $reason === 'disabled')
            <div class="alert alert-info" style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 10px; padding: 1rem; margin: 1.5rem 0;">
                <h6 style="color: #1976d2; margin-bottom: 0.5rem;">
                    <i class="fas fa-info-circle me-2"></i>
                    ¿Eres el propietario de esta clínica?
                </h6>
                <p style="color: #1976d2; margin-bottom: 0; font-size: 0.9rem;">
                    Si eres el administrador de esta clínica, puedes habilitar la página de aterrizaje desde tu panel de administración en 800Dent.
                </p>
            </div>
            @endif

            <!-- Características de 800Dent en layout compacto -->
            <div class="row features">
                <div class="col-12">
                    <h4 class="text-center mb-3" style="color: #2c3e50; font-size: 1.2rem;">¿Por qué elegir 800Dent?</h4>
                </div>
                
                <div class="col-md-6">
                    <div class="feature-item">
                        <i class="fas fa-calendar-check feature-icon"></i>
                        <div class="feature-text">
                            <div class="feature-title">Sistema de Citas Inteligente</div>
                            <div class="feature-description">Agenda y gestiona citas de manera eficiente</div>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <i class="fas fa-user-md feature-icon"></i>
                        <div class="feature-text">
                            <div class="feature-title">Gestión de Pacientes</div>
                            <div class="feature-description">Historiales médicos completos y seguimiento personalizado</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="feature-item">
                        <i class="fas fa-chart-line feature-icon"></i>
                        <div class="feature-text">
                            <div class="feature-title">Reportes y Analytics</div>
                            <div class="feature-description">Obtén insights valiosos sobre tu clínica</div>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <i class="fas fa-mobile-alt feature-icon"></i>
                        <div class="feature-text">
                            <div class="feature-title">Acceso Móvil</div>
                            <div class="feature-description">Gestiona tu clínica desde cualquier dispositivo</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 text-center">
                <p style="color: #7f8c8d; font-size: 0.85rem;">
                    <i class="fas fa-envelope me-2"></i>
                    ¿Tienes preguntas? Contáctanos en 
                    <a href="mailto:soporte@800dent.com" style="color: #667eea;">soporte@800dent.com</a>
                </p>
            </div>
        </div>
    </div>    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
