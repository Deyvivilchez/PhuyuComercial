<?php $codControlDiario = $_SESSION["phuyu_codcontroldiario"] ?? 0; ?>
<style>
	.hotel-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(145px,1fr));gap:.75rem}
	.hotel-room{border:1px solid #e9ebec;border-radius:8px;background:#fff;padding:.85rem;cursor:pointer;min-height:122px;box-shadow:0 1px 2px rgba(56,65,74,.08)}
	.hotel-room.active{border-color:#405189;box-shadow:0 8px 18px rgba(64,81,137,.14)}
	.hotel-room.change-target{outline:2px dashed #405189;outline-offset:2px}
	.hotel-room h3{font-size:1.65rem;margin:0;color:#2f3a56}
	.hotel-room .badge{font-size:.66rem}
	.hotel-room-features{display:flex;flex-wrap:wrap;gap:.25rem;margin:.35rem 0;color:#405189;font-size:1rem}
	.hotel-room-features i{line-height:1}
	.hotel-room.disponible{border-left:4px solid #0ab39c}
	.hotel-room.ocupada{border-left:4px solid #f06548}
	.hotel-room.limpieza{border-left:4px solid #f7b84b}
	.hotel-room.mantenimiento,.hotel-room.bloqueada{border-left:4px solid #878a99}
	.hotel-room.disponible .badge{background:#daf4f0;color:#0ab39c}
	.hotel-room.ocupada .badge{background:#fde8e4;color:#f06548}
	.hotel-room.limpieza .badge{background:#fef4e4;color:#f7b84b}
	.hotel-room.mantenimiento .badge,.hotel-room.bloqueada .badge{background:#e2e5ed;color:#495057}
	.hotel-room.reservada .badge{background:#dff0fa;color:#299cdb}
	.hotel-charge-panel{border:1px solid #e9ebec;border-radius:8px;padding:.85rem;background:#fbfcfd}
	.hotel-charge-total{font-size:1.35rem;font-weight:700;color:#405189}
	.hotel-product-empty{border:1px dashed #d7dde8;border-radius:8px;background:#fff;padding:.85rem;text-align:center;color:#878a99;font-size:.86rem}
	.hotel-product-empty i{font-size:1.35rem;display:block;margin-bottom:.25rem;color:#405189}
	.hotel-product-results{border:1px solid #e9ebec;border-radius:6px;max-height:210px;overflow:auto;background:#fff;margin-bottom:.5rem}
	.hotel-product-option{padding:.55rem .65rem;border-bottom:1px solid #f3f3f3;cursor:pointer}
	.hotel-product-option:hover{background:#f3f6f9}
	.hotel-product-option:last-child{border-bottom:0}
	.phuyu-hotel .select2-container{width:100%!important}
	.phuyu-hotel .select2-selection--single{height:38px!important;border:1px solid #ced4da!important;border-radius:.375rem!important}
	.phuyu-hotel .select2-selection__rendered{line-height:38px!important;padding-left:.75rem!important}
	.phuyu-hotel .select2-selection__arrow{height:38px!important}
</style>
<div id="phuyu_hotel" class="phuyu-hotel">
	<input type="hidden" id="sessioncaja" value="<?php echo $codControlDiario;?>">
	<input type="hidden" id="sessionstockalmacen" value="<?php echo $_SESSION["phuyu_stockalmacen"] ?? 0;?>">
	<div class="page-title-box d-flex align-items-center justify-content-between">
		<div>
			<h4 class="mb-1"><i class="ri-hotel-bed-line me-1"></i> Recepcion hotel</h4>
			<div class="text-muted small">Check-in, consumos y check-out integrado a caja</div>
		</div>
		<span class="badge <?php echo $codControlDiario > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
			<?php echo $codControlDiario > 0 ? 'Caja aperturada' : 'Caja cerrada'; ?>
		</span>
	</div>
	<div class="alert alert-warning py-2" v-if="modoCambioHabitacion">
		<strong>Cambio de habitacion activo.</strong> Seleccione una habitacion disponible para mover la estadia 000{{estadia.estadia ? estadia.estadia.codestadia : ''}}.
		<button type="button" class="btn btn-sm btn-light ms-2" v-on:click="cancelar_cambio_habitacion()">Cancelar</button>
	</div>

	<div class="row g-3">
		<div class="col-xl-7">
				<div class="card">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="card-title mb-0">Habitaciones</h5>
						<div class="d-flex gap-2 align-items-center">
							<select class="form-select form-select-sm" v-model="codambiente" v-on:change="cargar_habitaciones()">
								<option value="0">TODOS</option>
								<?php foreach ($ambientes as $ambiente) { ?>
									<option value="<?php echo $ambiente["codambiente"];?>"><?php echo $ambiente["descripcion"];?></option>
								<?php } ?>
							</select>
							<select class="form-select form-select-sm" v-model="codcaracteristica" v-on:change="cargar_habitaciones()">
								<option value="0">CARACTERISTICAS</option>
								<?php foreach ($caracteristicas as $caracteristica) { ?>
									<option value="<?php echo $caracteristica["codcaracteristica"];?>"><?php echo htmlspecialchars($caracteristica["descripcion"]);?></option>
								<?php } ?>
							</select>
							<button class="btn btn-sm btn-light" v-on:click="cargar_habitaciones()"><i class="ri-refresh-line"></i></button>
						</div>
					</div>
				<div class="card-body">
					<div v-for="grupo in habitaciones_agrupadas()" class="mb-3">
						<h6 class="text-muted mb-2"><i class="ri-building-2-line me-1"></i>{{grupo.ambiente}}</h6>
						<div class="hotel-grid">
							<div class="hotel-room" v-for="habitacion in grupo.habitaciones" v-bind:class="[habitacion.situacion_clase, habitacionActiva && habitacionActiva.codhabitacion==habitacion.codhabitacion ? 'active' : '', modoCambioHabitacion && habitacion.situacion==1 ? 'change-target' : '']" v-on:click="seleccionar_habitacion(habitacion)">
								<span class="badge">{{habitacion.situacion_texto}}</span>
								<h3>{{habitacion.numero}}</h3>
								<div class="text-muted small">{{habitacion.tipo}}</div>
								<div class="text-muted small">{{habitacion.ambiente}}</div>
								<div class="hotel-room-features" v-if="habitacion.caracteristicas && habitacion.caracteristicas.length">
									<i v-for="caracteristica in habitacion.caracteristicas" v-bind:class="caracteristica.icono || 'ri-checkbox-circle-line'" v-bind:title="caracteristica.descripcion"></i>
								</div>
								<div class="small">S/. {{Number(habitacion.preciobase).toFixed(2)}}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-xl-5">
			<div class="card">
				<div class="card-header"><h5 class="card-title mb-0">Operacion</h5></div>
				<div class="card-body" v-if="!habitacionActiva">
					<div class="text-muted text-center py-4">Seleccione una habitacion</div>
				</div>
				<div class="card-body" v-if="habitacionActiva">
					<h5>Habitacion {{habitacionActiva.numero}}</h5>
					<div class="mb-3"><span class="badge bg-primary-subtle text-primary">{{habitacionActiva.situacion_texto}}</span></div>
					<div class="hotel-room-features mb-3" v-if="habitacionActiva.caracteristicas && habitacionActiva.caracteristicas.length">
						<span class="badge bg-light text-muted border me-1" v-for="caracteristica in habitacionActiva.caracteristicas">
							<i v-bind:class="caracteristica.icono || 'ri-checkbox-circle-line'" class="me-1"></i>{{caracteristica.descripcion}}
						</span>
					</div>

					<div v-if="parseInt(habitacionActiva.situacion || 0)==1 || parseInt(habitacionActiva.situacion || 0)==2">
						<h6>Check-in</h6>
						<label class="form-label">Cliente</label>
						<div class="input-group mb-2">
							<select id="codpersona_hotel" class="form-select" style="width:calc(100% - 42px)"></select>
							<button type="button" class="btn btn-primary" v-on:click="abrir_cliente_nuevo()" title="Agregar cliente"><i class="ri-add-line"></i></button>
						</div>
						<div class="alert alert-info py-2" v-if="checkin.codpersona">{{checkin.documento}} - {{checkin.cliente}}</div>
						<div class="row g-2">
							<div class="col-6"><input type="date" class="form-control" v-model="checkin.fecha_checkin"></div>
							<div class="col-6"><input type="date" class="form-control" v-model="checkin.fecha_checkout"></div>
							<div class="col-6"><input type="number" step="0.01" class="form-control" v-model.number="checkin.precio_noche" placeholder="Precio noche"></div>
							<div class="col-6">
								<select class="form-select" v-model="checkin.codempleado">
									<option value="0">Empleado</option>
									<?php foreach ($vendedores as $v) { ?>
										<option value="<?php echo $v["codpersona"];?>"><?php echo $v["razonsocial"];?></option>
									<?php } ?>
								</select>
							</div>
							<div class="col-12"><textarea class="form-control" v-model="checkin.observacion" placeholder="Observacion"></textarea></div>
						</div>
						<button class="btn btn-success w-100 mt-3" v-on:click="guardar_checkin()"><i class="ri-login-circle-line me-1"></i> Registrar check-in</button>
					</div>

					<div v-if="parseInt(habitacionActiva.situacion || 0)==3 && estadia.estadia">
						<div class="alert alert-success py-2">
							<strong>Estadia 000{{estadia.estadia.codestadia}}</strong><br>
							{{estadia.estadia.cliente}} - Check-in {{estadia.estadia.fecha_checkin}}
						</div>
						<button class="btn btn-outline-primary w-100 mb-3" v-on:click="iniciar_cambio_habitacion()">
							<i class="ri-arrow-left-right-line me-1"></i> Cambiar habitacion
						</button>
						<div class="alert alert-info py-2" v-if="modoCambioHabitacion">
							<div class="small fw-semibold mb-1">Habitacion origen despues del cambio</div>
							<select class="form-select form-select-sm" v-model="cambioHabitacion.situacion_origen">
								<option value="4">LIMPIEZA</option>
								<option value="1">DISPONIBLE</option>
							</select>
						</div>

						<h6>{{consumo.codconsumo ? 'Editar consumo' : 'Agregar consumo'}}</h6>
						<div class="hotel-charge-panel">
							<div class="small text-muted mb-2">
								<strong>Habitacion {{habitacionActiva.numero}}</strong> - {{estadia.estadia.cliente}}
								<span class="d-block">Estadia 000{{estadia.estadia.codestadia}}</span>
							</div>
							<label class="form-label">Producto</label>
							<select id="codproducto_consumo" class="form-select mb-2" style="width:100%"></select>
							<div class="form-text mb-2">Busque por nombre o codigo y seleccione el producto.</div>
							<div class="row g-2" v-if="consumo.codproducto">
								<div class="col-12">
									<div class="alert alert-light border py-2 mb-0">
										<strong>{{consumo.producto}}</strong>
										<div class="small text-muted">
											Unidad: {{consumo.unidad}} | Precio: S/. {{Number(consumo.preciounitario).toFixed(2)}}
											<span v-if="controlaStockSistema==1 && consumo.controlstock==1"> | Stock: {{Number(consumo.stock).toFixed(2)}}</span>
											<span v-if="consumo.controlarseries==1"> | Requiere serie</span>
										</div>
										<div class="small text-danger fw-semibold" v-if="controlaStockSistema==1 && consumo.controlstock==1 && Number(consumo.stock || 0)<=0">No hay stock disponible para este producto.</div>
									</div>
								</div>
								<div class="col-4">
									<label class="form-label">Cantidad</label>
									<input type="number" step="0.0001" class="form-control" v-model.number="consumo.cantidad" v-bind:readonly="consumo.controlarseries==1">
								</div>
								<div class="col-4">
									<label class="form-label">Precio</label>
									<input type="number" step="0.01" class="form-control" v-model.number="consumo.preciounitario">
								</div>
								<div class="col-4">
									<label class="form-label">Subtotal</label>
									<div class="hotel-charge-total">S/. {{subtotal_consumo().toFixed(2)}}</div>
								</div>
								<div class="col-12">
									<label class="form-label" v-if="consumo.controlarseries==1">Serie</label>
									<select class="form-select" v-if="consumo.controlarseries==1" v-model="consumo.id_serie">
										<option value="0">SELECCIONE SERIE</option>
										<option v-for="serie in consumo.series" v-bind:value="serie.id_serie">{{serie.serie_codigo}}</option>
									</select>
								</div>
								<div class="col-12">
									<label class="form-label">Detalle</label>
									<input class="form-control" v-model="consumo.descripcion">
								</div>
								<div class="col-12">
									<div class="d-flex gap-2">
										<button class="btn btn-primary flex-grow-1" v-on:click="guardar_consumo()">
											{{guardandoConsumo ? 'Guardando...' : (consumo.codconsumo ? 'Actualizar consumo' : 'Agregar consumo a habitacion ' + habitacionActiva.numero)}}
										</button>
										<button class="btn btn-light" v-if="consumo.codconsumo" v-on:click="limpiar_consumo()">Cancelar edicion</button>
									</div>
								</div>
							</div>
							<div class="hotel-product-empty" v-if="!consumo.codproducto">
								<i class="ri-shopping-basket-2-line"></i>
								Seleccione un producto para continuar.
							</div>
						</div>

						<div class="table-responsive mt-3">
							<table class="table table-sm">
								<thead><tr><th>Habitacion</th><th>Producto</th><th>Cant.</th><th>Total</th><th></th></tr></thead>
								<tbody>
									<tr v-for="c in estadia.consumos" v-if="c.situacion==1">
										<td>{{c.habitacion}}</td>
											<td>{{c.producto}}<div class="small text-muted" v-if="c.serie_codigo">Serie: {{c.serie_codigo}}</div></td>
											<td>{{c.cantidad}}</td>
										<td>S/. {{Number(c.subtotal).toFixed(2)}}</td>
										<td>
											<div class="btn-group btn-group-sm">
												<button class="btn btn-light" v-on:click="editar_consumo(c)"><i class="ri-edit-line"></i></button>
												<button class="btn btn-danger" v-on:click="anular_consumo(c)"><i class="ri-close-line"></i></button>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>

						<div class="d-grid gap-2 mt-2">
							<button class="btn btn-outline-success" v-on:click="abrir_cobro_consumos()" v-if="total_consumos_pendientes()>0"><i class="ri-bank-card-line me-1"></i> Cobrar solo consumos</button>
							<button class="btn btn-success" v-on:click="abrir_checkout()"><i class="ri-cash-line me-1"></i> Check-out / cobrar</button>
						</div>
					</div>

					<div class="alert alert-warning mt-3" v-if="parseInt(habitacionActiva.situacion || 0)==3 && !estadia.estadia">
						<strong>No se pudo cargar la estadia activa.</strong><br>
						Vuelva a seleccionar la habitacion o actualice la pantalla.
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modal_cliente_hotel" tabindex="-1">
		<div class="modal-dialog modal-lg modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><i class="ri-user-add-line me-1"></i> Registrar cliente</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="row g-2">
						<div class="col-md-4">
							<label class="form-label">Tipo documento</label>
							<select class="form-select" v-model="clienteNuevo.coddocumentotipo" v-on:change="cambiar_tipo_documento_cliente()">
								<?php foreach ($tipodocumentos as $doc) { ?>
									<option value="<?php echo $doc["coddocumentotipo"];?>"><?php echo $doc["descripcion"];?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Documento</label>
							<input class="form-control" v-model="clienteNuevo.documento" v-bind:readonly="clienteNuevo.coddocumentotipo==1" v-bind:maxlength="clienteDocumentoMax">
						</div>
						<div class="col-md-2 d-flex align-items-end">
							<button type="button" class="btn btn-light w-100" v-on:click="consultar_cliente_nuevo()" v-bind:disabled="clienteNuevo.coddocumentotipo==1"><i class="ri-search-line"></i></button>
						</div>
						<div class="col-md-12">
							<label class="form-label">Nombre / razon social</label>
							<input class="form-control" v-model="clienteNuevo.razonsocial">
						</div>
						<div class="col-md-12">
							<label class="form-label">Nombre comercial</label>
							<input class="form-control" v-model="clienteNuevo.nombrecomercial">
						</div>
						<div class="col-md-12">
							<label class="form-label">Direccion</label>
							<input class="form-control" v-model="clienteNuevo.direccion">
						</div>
						<div class="col-md-6">
							<label class="form-label">Telefono</label>
							<input class="form-control" v-model="clienteNuevo.telefono">
						</div>
						<div class="col-md-6">
							<label class="form-label">Email</label>
							<input class="form-control" v-model="clienteNuevo.email">
						</div>
						<div class="col-md-6">
							<label class="form-label">Sexo / empresa</label>
							<select class="form-select" v-model="clienteNuevo.sexo">
								<option value="M">MASCULINO</option>
								<option value="F">FEMENINO</option>
								<option value="E">EMPRESA</option>
							</select>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
					<button type="button" class="btn btn-success" v-on:click="guardar_cliente_nuevo()" v-bind:disabled="guardandoCliente">
						<i class="ri-save-line me-1"></i> Guardar cliente
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modal_checkout" tabindex="-1">
		<div class="modal-dialog modal-lg modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header"><h5 class="modal-title">{{checkout.tipo=='consumos' ? 'Cobro de consumos' : 'Check-out'}} estadia 000{{checkout.campos ? checkout.campos.codestadia : ''}}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
				<div class="modal-body">
					<div class="alert alert-primary fs-5 text-center">TOTAL S/. {{Number(total_pago_actual()).toFixed(2)}}</div>
					<div class="row g-2">
						<div class="col-md-4">
							<label class="form-label">Comprobante</label>
							<select class="form-select" v-model="checkout.campos.codcomprobantetipo" v-on:change="series()">
								<?php foreach ($comprobantes as $c) { ?>
									<option value="<?php echo $c["codcomprobantetipo"];?>"><?php echo $c["descripcion"];?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-4"><label class="form-label">Serie</label><select class="form-select" v-model="checkout.campos.seriecomprobante" v-on:change="correlativo()"><option v-for="s in seriesLista" v-bind:value="s.seriecomprobante">{{s.seriecomprobante}}</option></select></div>
						<div class="col-md-4"><label class="form-label">Numero</label><input class="form-control" v-model="checkout.campos.nro" readonly></div>
						<div class="col-md-4"><label class="form-label">Condicion</label><select class="form-select" v-model="checkout.campos.condicionpago"><option value="1">CONTADO</option><option value="2">CREDITO</option></select></div>
						<div class="col-md-4"><label class="form-label">Efectivo</label><input type="number" step="0.01" class="form-control" v-model.number="checkout.pagos.monto_efectivo" v-on:keyup="vuelto()"></div>
						<div class="col-md-4"><label class="form-label">Vuelto</label><input class="form-control" v-model="checkout.pagos.vuelto_efectivo" readonly></div>
						<div class="col-md-4"><label class="form-label">Tarjeta</label><select class="form-select" v-model="checkout.pagos.codtipopago_tarjeta"><option value="0">SIN TARJETA</option><?php foreach ($tipopagos as $t) { if ((int)$t["codtipopago"] != 1) { ?><option value="<?php echo $t["codtipopago"];?>"><?php echo $t["descripcion"];?></option><?php }} ?></select></div>
						<div class="col-md-4"><label class="form-label">Monto tarjeta</label><input type="number" step="0.01" class="form-control" v-model.number="checkout.pagos.monto_tarjeta"></div>
						<div class="col-md-4"><label class="form-label">Voucher</label><input class="form-control" v-model="checkout.pagos.nrovoucher"></div>
						<div class="col-md-6" v-if="checkout.tipo=='checkout'"><label class="form-label">Estado habitacion</label><select class="form-select" v-model="checkout.destino_habitacion"><option value="4">LIMPIEZA</option><option value="1">DISPONIBLE</option></select></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
					<button type="button" class="btn btn-success" v-on:click="checkout_estadia()" v-bind:disabled="cobrandoHotel">{{cobrandoHotel ? 'Procesando...' : (checkout.tipo=='consumos' ? 'Cobrar consumos' : 'Cobrar y cerrar estadia')}}</button>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="<?php echo base_url();?>phuyu/phuyu_hotel/recepcion.js"></script>
