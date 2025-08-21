<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Producción - Investigación</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            font-size: 28px;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #ddd;
            flex-wrap: wrap;
        }
        
        .tab {
            padding: 10px 20px;
            background-color: #e0e0e0;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px 5px 0 0;
        }
        
        .tab.active {
            background-color: #4CAF50;
            color: white;
        }
        
        .section {
            display: none;
        }
        
        .section.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .btn-secondary {
            background-color: #008CBA;
        }
        
        .btn-secondary:hover {
            background-color: #007399;
        }
        
        .btn-danger {
            background-color: #f44336;
            padding: 5px 10px !important;
            margin: 0 !important;
        }
        
        .btn-danger:hover {
            background-color: #da190b;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            overflow-x: auto;
            display: block;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            white-space: nowrap;
        }
        
        th {
            background-color: #4CAF50;
            color: white;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #f8f8f8;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #e0e0e0;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #4CAF50;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .alert {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-completado, .status-entregado {
            background-color: #4CAF50;
            color: white;
        }
        
        .status-en-proceso {
            background-color: #FF9800;
            color: white;
        }
        
        .status-con-observaciones {
            background-color: #f44336;
            color: white;
        }
        
        .status-en-revisión {
            background-color: #2196F3;
            color: white;
        }
        
        .filter-section {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .summary-section {
            background-color: #e8f4f8;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #2196F3;
        }
        
        .summary-title {
            font-size: 18px;
            font-weight: bold;
            color: #1976D2;
            margin-bottom: 15px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .summary-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 SISTEMA DE CONTROL DE PRODUCCIÓN - INVESTIGACIÓN</h1>
        
        <div id="alertMessage" class="alert" style="display: none;"></div>
        
        <div class="tabs">
            <button class="tab active" onclick="showSection('dashboard')">📊 DASHBOARD</button>
            <button class="tab" onclick="showSection('registro')">📝 NUEVO REGISTRO</button>
            <button class="tab" onclick="showSection('lista')">📋 VER REGISTROS</button>
            <button class="tab" onclick="showSection('resumen')">📈 RESUMEN SEMANAL</button>
        </div>
        
        <!-- DASHBOARD -->
        <div id="dashboard" class="section active">
            <h2>Resumen del Día - <span id="fechaHoy"></span></h2>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number" id="horasHoy">0</div>
                    <div class="stat-label">Horas Trabajadas Hoy</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="proyectosActivos">0</div>
                    <div class="stat-label">Proyectos Activos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="completadosHoy">0</div>
                    <div class="stat-label">Completados Hoy</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalAuxiliares">4</div>
                    <div class="stat-label">Auxiliares Activos</div>
                </div>
            </div>
            
            <h3>Productividad por Auxiliar (Últimos 7 días)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Auxiliar</th>
                        <th>Horas Totales</th>
                        <th>Proyectos Completados</th>
                        <th>En Proceso</th>
                        <th>Eficiencia %</th>
                    </tr>
                </thead>
                <tbody id="tablaProductividad">
                    <tr>
                        <td colspan="5" style="text-align: center;">No hay datos aún. Empieza agregando registros.</td>
                    </tr>
                </tbody>
            </table>
            
            <h3 style="margin-top: 30px;">Proyectos que Requieren Atención</h3>
            <table>
                <thead>
                    <tr>
                        <th>Proyecto</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Último Movimiento</th>
                        <th>Auxiliar</th>
                    </tr>
                </thead>
                <tbody id="proyectosAtencion">
                    <tr>
                        <td colspan="5" style="text-align: center;">Sin proyectos pendientes</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- NUEVO REGISTRO -->
        <div id="registro" class="section">
            <h2>Agregar Nuevo Registro de Trabajo</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Fecha: *</label>
                    <input type="date" id="fecha" value="">
                </div>
                <div class="form-group">
                    <label>Auxiliar: *</label>
                    <select id="auxiliar">
                        <option value="">Seleccionar...</option>
                        <option value="Juan Pérez">Juan Pérez</option>
                        <option value="María García">María García</option>
                        <option value="Carlos López">Carlos López</option>
                        <option value="Ana Martínez">Ana Martínez</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Proyecto/Informe: *</label>
                    <input type="text" id="proyecto" placeholder="Ej: Investigación de Mercado X">
                </div>
                <div class="form-group">
                    <label>Cliente: *</label>
                    <input type="text" id="cliente" placeholder="Ej: Empresa ABC">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Hora Inicio: *</label>
                    <input type="time" id="horaInicio">
                </div>
                <div class="form-group">
                    <label>Hora Fin: *</label>
                    <input type="time" id="horaFin">
                </div>
            </div>
            
            <div class="form-group">
                <label>Link de Google Drive:</label>
                <input type="url" id="linkDrive" placeholder="https://drive.google.com/...">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Estado: *</label>
                    <select id="estado">
                        <option value="En Proceso">En Proceso</option>
                        <option value="Completado">Completado</option>
                        <option value="Con Observaciones">Con Observaciones</option>
                        <option value="En Revisión">En Revisión</option>
                        <option value="Entregado">Entregado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fecha de Entrega Comprometida:</label>
                    <input type="date" id="fechaEntrega">
                </div>
            </div>
            
            <div class="form-group">
                <label>Observaciones:</label>
                <textarea id="observaciones" rows="3" placeholder="Notas adicionales..."></textarea>
            </div>
            
            <button onclick="guardarRegistro()">💾 GUARDAR REGISTRO</button>
            <p style="margin-top: 10px; color: #666; font-size: 14px;">* Campos obligatorios</p>
        </div>
        
        <!-- LISTA DE REGISTROS -->
        <div id="lista" class="section">
            <h2>Registros de Producción</h2>
            
            <div class="filter-section">
                <div class="filter-group">
                    <label>Filtrar por Fecha:</label>
                    <input type="date" id="filtroFecha" onchange="aplicarFiltros()">
                </div>
                <div class="filter-group">
                    <label>Filtrar por Auxiliar:</label>
                    <select id="filtroAuxiliar" onchange="aplicarFiltros()">
                        <option value="">Todos</option>
                        <option value="Juan Pérez">Juan Pérez</option>
                        <option value="María García">María García</option>
                        <option value="Carlos López">Carlos López</option>
                        <option value="Ana Martínez">Ana Martínez</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Filtrar por Estado:</label>
                    <select id="filtroEstado" onchange="aplicarFiltros()">
                        <option value="">Todos</option>
                        <option value="En Proceso">En Proceso</option>
                        <option value="Completado">Completado</option>
                        <option value="Con Observaciones">Con Observaciones</option>
                        <option value="En Revisión">En Revisión</option>
                        <option value="Entregado">Entregado</option>
                    </select>
                </div>
                <button class="btn-secondary" onclick="limpiarFiltros()">Limpiar Filtros</button>
            </div>
            
            <div style="margin-bottom: 20px;">
                <button class="btn-secondary" onclick="exportarCSV()">📥 Exportar a Excel (CSV)</button>
            </div>
            
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Auxiliar</th>
                            <th>Proyecto</th>
                            <th>Cliente</th>
                            <th>Horario</th>
                            <th>Horas</th>
                            <th>Estado</th>
                            <th>Fecha Entrega</th>
                            <th>Link</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaRegistros">
                        <tr>
                            <td colspan="10" style="text-align: center;">No hay registros aún</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- RESUMEN SEMANAL -->
        <div id="resumen" class="section">
            <h2>Resumen Semanal</h2>
            
            <div class="form-row" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label>Seleccionar Semana:</label>
                    <input type="week" id="semanaSeleccionada" onchange="generarResumenSemanal()">
                </div>
            </div>
            
            <div id="resumenSemanalContent">
                <p style="text-align: center;">Selecciona una semana para ver el resumen</p>
            </div>
        </div>
    </div>
    
    <script>
        // Datos almacenados
        let registros = JSON.parse(localStorage.getItem('registrosProduccion')) || [];
        let registrosFiltrados = [...registros];
        
        // Establecer fecha de hoy
        const hoy = new Date();
        document.getElementById('fecha').value = hoy.toISOString().split('T')[0];
        document.getElementById('fechaHoy').textContent = hoy.toLocaleDateString('es-ES', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        // Establecer semana actual
        const year = hoy.getFullYear();
        const month = String(hoy.getMonth() + 1).padStart(2, '0');
        const day = String(hoy.getDate()).padStart(2, '0');
        const weekNumber = getWeekNumber(hoy);
        document.getElementById('semanaSeleccionada').value = `${year}-W${String(weekNumber).padStart(2, '0')}`;
        
        // Funciones de navegación
        function showSection(section) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            
            document.getElementById(section).classList.add('active');
            event.target.classList.add('active');
            
            if (section === 'dashboard') {
                actualizarDashboard();
            } else if (section === 'lista') {
                mostrarRegistros();
            } else if (section === 'resumen') {
                generarResumenSemanal();
            }
        }
        
        // Calcular número de semana
        function getWeekNumber(date) {
            const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
            const dayNum = d.getUTCDay() || 7;
            d.setUTCDate(d.getUTCDate() + 4 - dayNum);
            const yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
            return Math.ceil((((d - yearStart) / 86400000) + 1)/7);
        }
        
        // Calcular horas trabajadas
        function calcularHoras(inicio, fin) {
            const [hi, mi] = inicio.split(':').map(Number);
            const [hf, mf] = fin.split(':').map(Number);
            const minutos = (hf * 60 + mf) - (hi * 60 + mi);
            return Math.round((minutos / 60) * 10) / 10;
        }
        
        // Mostrar alerta
        function mostrarAlerta(mensaje, tipo = 'success') {
            const alert = document.getElementById('alertMessage');
            alert.textContent = mensaje;
            alert.style.display = 'block';
            alert.style.backgroundColor = tipo === 'success' ? '#4CAF50' : '#f44336';
            
            setTimeout(() => {
                alert.style.display = 'none';
            }, 3000);
        }
        
        // Guardar registro
        function guardarRegistro() {
            const fecha = document.getElementById('fecha').value;
            const auxiliar = document.getElementById('auxiliar').value;
            const proyecto = document.getElementById('proyecto').value;
            const cliente = document.getElementById('cliente').value;
            const horaInicio = document.getElementById('horaInicio').value;
            const horaFin = document.getElementById('horaFin').value;
            const linkDrive = document.getElementById('linkDrive').value;
            const estado = document.getElementById('estado').value;
            const fechaEntrega = document.getElementById('fechaEntrega').value;
            const observaciones = document.getElementById('observaciones').value;
            
            if (!fecha || !auxiliar || !proyecto || !cliente || !horaInicio || !horaFin) {
                mostrarAlerta('⚠️ Por favor llena todos los campos obligatorios', 'error');
                return;
            }
            
            const horas = calcularHoras(horaInicio, horaFin);
            if (horas <= 0) {
                mostrarAlerta('⚠️ La hora de fin debe ser mayor que la hora de inicio', 'error');
                return;
            }
            
            const registro = {
                id: Date.now(),
                fecha,
                auxiliar,
                proyecto,
                cliente,
                horaInicio,
                horaFin,
                horas,
                linkDrive,
                estado,
                fechaEntrega,
                observaciones,
                fechaRegistro: new Date().toISOString()
            };
            
            registros.push(registro);
            localStorage.setItem('registrosProduccion', JSON.stringify(registros));
            
            // Limpiar formulario
            document.getElementById('auxiliar').value = '';
            document.getElementById('proyecto').value = '';
            document.getElementById('cliente').value = '';
            document.getElementById('horaInicio').value = '';
            document.getElementById('horaFin').value = '';
            document.getElementById('linkDrive').value = '';
            document.getElementById('estado').value = 'En Proceso';
            document.getElementById('fechaEntrega').value = '';
            document.getElementById('observaciones').value = '';
            
            mostrarAlerta('✅ Registro guardado exitosamente!');
            
            // Actualizar dashboard
            showSection('dashboard');
            document.querySelectorAll('.tab')[0].classList.add('active');
            document.querySelectorAll('.tab')[1].classList.remove('active');
        }
        
        // Aplicar filtros
        function aplicarFiltros() {
            const filtroFecha = document.getElementById('filtroFecha').value;
            const filtroAuxiliar = document.getElementById('filtroAuxiliar').value;
            const filtroEstado = document.getElementById('filtroEstado').value;
            
            registrosFiltrados = registros.filter(r => {
                const cumpleFecha = !filtroFecha || r.fecha === filtroFecha;
                const cumpleAuxiliar = !filtroAuxiliar || r.auxiliar === filtroAuxiliar;
                const cumpleEstado = !filtroEstado || r.estado === filtroEstado;
                
                return cumpleFecha && cumpleAuxiliar && cumpleEstado;
            });
            
            mostrarRegistros();
        }
        
        // Limpiar filtros
        function limpiarFiltros() {
            document.getElementById('filtroFecha').value = '';
            document.getElementById('filtroAuxiliar').value = '';
            document.getElementById('filtroEstado').value = '';
            registrosFiltrados = [...registros];
            mostrarRegistros();
        }
        
        // Mostrar registros en la tabla
        function mostrarRegistros() {
            const tabla = document.getElementById('tablaRegistros');
            
            if (registrosFiltrados.length === 0) {
                tabla.innerHTML = '<tr><td colspan="10" style="text-align: center;">No hay registros que coincidan con los filtros</td></tr>';
                return;
            }
            
            // Ordenar por fecha descendente
            registrosFiltrados.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
            
            tabla.innerHTML = registrosFiltrados.map(r => `
                <tr>
                    <td>${formatearFecha(r.fecha)}</td>
                    <td>${r.auxiliar}</td>
                    <td>${r.proyecto}</td>
                    <td>${r.cliente}</td>
                    <td>${r.horaInicio} - ${r.horaFin}</td>
                    <td>${r.horas}h</td>
                    <td><span class="status status-${r.estado.toLowerCase().replace(/ /g, '-')}">${r.estado}</span></td>
                    <td>${r.fechaEntrega ? formatearFecha(r.fechaEntrega) : '-'}</td>
                    <td>${r.linkDrive ? `<a href="${r.linkDrive}" target="_blank">Ver</a>` : '-'}</td>
                    <td>
                        <button class="btn-danger" onclick="eliminarRegistro(${r.id})">Eliminar</button>
                    </td>
                </tr>
            `).join('');
        }
        
        // Formatear fecha
        function formatearFecha(fecha) {
            const date = new Date(fecha + 'T00:00:00');
            return date.toLocaleDateString('es-ES', { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric' 
            });
        }
        
        // Eliminar registro
        function eliminarRegistro(id) {
            if (confirm('¿Estás seguro de eliminar este registro?')) {
                registros = registros.filter(r => r.id !== id);
                registrosFiltrados = registrosFiltrados.filter(r => r.id !== id);
                localStorage.setItem('registrosProduccion', JSON.stringify(registros));
                mostrarRegistros();
                mostrarAlerta('Registro eliminado');
            }
        }
        
        // Actualizar dashboard
        function actualizarDashboard() {
            const hoy = new Date().toISOString().split('T')[0];
            const registrosHoy = registros.filter(r => r.fecha === hoy);
            
            // Estadísticas generales
            document.getElementById('horasHoy').textContent = registrosHoy.reduce((sum, r) => sum + r.horas, 0).toFixed(1);
            document.getElementById('proyectosActivos').textContent = registros.filter(r => r.estado === 'En Proceso').length;
            document.getElementById('completadosHoy').textContent = registrosHoy.filter(r => r.estado === 'Completado' || r.estado === 'Entregado').length;
            
            // Productividad por auxiliar (últimos 7 días)
            const hace7Dias = new Date();
            hace7Dias.setDate(hace7Dias.getDate() - 7);
            const registrosRecientes = registros.filter(r => new Date(r.fecha) >= hace7Dias);
            
            const auxiliares = ['Juan Pérez', 'María García', 'Carlos López', 'Ana Martínez'];
            const tablaProductividad = document.getElementById('tablaProductividad');
            
            const productividad = auxiliares.map(aux => {
                const registrosAux = registrosRecientes.filter(r => r.auxiliar === aux);
                const totalHoras = registrosAux.reduce((sum, r) => sum + r.horas, 0);
                const completados = registrosAux.filter(r => r.estado === 'Completado' || r.estado === 'Entregado').length;
                const enProceso = registrosAux.filter(r => r.estado === 'En Proceso').length;
                const totalProyectos = registrosAux.length;
                
                return {
                    nombre: aux,
                    horas: totalHoras,
                    completados: completados,
                    enProceso: enProceso,
                    eficiencia: totalProyectos > 0 ? (completados / totalProyectos * 100).toFixed(0) : 0
                };
            });
            
            if (registrosRecientes.length > 0) {
                tablaProductividad.innerHTML = productividad.map(p => `
                    <tr>
                        <td>${p.nombre}</td>
                        <td>${p.horas.toFixed(1)}h</td>
                        <td>${p.completados}</td>
                        <td>${p.enProceso}</td>
                        <td>${p.eficiencia}%</td>
                    </tr>
                `).join('');
            }
            
            // Proyectos que requieren atención
            const proyectosConObservaciones = registros.filter(r => 
                r.estado === 'Con Observaciones' || 
                (r.estado === 'En Proceso' && r.fechaEntrega && new Date(r.fechaEntrega) < new Date())
            );
            
            const tablaAtencion = document.getElementById('proyectosAtencion');
            if (proyectosConObservaciones.length > 0) {
                // Ordenar por fecha más reciente
                proyectosConObservaciones.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
                
                tablaAtencion.innerHTML = proyectosConObservaciones.slice(0, 5).map(r => `
                    <tr>
                        <td>${r.proyecto}</td>
                        <td>${r.cliente}</td>
                        <td><span class="status status-${r.estado.toLowerCase().replace(/ /g, '-')}">${r.estado}</span></td>
                        <td>${formatearFecha(r.fecha)}</td>
                        <td>${r.auxiliar}</td>
                    </tr>
                `).join('');
            } else {
                tablaAtencion.innerHTML = '<tr><td colspan="5" style="text-align: center;">Sin proyectos pendientes</td></tr>';
            }
        }
        
        // Generar resumen semanal
        function generarResumenSemanal() {
            const semanaInput = document.getElementById('semanaSeleccionada').value;
            if (!semanaInput) return;
            
            const [year, week] = semanaInput.split('-W');
            const startDate = getDateOfWeek(parseInt(week), parseInt(year));
            const endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + 6);
            
            const registrosSemana = registros.filter(r => {
                const fecha = new Date(r.fecha);
                return fecha >= startDate && fecha <= endDate;
            });
            
            const resumenContent = document.getElementById('resumenSemanalContent');
            
            if (registrosSemana.length === 0) {
                resumenContent.innerHTML = '<p style="text-align: center;">No hay registros para la semana seleccionada</p>';
                return;
            }
            
            // Calcular métricas
            const totalHoras = registrosSemana.reduce((sum, r) => sum + r.horas, 0);
            const completados = registrosSemana.filter(r => r.estado === 'Completado' || r.estado === 'Entregado').length;
            const enProceso = registrosSemana.filter(r => r.estado === 'En Proceso').length;
            const conObservaciones = registrosSemana.filter(r => r.estado === 'Con Observaciones').length;
            
            // Productividad por auxiliar
            const auxiliares = ['Juan Pérez', 'María García', 'Carlos López', 'Ana Martínez'];
            const productiividadSemanal = auxiliares.map(aux => {
                const registrosAux = registrosSemana.filter(r => r.auxiliar === aux);
                return {
                    nombre: aux,
                    horas: registrosAux.reduce((sum, r) => sum + r.horas, 0),
                    proyectos: registrosAux.length
                };
            }).filter(a => a.proyectos > 0);
            
            // Proyectos por cliente
            const proyectosPorCliente = {};
            registrosSemana.forEach(r => {
                if (!proyectosPorCliente[r.cliente]) {
                    proyectosPorCliente[r.cliente] = {
                        proyectos: new Set(),
                        horas: 0
                    };
                }
                proyectosPorCliente[r.cliente].proyectos.add(r.proyecto);
                proyectosPorCliente[r.cliente].horas += r.horas;
            });
            
            resumenContent.innerHTML = `
                <div class="summary-section">
                    <div class="summary-title">
                        Semana del ${startDate.toLocaleDateString('es-ES')} al ${endDate.toLocaleDateString('es-ES')}
                    </div>
                    
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="summary-value">${totalHoras.toFixed(1)}h</div>
                            <div class="summary-label">Horas Totales</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-value">${registrosSemana.length}</div>
                            <div class="summary-label">Total Trabajos</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-value">${completados}</div>
                            <div class="summary-label">Completados</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-value">${enProceso}</div>
                            <div class="summary-label">En Proceso</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-value">${conObservaciones}</div>
                            <div class="summary-label">Con Observaciones</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-value">${((completados / registrosSemana.length) * 100).toFixed(0)}%</div>
                            <div class="summary-label">Tasa Completado</div>
                        </div>
                    </div>
                </div>
                
                <h3>Productividad por Auxiliar</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Auxiliar</th>
                            <th>Horas Trabajadas</th>
                            <th>Proyectos</th>
                            <th>Promedio h/proyecto</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${productiividadSemanal.map(p => `
                            <tr>
                                <td>${p.nombre}</td>
                                <td>${p.horas.toFixed(1)}h</td>
                                <td>${p.proyectos}</td>
                                <td>${(p.horas / p.proyectos).toFixed(1)}h</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                
                <h3>Trabajo por Cliente</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Proyectos</th>
                            <th>Horas Totales</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Object.entries(proyectosPorCliente).map(([cliente, data]) => `
                            <tr>
                                <td>${cliente}</td>
                                <td>${data.proyectos.size}</td>
                                <td>${data.horas.toFixed(1)}h</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                
                <div style="margin-top: 20px;">
                    <button class="btn-secondary" onclick="exportarResumenSemanal()">📥 Exportar Resumen Semanal</button>
                </div>
            `;
        }
        
        // Obtener fecha de inicio de semana
        function getDateOfWeek(week, year) {
            const simple = new Date(year, 0, 1 + (week - 1) * 7);
            const dow = simple.getDay();
            const ISOweekStart = simple;
            if (dow <= 4)
                ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
            else
                ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
            return ISOweekStart;
        }
        
        // Exportar a CSV
        function exportarCSV() {
            if (registros.length === 0) {
                mostrarAlerta('No hay registros para exportar', 'error');
                return;
            }
            
            let csv = 'Fecha,Auxiliar,Proyecto,Cliente,Hora Inicio,Hora Fin,Horas,Link Drive,Estado,Fecha Entrega,Observaciones\n';
            
            registros.forEach(r => {
                csv += `${r.fecha},"${r.auxiliar}","${r.proyecto}","${r.cliente}",${r.horaInicio},${r.horaFin},${r.horas},"${r.linkDrive || ''}","${r.estado}","${r.fechaEntrega || ''}","${r.observaciones || ''}"\n`;
            });
            
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('download', `produccion_${new Date().toISOString().split('T')[0]}.csv`);
            a.click();
            
            mostrarAlerta('✅ Archivo CSV descargado');
        }
        
        // Exportar resumen semanal
        function exportarResumenSemanal() {
            const semanaInput = document.getElementById('semanaSeleccionada').value;
            if (!semanaInput) return;
            
            const [year, week] = semanaInput.split('-W');
            const startDate = getDateOfWeek(parseInt(week), parseInt(year));
            const endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + 6);
            
            const registrosSemana = registros.filter(r => {
                const fecha = new Date(r.fecha);
                return fecha >= startDate && fecha <= endDate;
            });
            
            if (registrosSemana.length === 0) {
                mostrarAlerta('No hay datos para exportar', 'error');
                return;
            }
            
            let csv = 'RESUMEN SEMANAL\n';
            csv += `Semana del ${startDate.toLocaleDateString('es-ES')} al ${endDate.toLocaleDateString('es-ES')}\n\n`;
            csv += 'DETALLE DE REGISTROS\n';
            csv += 'Fecha,Auxiliar,Proyecto,Cliente,Horas,Estado\n';
            
            registrosSemana.forEach(r => {
                csv += `${r.fecha},"${r.auxiliar}","${r.proyecto}","${r.cliente}",${r.horas},"${r.estado}"\n`;
            });
            
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('download', `resumen_semanal_${year}_W${week}.csv`);
            a.click();
            
            mostrarAlerta('✅ Resumen semanal exportado');
        }
        
        // Cargar dashboard al inicio
        actualizarDashboard();
    </script>
</body>
</html>