<style>
	#phuyu_form.stock-migrador-wrap {
		background: linear-gradient(180deg, #eef3ff 0%, #f5f7fb 100%);
		color: #1f2937;
		font-family: inherit;
		min-height: calc(100vh - 120px);
		padding: 28px;
	}
	#phuyu_form.stock-migrador-wrap .hero {
		margin: 0 auto 18px;
		max-width: 1380px;
	}
	#phuyu_form.stock-migrador-wrap .hero h1 {
		font-size: 30px;
		font-weight: 800;
		margin: 0;
	}
	#phuyu_form.stock-migrador-wrap .hero p {
		color: #6b7280;
		line-height: 1.5;
		margin: 10px 0 0;
	}
	#phuyu_form.stock-migrador-wrap .migrador-container {
		margin: 0 auto;
		max-width: 1380px;
	}
	#phuyu_form.stock-migrador-wrap .migrador-grid {
		display: grid;
		gap: 18px;
		grid-template-columns: 1.2fr .8fr;
	}
	#phuyu_form.stock-migrador-wrap .migrador-card {
		background: #fff;
		border: 1px solid rgba(229, 231, 235, .8);
		border-radius: 22px;
		box-shadow: 0 20px 45px rgba(15, 23, 42, .06);
		margin-bottom: 18px;
		padding: 22px;
	}
	#phuyu_form.stock-migrador-wrap .section-title {
		align-items: center;
		display: flex;
		gap: 12px;
		justify-content: space-between;
		margin-bottom: 14px;
	}
	#phuyu_form.stock-migrador-wrap .section-title h2 {
		font-size: 18px;
		font-weight: 800;
		margin: 0;
	}
	#phuyu_form.stock-migrador-wrap .tag {
		background: #eff6ff;
		border-radius: 999px;
		color: #1d4ed8;
		display: inline-flex;
		font-size: 12px;
		font-weight: 800;
		padding: 6px 10px;
	}
	#phuyu_form.stock-migrador-wrap .subgrid,
	#phuyu_form.stock-migrador-wrap .two-col {
		display: grid;
		gap: 14px;
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
	#phuyu_form.stock-migrador-wrap .mapping-grid {
		display: grid;
		gap: 12px;
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}
	#phuyu_form.stock-migrador-wrap .summary-grid {
		display: grid;
		gap: 12px;
		grid-template-columns: repeat(4, minmax(0, 1fr));
	}
	#phuyu_form.stock-migrador-wrap .field label,
	#phuyu_form.stock-migrador-wrap .mapping-item label,
	#phuyu_form.stock-migrador-wrap .form-check-label {
		color: #374151;
		display: block;
		font-size: 13px;
		font-weight: 700;
		margin-bottom: 8px;
	}
	#phuyu_form.stock-migrador-wrap .field small,
	#phuyu_form.stock-migrador-wrap .muted {
		color: #6b7280;
		line-height: 1.4;
	}
	#phuyu_form.stock-migrador-wrap .form-control,
	#phuyu_form.stock-migrador-wrap .form-select {
		border: 1px solid #d1d5db;
		border-radius: 14px;
		box-shadow: none;
		min-height: 44px;
	}
	#phuyu_form.stock-migrador-wrap .pill-row {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		margin-top: 16px;
	}
	#phuyu_form.stock-migrador-wrap .pill {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 999px;
		font-size: 13px;
		font-weight: 700;
		padding: 9px 13px;
	}
	#phuyu_form.stock-migrador-wrap .mode-options {
		display: grid;
		gap: 10px;
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
	#phuyu_form.stock-migrador-wrap .mode-option {
		align-items: flex-start;
		background: #fbfdff;
		border: 1px solid #dbe3ef;
		border-radius: 14px;
		cursor: pointer;
		display: flex;
		gap: 10px;
		min-height: 74px;
		padding: 12px;
	}
	#phuyu_form.stock-migrador-wrap .mode-option input {
		margin-top: 3px;
	}
	#phuyu_form.stock-migrador-wrap .mode-option strong {
		display: block;
		font-size: 13px;
	}
	#phuyu_form.stock-migrador-wrap .actions {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		margin-top: 18px;
	}
	#phuyu_form.stock-migrador-wrap .alert-soft {
		background: #fff7ed;
		border: 1px solid #fdba74;
		border-radius: 16px;
		color: #9a3412;
		line-height: 1.5;
		padding: 16px 18px;
	}
	#phuyu_form.stock-migrador-wrap .confirm-backdrop {
		align-items: center;
		background: rgba(15, 23, 42, .52);
		display: flex;
		inset: 0;
		justify-content: center;
		padding: 24px;
		position: fixed;
		z-index: 9999;
	}
	#phuyu_form.stock-migrador-wrap .confirm-dialog {
		background: #fff;
		border: 1px solid #e5e7eb;
		border-radius: 18px;
		box-shadow: 0 28px 70px rgba(15, 23, 42, .24);
		max-width: 460px;
		overflow: hidden;
		width: 100%;
	}
	#phuyu_form.stock-migrador-wrap .confirm-head {
		align-items: center;
		display: flex;
		gap: 12px;
		padding: 20px 22px 12px;
	}
	#phuyu_form.stock-migrador-wrap .confirm-icon {
		align-items: center;
		background: #fff7ed;
		border: 1px solid #fed7aa;
		border-radius: 14px;
		color: #c2410c;
		display: flex;
		font-size: 22px;
		font-weight: 900;
		height: 44px;
		justify-content: center;
		width: 44px;
	}
	#phuyu_form.stock-migrador-wrap .confirm-icon.danger {
		background: #fef2f2;
		border-color: #fecaca;
		color: #b91c1c;
	}
	#phuyu_form.stock-migrador-wrap .confirm-icon.dark {
		background: #f8fafc;
		border-color: #cbd5e1;
		color: #0f172a;
	}
	#phuyu_form.stock-migrador-wrap .confirm-title {
		font-size: 18px;
		font-weight: 800;
		margin: 0;
	}
	#phuyu_form.stock-migrador-wrap .confirm-body {
		color: #4b5563;
		line-height: 1.5;
		padding: 0 22px 18px;
	}
	#phuyu_form.stock-migrador-wrap .confirm-body strong {
		color: #111827;
		display: block;
		margin-bottom: 6px;
	}
	#phuyu_form.stock-migrador-wrap .confirm-scope {
		background: #f8fafc;
		border: 1px solid #e5e7eb;
		border-radius: 14px;
		margin-top: 14px;
		padding: 12px;
	}
	#phuyu_form.stock-migrador-wrap .confirm-scope label {
		color: #374151;
		font-size: 13px;
		font-weight: 700;
		margin-bottom: 8px;
	}
	#phuyu_form.stock-migrador-wrap .line-list {
		display: grid;
		gap: 8px;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		margin-top: 10px;
		max-height: 190px;
		overflow: auto;
	}
	#phuyu_form.stock-migrador-wrap .line-option {
		align-items: center;
		background: #fff;
		border: 1px solid #e5e7eb;
		border-radius: 10px;
		display: flex;
		gap: 8px;
		padding: 8px;
	}
	#phuyu_form.stock-migrador-wrap .confirm-actions {
		background: #f8fafc;
		border-top: 1px solid #e5e7eb;
		display: flex;
		gap: 10px;
		justify-content: flex-end;
		padding: 14px 18px;
	}
	#phuyu_form.stock-migrador-wrap .mapping-item,
	#phuyu_form.stock-migrador-wrap .summary-item {
		background: #fbfdff;
		border: 1px solid #e5e7eb;
		border-radius: 16px;
		padding: 12px;
	}
	#phuyu_form.stock-migrador-wrap .required {
		color: #b91c1c;
		font-weight: 800;
	}
	#phuyu_form.stock-migrador-wrap .summary-item .label {
		color: #6b7280;
		font-size: 12px;
		margin-bottom: 4px;
	}
	#phuyu_form.stock-migrador-wrap .summary-item .value {
		font-size: 20px;
		font-weight: 800;
	}
	#phuyu_form.stock-migrador-wrap .table-box {
		border: 1px solid #e5e7eb;
		border-radius: 18px;
		cursor: grab;
		max-height: 560px;
		overflow: auto;
		position: relative;
		scrollbar-width: thin;
	}
	#phuyu_form.stock-migrador-wrap .table-box.is-dragging {
		cursor: grabbing;
		user-select: none;
	}
	#phuyu_form.stock-migrador-wrap table {
		border-collapse: collapse;
		margin: 0;
		min-width: 1050px;
		width: 100%;
	}
	#phuyu_form.stock-migrador-wrap th,
	#phuyu_form.stock-migrador-wrap td {
		border-bottom: 1px solid #e5e7eb;
		font-size: 13px;
		padding: 12px 10px;
		white-space: nowrap;
	}
	#phuyu_form.stock-migrador-wrap th {
		background: #f8fafc;
		color: #374151;
		position: sticky;
		top: 0;
		z-index: 1;
	}
	#phuyu_form.stock-migrador-wrap .excel-preview .sticky-col {
		background: #fff;
		position: sticky;
		z-index: 2;
	}
	#phuyu_form.stock-migrador-wrap .excel-preview th.sticky-col {
		background: #f8fafc;
		z-index: 4;
	}
	#phuyu_form.stock-migrador-wrap .excel-preview .sticky-num {
		left: 0;
		min-width: 56px;
		width: 56px;
	}
	#phuyu_form.stock-migrador-wrap .excel-preview .sticky-code {
		left: 56px;
		max-width: 160px;
		min-width: 160px;
		width: 160px;
	}
	#phuyu_form.stock-migrador-wrap .excel-preview .sticky-desc {
		left: 216px;
		max-width: 320px;
		min-width: 320px;
		overflow: hidden;
		text-overflow: ellipsis;
		width: 320px;
	}
	#phuyu_form.stock-migrador-wrap .excel-preview .sticky-shadow {
		box-shadow: 8px 0 14px rgba(15, 23, 42, .08);
	}
	@media (max-width: 1024px) {
		#phuyu_form.stock-migrador-wrap .migrador-grid,
		#phuyu_form.stock-migrador-wrap .subgrid,
		#phuyu_form.stock-migrador-wrap .two-col,
		#phuyu_form.stock-migrador-wrap .mode-options,
		#phuyu_form.stock-migrador-wrap .mapping-grid,
		#phuyu_form.stock-migrador-wrap .summary-grid {
			grid-template-columns: 1fr;
		}
	}
</style>

<div id="phuyu_form" class="stock-migrador-wrap">
	<div class="hero">
		<h1>Migrar / actualizar stock</h1>
		<p>
			Carga tu Excel, elige el almacen destino, detecta columnas, revisa una vista previa y aplica la cantidad como suma o como reemplazo del stock actual.
		</p>
	</div>

	<div class="migrador-container">
		<div class="migrador-grid">
			<div class="migrador-card">
				<div class="section-title">
					<h2>Configuracion de migracion</h2>
					<span class="tag">{{ campos.modo_stock == 'reemplazar' ? 'Reemplazar stock' : 'Sumar stock' }}</span>
				</div>

				<div class="subgrid">
					<div class="field">
						<label>Archivo Excel</label>
						<input type="file" class="form-control" accept=".xls,.xlsx,.csv" v-on:change="np_leer_archivo_migrarstock">
						<small>Se usara la primera hoja del archivo y podras mapear sus columnas.</small>
					</div>

					<div class="field">
						<label>Almacen destino</label>
						<select class="form-select" v-model="campos.codalmacen" v-on:change="np_cambio_almacen_migrarstock()">
							<option value="">-- Seleccionar almacen --</option>
							<option v-for="almacen in almacenes" v-bind:value="String(almacen.codalmacen)">
								{{ almacen.descripcion }}{{ almacen.sucursal ? ' - ' + almacen.sucursal : '' }}
							</option>
						</select>
						<small>El stock se actualizara solo en este almacen.</small>
					</div>

					<div class="field">
						<label>Buscar producto por</label>
						<select class="form-select" v-model="campos.buscar_por" v-on:change="np_cambio_buscar_por_migrarstock()">
							<option value="auto">Automatico: codigo, ID, barra o nombre</option>
							<option value="codigo">Codigo / ID producto</option>
							<option value="barra">Codigo de barra</option>
							<option value="nombre">Descripcion / nombre exacto</option>
						</select>
						<small>Para accesorios sin codigo de barra usa Descripcion / nombre exacto.</small>
					</div>

					<div class="field">
						<label>Modo de actualizacion</label>
						<div class="mode-options">
							<label class="mode-option">
								<input type="radio" value="sumar" v-model="campos.modo_stock" v-on:change="np_cambio_crear_productos_migrarstock()">
								<span>
									<strong>Sumar</strong>
									<small>Agrega la cantidad del Excel al stock actual.</small>
								</span>
							</label>
							<label class="mode-option">
								<input type="radio" value="reemplazar" v-model="campos.modo_stock" v-on:change="np_cambio_crear_productos_migrarstock()">
								<span>
									<strong>Reemplazar</strong>
									<small>Deja como stock final la cantidad del Excel.</small>
								</span>
							</label>
						</div>
					</div>

					<div class="field">
						<label>Reglas</label>
						<div class="two-col">
							<div class="form-check form-switch">
								<input class="form-check-input" type="checkbox" id="ignorar_stock_cero" v-model="campos.ignorar_stock_cero">
								<label class="form-check-label" for="ignorar_stock_cero">Omitir productos con stock actual 0</label>
							</div>
							<div class="form-check form-switch">
								<input class="form-check-input" type="checkbox" id="crear_productos" v-model="campos.crear_productos" v-on:change="np_cambio_crear_productos_migrarstock()">
								<label class="form-check-label" for="crear_productos">Crear productos no encontrados</label>
							</div>
							<div class="form-check form-switch">
								<input class="form-check-input" type="checkbox" id="reemplazar_nombre" v-model="campos.reemplazar_nombre" v-on:change="np_cambio_crear_productos_migrarstock()">
								<label class="form-check-label" for="reemplazar_nombre">Reemplazar nombre del producto</label>
							</div>
						</div>
					</div>

					<div class="field">
						<label>Productos no seleccionados</label>
						<select class="form-select" v-model="campos.limpieza_almacen">
							<option value="conservar">Conservar como estan</option>
							<option value="cero">Poner stock en 0</option>
							<option value="inactivar">Poner stock en 0 e inhabilitar en este almacen</option>
						</select>
						<small>Solo afecta el almacen destino. No desactiva el producto maestro ni otros almacenes.</small>
					</div>
				</div>

				<div class="pill-row">
					<div class="pill">Filas cargadas: {{ campos.filas.length }}</div>
					<div class="pill">Columnas detectadas: {{ campos.columnas.length }}</div>
					<div class="pill">Almacen: {{ np_nombre_almacen_migrarstock() }}</div>
					<div class="pill">Seleccionadas: {{ np_filas_migrarstock_seleccionadas() }}</div>
				</div>

				<div class="actions">
					<button type="button" class="btn btn-dark" v-on:click="np_preparar_catalogo_migrarstock()" v-bind:disabled="estado==1">
						Preparar catalogo
					</button>
					<button type="button" class="btn btn-outline-danger" v-on:click="np_poner_almacen_cero_migrarstock()" v-bind:disabled="estado==1 || !campos.codalmacen">
						Poner almacen en 0
					</button>
					<button type="button" class="btn btn-light" v-on:click="np_autodetectar_migrarstock()" v-bind:disabled="!campos.columnas.length">
						Autodetectar columnas
					</button>
					<button type="button" class="btn btn-primary" v-on:click="np_previsualizar_migrarstock()" v-bind:disabled="estado==1 || !campos.filas.length">
						Validar / Previsualizar
					</button>
					<button type="button" class="btn btn-warning" v-on:click="np_procesar_migrarstock()" v-bind:disabled="estado==1 || np_filas_migrarstock_seleccionadas()==0">
						Aplicar stock seleccionado
					</button>
					<button type="button" class="btn btn-outline-primary" v-on:click="np_toggle_migrarstock_todos()" v-bind:disabled="!campos.preview.length">
						{{ campos.seleccionar_todo ? 'Quitar seleccion' : 'Seleccionar todo' }}
					</button>
					<button type="button" class="btn btn-success" v-on:click="np_formato_migrarstock()">
						Descargar formato
					</button>
					<button type="button" class="btn btn-secondary" v-on:click="np_limpiar_migrarstock()">
						Limpiar
					</button>
					<button type="button" class="btn btn-danger" v-on:click="np_volver_migrarstock_productos()">
						Volver a Productos
					</button>
				</div>
			</div>

			<div class="migrador-card">
				<div class="section-title">
					<h2>Formato esperado</h2>
				</div>

				<div class="two-col">
					<div>
						<strong>Campos clave</strong>
						<p class="muted mb-0">Codigo/SKU y cantidad de stock.</p>
					</div>
					<div>
						<strong>Opcionales</strong>
						<p class="muted mb-0">Descripcion, unidad, codigo de barra, precios, marca, linea y familia si quieres crear faltantes.</p>
					</div>
				</div>

				<div class="alert-soft mt-3">
					El sistema intenta reconocer cabeceras como <strong>codigo</strong>, <strong>SKU</strong>,
					<strong>cantidad</strong>, <strong>stock</strong>, <strong>stock inicial</strong> o <strong>inventario</strong>.
				</div>
				<div class="alert-soft mt-3">
					<strong>Preparar catalogo</strong> asigna GENERAL/GENERAL/GENERICO a los productos, habilita la linea GENERAL para las sucursales activas y consolida duplicados por codigo de barra.
				</div>
			</div>
		</div>

		<div class="migrador-card" v-if="campos.columnas.length">
			<div class="section-title">
				<h2>Mapeo de columnas</h2>
				<span class="muted">{{ np_estado_mapeo_migrarstock() }}</span>
			</div>

			<div class="mapping-grid">
				<div class="mapping-item" v-for="campo in camposSistema">
					<label>
						{{ campo.label }}
						<span v-if="np_campo_requerido_migrarstock(campo)" class="required">*</span>
					</label>
					<select class="form-select" v-model="campos.mapeo[campo.key]">
						<option value="">-- No asignar --</option>
						<option v-for="col in campos.columnas" v-bind:value="col">{{ col }}</option>
					</select>
				</div>
			</div>
		</div>

		<div class="migrador-card" v-if="campos.filas.length">
			<div class="section-title">
				<h2>Vista previa del Excel</h2>
				<span class="muted">Mostrando {{ np_filas_excel_visibles_migrarstock() }} de {{ campos.filas.length }} filas</span>
			</div>

			<div class="table-box excel-preview">
				<table>
					<thead>
						<tr>
							<th class="sticky-col sticky-num">#</th>
							<th v-for="col in campos.columnas" v-bind:class="np_clase_columna_excel_migrarstock(col)">
								{{ col }}
							</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(fila, idx) in campos.filas.slice(0, campos.limite_excel)">
							<td class="sticky-col sticky-num">{{ idx + 1 }}</td>
							<td v-for="col in campos.columnas" v-bind:class="np_clase_columna_excel_migrarstock(col)">
								{{ fila[col] }}
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="actions" v-if="campos.limite_excel < campos.filas.length">
				<button type="button" class="btn btn-outline-primary" v-on:click="np_cargar_mas_excel_migrarstock()">
					Ver mas filas
				</button>
			</div>
		</div>

		<div class="migrador-card" v-if="campos.preview.length">
			<div class="section-title">
				<h2>Vista previa validada</h2>
				<span class="muted">Seleccione solo las filas que desea actualizar</span>
			</div>

			<div class="summary-grid mb-3" v-if="campos.resumen">
				<div class="summary-item">
					<div class="label">Filas leidas</div>
					<div class="value">{{ campos.resumen.total }}</div>
				</div>
				<div class="summary-item">
					<div class="label">Listas para aplicar</div>
					<div class="value">{{ campos.resumen.validas }}</div>
				</div>
				<div class="summary-item">
					<div class="label">Con error</div>
					<div class="value">{{ campos.resumen.errores }}</div>
				</div>
				<div class="summary-item">
					<div class="label">Omitidas</div>
					<div class="value">{{ campos.resumen.omitidas }}</div>
				</div>
			</div>

			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" id="marcar_migrarstock" v-model="campos.seleccionar_todo" v-on:change="np_marcar_migrarstock_todos()">
					<label class="form-check-label" for="marcar_migrarstock">Seleccionar todas las filas validas</label>
				</div>
				<div class="actions mt-0">
					<button type="button" class="btn btn-outline-primary" v-on:click="np_toggle_migrarstock_todos()">
						{{ campos.seleccionar_todo ? 'Quitar seleccion' : 'Seleccionar todo' }}
					</button>
					<button type="button" class="btn btn-primary" v-bind:disabled="estado==1 || np_filas_migrarstock_seleccionadas()==0" v-on:click="np_procesar_migrarstock()">
						Aplicar stock seleccionado
					</button>
				</div>
			</div>

			<div class="table-box">
				<table>
					<thead>
						<tr>
							<th>Usar</th>
							<th>Fila</th>
							<th>Codigo</th>
							<th>Producto</th>
							<th>Operacion</th>
							<th>Unidad</th>
							<th class="text-end">Stock actual</th>
							<th class="text-end">Cantidad Excel</th>
							<th class="text-end">Stock final</th>
							<th>Estado</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="fila in campos.preview" v-bind:class="fila.valido ? '' : 'table-warning'">
							<td class="text-center">
								<input type="checkbox" class="form-check-input" v-model="fila.seleccionado" v-bind:disabled="!fila.valido">
							</td>
							<td>{{ fila.fila }}</td>
							<td>{{ fila.codigo }}</td>
							<td>{{ fila.producto }}</td>
							<td>
								<span class="badge bg-info" v-if="fila.nuevo">Nuevo</span>
								<span class="badge bg-secondary" v-else>Actualizar</span>
							</td>
							<td>{{ fila.unidad }}</td>
							<td class="text-end">{{ fila.stock_actual }}</td>
							<td class="text-end">{{ fila.cantidad }}</td>
							<td class="text-end fw-bold">{{ fila.stock_final }}</td>
							<td>
								<span class="badge bg-success" v-if="fila.valido">OK</span>
								<span class="badge bg-warning text-dark" v-else>{{ fila.mensaje }}</span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="confirm-backdrop" v-if="confirmacion.visible">
		<div class="confirm-dialog">
			<div class="confirm-head">
				<div class="confirm-icon" v-bind:class="confirmacion.tipo">!</div>
				<h3 class="confirm-title">{{ confirmacion.titulo }}</h3>
			</div>
			<div class="confirm-body">
				<strong>{{ confirmacion.mensaje }}</strong>
				<div>{{ confirmacion.detalle }}</div>
				<div class="confirm-scope" v-if="confirmacion.accion == 'poner_almacen_cero'">
					<label>Alcance por linea</label>
					<select class="form-select" v-model="confirmacion.alcance_lineas">
						<option value="todos">Todas las lineas</option>
						<option value="solo">Solo lineas seleccionadas</option>
						<option value="excepto">Todas excepto las seleccionadas</option>
					</select>
					<div class="line-list" v-if="confirmacion.alcance_lineas != 'todos'">
						<label class="line-option" v-for="linea in lineas">
							<input type="checkbox" v-bind:value="String(linea.codlinea)" v-model="confirmacion.lineas">
							<span>{{ linea.descripcion }}</span>
						</label>
					</div>
				</div>
			</div>
			<div class="confirm-actions">
				<button type="button" class="btn btn-light" v-on:click="np_cerrar_confirmacion_migrarstock()">
					Cancelar
				</button>
				<button type="button" class="btn" v-bind:class="confirmacion.tipo == 'danger' ? 'btn-danger' : (confirmacion.tipo == 'dark' ? 'btn-dark' : 'btn-warning')" v-on:click="np_ejecutar_confirmacion_migrarstock()">
					{{ confirmacion.confirmar }}
				</button>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script>
	var almacenesMigrarStock = <?php echo json_encode($almacenes); ?>;
	var lineasMigrarStock = <?php echo json_encode($lineas); ?>;
	var campos = {
		filas: [],
		columnas: [],
		preview: [],
		resumen: null,
		seleccionar_todo: true,
		limite_excel: 80,
		ignorar_stock_cero: false,
		crear_productos: false,
		reemplazar_nombre: false,
		limpieza_almacen: "conservar",
		buscar_por: "auto",
		modo_stock: "sumar",
		codalmacen: "<?php echo (int) $codalmacenActual; ?>",
		mapeo: {
			codigo: '',
			cantidad: '',
			descripcion: '',
			unidad: '',
			codigo_barra: '',
			precio_compra: '',
			precio_venta: '',
			marca: '',
			linea: '',
			familia: ''
		}
	};
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_almacen/migrarstock.js"></script>
