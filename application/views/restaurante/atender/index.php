<?php
$sucursalActual = $sucursal[0] ?? ['codcomprobantetipo' => '', 'seriecomprobante' => ''];
$codControlDiario = $_SESSION['phuyu_codcontroldiario'] ?? 0;
$stockAlmacen = $_SESSION['phuyu_stockalmacen'] ?? 0;
$itemRepetir = $_SESSION['phuyu_itemrepetir'] ?? 0;
$igvSunat = $_SESSION['phuyu_igv'] ?? 0;
$icbperSunat = $_SESSION['phuyu_icbper'] ?? 0;
?>
<style>
	#phuyu_operacion.phuyu-restobar-velzon {
		--phuyu-primary: #405189;
		--phuyu-soft: #f3f6f9;
		--phuyu-border: #e9ebec;
		--phuyu-text: #212529;
		color: var(--phuyu-text);
	}

	#phuyu_operacion.phuyu-restobar-velzon .page-title-box {
		padding: 0 0 14px;
	}

	#phuyu_operacion.phuyu-restobar-velzon .page-title-box h4 {
		color: #2f3a56;
		font-size: 1.05rem;
		font-weight: 700;
		letter-spacing: .2px;
		margin: 0;
	}

	#phuyu_operacion.phuyu-restobar-velzon .breadcrumb {
		font-size: .78rem;
		margin: 4px 0 0;
	}

	#phuyu_operacion.phuyu-restobar-velzon .card {
		border: 0;
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, .08);
	}

	#phuyu_operacion.phuyu-restobar-velzon .card-header {
		align-items: center;
		background: #fff;
		border-bottom: 1px solid var(--phuyu-border);
		display: flex;
		gap: .75rem;
		min-height: 52px;
		padding: .75rem 1rem;
	}

	#phuyu_operacion.phuyu-restobar-velzon .card-title {
		color: #343a40;
		font-size: .9rem;
		font-weight: 700;
		margin: 0;
	}

	#phuyu_operacion.phuyu-restobar-velzon .card-subtitle {
		color: #878a99;
		font-size: .72rem;
		margin: 2px 0 0;
	}

	#phuyu_operacion.phuyu-restobar-velzon .form-control,
	#phuyu_operacion.phuyu-restobar-velzon .form-select {
		border-color: rgba(64, 81, 137, .16);
		border-radius: 6px;
		font-size: .84rem;
		min-height: 36px;
	}

	#phuyu_operacion.phuyu-restobar-velzon .form-control:focus,
	#phuyu_operacion.phuyu-restobar-velzon .form-select:focus {
		border-color: var(--phuyu-primary);
		box-shadow: 0 0 0 .15rem rgba(64, 81, 137, .12);
	}

	#phuyu_operacion.phuyu-restobar-velzon .btn {
		border-radius: 6px;
		font-weight: 600;
	}

	#phuyu_operacion.phuyu-restobar-velzon .btn-icon {
		align-items: center;
		display: inline-flex;
		height: 36px;
		justify-content: center;
		padding: 0;
		width: 36px;
	}

	#phuyu_operacion.phuyu-restobar-velzon .btn-soft-primary {
		background: rgba(64, 81, 137, .1);
		border-color: transparent;
		color: var(--phuyu-primary);
	}

	#phuyu_operacion.phuyu-restobar-velzon .btn-soft-success {
		background: rgba(10, 179, 156, .12);
		border-color: transparent;
		color: #0ab39c;
	}

	#phuyu_operacion.phuyu-restobar-velzon .btn-soft-info {
		background: rgba(41, 156, 219, .12);
		border-color: transparent;
		color: #299cdb;
	}

	#phuyu_operacion.phuyu-restobar-velzon .btn-soft-warning {
		background: rgba(247, 184, 75, .15);
		border-color: transparent;
		color: #b97900;
	}

	#phuyu_operacion.phuyu-restobar-velzon .btn-soft-danger {
		background: rgba(240, 101, 72, .12);
		border-color: transparent;
		color: #f06548;
	}

	.restobar-panel-body {
		height: 330px;
		overflow: auto;
	}

	.mesas-grid {
		display: grid;
		gap: .7rem;
		grid-template-columns: repeat(auto-fill, minmax(128px, 1fr));
		padding: .85rem;
	}

	.mesa-tile {
		background: #fff;
		border: 1px solid var(--phuyu-border);
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, .05);
		cursor: pointer;
		min-height: 96px;
		padding: .85rem .65rem .65rem;
		position: relative;
		text-align: center;
		transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
		user-select: none;
	}

	.mesa-tile:hover,
	.mesa-tile.mesa-activa {
		border-color: var(--phuyu-primary);
		box-shadow: 0 8px 18px rgba(64, 81, 137, .13);
		transform: translateY(-1px);
	}

	.mesa-tile.mesa-cambio-activo {
		border-color: #f7b84b;
		box-shadow: 0 0 0 3px rgba(247, 184, 75, .18);
	}

	.mesa-tile h6 {
		color: #878a99;
		font-size: .72rem;
		font-weight: 700;
		margin: 0;
		text-transform: uppercase;
	}

	.mesa-tile h3 {
		color: #2f3a56;
		font-size: 1.75rem;
		font-weight: 800;
		margin: .2rem 0;
	}

	.mesa-badge {
		border-radius: 999px;
		font-size: .62rem;
		font-weight: 800;
		line-height: 1;
		padding: .28rem .45rem;
		position: absolute;
		right: .45rem;
		top: .45rem;
	}

	.mesa-badge.ocupada {
		background: rgba(240, 101, 72, .12);
		color: #d84b2a;
	}

	.mesa-badge.libre {
		background: rgba(10, 179, 156, .12);
		color: #099885;
	}

	.mesa-tile .hint {
		color: #878a99;
		font-size: .7rem;
		margin-top: .2rem;
	}

	#phuyu_restaurante {
		height: 330px !important;
		overflow-x: hidden !important;
		overflow-y: auto !important;
		padding: .85rem !important;
	}

	.lineas-list {
		display: grid;
		gap: .5rem;
		padding: .85rem;
	}

	.linea-btn {
		align-items: center;
		background: #fff;
		border: 1px solid var(--phuyu-border);
		border-radius: 8px;
		color: #495057;
		cursor: pointer;
		display: flex;
		font-size: .78rem;
		font-weight: 700;
		gap: .45rem;
		justify-content: flex-start;
		padding: .55rem .65rem;
		text-transform: uppercase;
		transition: background .18s ease, border-color .18s ease, color .18s ease;
	}

	.linea-btn:hover {
		background: rgba(64, 81, 137, .08);
		border-color: rgba(64, 81, 137, .26);
		color: var(--phuyu-primary);
	}

	.linea-swatch {
		border-radius: 50%;
		box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .08);
		display: inline-block;
		flex: 0 0 auto;
		height: 10px;
		width: 10px;
	}

	.action-grid {
		display: grid;
		gap: .6rem;
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}

	.action-grid .btn {
		align-items: center;
		display: inline-flex;
		font-size: .74rem;
		justify-content: center;
		min-height: 36px;
		white-space: normal;
	}

	.pedido-toolbar {
		display: grid;
		gap: .6rem;
		grid-template-columns: 1.1fr 42px 1.1fr .9fr;
	}

	.detalle {
		min-height: 210px;
		overflow: auto;
	}

	.detalle .table {
		font-size: .78rem;
		margin-bottom: 0;
		min-width: 920px;
	}

	.detalle .table thead th {
		background: var(--phuyu-soft);
		color: #495057;
		font-size: .72rem;
		font-weight: 800;
		position: sticky;
		text-transform: uppercase;
		top: 0;
		z-index: 2;
	}

	.detalle .form-control {
		font-size: .78rem;
		min-height: 30px;
		padding: .2rem .4rem;
	}

	.total-pill {
		align-items: center;
		background: #0ab39c;
		border-radius: 8px;
		color: #fff;
		display: inline-flex;
		font-size: 1rem;
		font-weight: 800;
		gap: .45rem;
		padding: .6rem .9rem;
	}

	.modal-content {
		border: 0;
		border-radius: 8px;
		box-shadow: 0 12px 32px rgba(15, 23, 42, .16);
	}

	.modal-header {
		background: #fff;
		border-bottom: 1px solid var(--phuyu-border);
	}

	.modal-title {
		color: #343a40;
		font-weight: 700;
	}

	.payment-total {
		background: linear-gradient(135deg, #0ab39c 0%, #299cdb 100%);
		border-radius: 8px;
		color: #fff;
		font-size: 1.35rem;
		font-weight: 800;
		padding: .8rem 1rem;
		text-align: center;
	}

	.payment-box {
		background: var(--phuyu-soft);
		border: 1px solid var(--phuyu-border);
		border-radius: 8px;
		padding: .9rem;
	}

	@media (max-width: 1199.98px) {
		.restobar-panel-body,
		#phuyu_restaurante {
			height: 300px !important;
		}

		.action-grid,
		.pedido-toolbar {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 575.98px) {
		.restobar-panel-body,
		#phuyu_restaurante {
			height: 55vh !important;
		}

		.action-grid,
		.pedido-toolbar {
			grid-template-columns: 1fr;
		}
	}
</style>

<div id="phuyu_operacion" class="phuyu-restobar-velzon">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_pedido()">
		<input type="hidden" id="comprobante" value="<?php echo $sucursalActual['codcomprobantetipo']; ?>">
		<input type="hidden" id="serie" value="<?php echo $sucursalActual['seriecomprobante']; ?>">
		<input type="hidden" id="stockalmacen" value="<?php echo $stockAlmacen; ?>">
		<input type="hidden" id="itemrepetir" value="<?php echo $itemRepetir; ?>">
		<input type="hidden" id="igvsunat" value="<?php echo $igvSunat; ?>">
		<input type="hidden" id="icbpersunat" value="<?php echo $icbperSunat; ?>">
		<input type="hidden" id="fechapedido" value="<?php echo date('Y-m-d'); ?>">
		<input type="hidden" id="sessioncaja" value="<?php echo $codControlDiario; ?>">

		<div class="row g-3 align-items-center page-title-box">
			<div class="col">
				<h4><i class="bi bi-shop-window me-1"></i> Atencion Restobar</h4>
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="javascript:;">Restaurante</a></li>
						<li class="breadcrumb-item active">Atender pedido</li>
					</ol>
				</nav>
			</div>
			<div class="col-auto">
				<span class="badge <?php echo $codControlDiario > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> fs-12">
					<i class="bi <?php echo $codControlDiario > 0 ? 'bi-check-circle' : 'bi-exclamation-triangle'; ?> me-1"></i>
					<?php echo $codControlDiario > 0 ? 'Caja aperturada' : 'Caja cerrada'; ?>
				</span>
			</div>
		</div>

		<div class="row g-3">
			<div class="col-xl-5 col-lg-6">
				<div class="card h-100">
					<div class="card-header flex-wrap">
						<div class="flex-grow-1">
							<h5 class="card-title"><i class="bi bi-grid-3x3-gap me-1"></i> Mesas registradas</h5>
							<p class="card-subtitle">Selecciona una mesa para cargar o crear pedido.</p>
						</div>
						<div class="d-flex gap-2">
							<select class="form-select form-select-sm" v-model="campos.codambiente" v-on:change="phuyu_mesas()" id="codambiente">
								<?php foreach ($ambientes as $key => $value) { ?>
									<option value="<?php echo $value['codambiente']; ?>"><?php echo $value['descripcion']; ?></option>
								<?php } ?>
							</select>
							<button type="button" class="btn btn-primary btn-sm text-nowrap" v-on:click="cambiar_mesa()">
								<i class="bi bi-arrow-left-right me-1"></i> Cambiar
							</button>
						</div>
					</div>
					<div class="card-body p-0">
						<div class="restobar-panel-body">
							<div class="mesas-grid">
								<div class="phuyu-mesas" v-for="dato in mesas" v-on:click="phuyu_pedido(dato)">
									<div class="mesa-tile" v-bind:class="[dato.color, campos.codmesa == dato.codmesa ? 'mesa-activa' : '', modoCambioMesa ? 'mesa-cambio-activo' : '']" v-bind:id="'mesa-' + dato.codmesa">
										<span class="mesa-badge" v-bind:class="(dato.texto || '').toString().toLowerCase().indexOf('ocup') > -1 ? 'ocupada' : 'libre'">
											{{ (dato.texto || '').toString().toLowerCase().indexOf('ocup') > -1 ? 'OCUPADA' : 'LIBRE' }}
										</span>
										<h6>Mesa</h6>
										<h3>{{ dato.nromesa }}</h3>
										<div class="hint"><i class="bi bi-info-circle me-1"></i>{{ dato.texto }}</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-xl-5 col-lg-6">
				<div class="card h-100">
					<div class="card-header">
						<div>
							<h5 class="card-title"><i class="bi bi-cup-hot me-1"></i> Productos</h5>
							<p class="card-subtitle">Busca platos, bebidas o productos para el pedido.</p>
						</div>
					</div>
					<div class="card-body p-0">
						<div class="phuyu_card scroll-phuyu-view" id="phuyu_restaurante"></div>
					</div>
				</div>
			</div>

			<div class="col-xl-2 col-lg-12">
				<div class="card h-100">
					<div class="card-header">
						<div>
							<h5 class="card-title"><i class="bi bi-tags me-1"></i> Lineas</h5>
							<p class="card-subtitle">Filtro rapido.</p>
						</div>
					</div>
					<div class="card-body p-0">
						<div class="restobar-panel-body">
							<div class="lineas-list">
								<?php foreach ($lineas as $key => $value) {
									$background = !empty($value['background']) ? $value['background'] : '#405189'; ?>
									<button type="button" class="linea-btn phuyu-restaurante-table" v-on:click="phuyu_producto(<?php echo $value['codlinea']; ?>)">
										<span class="linea-swatch" style="background: <?php echo $background; ?>"></span>
										<?php echo $value['descripcion']; ?>
									</button>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row g-3 mt-1">
			<div class="col-xl-5">
				<div class="card h-100">
					<div class="card-header">
						<div class="flex-grow-1">
							<h5 class="card-title"><i class="bi bi-receipt me-1"></i> Mesa {{ campos.mesa }} | Pedido 00000{{ campos.codpedido }}</h5>
							<p class="card-subtitle">Acciones de caja y atencion del pedido.</p>
						</div>
					</div>
					<div class="card-body">
						<?php if ($codControlDiario > 0) { ?>
							<div class="action-grid">
								<button type="button" class="btn btn-soft-success btn-sm" v-on:click="phuyu_vendedores_caja()">
									<i class="bi bi-person-badge me-1"></i> Anfitrion
								</button>
								<button type="button" class="btn btn-soft-warning btn-sm" v-on:click="phuyu_movimientos(1)">
									<i class="bi bi-arrow-down-circle me-1"></i> Ingre. caja
								</button>
								<button type="button" class="btn btn-soft-danger btn-sm" v-on:click="phuyu_movimientos(2)">
									<i class="bi bi-arrow-up-circle me-1"></i> Egre. caja
								</button>
								<button type="button" class="btn btn-soft-info btn-sm" v-on:click="phuyu_ventadiaria()">
									<i class="bi bi-calendar-check me-1"></i> Venta diaria
								</button>
								<button type="button" class="btn btn-soft-primary btn-sm" v-on:click="phuyu_balancecaja()">
									<i class="bi bi-graph-up-arrow me-1"></i> Balance caja
								</button>
								<button type="button" class="btn btn-soft-info btn-sm" v-on:click="phuyu_avance_pedido()">
									<i class="bi bi-printer me-1"></i> Pre-cuenta
								</button>
								<button type="button" class="btn btn-soft-warning btn-sm" v-on:click="phuyu_comanda()">
									<i class="bi bi-printer-fill me-1"></i> Comanda
								</button>
								<button type="submit" class="btn btn-success btn-sm" v-bind:disabled="estado==1">
									<i class="bi bi-send-check me-1"></i> Guardar pedido
								</button>
								<button type="button" class="btn btn-warning btn-sm" v-on:click="phuyu_atender_pedido()">
									<i class="bi bi-check2-square me-1"></i> Atender pedido
								</button>
								<button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_anular_pedido()">
									<i class="bi bi-trash3 me-1"></i> Anular pedido
								</button>
								<button type="button" class="btn btn-primary btn-sm" v-on:click="phuyu_cobrar_pedido()">
									<i class="bi bi-cash-coin me-1"></i> Cobrar pedido
								</button>
							</div>
							<div class="ticket d-none">
								<div id="imprimir_pedido"></div>
							</div>
						<?php } else { ?>
							<div class="alert alert-danger text-center mb-0">
								<i class="bi bi-exclamation-triangle display-6 d-block mb-2"></i>
								<h5 class="mb-2">Debe aperturar caja</h5>
								<a href="<?php echo base_url(); ?>phuyu/w/caja/controlcajas" class="btn btn-success">
									<i class="bi bi-cash-stack me-1"></i> Ir a caja
								</a>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>

			<div class="col-xl-7">
				<div class="card h-100">
					<div class="card-header flex-wrap">
						<div class="flex-grow-1">
							<h5 class="card-title"><i class="bi bi-bag-check me-1"></i> Detalle del pedido</h5>
							<p class="card-subtitle">Comprobante, mozo y tipo de entrega.</p>
						</div>
					</div>
					<div class="card-body">
						<div class="pedido-toolbar mb-3">
							<select class="form-select form-select-sm" v-model="campos.codcomprobante">
								<option value="0">SIN COMPROBANTE</option>
								<?php foreach ($comprobantes as $key => $value) { ?>
									<option value="<?php echo $value['codcomprobantetipo']; ?>"><?php echo $value['descripcion']; ?></option>
								<?php } ?>
							</select>
							<button type="button" class="btn btn-success btn-icon" v-on:click="phuyu_addcliente()" title="Agregar cliente">
								<i class="bi bi-person-plus"></i>
							</button>
							<select class="form-select form-select-sm" id="codempleado" v-model="campos.codempleado">
								<option value="0">SELECCIONE MOZO</option>
								<?php foreach ($vendedores as $key => $value) { ?>
									<option value="<?php echo $value['codpersona']; ?>"><?php echo $value['razonsocial']; ?></option>
								<?php } ?>
							</select>
							<select class="form-select form-select-sm" id="tipopedido" v-model="campos.tipopedido" v-on:change="phuyu_tipopedido()">
								<option value="0">PARA SALON</option>
								<option value="1">PARA LLEVAR</option>
								<option value="2">PARA DELIVERY</option>
							</select>
						</div>

						<div class="table-responsive detalle">
							<table class="table table-sm table-hover align-middle">
								<thead>
									<tr>
										<th width="52"><i class="bi bi-sticky"></i></th>
										<th width="140"><i class="bi bi-flag me-1"></i>Estado</th>
										<th>Producto</th>
										<th width="110">Unidad</th>
										<th width="105">Cantidad</th>
										<th width="105">Precio</th>
										<th width="110">Subtotal</th>
										<th width="48" class="text-center"><i class="bi bi-trash3"></i></th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(dato,index) in detalle">
										<td>
											<button type="button" class="btn btn-soft-warning btn-sm" v-on:click="phuyu_itemdetalle(index,dato)">
												<i class="bi bi-pencil-square"></i>
											</button>
										</td>
										<td>
											<span class="badge bg-danger-subtle text-danger" v-if="dato.cantidad!=dato.atendido">
												<i class="bi bi-hourglass-split me-1"></i>Pendiente {{ dato.cantidad - dato.atendido }}
											</span>
											<span class="badge bg-success-subtle text-success" v-if="dato.cantidad==dato.atendido">
												<i class="bi bi-check2-circle me-1"></i>Atendido {{ dato.atendido }}
											</span>
										</td>
										<td class="fw-semibold">{{ dato.producto }}</td>
										<td><input type="hidden" v-model="dato.codunidad">{{ dato.unidad }}</td>
										<td>
											<input type="number" step="0.0001" class="form-control number" v-if="dato.control==1" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
											<input type="number" step="0.0001" class="form-control number" v-if="dato.control==0" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
										</td>
										<td>
											<input type="number" step="0.01" class="form-control number" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato,3)" min="0" required>
										</td>
										<td>
											<input type="number" step="0.01" class="form-control number" v-model.number="dato.subtotal" readonly>
										</td>
										<td class="text-center">
											<button type="button" class="btn btn-soft-danger btn-sm" v-on:click="phuyu_deleteitem(index,dato)">
												<i class="bi bi-x-lg"></i>
											</button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>

						<div class="text-end mt-3">
							<span class="total-pill"><i class="bi bi-cash-stack"></i> S/. TOTAL PEDIDO: {{ totales.importe }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>

	<div id="modal_itemdetalle" class="modal fade" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><i class="bi bi-sticky me-1"></i> Detalle del item del pedido</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="text-center mb-3">
						<h5 class="mb-2">{{ item.producto }}</h5>
						<span class="badge bg-warning-subtle text-warning">Unidad: {{ item.unidad }}</span>
					</div>
					<label class="form-label">Descripcion del item del pedido</label>
					<textarea class="form-control" v-model="item.descripcion" rows="4" maxlength="250"></textarea>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" v-on:click="phuyu_cerrar_itemdetalle()">
						<i class="bi bi-check-lg me-1"></i> Guardar y cerrar
					</button>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_pago" class="modal fade" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><i class="bi bi-cash-coin me-1"></i> Registrar pago de la venta</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<form v-on:submit.prevent="phuyu_pagar()">
					<div class="modal-body">
						<div class="payment-total mb-3">TOTAL VENTA S/. {{ totales.importe }}</div>

						<div class="row g-3">
							<div class="col-md-8">
								<label class="form-label">Cliente de la venta</label>
								<select class="form-select" name="codpersona" v-model="campos.codpersona" id="codpersona" required>
									<option value="2">CLIENTES VARIOS</option>
								</select>
							</div>
							<div class="col-md-4">
								<label class="form-label">Condicion pago</label>
								<select class="form-select" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
									<option value="1">CONTADO</option>
									<option value="2">CREDITO</option>
								</select>
							</div>
							<div class="col-md-5">
								<label class="form-label">Tipo comprobante</label>
								<select class="form-select" name="codcomprobantetipo" v-model="campos.codcomprobantetipo" required v-on:change="phuyu_series()">
									<?php foreach ($comprobantes as $key => $value) { ?>
										<option value="<?php echo $value['codcomprobantetipo']; ?>"><?php echo $value['descripcion']; ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="col-md-3">
								<label class="form-label">Serie</label>
								<select class="form-select" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()" required>
									<option value="">SERIE</option>
									<option v-for="dato in series" v-bind:value="dato.seriecomprobante">{{ dato.seriecomprobante }}</option>
								</select>
							</div>
							<div class="col-md-4">
								<label class="form-label d-flex justify-content-between">
									<span>Vendedor</span>
									<span class="text-danger">Nro: {{ campos.seriecomprobante }} - {{ campos.nro }}</span>
								</label>
								<select class="form-select" name="codempleado" v-model="campos.codempleado" required>
									<option value="0">SIN VENDEDOR</option>
									<?php foreach ($vendedores as $key => $value) { ?>
										<option value="<?php echo $value['codpersona']; ?>"><?php echo $value['razonsocial']; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>

						<div class="row g-3 mt-1" v-if="campos.condicionpago==2">
							<div class="col-md-4">
								<label class="form-label">Nro dias</label>
								<input class="form-control" name="nrodias" v-model="campos.nrodias" v-on:keyup="phuyu_cuotas()" required>
							</div>
							<div class="col-md-4">
								<label class="form-label">Cuotas</label>
								<input class="form-control" name="nrocuotas" v-model="campos.nrocuotas" v-on:keyup="phuyu_cuotas()" required>
							</div>
							<div class="col-md-4">
								<label class="form-label">Interes (%)</label>
								<input class="form-control" name="tasainteres" v-model="campos.tasainteres" v-on:keyup="phuyu_cuotas()" required>
							</div>
						</div>

						<div class="mt-3" v-if="campos.condicionpago==1">
							<div class="row g-3">
								<div class="col-md-6">
									<div class="payment-box h-100">
										<h6 class="fw-bold mb-3"><i class="bi bi-cash-stack me-1"></i> Pago en efectivo</h6>
										<label class="form-label">S/. monto recibido</label>
										<input type="number" step="0.01" class="form-control number phuyu-money-success" min="0" required v-model="pagos.monto_efectivo" placeholder="S/. 0.00" v-on:keyup="phuyu_vuelto()">
										<label class="form-label mt-2">Vuelto</label>
										<input type="number" step="0.01" class="form-control phuyu-money-error" readonly v-model="pagos.vuelto_efectivo">
									</div>
								</div>
								<div class="col-md-6">
									<div class="payment-box h-100">
										<h6 class="fw-bold mb-3"><i class="bi bi-credit-card me-1"></i> Tarjeta o cheque</h6>
										<label class="form-label">Tipo pago</label>
										<select class="form-select" v-model="pagos.codtipopago_tarjeta" v-on:change="phuyu_pagotarjeta()" required>
											<option value="0">SIN TARJETA</option>
											<?php foreach ($tipopagos as $key => $value) {
												if ($value['codtipopago'] != 1) { ?>
													<option value="<?php echo $value['codtipopago']; ?>"><?php echo $value['descripcion']; ?></option>
												<?php }
											} ?>
										</select>
										<label class="form-label mt-2">S/. monto</label>
										<input type="number" step="0.01" class="form-control number phuyu-money-success" min="0.01" id="monto_tarjeta" v-model="pagos.monto_tarjeta" placeholder="S/. 0.00" readonly>
										<label class="form-label mt-2">Nro voucher</label>
										<input type="text" class="form-control phuyu-money-default" id="nrovoucher" v-model.trim="pagos.nrovoucher" autocomplete="off" readonly>
									</div>
								</div>
							</div>
						</div>

						<div class="mt-3" v-if="campos.condicionpago==2">
							<div class="table-responsive" style="max-height: 180px;">
								<table class="table table-sm table-bordered align-middle">
									<thead class="table-light">
										<tr>
											<th>Fecha vence</th>
											<th>Importe</th>
											<th>Interes</th>
											<th>Total</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="dato in cuotas">
											<td>{{ dato.fechavence }}</td>
											<td>{{ dato.importe }}</td>
											<td>{{ dato.interes }}</td>
											<td>{{ dato.total }}</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="d-flex justify-content-center flex-wrap gap-2 mt-2">
								<span class="badge bg-warning text-dark fs-12">Interes: S/. {{ totales.interes }}</span>
								<span class="badge bg-danger fs-12">Total credito: S/. {{ campos.totalcredito }}</span>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">
							<i class="bi bi-x-circle me-1"></i> Cancelar
						</button>
						<button type="submit" class="btn btn-success" v-bind:disabled="estado==1">
							<i class="bi bi-save me-1"></i> Guardar venta
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="modal_atender" class="modal fade" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><i class="bi bi-check2-square me-1"></i> Pedido Nro: 0000{{ campos.codpedido }} | Mesa {{ campos.mesa }}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="table-responsive" style="max-height: 260px;">
						<table class="table table-sm table-bordered align-middle">
							<thead class="table-light">
								<tr>
									<th>Descripcion</th>
									<th width="95">Unidad</th>
									<th width="95">Cantidad</th>
									<th width="95">Atendido</th>
									<th width="100">Atender</th>
									<th width="110" colspan="2">Agregar</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in atender">
									<td>{{ dato.producto }} - {{ dato.descripcion }}</td>
									<td>{{ dato.unidad }}</td>
									<td>{{ dato.cantidad }}</td>
									<td>{{ dato.atendido }}</td>
									<td>
										<input type="number" step="0.1" class="form-control number line-success" v-model.number="dato.atender" min="0" max="dato.cantidad" readonly>
									</td>
									<td v-if="dato.cantidad!=dato.atendido">
										<button type="button" class="btn btn-info btn-sm w-100" v-on:click="phuyu_mas_menos(dato,1)">
											<i class="bi bi-plus-lg"></i>
										</button>
									</td>
									<td v-if="dato.cantidad!=dato.atendido">
										<button type="button" class="btn btn-warning btn-sm w-100" v-on:click="phuyu_mas_menos(dato,2)">
											<i class="bi bi-dash-lg"></i>
										</button>
									</td>
									<td v-if="dato.cantidad==dato.atendido" colspan="2">
										<span class="badge bg-success-subtle text-success w-100">Atendido</span>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr v-for="dato in totales">
									<td colspan="2" class="text-end fw-bold">Totales</td>
									<td class="fw-bold">{{ dato.cantidad }}</td>
									<td class="fw-bold">{{ dato.atendido }}</td>
									<td colspan="3">
										<button type="button" class="btn btn-success btn-sm w-100" v-on:click="phuyu_atender()" v-bind:disabled="estado==1">
											<i class="bi bi-save me-1"></i> Guardar atencion
										</button>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>

					<h6 class="fw-bold text-center mt-3 mb-2">Detalle de las atenciones del pedido</h6>
					<div class="table-responsive" style="max-height: 220px;">
						<table class="table table-sm table-bordered align-middle">
							<thead class="table-light">
								<tr>
									<th>Descripcion</th>
									<th width="95">Unidad</th>
									<th width="95">Cantidad</th>
									<th width="150">Fecha y hora</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in atendidos">
									<td class="fw-semibold">{{ dato.producto }} - {{ dato.descripcion }}</td>
									<td>{{ dato.unidad }}</td>
									<td>{{ dato.cantidad }}</td>
									<td class="fw-bold text-danger">{{ dato.fecha }} {{ dato.hora }}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_reportes" class="modal fade" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><?php echo $_SESSION['phuyu_empresa'] . ' - ' . $_SESSION['phuyu_sucursal']; ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body p-0" id="reportes_modal">
					<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"></iframe>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_empleados" class="modal fade" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><i class="bi bi-person-badge me-1"></i> Reporte de anfitrionas</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body" id="modal_empleados_contenido"></div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url(); ?>phuyu/phuyu_restaurante/atender.js"></script>
<script src="<?php echo base_url(); ?>phuyu/phuyu_personas_2.js"></script>
<script>
	var pantalla = jQuery(document).height();
	$("#reportes_modal").css({
		height: pantalla - 65
	});
	$(".detalle").css("height", Math.max(210, pantalla - 540));
</script>
