<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Control de Ventas - Completo</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .nav-tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            flex-wrap: wrap;
        }

        .tab-button {
            flex: 1;
            min-width: 120px;
            padding: 15px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            color: #6c757d;
        }

        .tab-button.active {
            background: white;
            color: #495057;
            border-bottom: 3px solid #007bff;
        }

        .tab-button:hover {
            background: #e9ecef;
        }

        .tab-content {
            display: none;
            padding: 30px;
            min-height: 600px;
        }

        .tab-content.active {
            display: block;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
        }

        .metric-value {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .metric-label {
            font-size: 0.9em;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2c3e50;
            text-align: center;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .input-group input, .input-group select, .input-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .input-group input:focus, .input-group select:focus, .input-group textarea:focus {
            outline: none;
            border-color: #007bff;
        }

        .btn {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }

        .btn-export {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .data-table tr:hover {
            background-color: #f8f9fa;
        }

        .vendor-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            position: relative;
        }

        .vendor-name {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .vendor-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .stat-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            font-size: 0.8em;
            color: #6c757d;
            text-transform: uppercase;
        }

        .file-upload {
            border: 2px dashed #007bff;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 20px 0;
        }

        .file-upload:hover {
            background: #e9ecef;
            border-color: #0056b3;
        }

        .file-upload.dragover {
            background: #e3f2fd;
            border-color: #2196f3;
        }

        .activities-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .activity-item {
            padding: 15px;
            border-bottom: 1px solid #f8f9fa;
            transition: background-color 0.3s ease;
        }

        .activity-item:hover {
            background-color: #f8f9fa;
        }

        .activity-time {
            font-size: 0.9em;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .activity-type {
            font-weight: 600;
            color: #2c3e50;
        }

        .activity-client {
            color: #007bff;
            margin-left: 10px;
        }

        .progress-bar {
            background: #e9ecef;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }

        .phone-debug {
            background: #f0f8ff;
            border: 1px solid #4682b4;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 0.9em;
        }

        .phone-match {
            background: #d4edda;
            padding: 2px 5px;
            border-radius: 3px;
        }

        .phone-no-match {
            background: #f8d7da;
            padding: 2px 5px;
            border-radius: 3px;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-tabs {
                flex-direction: column;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Sistema de Control de Ventas</h1>
            <p>Monitoreo Completo del Equipo Comercial - Versión Completa</p>
        </div>

        <div class="nav-tabs">
            <button class="tab-button active" data-tab="dashboard">📈 Dashboard</button>
            <button class="tab-button" data-tab="registro">✏️ Registro</button>
            <button class="tab-button" data-tab="seguimiento">🔍 Seguimiento</button>
            <button class="tab-button" data-tab="vendedores">👥 Vendedores</button>
            <button class="tab-button" data-tab="reportes">📊 Reportes</button>
            <button class="tab-button" data-tab="carga">📁 Cargar Archivo</button>
            <button class="tab-button" data-tab="debug">🔧 Debug Teléfonos</button>
        </div>

        <!-- Dashboard -->
        <div id="dashboard" class="tab-content active">
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-value" id="total-interactions">0</div>
                    <div class="metric-label">Interacciones Totales</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value" id="new-leads">0</div>
                    <div class="metric-label">Nuevos Leads</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value" id="meetings-held">0</div>
                    <div class="metric-label">Reuniones Realizadas</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value" id="contracts-closed">0</div>
                    <div class="metric-label">Contratos Cerrados</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value" id="conversion-rate">0%</div>
                    <div class="metric-label">Tasa de Conversión</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value" id="total-revenue">S/ 0</div>
                    <div class="metric-label">Ingresos del Día</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value" id="monthly-revenue">S/ 0</div>
                    <div class="metric-label">Ingresos del Mes</div>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-title">🎯 Indicadores de Rendimiento</div>
                <div id="performance-indicators">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center;">
                            <h4 style="color: #495057; margin-bottom: 15px;">📊 Progreso Total</h4>
                            <div class="progress-bar" style="height: 25px; margin: 10px 0;">
                                <div class="progress-fill" id="total-progress" style="width: 0%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">0%</div>
                            </div>
                            <small style="color: #6c757d;">Basado en actividades vs meta diaria</small>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center;">
                            <h4 style="color: #495057; margin-bottom: 15px;">🎯 Eficiencia de Conversión</h4>
                            <div class="progress-bar" style="height: 25px; margin: 10px 0;">
                                <div class="progress-fill" id="conversion-progress" style="width: 0%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">0%</div>
                            </div>
                            <small style="color: #6c757d;">Interacciones → Seguimientos → Cierres</small>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center;">
                            <h4 style="color: #495057; margin-bottom: 15px;">💰 Meta Mensual de Ingresos</h4>
                            <div class="progress-bar" style="height: 25px; margin: 10px 0;">
                                <div class="progress-fill" id="revenue-progress" style="width: 0%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">0%</div>
                            </div>
                            <div style="font-size: 1.2em; font-weight: bold; color: #28a745; margin: 10px 0;" id="monthly-revenue-details">S/ 0 / S/ 20,000</div>
                            <small style="color: #6c757d;">Meta: S/ 20,000 mensuales por vendedor</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-title">🕐 Actividades Recientes</div>
                <div id="recent-activities" class="activities-list">
                    <div class="activity-item">
                        <div class="activity-time">Sin actividades registradas</div>
                        <div class="activity-type">Comienza registrando tu primera actividad</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registro -->
        <div id="registro" class="tab-content">
            <div class="chart-container">
                <div class="chart-title">✏️ Registrar Nueva Actividad</div>
                <form id="activity-form">
                    <div class="form-grid">
                        <div class="input-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" id="fecha" required>
                        </div>
                        <div class="input-group">
                            <label for="hora">Hora</label>
                            <input type="time" id="hora" required>
                        </div>
                        <div class="input-group">
                            <label for="vendedor">Vendedor</label>
                            <select id="vendedor" required>
                                <option value="">Seleccionar vendedor</option>
                                <option value="Rafael">Rafael</option>
                                <option value="Kattya Huarcaya">Kattya Huarcaya</option>
                                <option value="Sumiko Gomero">Sumiko Gomero</option>
                                <option value="Thalia Sobrado">Thalia Sobrado</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="tipo-actividad">Tipo de Actividad</label>
                            <select id="tipo-actividad" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="Interaccion con nuevo cliente (potencial)">Nuevo Cliente Potencial</option>
                                <option value="Seguimiento">Seguimiento</option>
                                <option value="Programar Reunión de Enfoque">Programar Reunión</option>
                                <option value="Cliente actual">Cliente Actual</option>
                                <option value="En proceso de cierre">Proceso de Cierre</option>
                                <option value="Reunión con producción">Reunión Producción</option>
                                <option value="Reunion con equipo de ventas">Reunión Equipo</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="cliente">Cliente</label>
                            <input type="text" id="cliente" placeholder="Nombre del cliente" required>
                        </div>
                        <div class="input-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" id="telefono" placeholder="999 999 999">
                        </div>
                        <div class="input-group">
                            <label for="universidad">Universidad</label>
                            <input type="text" id="universidad" placeholder="Universidad">
                        </div>
                        <div class="input-group">
                            <label for="origen">Origen</label>
                            <select id="origen">
                                <option value="">Seleccionar origen</option>
                                <option value="WhatsApp">WhatsApp</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Instagram">Instagram</option>
                                <option value="Llamada">Llamada</option>
                                <option value="Email">Email</option>
                                <option value="Referido">Referido</option>
                                <option value="Web">Página Web</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="etapa-actual">Etapa Actual</label>
                            <select id="etapa-actual" required>
                                <option value="">Seleccionar etapa</option>
                                <option value="Primer contacto">Primer Contacto</option>
                                <option value="Información enviada">Información Enviada</option>
                                <option value="Enfoque programado">Enfoque Programado</option>
                                <option value="reuniones">Reuniones</option>
                                <option value="Post Enfoque">Post Enfoque</option>
                                <option value="Material de valor">Material de Valor</option>
                                <option value="En negociación">En Negociación</option>
                                <option value="Ya Cerro">Ya Cerró</option>
                                <option value="En proceso con el servicio">En Proceso con Servicio</option>
                                <option value="Fidelización">Fidelización</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="monto">Monto en Soles (si aplica)</label>
                            <input type="number" id="monto" placeholder="0" min="0" step="0.01">
                        </div>
                        <div class="input-group">
                            <label for="prioridad">Prioridad</label>
                            <select id="prioridad">
                                <option value="Media">Media</option>
                                <option value="Alta">Alta</option>
                                <option value="Baja">Baja</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="acciones">Acciones Realizadas</label>
                        <textarea id="acciones" rows="3" placeholder="Describe las acciones realizadas..." required></textarea>
                    </div>
                    
                    <div class="input-group">
                        <label for="resultado">Resultado</label>
                        <textarea id="resultado" rows="2" placeholder="Resultado obtenido..."></textarea>
                    </div>
                    
                    <div class="input-group">
                        <label for="proxima-accion">Próxima Acción</label>
                        <textarea id="proxima-accion" rows="2" placeholder="¿Qué sigue?"></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">💾 Guardar Actividad</button>
                    <button type="button" class="btn" onclick="clearForm()">🗑️ Limpiar</button>
                </form>
            </div>
        </div>

        <!-- Seguimiento -->
        <div id="seguimiento" class="tab-content">
            <div class="chart-container">
                <div class="chart-title">📋 Lista de Seguimientos Pendientes</div>
                
                <!-- Nueva sección: Historial de Seguimientos Guardados -->
                <div style="background: #d1f2eb; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #48bb78;">
                    <h5 style="margin: 0 0 15px 0; color: #22543d;">📚 Historial de Seguimientos Guardados</h5>
                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1;">
                            <p style="margin: 0; font-size: 0.9em; color: #2d3748;">
                                <strong id="total-seguimientos-guardados">0</strong> seguimientos guardados | 
                                <strong id="ultimo-guardado">Ninguno</strong>
                            </p>
                        </div>
                        <button class="btn" onclick="verHistorialSeguimientos()" style="background: linear-gradient(135deg, #48bb78, #38a169);">
                            📜 Ver Historial Completo
                        </button>
                        <button class="btn btn-export" onclick="exportarHistorialSeguimientos()">
                            📥 Exportar Historial
                        </button>
                    </div>
                </div>
                
                <!-- Filtro por vendedor -->
                <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #2c3e50;">
                                👤 Filtrar por Vendedor:
                            </label>
                            <select id="followup-vendor-filter" onchange="filterFollowupsByVendor()" style="width: 100%; padding: 8px; border: 2px solid #dee2e6; border-radius: 8px; font-size: 14px;">
                                <option value="todos">📊 Todos los vendedores</option>
                                <option value="Rafael">Rafael</option>
                                <option value="Kattya Huarcaya">Kattya Huarcaya</option>
                                <option value="Sumiko Gomero">Sumiko Gomero</option>
                                <option value="Thalia Sobrado">Thalia Sobrado</option>
                            </select>
                        </div>
                        <div id="vendor-followup-stats" style="flex: 2; display: flex; gap: 15px; flex-wrap: wrap;">
                            <!-- Estadísticas del vendedor seleccionado -->
                        </div>
                    </div>
                </div>
                
                <!-- Verificador de Excel -->
                <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #2196f3;">
                    <h5 style="margin: 0 0 10px 0; color: #1976d2;">📊 Verificar Números en Seguimiento</h5>
                    <p style="margin: 0 0 15px 0; font-size: 0.9em; color: #0c5460;">
                        Carga un archivo Excel con los números telefónicos que deberían estar en seguimiento para verificar cuáles están siendo trabajados.
                    </p>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <input type="file" id="followup-check-file" accept=".xlsx,.xls,.csv" style="display: none;" onchange="handleFollowupCheckFile(event)">
                        <button class="btn" onclick="document.getElementById('followup-check-file').click()" style="background: linear-gradient(135deg, #2196f3, #1976d2);">
                            📤 Cargar Excel de Verificación
                        </button>
                        <div id="followup-check-status" style="flex: 1; font-size: 0.9em;"></div>
                    </div>
                    <div id="followup-verification-results" style="margin-top: 15px; display: none;">
                        <!-- Resultados de verificación -->
                    </div>
                </div>
                
                <!-- Botones de acción mejorados -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <button class="btn btn-success" onclick="generateFollowupReport()">📊 Actualizar Lista</button>
                    <button class="btn" onclick="guardarSeguimientoActual()" style="background: linear-gradient(135deg, #805ad5, #6b46c1);">
                        💾 Guardar Seguimiento del Día
                    </button>
                    <button class="btn btn-export" onclick="exportFollowupExcel()">📥 Descargar Excel</button>
                </div>
                
                <div id="followup-table-container">
                    <p style="text-align: center; color: #6c757d;">Cargando lista de seguimientos...</p>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-title">👥 Seguimientos por Vendedor</div>
                <div id="vendor-followup-summary">
                    <p style="text-align: center; color: #6c757d;">Analizando por vendedor...</p>
                </div>
            </div>
            
            <!-- Modal para ver historial (oculto por defecto) -->
            <div id="modal-historial" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
                <div style="background: white; margin: 50px auto; max-width: 90%; max-height: 80vh; overflow-y: auto; padding: 30px; border-radius: 15px;">
                    <h3 style="margin-bottom: 20px;">📚 Historial de Seguimientos Guardados</h3>
                    <div id="historial-content">
                        <!-- Contenido del historial -->
                    </div>
                    <button class="btn" onclick="document.getElementById('modal-historial').style.display='none'" style="margin-top: 20px;">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        <!-- Vendedores -->
        <div id="vendedores" class="tab-content">
            <div id="vendors-list">
                <!-- Se llena dinámicamente -->
            </div>
        </div>

        <!-- Reportes -->
        <div id="reportes" class="tab-content">
            <div class="chart-container">
                <div class="chart-title">📊 Ingresos Cerrados por Vendedor</div>
                <div id="revenue-charts" style="background: #f8f9fa; padding: 20px; border-radius: 10px; min-height: 300px;">
                    <p style="text-align: center; color: #6c757d;">Cargando gráficas de cierres por vendedor...</p>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-title">💾 Gestión de Datos y Respaldos</div>
                
                <!-- Información del sistema -->
                <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #2196f3;">
                    <h4 style="margin: 0 0 15px 0; color: #1976d2;">📊 Estado del Sistema</h4>
                    <div id="system-info" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <strong>Actividades:</strong> <span id="info-activities">0</span><br>
                            <strong>Último guardado:</strong> <span id="info-last-saved">Nunca</span>
                        </div>
                        <div>
                            <strong>Tamaño de datos:</strong> <span id="info-data-size">0 KB</span><br>
                            <strong>Respaldos:</strong> <span id="info-backups">0</span>
                        </div>
                        <div>
                            <strong>Sesiones:</strong> <span id="info-sessions">1</span><br>
                            <strong>Estado:</strong> <span style="color: #28a745;">✅ Funcionando</span>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones de respaldo -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div style="background: #d4edda; padding: 15px; border-radius: 8px;">
                        <h5 style="margin: 0 0 10px 0; color: #155724;">💾 Crear Respaldo</h5>
                        <p style="margin: 0 0 15px 0; font-size: 0.9em;">Exporta todos tus datos a un archivo JSON</p>
                        <button class="btn btn-success" onclick="exportBackup()">📥 Descargar Respaldo</button>
                    </div>
                    
                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px;">
                        <h5 style="margin: 0 0 10px 0; color: #856404;">📤 Restaurar Respaldo</h5>
                        <p style="margin: 0 0 15px 0; font-size: 0.9em;">Importa datos desde un archivo de respaldo</p>
                        <button class="btn btn-export" onclick="document.getElementById('backup-file').click()">📤 Cargar Respaldo</button>
                        <input type="file" id="backup-file" accept=".json" style="display: none;" onchange="importBackup(event)">
                    </div>
                    
                    <div style="background: #f8d7da; padding: 15px; border-radius: 8px;">
                        <h5 style="margin: 0 0 10px 0; color: #721c24;">🗑️ Limpiar Datos</h5>
                        <p style="margin: 0 0 15px 0; font-size: 0.9em;">Elimina todos los datos (irreversible)</p>
                        <button class="btn btn-danger" onclick="clearAllDataForced()">🗑️ Limpiar Todo</button>
                    </div>
                </div>

                <div class="chart-container">
                    <div class="chart-title">📊 Exportar Reportes</div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <button class="btn btn-export" onclick="exportDailyReport()">📋 Reporte Diario</button>
                        <button class="btn btn-export" onclick="exportWeeklyReport()">📅 Reporte Semanal</button>
                        <button class="btn btn-export" onclick="exportMonthlyReport()">📆 Reporte Mensual</button>
                        <button class="btn btn-export" onclick="exportVendorReport()">👥 Por Vendedor</button>
                        <button class="btn btn-export" onclick="generateExcelTemplate()">📊 Excel Completo</button>
                    </div>
                </div>

                <!-- Nueva sección: Historial de Reportes Subidos -->
                <div class="chart-container" style="margin-top: 20px;">
                    <div class="chart-title">📤 Historial de Reportes Generados</div>
                    <div style="background: #f0f8ff; padding: 20px; border-radius: 10px; border-left: 4px solid #4169e1;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h5 style="margin: 0; color: #1e3a8a;">📚 Reportes Generados y Exportados</h5>
                            <button class="btn" onclick="verHistorialReportes()" style="background: linear-gradient(135deg, #4169e1, #1e3a8a);">
                                Ver Historial Completo
                            </button>
                        </div>
                        <div id="reportes-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                            <div style="text-align: center; padding: 10px; background: white; border-radius: 8px;">
                                <div style="font-size: 1.5em; font-weight: bold; color: #4169e1;" id="total-reportes">0</div>
                                <div style="font-size: 0.8em; color: #6c757d;">Total Reportes</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: white; border-radius: 8px;">
                                <div style="font-size: 1.5em; font-weight: bold; color: #28a745;" id="reportes-mes">0</div>
                                <div style="font-size: 0.8em; color: #6c757d;">Este Mes</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: white; border-radius: 8px;">
                                <div style="font-size: 1.5em; font-weight: bold; color: #ffc107;" id="reportes-semana">0</div>
                                <div style="font-size: 0.8em; color: #6c757d;">Esta Semana</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: white; border-radius: 8px;">
                                <div style="font-size: 0.9em; color: #495057;" id="ultimo-reporte">Ninguno</div>
                                <div style="font-size: 0.8em; color: #6c757d;">Último Reporte</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal para ver historial de reportes (oculto por defecto) -->
                <div id="modal-reportes" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
                    <div style="background: white; margin: 50px auto; max-width: 90%; max-height: 80vh; overflow-y: auto; padding: 30px; border-radius: 15px;">
                        <h3 style="margin-bottom: 20px;">📤 Historial de Reportes Generados</h3>
                        <div id="reportes-historial-content">
                            <!-- Contenido del historial de reportes -->
                        </div>
                        <button class="btn" onclick="document.getElementById('modal-reportes').style.display='none'" style="margin-top: 20px;">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cargar Archivo -->
        <div id="carga" class="tab-content">
            <div class="chart-container">
                <div class="chart-title">📁 Cargar Archivo Excel</div>
                
                <!-- Selector de Vendedor -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h4>👤 Seleccionar Vendedor</h4>
                    <p>Elige el vendedor al que se asignarán automáticamente las actividades del archivo Excel:</p>
                    <div class="input-group" style="margin-bottom: 0;">
                        <label for="selected-vendor">Vendedor:</label>
                        <select id="selected-vendor" style="max-width: 300px;">
                            <option value="">Detectar automáticamente</option>
                            <option value="Rafael">Rafael</option>
                            <option value="Kattya Huarcaya">Kattya Huarcaya</option>
                            <option value="Sumiko Gomero">Sumiko Gomero</option>
                            <option value="Thalia Sobrado">Thalia Sobrado</option>
                        </select>
                    </div>
                    <small style="color: #6c757d;">Si no seleccionas un vendedor, el sistema intentará detectarlo automáticamente desde el archivo.</small>
                </div>
                
                <div class="file-upload" id="file-upload-zone">
                    <input type="file" id="excel-file-input" accept=".xlsx,.xls" style="display: none;">
                    <div>
                        <h3>📊 Arrastra tu archivo Excel aquí</h3>
                        <p>o haz clic para seleccionar</p>
                        <small>Formatos soportados: .xlsx, .xls</small>
                        <br><br>
                        <button class="btn" onclick="document.getElementById('excel-file-input').click()">
                            📁 Seleccionar Archivo
                        </button>
                    </div>
                </div>

                <div id="file-status" class="hidden">
                    <div class="chart-container" style="background: #d4edda; border-left: 4px solid #28a745;">
                        <h4>✅ Archivo cargado exitosamente</h4>
                        <p id="file-name"></p>
                        <p id="assigned-vendor" style="margin-top: 10px; font-weight: bold;"></p>
                        <button class="btn btn-success" onclick="processFile()">🔍 Analizar Datos</button>
                    </div>
                </div>

                <div id="analysis-results" class="hidden">
                    <div class="chart-container">
                        <div class="chart-title">📊 Resultados del Análisis</div>
                        <div id="analysis-content">
                            <!-- Los resultados se mostrarán aquí -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Debug Teléfonos (Nueva Pestaña) -->
        <div id="debug" class="tab-content">
            <div class="chart-container">
                <div class="chart-title">🔧 Debug y Diagnóstico de Teléfonos</div>
                
                <div style="background: #f0f8ff; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h4>📱 Herramienta de Diagnóstico de Números</h4>
                    <p>Esta herramienta te ayuda a entender cómo el sistema procesa y normaliza los números telefónicos.</p>
                    
                    <div class="input-group">
                        <label for="test-phone">Probar número:</label>
                        <input type="text" id="test-phone" placeholder="Ej: 999888777, +51999888777, 051999888777">
                        <button class="btn" onclick="testPhoneNormalization()">🔍 Analizar</button>
                    </div>
                    
                    <div id="phone-test-result" style="margin-top: 20px;"></div>
                </div>
                
                <div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h4>📊 Estadísticas del Sistema</h4>
                    <div id="system-phone-stats"></div>
                </div>
                
                <div style="background: #e7f3ff; padding: 20px; border-radius: 10px;">
                    <h4>📋 Listado de Números en el Sistema</h4>
                    <button class="btn" onclick="showAllPhones()">Ver Todos los Números</button>
                    <div id="all-phones-list" style="margin-top: 20px; max-height: 400px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let salesData = {
            activities: [],
            vendors: ['Rafael', 'Kattya Huarcaya', 'Sumiko Gomero', 'Thalia Sobrado'],
            settings: { 
                metaInteracciones: 8, 
                metaLeads: 3, 
                metaReuniones: 2, 
                metaContratos: 1,
                metaMensualSoles: 20000
            },
            seguimientosGuardados: [], // Nuevo: Historial de seguimientos guardados
            reportesSubidos: [] // Nuevo: Historial de reportes subidos
        };

        let uploadedFile = null;
        let fileData = null;
        let currentVendorFilter = 'todos';
        let phoneNumbersToCheck = [];
        let verificationResults = {};

        // Inicialización del sistema
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Inicializando sistema completo...');
            initializeSystem();
        });
        
        function initializeSystem() {
            console.log('✅ Sistema iniciando...');
            
            setupTabs();
            setupFileUpload();
            loadData();
            updateDashboard();
            setCurrentDateTime();
            setupForm();
            updateSystemInfo();
            updateSystemPhoneStats();
            
            console.log('✅ Sistema inicializado correctamente');
        }

        // FUNCIÓN MEJORADA: Normalización de teléfonos
        function normalizePhone(phone) {
            if (!phone) return '';
            
            let normalized = phone.toString().trim();
            normalized = normalized.replace(/\D/g, '');
            
            console.log(`📱 Normalizando: "${phone}" -> "${normalized}"`);
            
            if (normalized.length === 0) return '';
            
            // Manejo de código de país 51 (Perú)
            if (normalized.startsWith('51')) {
                if (normalized.length === 11 && normalized[2] === '9') {
                    normalized = normalized.substring(2);
                } else if (normalized.length === 9 || normalized.length === 10) {
                    normalized = normalized.substring(2);
                }
            }
            
            if (normalized.startsWith('0')) {
                normalized = normalized.substring(1);
            }
            
            if (normalized.length > 9) {
                const last9 = normalized.slice(-9);
                if (last9.startsWith('9')) {
                    normalized = last9;
                } else {
                    normalized = normalized.slice(-8);
                }
            }
            
            console.log(`✅ Resultado normalizado: "${normalized}"`);
            return normalized;
        }

        // FUNCIÓN MEJORADA: Comparación de teléfonos
        function phonesMatch(phone1, phone2) {
            const norm1 = normalizePhone(phone1);
            const norm2 = normalizePhone(phone2);
            
            if (norm1 === norm2) return true;
            if (!norm1 || !norm2) return false;
            
            const minLen = Math.min(norm1.length, norm2.length);
            const maxLen = Math.max(norm1.length, norm2.length);
            
            if (maxLen - minLen > 2) return false;
            
            if (minLen >= 7) {
                const end1 = norm1.slice(-minLen);
                const end2 = norm2.slice(-minLen);
                if (end1 === end2) return true;
            }
            
            if (norm1.includes(norm2) || norm2.includes(norm1)) return true;
            
            return false;
        }

        // FUNCIÓN MEJORADA: Extracción de teléfonos del Excel
        function extractPhonesFromExcel(data) {
            const phones = new Set();
            
            const patterns = [
                /\b9\d{8}\b/g,
                /\b[2-8]\d{6,7}\b/g,
                /\+?51\s*9\d{8}\b/g,
                /\+?51\s*[1-8]\d{7,8}\b/g,
                /\b0[1-8]\d{7,8}\b/g
            ];
            
            data.forEach(row => {
                if (!Array.isArray(row)) return;
                
                row.forEach(cell => {
                    if (cell === null || cell === undefined) return;
                    
                    const cellStr = cell.toString();
                    
                    patterns.forEach(pattern => {
                        const matches = cellStr.match(pattern);
                        if (matches) {
                            matches.forEach(match => {
                                const normalized = normalizePhone(match);
                                if (normalized && normalized.length >= 6) {
                                    phones.add(normalized);
                                }
                            });
                        }
                    });
                    
                    if (/^\+?\d[\d\s\-\(\)]+$/.test(cellStr)) {
                        const normalized = normalizePhone(cellStr);
                        if (normalized && normalized.length >= 6) {
                            phones.add(normalized);
                        }
                    }
                });
            });
            
            return Array.from(phones);
        }
        
        function setupTabs() {
            const tabButtons = document.querySelectorAll('.tab-button');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    showTab(tabName);
                });
            });
        }

        function showTab(tabName) {
            console.log('Cambiando a pestaña:', tabName);
            
            const allTabs = document.querySelectorAll('.tab-content');
            allTabs.forEach(tab => {
                tab.classList.remove('active');
            });
            
            const allButtons = document.querySelectorAll('.tab-button');
            allButtons.forEach(button => {
                button.classList.remove('active');
            });
            
            const targetTab = document.getElementById(tabName);
            if (targetTab) {
                targetTab.classList.add('active');
            }
            
            const targetButton = document.querySelector(`[data-tab="${tabName}"]`);
            if (targetButton) {
                targetButton.classList.add('active');
            }
            
            if (tabName === 'vendedores') {
                updateVendorsList();
            } else if (tabName === 'reportes') {
                updateSystemInfo();
                updateRevenueCharts();
                actualizarEstadisticasReportes();
            } else if (tabName === 'dashboard') {
                updateDashboard();
                updatePerformanceIndicators();
            } else if (tabName === 'seguimiento') {
                generateFollowupReport();
                updateVendorFollowupSummary();
                actualizarEstadisticasHistorial();
            } else if (tabName === 'debug') {
                updateSystemPhoneStats();
            }
        }

        function setupForm() {
            const form = document.getElementById('activity-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = {
                        fecha: document.getElementById('fecha').value,
                        hora: document.getElementById('hora').value,
                        vendedor: document.getElementById('vendedor').value,
                        tipoActividad: document.getElementById('tipo-actividad').value,
                        cliente: document.getElementById('cliente').value,
                        telefono: document.getElementById('telefono').value,
                        universidad: document.getElementById('universidad').value,
                        origen: document.getElementById('origen').value,
                        etapaActual: document.getElementById('etapa-actual').value,
                        monto: parseFloat(document.getElementById('monto').value) || 0,
                        prioridad: document.getElementById('prioridad').value,
                        acciones: document.getElementById('acciones').value,
                        resultado: document.getElementById('resultado').value,
                        proximaAccion: document.getElementById('proxima-accion').value,
                        timestamp: new Date().toISOString()
                    };
                    
                    salesData.activities.push(formData);
                    saveData();
                    updateDashboard();
                    
                    alert('✅ Actividad registrada exitosamente!');
                    clearForm();
                });
            }
        }

        function clearForm() {
            document.getElementById('activity-form').reset();
            setCurrentDateTime();
        }

        function setCurrentDateTime() {
            const now = new Date();
            const dateField = document.getElementById('fecha');
            const timeField = document.getElementById('hora');
            
            if (dateField) dateField.value = now.toISOString().split('T')[0];
            if (timeField) timeField.value = now.toTimeString().slice(0, 5);
        }

        function loadData() {
            try {
                const saved = localStorage.getItem('salesSystemData');
                if (saved) {
                    const parsedData = JSON.parse(saved);
                    salesData = {
                        ...salesData,
                        ...parsedData
                    };
                    console.log(`✅ Datos cargados: ${salesData.activities.length} actividades`);
                }
            } catch (e) {
                console.error('Error cargando datos:', e);
            }
        }

        function saveData() {
            try {
                localStorage.setItem('salesSystemData', JSON.stringify(salesData));
                console.log('💾 Datos guardados');
            } catch (e) {
                console.error('Error guardando datos:', e);
            }
        }

        function updateDashboard() {
            const activities = salesData.activities;
            const today = new Date().toISOString().split('T')[0];
            const todayActivities = activities.filter(a => a.fecha === today);
            
            document.getElementById('total-interactions').textContent = todayActivities.length;
            document.getElementById('new-leads').textContent = 
                todayActivities.filter(a => a.tipoActividad && a.tipoActividad.includes('nuevo cliente')).length;
            document.getElementById('meetings-held').textContent = 
                todayActivities.filter(a => a.tipoActividad && a.tipoActividad.includes('Reunión')).length;
            document.getElementById('contracts-closed').textContent = 
                todayActivities.filter(a => a.etapaActual && a.etapaActual.includes('Cerr')).length;
            
            const conversionRate = todayActivities.length > 0 ? 
                Math.round((todayActivities.filter(a => a.etapaActual && a.etapaActual.includes('Cerr')).length / todayActivities.length) * 100) : 0;
            document.getElementById('conversion-rate').textContent = conversionRate + '%';
            
            const dailyRevenue = todayActivities.reduce((sum, a) => sum + (a.monto || 0), 0);
            document.getElementById('total-revenue').textContent = 'S/ ' + dailyRevenue.toLocaleString();
            
            const currentMonth = new Date().getMonth();
            const monthlyActivities = activities.filter(a => {
                const activityDate = new Date(a.fecha);
                return activityDate.getMonth() === currentMonth;
            });
            const monthlyRevenue = monthlyActivities.reduce((sum, a) => sum + (a.monto || 0), 0);
            document.getElementById('monthly-revenue').textContent = 'S/ ' + monthlyRevenue.toLocaleString();
            
            updateRecentActivities();
            updatePerformanceIndicators();
        }

        function updatePerformanceIndicators() {
            const activities = salesData.activities;
            const today = new Date().toISOString().split('T')[0];
            const todayActivities = activities.filter(a => a.fecha === today);
            
            // Progreso total del día
            const metaDiaria = salesData.settings.metaInteracciones || 8;
            const progresoTotal = Math.min(Math.round((todayActivities.length / metaDiaria) * 100), 100);
            const progressBar = document.getElementById('total-progress');
            if (progressBar) {
                progressBar.style.width = progresoTotal + '%';
                progressBar.textContent = progresoTotal + '%';
            }
            
            // Eficiencia de conversión
            const leads = todayActivities.filter(a => a.tipoActividad && a.tipoActividad.includes('nuevo cliente')).length;
            const cierres = todayActivities.filter(a => a.etapaActual && a.etapaActual.includes('Cerr')).length;
            const eficiencia = leads > 0 ? Math.round((cierres / leads) * 100) : 0;
            const conversionBar = document.getElementById('conversion-progress');
            if (conversionBar) {
                conversionBar.style.width = eficiencia + '%';
                conversionBar.textContent = eficiencia + '%';
            }
            
            // Meta mensual de ingresos
            const currentMonth = new Date().getMonth();
            const monthlyActivities = activities.filter(a => {
                const activityDate = new Date(a.fecha);
                return activityDate.getMonth() === currentMonth;
            });
            const monthlyRevenue = monthlyActivities.reduce((sum, a) => sum + (a.monto || 0), 0);
            const metaMensual = salesData.settings.metaMensualSoles || 20000;
            const progresoRevenue = Math.min(Math.round((monthlyRevenue / metaMensual) * 100), 100);
            
            const revenueBar = document.getElementById('revenue-progress');
            if (revenueBar) {
                revenueBar.style.width = progresoRevenue + '%';
                revenueBar.textContent = progresoRevenue + '%';
            }
            
            const revenueDetails = document.getElementById('monthly-revenue-details');
            if (revenueDetails) {
                revenueDetails.textContent = `S/ ${monthlyRevenue.toLocaleString()} / S/ ${metaMensual.toLocaleString()}`;
            }
        }

        function updateRecentActivities() {
            const container = document.getElementById('recent-activities');
            if (!container) return;
            
            const recent = salesData.activities.slice(-10).reverse();
            
            if (recent.length === 0) {
                container.innerHTML = `
                    <div class="activity-item">
                        <div class="activity-time">Sin actividades registradas</div>
                        <div class="activity-type">Comienza registrando tu primera actividad</div>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = recent.map(activity => `
                <div class="activity-item">
                    <div class="activity-time">${activity.fecha} - ${activity.hora}</div>
                    <div class="activity-type">${activity.tipoActividad}
                        <span class="activity-client">${activity.cliente}</span>
                    </div>
                </div>
            `).join('');
        }

        function updateVendorsList() {
            const container = document.getElementById('vendors-list');
            if (!container) return;
            
            const vendorStats = {};
            salesData.vendors.forEach(vendor => {
                vendorStats[vendor] = {
                    total: 0,
                    leads: 0,
                    meetings: 0,
                    revenue: 0
                };
            });
            
            salesData.activities.forEach(activity => {
                if (vendorStats[activity.vendedor]) {
                    vendorStats[activity.vendedor].total++;
                    if (activity.tipoActividad && activity.tipoActividad.includes('nuevo cliente')) {
                        vendorStats[activity.vendedor].leads++;
                    }
                    if (activity.tipoActividad && activity.tipoActividad.includes('Reunión')) {
                        vendorStats[activity.vendedor].meetings++;
                    }
                    vendorStats[activity.vendedor].revenue += activity.monto || 0;
                }
            });
            
            container.innerHTML = Object.entries(vendorStats).map(([vendor, stats]) => `
                <div class="vendor-card">
                    <div class="vendor-name">👤 ${vendor}</div>
                    <div class="vendor-stats">
                        <div class="stat-item">
                            <div class="stat-value">${stats.total}</div>
                            <div class="stat-label">Actividades</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${stats.leads}</div>
                            <div class="stat-label">Leads</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${stats.meetings}</div>
                            <div class="stat-label">Reuniones</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">S/ ${stats.revenue.toLocaleString()}</div>
                            <div class="stat-label">Ingresos</div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function updateSystemInfo() {
            try {
                const data = localStorage.getItem('salesSystemData');
                const dataSize = data ? new Blob([data]).size : 0;
                
                document.getElementById('info-activities').textContent = salesData.activities.length;
                document.getElementById('info-last-saved').textContent = new Date().toLocaleString();
                document.getElementById('info-data-size').textContent = (dataSize / 1024).toFixed(2) + ' KB';
                document.getElementById('info-backups').textContent = '1';
                document.getElementById('info-sessions').textContent = '1';
            } catch (e) {
                console.error('Error actualizando información del sistema:', e);
            }
        }

        function updateRevenueCharts() {
            const container = document.getElementById('revenue-charts');
            if (!container) return;
            
            const vendorRevenue = {};
            salesData.vendors.forEach(vendor => {
                vendorRevenue[vendor] = 0;
            });
            
            salesData.activities.forEach(activity => {
                if (vendorRevenue.hasOwnProperty(activity.vendedor)) {
                    vendorRevenue[activity.vendedor] += activity.monto || 0;
                }
            });
            
            let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">';
            
            Object.entries(vendorRevenue).forEach(([vendor, revenue]) => {
                const percentage = Math.round((revenue / salesData.settings.metaMensualSoles) * 100);
                
                html += `
                    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <h5 style="margin: 0 0 15px 0; color: #2c3e50;">👤 ${vendor}</h5>
                        <div style="font-size: 1.5em; font-weight: bold; color: #28a745; margin-bottom: 10px;">
                            S/ ${revenue.toLocaleString()}
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${Math.min(percentage, 100)}%;">
                                ${percentage}%
                            </div>
                        </div>
                        <small style="color: #6c757d;">Meta: S/ ${salesData.settings.metaMensualSoles.toLocaleString()}</small>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }

        function updateVendorFollowupSummary() {
            const container = document.getElementById('vendor-followup-summary');
            if (!container) return;
            
            const vendorSummary = {};
            salesData.vendors.forEach(vendor => {
                vendorSummary[vendor] = {
                    total: 0,
                    pendientes: 0,
                    completados: 0
                };
            });
            
            salesData.activities.forEach(activity => {
                if (vendorSummary[activity.vendedor]) {
                    vendorSummary[activity.vendedor].total++;
                    
                    const daysSince = Math.floor((new Date() - new Date(activity.fecha)) / (1000 * 60 * 60 * 24));
                    if (daysSince <= 7 && activity.tipoActividad === 'Seguimiento') {
                        vendorSummary[activity.vendedor].pendientes++;
                    }
                    
                    if (activity.etapaActual && activity.etapaActual.includes('Cerr')) {
                        vendorSummary[activity.vendedor].completados++;
                    }
                }
            });
            
            let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">';
            
            Object.entries(vendorSummary).forEach(([vendor, stats]) => {
                html += `
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                        <h5 style="margin: 0 0 10px 0; color: #2c3e50;">${vendor}</h5>
                        <div style="display: flex; justify-content: space-around;">
                            <div>
                                <div style="font-size: 1.2em; font-weight: bold; color: #007bff;">${stats.pendientes}</div>
                                <small style="color: #6c757d;">Pendientes</small>
                            </div>
                            <div>
                                <div style="font-size: 1.2em; font-weight: bold; color: #28a745;">${stats.completados}</div>
                                <small style="color: #6c757d;">Completados</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }

        function filterFollowupsByVendor() {
            currentVendorFilter = document.getElementById('followup-vendor-filter').value;
            generateFollowupReport();
        }

        function generateFollowupReport() {
            const container = document.getElementById('followup-table-container');
            
            let activities = salesData.activities.filter(a => {
                if (currentVendorFilter !== 'todos' && a.vendedor !== currentVendorFilter) {
                    return false;
                }
                const daysSince = Math.floor((new Date() - new Date(a.fecha)) / (1000 * 60 * 60 * 24));
                return daysSince <= 7;
            });
            
            if (activities.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #28a745;">✅ No hay seguimientos pendientes</p>';
                return;
            }
            
            let html = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Vendedor</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Etapa</th>
                            <th>Días</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            activities.forEach(activity => {
                const daysSince = Math.floor((new Date() - new Date(activity.fecha)) / (1000 * 60 * 60 * 24));
                html += `
                    <tr>
                        <td>${activity.fecha}</td>
                        <td>${activity.vendedor}</td>
                        <td>${activity.cliente}</td>
                        <td>${activity.telefono || 'N/A'}</td>
                        <td>${activity.etapaActual || 'N/A'}</td>
                        <td>${daysSince} días</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function handleFollowupCheckFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const statusDiv = document.getElementById('followup-check-status');
            statusDiv.innerHTML = '⏳ Procesando archivo...';
            statusDiv.style.color = '#ffc107';
            
            console.log('📁 Archivo de verificación seleccionado:', file.name);
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {type: 'array'});
                    
                    console.log('📊 Hojas encontradas:', workbook.SheetNames);
                    
                    let allData = [];
                    workbook.SheetNames.forEach(sheetName => {
                        const worksheet = workbook.Sheets[sheetName];
                        const jsonData = XLSX.utils.sheet_to_json(worksheet, {header: 1});
                        allData = allData.concat(jsonData);
                    });
                    
                    phoneNumbersToCheck = extractPhonesFromExcel(allData);
                    
                    console.log(`✅ ${phoneNumbersToCheck.length} números únicos encontrados`);
                    
                    if (phoneNumbersToCheck.length === 0) {
                        statusDiv.innerHTML = '⚠️ No se encontraron números telefónicos';
                        statusDiv.style.color = '#dc3545';
                        alert('⚠️ No se encontraron números telefónicos válidos en el archivo.');
                        return;
                    }
                    
                    statusDiv.innerHTML = `✅ ${phoneNumbersToCheck.length} números encontrados - Verificando...`;
                    statusDiv.style.color = '#28a745';
                    
                    console.log('📱 Primeros 10 números:', phoneNumbersToCheck.slice(0, 10));
                    
                    setTimeout(() => {
                        verifyPhoneNumbers();
                    }, 100);
                    
                } catch (error) {
                    console.error('❌ Error procesando archivo:', error);
                    statusDiv.innerHTML = '❌ Error al procesar el archivo';
                    statusDiv.style.color = '#dc3545';
                }
            };
            
            reader.readAsArrayBuffer(file);
        }
        
        function verifyPhoneNumbers() {
            console.log('🔍 Iniciando verificación de SEGUIMIENTOS REALES...');
            
            if (!phoneNumbersToCheck || phoneNumbersToCheck.length === 0) {
                alert('⚠️ No hay números para verificar');
                return;
            }
            
            const systemPhoneIndex = {};
            
            salesData.activities.forEach((activity, idx) => {
                if (activity.telefono) {
                    const normalized = normalizePhone(activity.telefono);
                    if (normalized) {
                        if (!systemPhoneIndex[normalized]) {
                            systemPhoneIndex[normalized] = [];
                        }
                        systemPhoneIndex[normalized].push({
                            activity: activity,
                            index: idx
                        });
                    }
                }
            });
            
            console.log('📊 Índice del sistema creado:', Object.keys(systemPhoneIndex).length, 'números únicos');
            
            verificationResults = {
                conSeguimientoReal: [],      // TIENEN seguimiento real (más de 1 interacción)
                sinSeguimientoAun: [],       // Solo tienen 1 interacción (primer contacto)
                noEncontrados: [],           // No están en el sistema
                totalEnSistema: 0,
                totalConSeguimientoReal: 0,  // Los que SÍ tienen seguimiento
                totalSinSeguimientoReal: 0,  // Los que NO tienen seguimiento (incluye no encontrados)
                numerosProcesados: phoneNumbersToCheck.length
            };
            
            phoneNumbersToCheck.forEach(phoneToCheck => {
                console.log(`🔍 Verificando: ${phoneToCheck}`);
                
                let found = false;
                let phoneData = null;
                
                if (systemPhoneIndex[phoneToCheck]) {
                    found = true;
                    phoneData = processPhoneDataSimplified(systemPhoneIndex[phoneToCheck], phoneToCheck);
                } else {
                    for (let sysPhone in systemPhoneIndex) {
                        if (phonesMatch(phoneToCheck, sysPhone)) {
                            found = true;
                            phoneData = processPhoneDataSimplified(systemPhoneIndex[sysPhone], phoneToCheck);
                            console.log(`✅ Coincidencia flexible: ${phoneToCheck} ≈ ${sysPhone}`);
                            break;
                        }
                    }
                }
                
                if (found && phoneData) {
                    verificationResults.totalEnSistema++;
                    categorizePhoneSimplified(phoneData);
                } else {
                    console.log(`❌ No encontrado: ${phoneToCheck}`);
                    verificationResults.noEncontrados.push(phoneToCheck);
                    verificationResults.totalSinSeguimientoReal++;
                }
            });
            
            console.log('📊 Verificación completada:', verificationResults);
            displayVerificationResultsSimplified();
        }
        
        function processPhoneDataSimplified(entries, originalPhone) {
            const vendors = new Set();
            let totalInteracciones = 0;
            let tieneMultiplesInteracciones = false;
            let tieneSeguimientoExplicito = false;
            let lastActivity = null;
            let firstActivity = null;
            let cliente = '';
            let etapaActual = '';
            let todasLasActividades = [];
            
            entries.forEach(entry => {
                const activity = entry.activity;
                vendors.add(activity.vendedor);
                totalInteracciones++;
                todasLasActividades.push(activity);
                
                // Verificar si tiene seguimiento explícito
                if (activity.tipoActividad && 
                    (activity.tipoActividad.toLowerCase().includes('seguimiento') ||
                     activity.tipoActividad.toLowerCase().includes('reunión') ||
                     activity.tipoActividad.toLowerCase().includes('cierre'))) {
                    tieneSeguimientoExplicito = true;
                }
                
                const activityDate = new Date(activity.fecha);
                if (!lastActivity || activityDate > new Date(lastActivity.fecha)) {
                    lastActivity = activity;
                    cliente = activity.cliente;
                    etapaActual = activity.etapaActual;
                }
                
                if (!firstActivity || activityDate < new Date(firstActivity.fecha)) {
                    firstActivity = activity;
                }
            });
            
            // CLAVE: Solo tiene seguimiento real si hay más de 1 interacción
            tieneMultiplesInteracciones = totalInteracciones > 1;
            
            const daysSince = lastActivity ? 
                Math.floor((new Date() - new Date(lastActivity.fecha)) / (1000 * 60 * 60 * 24)) : 0;
            
            // Determinar si realmente tiene seguimiento
            const tieneSeguimientoReal = tieneMultiplesInteracciones || tieneSeguimientoExplicito;
            
            return {
                phone: originalPhone,
                client: cliente || 'Sin nombre',
                vendor: Array.from(vendors).join(', '),
                lastActivity: lastActivity ? lastActivity.fecha : '',
                firstActivity: firstActivity ? firstActivity.fecha : '',
                daysSince: daysSince,
                etapa: etapaActual || 'Sin etapa',
                totalInteracciones: totalInteracciones,
                tieneSeguimientoReal: tieneSeguimientoReal,
                todasLasActividades: todasLasActividades
            };
        }
        
        function categorizePhoneSimplified(phoneData) {
            // SIMPLIFICADO: Solo 2 categorías principales
            if (phoneData.tieneSeguimientoReal) {
                // TIENE seguimiento real (más de 1 interacción o seguimiento explícito)
                verificationResults.conSeguimientoReal.push(phoneData);
                verificationResults.totalConSeguimientoReal++;
            } else {
                // NO tiene seguimiento real (solo 1 interacción = primer contacto)
                verificationResults.sinSeguimientoAun.push(phoneData);
                verificationResults.totalSinSeguimientoReal++;
            }
        }
        
        function displayVerificationResultsSimplified() {
            const resultsDiv = document.getElementById('followup-verification-results');
            
            const total = verificationResults.numerosProcesados;
            const encontrados = verificationResults.totalEnSistema;
            const conSeguimiento = verificationResults.totalConSeguimientoReal;
            const sinSeguimientoEnSistema = verificationResults.sinSeguimientoAun.length;
            const noRegistrados = verificationResults.noEncontrados.length;
            
            // CÁLCULO CORREGIDO: Porcentaje SOLO sobre los que están en el sistema
            const porcentajeCumplimiento = encontrados > 0 ? Math.round((conSeguimiento / encontrados) * 100) : 0;
            const porcentajeSinSeguimiento = encontrados > 0 ? Math.round((sinSeguimientoEnSistema / encontrados) * 100) : 0;
            const porcentajeEncontrados = total > 0 ? Math.round((encontrados / total) * 100) : 0;
            
            let html = `
                <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h5 style="margin: 0 0 20px 0; color: #2c3e50; text-align: center;">
                        📊 VERIFICACIÓN DE SEGUIMIENTOS REALES
                    </h5>
                    
                    <!-- Indicador Principal Grande -->
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 15px; margin-bottom: 25px; color: white; text-align: center;">
                        <h2 style="margin: 0 0 10px 0; font-size: 4em;">${porcentajeCumplimiento}%</h2>
                        <p style="margin: 0; font-size: 1.3em; opacity: 0.95;">CON SEGUIMIENTO REAL</p>
                        <p style="margin: 10px 0 0 0; opacity: 0.9;">
                            ${conSeguimiento} de ${encontrados} números EN EL SISTEMA tienen más de 1 interacción
                        </p>
                        <p style="margin: 5px 0 0 0; opacity: 0.8; font-size: 0.9em;">
                            (No se incluyen los ${noRegistrados} números no registrados en el cálculo)
                        </p>
                    </div>
                    
                    <!-- Indicadores Secundarios -->
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
                        <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); padding: 20px; border-radius: 10px; color: white; text-align: center;">
                            <div style="font-size: 2em; font-weight: bold;">${encontrados} / ${total}</div>
                            <div style="font-size: 0.9em; opacity: 0.95;">NÚMEROS EN EL SISTEMA</div>
                            <div style="font-size: 1.2em; margin-top: 5px;">${porcentajeEncontrados}%</div>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); padding: 20px; border-radius: 10px; color: white; text-align: center;">
                            <div style="font-size: 2em; font-weight: bold;">${noRegistrados}</div>
                            <div style="font-size: 0.9em; opacity: 0.95;">NO REGISTRADOS</div>
                            <div style="font-size: 0.8em; margin-top: 5px; opacity: 0.8;">No afectan el porcentaje</div>
                        </div>
                    </div>
                    
                    <!-- Métricas Detalladas SOLO de los que están en el sistema -->
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                        <h6 style="margin: 0 0 15px 0; color: #2c3e50; text-align: center;">
                            📈 Análisis de los ${encontrados} Números en el Sistema
                        </h6>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                            <div style="background: #d4edda; padding: 20px; border-radius: 10px; text-align: center; border: 2px solid #28a745;">
                                <div style="font-size: 2.5em; font-weight: bold; color: #155724;">
                                    ${conSeguimiento}
                                </div>
                                <div style="font-size: 0.9em; color: #155724; font-weight: 600;">✅ CON SEGUIMIENTO</div>
                                <div style="font-size: 1.2em; color: #155724; margin-top: 5px; font-weight: bold;">
                                    ${porcentajeCumplimiento}%
                                </div>
                                <div style="font-size: 0.8em; color: #155724;">
                                    Más de 1 interacción
                                </div>
                            </div>
                            
                            <div style="background: #fff3cd; padding: 20px; border-radius: 10px; text-align: center; border: 2px solid #ffc107;">
                                <div style="font-size: 2.5em; font-weight: bold; color: #856404;">
                                    ${sinSeguimientoEnSistema}
                                </div>
                                <div style="font-size: 0.9em; color: #856404; font-weight: 600;">⏳ PRIMER CONTACTO</div>
                                <div style="font-size: 1.2em; color: #856404; margin-top: 5px; font-weight: bold;">
                                    ${porcentajeSinSeguimiento}%
                                </div>
                                <div style="font-size: 0.8em; color: #856404;">
                                    Solo 1 interacción
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Barra de Progreso Visual SOLO de los que están en el sistema -->
                    <div style="margin-bottom: 25px;">
                        <h6 style="margin: 0 0 10px 0; color: #495057;">Distribución del Cumplimiento (Solo números en el sistema):</h6>
                        <div style="display: flex; height: 50px; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            ${conSeguimiento > 0 ? 
                                `<div style="width: ${porcentajeCumplimiento}%; background: #28a745; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.1em;">
                                    ${porcentajeCumplimiento}% CON SEGUIMIENTO
                                </div>` : ''}
                            ${sinSeguimientoEnSistema > 0 ? 
                                `<div style="width: ${porcentajeSinSeguimiento}%; background: #ffc107; display: flex; align-items: center; justify-content: center; color: #212529; font-weight: bold;">
                                    ${porcentajeSinSeguimiento}% PRIMER CONTACTO
                                </div>` : ''}
                        </div>
                        <small style="display: block; text-align: center; margin-top: 5px; color: #6c757d;">
                            Base: ${encontrados} números en el sistema (${noRegistrados} no registrados excluidos del cálculo)
                        </small>
                    </div>
                    
                    <!-- Lista de NÚMEROS CON Seguimiento Real -->
                    ${verificationResults.conSeguimientoReal.length > 0 ? `
                        <div style="background: #d4edda; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                            <h6 style="margin: 0 0 15px 0; color: #155724;">
                                ✅ NÚMEROS CON SEGUIMIENTO REAL (${verificationResults.conSeguimientoReal.length})
                            </h6>
                            <div style="max-height: 200px; overflow-y: auto;">
                                <table style="width: 100%; font-size: 0.85em; background: white; border-radius: 5px;">
                                    <thead>
                                        <tr style="background: #28a745; color: white; position: sticky; top: 0;">
                                            <th style="padding: 8px;">Teléfono</th>
                                            <th style="padding: 8px;">Cliente</th>
                                            <th style="padding: 8px;">Vendedor</th>
                                            <th style="padding: 8px;">Interacciones</th>
                                            <th style="padding: 8px;">Última Actividad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${verificationResults.conSeguimientoReal.slice(0, 20).map(item => `
                                            <tr style="border-bottom: 1px solid #dee2e6;">
                                                <td style="padding: 8px;">${item.phone}</td>
                                                <td style="padding: 8px;">${item.client}</td>
                                                <td style="padding: 8px;">${item.vendor}</td>
                                                <td style="padding: 8px; text-align: center; font-weight: bold; color: #28a745;">
                                                    ${item.totalInteracciones}
                                                </td>
                                                <td style="padding: 8px;">${item.lastActivity}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                                ${verificationResults.conSeguimientoReal.length > 20 ? 
                                    `<p style="margin: 10px 0 0 0; text-align: center; color: #155724;">
                                        Mostrando 20 de ${verificationResults.conSeguimientoReal.length} números con seguimiento
                                    </p>` : ''}
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Lista de PRIMER CONTACTO (Solo 1 interacción) -->
                    ${verificationResults.sinSeguimientoAun.length > 0 ? `
                        <div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
                            <h6 style="margin: 0 0 15px 0; color: #856404;">
                                ⏳ SOLO PRIMER CONTACTO - REQUIEREN SEGUIMIENTO (${verificationResults.sinSeguimientoAun.length})
                            </h6>
                            <div style="max-height: 150px; overflow-y: auto; background: white; padding: 10px; border-radius: 5px;">
                                <table style="width: 100%; font-size: 0.85em;">
                                    <thead>
                                        <tr style="background: #ffc107; color: #212529;">
                                            <th style="padding: 5px;">Teléfono</th>
                                            <th style="padding: 5px;">Cliente</th>
                                            <th style="padding: 5px;">Vendedor</th>
                                            <th style="padding: 5px;">Fecha Contacto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${verificationResults.sinSeguimientoAun.slice(0, 10).map(item => `
                                            <tr>
                                                <td style="padding: 5px;">${item.phone}</td>
                                                <td style="padding: 5px;">${item.client}</td>
                                                <td style="padding: 5px;">${item.vendor}</td>
                                                <td style="padding: 5px;">${item.firstActivity}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Lista de NO REGISTRADOS (Informativo, no afecta porcentaje) -->
                    ${verificationResults.noEncontrados.length > 0 ? `
                        <div style="background: #e9ecef; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #6c757d;">
                            <h6 style="margin: 0 0 15px 0; color: #495057;">
                                ℹ️ NÚMEROS NO REGISTRADOS EN EL SISTEMA (${verificationResults.noEncontrados.length})
                                <span style="font-size: 0.8em; font-weight: normal; margin-left: 10px;">
                                    (No incluidos en el cálculo de porcentajes)
                                </span>
                            </h6>
                            <div style="max-height: 100px; overflow-y: auto; background: white; padding: 15px; border-radius: 5px;">
                                <div style="font-size: 0.9em; color: #495057; word-break: break-all;">
                                    ${verificationResults.noEncontrados.slice(0, 30).join(', ')}
                                    ${verificationResults.noEncontrados.length > 30 ? 
                                        `<br><br><strong>... y ${verificationResults.noEncontrados.length - 30} números más</strong>` : ''}
                                </div>
                            </div>
                            <button onclick="addMissingNumbersToSystem()" class="btn" style="margin-top: 15px; background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                                ➕ Agregar estos números al sistema
                            </button>
                        </div>
                    ` : ''}
                    
                    <!-- Resumen Final -->
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                        <h5 style="margin: 0 0 15px 0;">📈 RESUMEN DE CUMPLIMIENTO</h5>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                            <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px;">
                                <p style="margin: 0; font-size: 1.8em; font-weight: bold;">
                                    ${porcentajeCumplimiento}%
                                </p>
                                <p style="margin: 5px 0 0 0; font-size: 0.9em; opacity: 0.9;">
                                    Con Seguimiento Real
                                </p>
                                <small style="opacity: 0.8;">De los ${encontrados} en el sistema</small>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px;">
                                <p style="margin: 0; font-size: 1.8em; font-weight: bold;">
                                    ${porcentajeSinSeguimiento}%
                                </p>
                                <p style="margin: 5px 0 0 0; font-size: 0.9em; opacity: 0.9;">
                                    Requieren Seguimiento
                                </p>
                                <small style="opacity: 0.8;">Solo primer contacto</small>
                            </div>
                        </div>
                        <p style="margin: 15px 0 0 0; font-size: 0.9em; opacity: 0.9;">
                            📊 Base de cálculo: ${encontrados} números en el sistema<br>
                            ℹ️ Excluidos del cálculo: ${noRegistrados} números no registrados
                        </p>
                    </div>
                    
                    <!-- Botones de Acción -->
                    <div style="text-align: center; margin-top: 25px;">
                        <button onclick="exportVerificationReport()" class="btn btn-export" style="padding: 10px 25px;">
                            📥 Exportar Reporte Detallado
                        </button>
                        <button onclick="document.getElementById('followup-verification-results').style.display='none'" 
                                class="btn" style="padding: 10px 25px; background: #6c757d; margin-left: 10px;">
                            Cerrar
                        </button>
                    </div>
                </div>
            `;
            
            resultsDiv.innerHTML = html;
            resultsDiv.style.display = 'block';
        }
        
        function addMissingNumbersToSystem() {
            const vendor = prompt('¿A qué vendedor asignar estos números no registrados?\n\nRafael\nKattya Huarcaya\nSumiko Gomero\nThalia Sobrado');
            
            if (!vendor || !salesData.vendors.includes(vendor)) {
                alert('⚠️ Vendedor no válido');
                return;
            }
            
            verificationResults.noEncontrados.forEach(phone => {
                const newActivity = {
                    fecha: new Date().toISOString().split('T')[0],
                    hora: new Date().toTimeString().slice(0, 5),
                    vendedor: vendor,
                    tipoActividad: 'Interaccion con nuevo cliente (potencial)',
                    cliente: `Cliente ${phone}`,
                    telefono: phone,
                    etapaActual: 'Primer contacto',
                    timestamp: new Date().toISOString()
                };
                
                salesData.activities.push(newActivity);
            });
            
            saveData();
            alert(`✅ ${verificationResults.noEncontrados.length} números agregados al sistema para ${vendor}`);
            verifyPhoneNumbers(); // Volver a verificar
        }
        
        function exportVerificationReport() {
            const reportData = [];
            
            // Agregar datos con seguimiento
            verificationResults.conSeguimientoReal.forEach(item => {
                reportData.push({
                    Estado: 'CON SEGUIMIENTO',
                    Telefono: item.phone,
                    Cliente: item.client,
                    Vendedor: item.vendor,
                    Interacciones: item.totalInteracciones,
                    PrimeraActividad: item.firstActivity,
                    UltimaActividad: item.lastActivity,
                    DiasDesdeUltima: item.daysSince,
                    Etapa: item.etapa
                });
            });
            
            // Agregar primer contacto
            verificationResults.sinSeguimientoAun.forEach(item => {
                reportData.push({
                    Estado: 'PRIMER CONTACTO',
                    Telefono: item.phone,
                    Cliente: item.client,
                    Vendedor: item.vendor,
                    Interacciones: item.totalInteracciones,
                    PrimeraActividad: item.firstActivity,
                    UltimaActividad: item.lastActivity,
                    DiasDesdeUltima: item.daysSince,
                    Etapa: item.etapa
                });
            });
            
            // Agregar no encontrados
            verificationResults.noEncontrados.forEach(phone => {
                reportData.push({
                    Estado: 'NO REGISTRADO',
                    Telefono: phone,
                    Cliente: '',
                    Vendedor: '',
                    Interacciones: 0,
                    PrimeraActividad: '',
                    UltimaActividad: '',
                    DiasDesdeUltima: '',
                    Etapa: ''
                });
            });
            
            if (reportData.length === 0) {
                alert('No hay datos para exportar');
                return;
            }
            
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(reportData);
            XLSX.utils.book_append_sheet(wb, ws, 'Verificacion_Seguimientos');
            XLSX.writeFile(wb, `Verificacion_Seguimientos_${new Date().toISOString().split('T')[0]}.xlsx`);
            
            alert('✅ Reporte de verificación exportado exitosamente');
        }

        // Funciones de Debug
        function testPhoneNormalization() {
            const input = document.getElementById('test-phone').value;
            if (!input) {
                alert('Por favor ingresa un número para probar');
                return;
            }
            
            const normalized = normalizePhone(input);
            const resultDiv = document.getElementById('phone-test-result');
            
            let matches = [];
            salesData.activities.forEach(activity => {
                if (activity.telefono) {
                    const actNorm = normalizePhone(activity.telefono);
                    if (phonesMatch(input, activity.telefono)) {
                        matches.push({
                            original: activity.telefono,
                            normalized: actNorm,
                            cliente: activity.cliente,
                            vendedor: activity.vendedor
                        });
                    }
                }
            });
            
            resultDiv.innerHTML = `
                <div class="phone-debug">
                    <h5>Análisis del número: ${input}</h5>
                    <p><strong>Normalizado:</strong> <span class="phone-match">${normalized}</span></p>
                    <p><strong>Longitud:</strong> ${normalized.length} dígitos</p>
                    <p><strong>Tipo detectado:</strong> ${
                        normalized.startsWith('9') && normalized.length === 9 ? 'Celular' :
                        normalized.length === 7 || normalized.length === 8 ? 'Fijo' :
                        'Formato no estándar'
                    }</p>
                    
                    <h6>Coincidencias en el sistema:</h6>
                    ${matches.length > 0 ? 
                        matches.map(m => `
                            <div style="margin: 5px 0; padding: 5px; background: #e7f3ff;">
                                <strong>${m.cliente}</strong> (${m.vendedor})<br>
                                Original: ${m.original} → Normalizado: ${m.normalized}
                            </div>
                        `).join('') :
                        '<p class="phone-no-match">No se encontraron coincidencias</p>'
                    }
                </div>
            `;
        }

        function updateSystemPhoneStats() {
            const statsDiv = document.getElementById('system-phone-stats');
            if (!statsDiv) return;
            
            const phoneSet = new Set();
            let withPhone = 0;
            let withoutPhone = 0;
            
            salesData.activities.forEach(activity => {
                if (activity.telefono) {
                    const normalized = normalizePhone(activity.telefono);
                    if (normalized) {
                        phoneSet.add(normalized);
                        withPhone++;
                    } else {
                        withoutPhone++;
                    }
                } else {
                    withoutPhone++;
                }
            });
            
            statsDiv.innerHTML = `
                <p><strong>Total de actividades:</strong> ${salesData.activities.length}</p>
                <p><strong>Actividades con teléfono:</strong> ${withPhone}</p>
                <p><strong>Actividades sin teléfono:</strong> ${withoutPhone}</p>
                <p><strong>Números únicos:</strong> ${phoneSet.size}</p>
                <p><strong>Formatos detectados:</strong></p>
                <ul>
                    <li>Celulares (9 dígitos): ${Array.from(phoneSet).filter(p => p.startsWith('9') && p.length === 9).length}</li>
                    <li>Fijos (7-8 dígitos): ${Array.from(phoneSet).filter(p => !p.startsWith('9') && (p.length === 7 || p.length === 8)).length}</li>
                    <li>Otros formatos: ${Array.from(phoneSet).filter(p => !(p.startsWith('9') && p.length === 9) && !(p.length === 7 || p.length === 8)).length}</li>
                </ul>
            `;
        }

        function showAllPhones() {
            const listDiv = document.getElementById('all-phones-list');
            
            const phoneMap = new Map();
            
            salesData.activities.forEach(activity => {
                if (activity.telefono) {
                    const normalized = normalizePhone(activity.telefono);
                    if (normalized) {
                        if (!phoneMap.has(normalized)) {
                            phoneMap.set(normalized, []);
                        }
                        phoneMap.get(normalized).push({
                            cliente: activity.cliente,
                            vendedor: activity.vendedor,
                            fecha: activity.fecha,
                            original: activity.telefono
                        });
                    }
                }
            });
            
            let html = '<table class="data-table"><thead><tr><th>Teléfono Normalizado</th><th>Original(es)</th><th>Cliente(s)</th><th>Vendedor(es)</th><th>Actividades</th></tr></thead><tbody>';
            
            Array.from(phoneMap.entries()).forEach(([normalized, activities]) => {
                const uniqueOriginals = [...new Set(activities.map(a => a.original))];
                const uniqueClients = [...new Set(activities.map(a => a.cliente))];
                const uniqueVendors = [...new Set(activities.map(a => a.vendedor))];
                
                html += `
                    <tr>
                        <td><strong>${normalized}</strong></td>
                        <td>${uniqueOriginals.join(', ')}</td>
                        <td>${uniqueClients.join(', ')}</td>
                        <td>${uniqueVendors.join(', ')}</td>
                        <td>${activities.length}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            listDiv.innerHTML = html;
        }

        // Funciones de archivo
        function setupFileUpload() {
            const fileInput = document.getElementById('excel-file-input');
            const fileUploadZone = document.getElementById('file-upload-zone');
            
            if (fileInput) {
                fileInput.addEventListener('change', handleFileSelect);
            }
            
            if (fileUploadZone) {
                fileUploadZone.addEventListener('click', () => fileInput.click());
                fileUploadZone.addEventListener('dragover', handleDragOver);
                fileUploadZone.addEventListener('drop', handleFileDrop);
                fileUploadZone.addEventListener('dragleave', handleDragLeave);
            }
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.currentTarget.classList.add('dragover');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('dragover');
        }

        function handleFileDrop(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleSelectedFile(files[0]);
            }
        }

        function handleFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                handleSelectedFile(files[0]);
            }
        }

        function handleSelectedFile(file) {
            if (!file.name.match(/\.(xlsx|xls)$/)) {
                alert('⚠️ Por favor selecciona un archivo Excel (.xlsx o .xls)');
                return;
            }
            
            uploadedFile = file;
            document.getElementById('file-status').classList.remove('hidden');
            document.getElementById('file-name').textContent = `Archivo: ${file.name}`;
            
            const selectedVendor = document.getElementById('selected-vendor').value;
            if (selectedVendor) {
                document.getElementById('assigned-vendor').textContent = `Asignado a: ${selectedVendor}`;
            }
        }

        function processFile() {
            if (!uploadedFile) {
                alert('❌ No hay archivo para procesar');
                return;
            }
            
            console.log('🚀 Procesando archivo:', uploadedFile.name);
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {type: 'array'});
                    
                    console.log('📊 Hojas encontradas:', workbook.SheetNames);
                    
                    let sheetName = workbook.SheetNames[0];
                    let worksheet = workbook.Sheets[sheetName];
                    
                    const jsonData = XLSX.utils.sheet_to_json(worksheet, {header: 1});
                    fileData = jsonData;
                    
                    console.log('📄 Total de filas:', jsonData.length);
                    analyzeData(jsonData, sheetName);
                    
                } catch (error) {
                    console.error('❌ Error procesando archivo:', error);
                    alert('❌ Error al procesar el archivo Excel: ' + error.message);
                }
            };
            
            reader.readAsArrayBuffer(uploadedFile);
        }
        
        function analyzeData(data, sheetName) {
            console.log('🔍 Analizando datos de la hoja:', sheetName);
            
            const selectedVendor = document.getElementById('selected-vendor').value;
            let assignedVendor = selectedVendor || 'Rafael';
            
            let headerRowIndex = -1;
            let headers = [];
            
            for (let i = 0; i < Math.min(10, data.length); i++) {
                const row = data[i];
                if (row && row.length > 0) {
                    const rowStr = row.join(' ').toLowerCase();
                    if (rowStr.includes('fecha') || rowStr.includes('cliente') || rowStr.includes('telefono')) {
                        headerRowIndex = i;
                        headers = row;
                        console.log('📋 Encabezados encontrados en fila:', i);
                        break;
                    }
                }
            }
            
            if (headerRowIndex === -1 && data.length > 0) {
                headerRowIndex = 0;
                headers = data[0];
            }
            
            let activities = [];
            let totalMonto = 0;
            
            for (let i = headerRowIndex + 1; i < data.length; i++) {
                const row = data[i];
                if (!row || row.length === 0) continue;
                
                const activity = {
                    fecha: row[0] || new Date().toISOString().split('T')[0],
                    hora: row[1] || '09:00',
                    vendedor: assignedVendor,
                    tipoActividad: row[2] || 'Interaccion con nuevo cliente (potencial)',
                    cliente: row[3] || `Cliente ${i}`,
                    telefono: row[4] || '',
                    universidad: row[5] || '',
                    origen: row[6] || 'Excel',
                    etapaActual: row[7] || 'Primer contacto',
                    monto: parseFloat(row[8]) || 0,
                    prioridad: row[9] || 'Media',
                    acciones: row[10] || 'Importado desde Excel',
                    resultado: row[11] || '',
                    proximaAccion: row[12] || '',
                    timestamp: new Date().toISOString()
                };
                
                if (activity.cliente && activity.cliente !== '') {
                    activities.push(activity);
                    totalMonto += activity.monto;
                }
            }
            
            displayAnalysisResults({
                totalActivities: activities.length,
                totalMonto: totalMonto,
                assignedVendor: assignedVendor,
                sheetName: sheetName
            }, activities);
        }
        
        function displayAnalysisResults(summary, activities) {
            const resultsDiv = document.getElementById('analysis-results');
            const contentDiv = document.getElementById('analysis-content');
            
            let html = `
                <div class="metrics-grid" style="margin-bottom: 30px;">
                    <div class="metric-card">
                        <div class="metric-value">${summary.totalActivities}</div>
                        <div class="metric-label">Actividades Encontradas</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">S/ ${summary.totalMonto.toLocaleString()}</div>
                        <div class="metric-label">Monto Total</div>
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h4>📄 Información del Archivo</h4>
                    <p><strong>Hoja analizada:</strong> ${summary.sheetName}</p>
                    <p><strong>📊 Actividades encontradas:</strong> ${summary.totalActivities}</p>
                    <p><strong>💰 Monto total:</strong> S/ ${summary.totalMonto.toLocaleString()}</p>
                    <p><strong>👤 Asignado a:</strong> <span style="color: #28a745; font-weight: bold;">${summary.assignedVendor}</span></p>
                </div>
            `;
            
            if (activities.length > 0) {
                html += `
                    <div style="margin-top: 20px;">
                        <h4>📋 Vista Previa (Primeras 5 actividades)</h4>
                        <div style="overflow-x: auto; margin-top: 15px;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Teléfono</th>
                                        <th>Tipo</th>
                                        <th>Etapa</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;
                
                activities.slice(0, 5).forEach(activity => {
                    html += `
                        <tr>
                            <td>${activity.fecha}</td>
                            <td>${activity.cliente}</td>
                            <td>${activity.telefono || 'N/A'}</td>
                            <td>${activity.tipoActividad}</td>
                            <td>${activity.etapaActual}</td>
                            <td>S/ ${(activity.monto || 0).toLocaleString()}</td>
                        </tr>
                    `;
                });
                
                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                
                window.tempActivities = activities;
            }
            
            html += `
                <div style="margin-top: 30px; text-align: center;">
                    <button class="btn btn-success" onclick="importAnalyzedData()">
                        📥 Importar ${activities.length} Actividades al Sistema
                    </button>
                </div>
            `;
            
            contentDiv.innerHTML = html;
            resultsDiv.classList.remove('hidden');
        }
        
        function importAnalyzedData() {
            if (!window.tempActivities || window.tempActivities.length === 0) {
                alert('❌ No hay datos para importar');
                return;
            }
            
            const count = window.tempActivities.length;
            
            if (confirm(`¿Deseas importar ${count} actividades al sistema?`)) {
                window.tempActivities.forEach(activity => {
                    salesData.activities.push(activity);
                });
                
                saveData();
                updateDashboard();
                updateVendorsList();
                updateSystemInfo();
                
                window.tempActivities = null;
                
                document.getElementById('analysis-results').classList.add('hidden');
                document.getElementById('file-status').classList.add('hidden');
                document.getElementById('excel-file-input').value = '';
                uploadedFile = null;
                
                alert(`✅ ${count} actividades importadas exitosamente!`);
                
                showTab('dashboard');
            }
        }

        // Funciones de exportación
        function exportBackup() {
            try {
                const backupData = {
                    ...salesData,
                    exportDate: new Date().toISOString(),
                    version: '2.0'
                };
                
                const dataStr = JSON.stringify(backupData, null, 2);
                const dataBlob = new Blob([dataStr], {type: 'application/json'});
                const url = URL.createObjectURL(dataBlob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `Backup_SistemaVentas_${new Date().toISOString().split('T')[0]}.json`;
                link.click();
                URL.revokeObjectURL(url);
                
                alert('✅ Respaldo exportado exitosamente!');
            } catch (e) {
                console.error('Error exportando respaldo:', e);
                alert('❌ Error al crear el respaldo');
            }
        }

        function importBackup(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const imported = JSON.parse(e.target.result);
                    
                    if (confirm('¿Restaurar datos del respaldo? Esto reemplazará todos los datos actuales.')) {
                        salesData = imported;
                        saveData();
                        updateDashboard();
                        updateVendorsList();
                        updateSystemInfo();
                        updateSystemPhoneStats();
                        
                        alert('✅ Respaldo restaurado exitosamente!');
                    }
                } catch (error) {
                    console.error('Error importando respaldo:', error);
                    alert('❌ Error al importar el respaldo');
                }
            };
            reader.readAsText(file);
        }

        function clearAllDataForced() {
            if (confirm('⚠️ ¿Eliminar TODOS los datos del sistema?\n\nEsta acción es IRREVERSIBLE')) {
                if (confirm('🚨 CONFIRMACIÓN FINAL\n\n¿Estás completamente seguro?')) {
                    salesData.activities = [];
                    saveData();
                    updateDashboard();
                    updateVendorsList();
                    updateSystemInfo();
                    updateSystemPhoneStats();
                    
                    alert('✅ Todos los datos han sido eliminados');
                }
            }
        }

        function exportDailyReport() {
            const today = new Date().toISOString().split('T')[0];
            const todayActivities = salesData.activities.filter(a => a.fecha === today);
            exportToExcel(todayActivities, `Reporte_Diario_${today}.xlsx`);
            
            // Guardar en historial de reportes
            guardarReporteEnHistorial('Reporte Diario', todayActivities.length, `Reporte_Diario_${today}.xlsx`);
        }

        function exportWeeklyReport() {
            const today = new Date();
            const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            const weekActivities = salesData.activities.filter(a => {
                const activityDate = new Date(a.fecha);
                return activityDate >= weekAgo && activityDate <= today;
            });
            const filename = `Reporte_Semanal_${today.toISOString().split('T')[0]}.xlsx`;
            exportToExcel(weekActivities, filename);
            
            // Guardar en historial de reportes
            guardarReporteEnHistorial('Reporte Semanal', weekActivities.length, filename);
        }

        function exportMonthlyReport() {
            const today = new Date();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();
            const monthActivities = salesData.activities.filter(a => {
                const activityDate = new Date(a.fecha);
                return activityDate.getMonth() === currentMonth && 
                       activityDate.getFullYear() === currentYear;
            });
            const filename = `Reporte_Mensual_${today.toISOString().split('T')[0]}.xlsx`;
            exportToExcel(monthActivities, filename);
            
            // Guardar en historial de reportes
            guardarReporteEnHistorial('Reporte Mensual', monthActivities.length, filename);
        }

        function exportVendorReport() {
            const vendorData = [];
            const vendorStats = {};
            
            salesData.vendors.forEach(vendor => {
                vendorStats[vendor] = {
                    vendedor: vendor,
                    totalActividades: 0,
                    nuevosLeads: 0,
                    reuniones: 0,
                    contratos: 0,
                    ingresos: 0
                };
            });
            
            salesData.activities.forEach(activity => {
                if (vendorStats[activity.vendedor]) {
                    vendorStats[activity.vendedor].totalActividades++;
                    if (activity.tipoActividad && activity.tipoActividad.includes('nuevo cliente')) {
                        vendorStats[activity.vendedor].nuevosLeads++;
                    }
                    if (activity.tipoActividad && activity.tipoActividad.includes('Reunión')) {
                        vendorStats[activity.vendedor].reuniones++;
                    }
                    if (activity.etapaActual && activity.etapaActual.includes('Cerr')) {
                        vendorStats[activity.vendedor].contratos++;
                    }
                    vendorStats[activity.vendedor].ingresos += activity.monto || 0;
                }
            });
            
            const filename = `Reporte_Vendedores_${new Date().toISOString().split('T')[0]}.xlsx`;
            exportToExcel(Object.values(vendorStats), filename);
            
            // Guardar en historial de reportes
            guardarReporteEnHistorial('Reporte por Vendedor', Object.values(vendorStats).length, filename);
        }

        function generateExcelTemplate() {
            const filename = `Sistema_Ventas_Completo_${new Date().toISOString().split('T')[0]}.xlsx`;
            exportToExcel(salesData.activities, filename);
            
            // Guardar en historial de reportes
            guardarReporteEnHistorial('Excel Completo', salesData.activities.length, filename);
        }

        function guardarReporteEnHistorial(tipo, totalRegistros, nombreArchivo) {
            if (!salesData.reportesSubidos) {
                salesData.reportesSubidos = [];
            }

            const reporteData = {
                id: Date.now(),
                tipo: tipo,
                fecha: new Date().toISOString(),
                fechaFormateada: new Date().toLocaleDateString('es-PE'),
                hora: new Date().toLocaleTimeString('es-PE'),
                nombreArchivo: nombreArchivo,
                totalRegistros: totalRegistros
            };

            salesData.reportesSubidos.push(reporteData);
            saveData();
            actualizarEstadisticasReportes();
        }

        function actualizarEstadisticasReportes() {
            if (!salesData.reportesSubidos) {
                salesData.reportesSubidos = [];
            }

            const total = salesData.reportesSubidos.length;
            const hoy = new Date();
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            const inicioSemana = new Date(hoy.getTime() - 7 * 24 * 60 * 60 * 1000);

            const reportesMes = salesData.reportesSubidos.filter(r => new Date(r.fecha) >= inicioMes).length;
            const reportesSemana = salesData.reportesSubidos.filter(r => new Date(r.fecha) >= inicioSemana).length;

            const totalElement = document.getElementById('total-reportes');
            const mesElement = document.getElementById('reportes-mes');
            const semanaElement = document.getElementById('reportes-semana');
            const ultimoElement = document.getElementById('ultimo-reporte');

            if (totalElement) totalElement.textContent = total;
            if (mesElement) mesElement.textContent = reportesMes;
            if (semanaElement) semanaElement.textContent = reportesSemana;
            
            if (ultimoElement) {
                if (total > 0) {
                    const ultimo = salesData.reportesSubidos[salesData.reportesSubidos.length - 1];
                    ultimoElement.textContent = `${ultimo.tipo} - ${ultimo.fechaFormateada}`;
                } else {
                    ultimoElement.textContent = 'Ninguno';
                }
            }
        }

        function verHistorialReportes() {
            const modal = document.getElementById('modal-reportes');
            const content = document.getElementById('reportes-historial-content');
            
            if (!salesData.reportesSubidos || salesData.reportesSubidos.length === 0) {
                content.innerHTML = '<p style="text-align: center; color: #6c757d;">No hay reportes generados en el historial</p>';
            } else {
                let html = `
                    <div style="max-height: 60vh; overflow-y: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Tipo de Reporte</th>
                                    <th>Archivo</th>
                                    <th>Registros</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                salesData.reportesSubidos.slice().reverse().forEach((reporte) => {
                    html += `
                        <tr>
                            <td>${reporte.fechaFormateada}</td>
                            <td>${reporte.hora}</td>
                            <td><strong>${reporte.tipo}</strong></td>
                            <td style="font-size: 0.85em;">${reporte.nombreArchivo}</td>
                            <td style="text-align: center; font-weight: bold;">${reporte.totalRegistros}</td>
                            <td>
                                <button onclick="eliminarReporteHistorial(${reporte.id})" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8em;">
                                    🗑️ Eliminar
                                </button>
                            </td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <h5>📊 Estadísticas Generales</h5>
                        <p>Total de reportes generados: <strong>${salesData.reportesSubidos.length}</strong></p>
                        <button onclick="limpiarHistorialReportes()" class="btn btn-danger">
                            🗑️ Limpiar Todo el Historial
                        </button>
                    </div>
                `;

                content.innerHTML = html;
            }

            modal.style.display = 'block';
        }

        function eliminarReporteHistorial(id) {
            if (confirm('¿Eliminar este reporte del historial?')) {
                salesData.reportesSubidos = salesData.reportesSubidos.filter(r => r.id !== id);
                saveData();
                verHistorialReportes();
                actualizarEstadisticasReportes();
                alert('✅ Reporte eliminado del historial');
            }
        }

        function limpiarHistorialReportes() {
            if (confirm('¿Estás seguro de limpiar TODO el historial de reportes?\n\nEsta acción no se puede deshacer.')) {
                salesData.reportesSubidos = [];
                saveData();
                verHistorialReportes();
                actualizarEstadisticasReportes();
                alert('✅ Historial de reportes limpiado');
            }
        }

        function exportFollowupExcel() {
            const activities = salesData.activities;
            if (activities.length === 0) {
                alert('No hay datos para exportar');
                return;
            }
            
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(activities);
            XLSX.utils.book_append_sheet(wb, ws, 'Seguimientos');
            XLSX.writeFile(wb, `Seguimientos_${new Date().toISOString().split('T')[0]}.xlsx`);
        }

        function exportToExcel(data, filename) {
            if (data.length === 0) {
                alert('No hay datos para exportar');
                return;
            }
            
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(data);
            XLSX.utils.book_append_sheet(wb, ws, 'Datos');
            XLSX.writeFile(wb, filename);
            
            alert(`✅ Archivo exportado: ${filename}`);
        }

        // NUEVAS FUNCIONES PARA GESTIÓN DE SEGUIMIENTOS

        function guardarSeguimientoActual() {
            const activities = salesData.activities.filter(a => {
                if (currentVendorFilter !== 'todos' && a.vendedor !== currentVendorFilter) {
                    return false;
                }
                const daysSince = Math.floor((new Date() - new Date(a.fecha)) / (1000 * 60 * 60 * 24));
                return daysSince <= 7;
            });

            if (activities.length === 0) {
                alert('⚠️ No hay seguimientos pendientes para guardar');
                return;
            }

            const seguimientoData = {
                id: Date.now(),
                fecha: new Date().toISOString(),
                fechaFormateada: new Date().toLocaleDateString('es-PE'),
                hora: new Date().toLocaleTimeString('es-PE'),
                vendedor: currentVendorFilter,
                totalActividades: activities.length,
                actividades: activities,
                resumen: {
                    nuevosLeads: activities.filter(a => a.tipoActividad && a.tipoActividad.includes('nuevo cliente')).length,
                    seguimientos: activities.filter(a => a.tipoActividad === 'Seguimiento').length,
                    reuniones: activities.filter(a => a.tipoActividad && a.tipoActividad.includes('Reunión')).length,
                    cierres: activities.filter(a => a.etapaActual && a.etapaActual.includes('Cerr')).length
                }
            };

            if (!salesData.seguimientosGuardados) {
                salesData.seguimientosGuardados = [];
            }

            salesData.seguimientosGuardados.push(seguimientoData);
            saveData();

            alert(`✅ Seguimiento guardado exitosamente!\n\n📊 Resumen:\n- Total actividades: ${seguimientoData.totalActividades}\n- Nuevos leads: ${seguimientoData.resumen.nuevosLeads}\n- Seguimientos: ${seguimientoData.resumen.seguimientos}\n- Reuniones: ${seguimientoData.resumen.reuniones}\n- Cierres: ${seguimientoData.resumen.cierres}`);
            
            actualizarEstadisticasHistorial();
        }

        function actualizarEstadisticasHistorial() {
            if (!salesData.seguimientosGuardados) {
                salesData.seguimientosGuardados = [];
            }

            const total = salesData.seguimientosGuardados.length;
            const totalElement = document.getElementById('total-seguimientos-guardados');
            const ultimoElement = document.getElementById('ultimo-guardado');

            if (totalElement) {
                totalElement.textContent = total;
            }

            if (ultimoElement) {
                if (total > 0) {
                    const ultimo = salesData.seguimientosGuardados[salesData.seguimientosGuardados.length - 1];
                    ultimoElement.textContent = `Último: ${ultimo.fechaFormateada} ${ultimo.hora}`;
                } else {
                    ultimoElement.textContent = 'Ninguno';
                }
            }
        }

        function verHistorialSeguimientos() {
            const modal = document.getElementById('modal-historial');
            const content = document.getElementById('historial-content');
            
            if (!salesData.seguimientosGuardados || salesData.seguimientosGuardados.length === 0) {
                content.innerHTML = '<p style="text-align: center; color: #6c757d;">No hay seguimientos guardados en el historial</p>';
            } else {
                let html = `
                    <div style="max-height: 60vh; overflow-y: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Vendedor</th>
                                    <th>Total Act.</th>
                                    <th>Nuevos Leads</th>
                                    <th>Seguimientos</th>
                                    <th>Reuniones</th>
                                    <th>Cierres</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                salesData.seguimientosGuardados.reverse().forEach((seg, index) => {
                    html += `
                        <tr>
                            <td>${seg.fechaFormateada}</td>
                            <td>${seg.hora}</td>
                            <td>${seg.vendedor === 'todos' ? 'Todos' : seg.vendedor}</td>
                            <td style="text-align: center; font-weight: bold;">${seg.totalActividades}</td>
                            <td style="text-align: center;">${seg.resumen.nuevosLeads}</td>
                            <td style="text-align: center;">${seg.resumen.seguimientos}</td>
                            <td style="text-align: center;">${seg.resumen.reuniones}</td>
                            <td style="text-align: center;">${seg.resumen.cierres}</td>
                            <td>
                                <button onclick="verDetalleSeguimiento(${seg.id})" class="btn" style="padding: 5px 10px; font-size: 0.8em;">
                                    👁️ Ver
                                </button>
                                <button onclick="eliminarSeguimientoGuardado(${seg.id})" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8em;">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                content.innerHTML = html;
            }

            modal.style.display = 'block';
        }

        function verDetalleSeguimiento(id) {
            const seguimiento = salesData.seguimientosGuardados.find(s => s.id === id);
            if (!seguimiento) {
                alert('⚠️ Seguimiento no encontrado');
                return;
            }

            let detalle = `📊 DETALLE DEL SEGUIMIENTO\n`;
            detalle += `━━━━━━━━━━━━━━━━━━━━━━━\n`;
            detalle += `📅 Fecha: ${seguimiento.fechaFormateada} ${seguimiento.hora}\n`;
            detalle += `👤 Vendedor: ${seguimiento.vendedor === 'todos' ? 'Todos' : seguimiento.vendedor}\n`;
            detalle += `📈 Total actividades: ${seguimiento.totalActividades}\n\n`;
            detalle += `RESUMEN:\n`;
            detalle += `• Nuevos leads: ${seguimiento.resumen.nuevosLeads}\n`;
            detalle += `• Seguimientos: ${seguimiento.resumen.seguimientos}\n`;
            detalle += `• Reuniones: ${seguimiento.resumen.reuniones}\n`;
            detalle += `• Cierres: ${seguimiento.resumen.cierres}\n\n`;
            detalle += `PRIMEROS 5 CLIENTES:\n`;

            seguimiento.actividades.slice(0, 5).forEach((act, i) => {
                detalle += `${i + 1}. ${act.cliente} - ${act.etapaActual || 'Sin etapa'}\n`;
            });

            if (seguimiento.actividades.length > 5) {
                detalle += `\n... y ${seguimiento.actividades.length - 5} más`;
            }

            alert(detalle);
        }

        function eliminarSeguimientoGuardado(id) {
            if (confirm('¿Estás seguro de eliminar este seguimiento del historial?')) {
                salesData.seguimientosGuardados = salesData.seguimientosGuardados.filter(s => s.id !== id);
                saveData();
                verHistorialSeguimientos();
                actualizarEstadisticasHistorial();
                alert('✅ Seguimiento eliminado del historial');
            }
        }

        function exportarHistorialSeguimientos() {
            if (!salesData.seguimientosGuardados || salesData.seguimientosGuardados.length === 0) {
                alert('⚠️ No hay seguimientos guardados para exportar');
                return;
            }

            const exportData = [];
            
            salesData.seguimientosGuardados.forEach(seg => {
                seg.actividades.forEach(act => {
                    exportData.push({
                        'Fecha Guardado': seg.fechaFormateada,
                        'Hora Guardado': seg.hora,
                        'Vendedor Filtro': seg.vendedor === 'todos' ? 'Todos' : seg.vendedor,
                        'Fecha Actividad': act.fecha,
                        'Hora Actividad': act.hora,
                        'Vendedor': act.vendedor,
                        'Cliente': act.cliente,
                        'Teléfono': act.telefono || '',
                        'Tipo Actividad': act.tipoActividad,
                        'Etapa': act.etapaActual || '',
                        'Monto': act.monto || 0,
                        'Prioridad': act.prioridad || '',
                        'Acciones': act.acciones || '',
                        'Resultado': act.resultado || '',
                        'Próxima Acción': act.proximaAccion || ''
                    });
                });
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(exportData);
            XLSX.utils.book_append_sheet(wb, ws, 'Historial_Seguimientos');
            XLSX.writeFile(wb, `Historial_Seguimientos_${new Date().toISOString().split('T')[0]}.xlsx`);
            
            // También guardar esto como reporte subido
            const reporteData = {
                id: Date.now(),
                tipo: 'Historial de Seguimientos',
                fecha: new Date().toISOString(),
                fechaFormateada: new Date().toLocaleDateString('es-PE'),
                hora: new Date().toLocaleTimeString('es-PE'),
                nombreArchivo: `Historial_Seguimientos_${new Date().toISOString().split('T')[0]}.xlsx`,
                totalRegistros: exportData.length,
                detalles: {
                    totalSeguimientosGuardados: salesData.seguimientosGuardados.length,
                    rangoFechas: {
                        desde: salesData.seguimientosGuardados[0]?.fechaFormateada || '',
                        hasta: salesData.seguimientosGuardados[salesData.seguimientosGuardados.length - 1]?.fechaFormateada || ''
                    }
                }
            };

            if (!salesData.reportesSubidos) {
                salesData.reportesSubidos = [];
            }
            salesData.reportesSubidos.push(reporteData);
            saveData();

            alert(`✅ Historial exportado exitosamente!\n📊 Total de registros: ${exportData.length}`);
        }
    </script>
</body>
</html> 