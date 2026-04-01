<div id="modal_transferencias" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><b>LISTA DE TRANFERENCIAS A ESTA CAJA</b></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" style="height:270px;overflow-y: auto;">
				<table class="table table-bordered table-sm">
					<thead>
						<tr>
							<th width="80px">FECHA</th>
							<th width="110px">N° RECIBO</th>
							<th>CAJA</th>
							<th>CONCEPTO</th>
							<th>RAZÓN SOCIAL</th>
							<th width="100px">S/ IMPORTE</th>
							<th width="100px">ACEPTAR</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="dato in transferencias">
							<td>{{ dato.fechamovimiento }}</td>
							<td>{{ dato.seriecomprobante + '-' + dato.nrocomprobante }}</td>
							<td>{{ dato.caja }}</td>
							<td>{{ dato.concepto }}</td>
							<td>{{ dato.razonsocial }}</td>
							<td>S/. {{ dato.importe_r }}</td>
							<td>
								<button type="button" class="btn btn-success btn-sm" @click="phuyu_aceptar_transferencia(dato)">ACEPTAR</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
