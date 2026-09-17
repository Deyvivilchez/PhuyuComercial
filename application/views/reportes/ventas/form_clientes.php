<div class="row form-group">
	<div class="col-md-12">
		<label>CLIENTE</label>
		<select class="form-control" name="codpersona" id="codpersona" required>
			<option value="0">SELECCIONAR CLIENTE</option>
		</select>
	</div>
</div><br>
<div class="row form-group">
	<div class="col-md-12">
		<div align="center">
			<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_cliente()"><i class="bi bi-printer"></i> Resumen PDF</button>
			<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_cliente_detallado()">
				<i class="bi bi-printer"></i> Detallado PDF
			</button>
			<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_cliente()"><i class="bi bi-file-earmark-excel"></i> Resumen EXCEL</button>
			<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_cliente_detallado()"><i class="bi bi-file-earmark-excel"></i> Detallado EXCEL</button>
		</div>
	</div>
</div>


<script src="<?php echo base_url();?>phuyu/phuyu_reportes/selects.js"> </script>