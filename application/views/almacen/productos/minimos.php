<style>
	#phuyu_unidades.phuyu-productos-grid .page-title-box {
		margin-bottom: 18px;
	}
	#phuyu_unidades.phuyu-productos-grid .page-title-box h4 {
		color: #212529;
		font-weight: 700;
		margin-bottom: 4px;
	}
	#phuyu_unidades.phuyu-productos-grid .phuyu-list-card {
		border: 1px solid #e9ebec;
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
	}
	#phuyu_unidades.phuyu-productos-grid .phuyu-toolbar {
		align-items: center;
		gap: 8px;
	}
	#phuyu_unidades.phuyu-productos-grid .form-control {
		border: 1px solid #d9e2ef;
		border-radius: 6px;
		box-shadow: none;
		min-height: 36px;
	}
	#phuyu_unidades.phuyu-productos-grid .table {
		font-size: 12px;
	}
	#phuyu_unidades.phuyu-productos-grid .table thead th {
		background: #f3f6f9;
		color: #495057;
		font-size: 11px;
		text-transform: uppercase;
		white-space: nowrap;
	}
</style>

<div id="phuyu_unidades" class="phuyu-productos-grid">
	<div class="row page-title-box">
		<div class="col-12">
            <h4 id="title">Stocks Mínimos</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                    <li class="breadcrumb-item active">Stocks mínimos</li>
                </ol>
            </nav>
        </div>
	</div>
	<div class="phuyu_body">
		<div class="card phuyu-list-card">
			<div class="card-body">
				<div class="row g-2 phuyu-toolbar mb-3">
					<div class="col-sm-12 col-md-5 col-lg-4 col-xxl-2 mb-1">
	                    <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input class="form-control datatable-search border-start-0" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . ." />
                        </div>
	                </div>
	                <div class="col-sm-12 col-md-7 col-lg-8 col-xxl-10 text-end mb-1">
	                    <div class="d-flex flex-wrap justify-content-end phuyu-toolbar">
							<button type="button" class="btn btn-success" v-on:click="phuyu_guardar()" v-bind:disabled="estado==1"><i class="bi bi-save me-1"></i> Guardar cambios</button>
	                    </div>
	                </div>
			    </div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner">
					</div>
				</div>
			    <div class="row form-group scroll-track-visible sh-40">
					<div class="table-responsive">
						<table class="table table-hover align-middle mb-0">
							<thead>
								<tr>
									<th width="5%"> # </th>
									<th width="55%">PRODUCTO</th>
									<th width="10%">UNIDAD</th>
									<th width="5%">F</th>
									<th width="10%">STOCK</th>
									<th width="15%">STOCK MINIMO</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato, index) in buscar_productos">
									<td>{{dato.nro}}</td>
									<td style="font-size:10px;">{{dato.descripcion}}</td>
									<td>{{dato.unidad}}</td>
									<td>{{dato.factor}}</td>
									<td>{{dato.stock}}</td>
									<td><input type="number" class="form-control number" v-model="dato.stockminimo"></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_almacen/minimos.js"></script>

<script> 
	var div_altura = jQuery(document).height(); var productos = div_altura - 250; $(".lista").css("height",productos+"px");
</script>
