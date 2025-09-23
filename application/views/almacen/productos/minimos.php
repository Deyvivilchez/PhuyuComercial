<div id="phuyu_unidades">
	<div class="row">
		<div class="col-12 col-md-6">
            <h1 class="mb-0 pb-0 display-4" id="title">Stocks Mínimos</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
              </ul>
            </nav>
        </div>
	</div>
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-sm-12 col-md-5 col-lg-4 col-xxl-2 mb-1">
	                    <div class="d-inline-block float-md-start me-1 mb-1 search-input-container w-100 shadow bg-foreground">
	                      <input class="form-control datatable-search" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . ." />
	                      <span class="search-magnifier-icon">
	                        <i data-acorn-icon="search"></i>
	                      </span>
	                      <span class="search-delete-icon d-none">
	                        <i data-acorn-icon="close"></i>
	                      </span>
	                    </div>
	                </div>
	                <div class="col-sm-12 col-md-7 col-lg-8 col-xxl-10 text-end mb-1">
	                    <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
	                    	<button type="button" class="btn btn-success btn-icon" v-on:click="phuyu_guardar()" v-bind:disabled="estado==1"><i data-acorn-icon="save"></i> GUARDAR CAMBIOS</button>
	                    </div>
	                </div>
			    </div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner">
					</div>
				</div>
			    <div class="row form-group scroll-track-visible sh-40">
					<div class="table-responsive">
						<table class="table table-striped" style="font-size: 11px">
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