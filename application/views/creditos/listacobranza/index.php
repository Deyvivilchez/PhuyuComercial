<div id="phuyu_datos">
	<div class="row">
		<div class="col-12 col-md-6">
			<input type="hidden" id="sessioncaja" value="<?php echo $_SESSION["phuyu_codcontroldiario"];?>"> 
            <h1 class="mb-0 pb-0 display-4" id="title">Lista de Cobranza</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Cobranza</a></li>
              </ul>
            </nav>
        </div>

        <div class="col-12 col-md-6 d-flex align-items-start justify-content-end" style="font-size: 11px">
			<div class="col-md-4 col-xs-12">
				<label><i class="fa fa-calendar"></i> DESDE:</label>
				<input type="date" class="form-control input-sm" id="fechadesde" value="<?php echo date('Y-m-01');?>" v-on:change="phuyu_buscar()" autocomplete="off">
			</div>
			<div class="col-md-4 col-xs-12">
				<label><i class="fa fa-calendar"></i> HASTA:</label>
				<input type="date" class="form-control input-sm" id="fechahasta" value="<?php echo date('Y-m-d');?>" v-on:change="phuyu_buscar()" autocomplete="off">
			</div>
			<div class="col-md-4">
				<label>POR FECHAS?</label>
				<select class="form-select input-sm" v-model="campos.filtro" v-on:change="phuyu_buscar()">
					<option value="1">FECHAS FILTRO (SI)</option>
					<option value="0">FECHAS FILTRO (NO)</option>
				</select>
			</div>
        </div>
    </div>
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row">
                    <div class="col-md-2">
                    	<div class="dropdown">
                          <button
                            class="btn btn-primary dropdown-toggle mb-1"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                          >
                            Lista General
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimirlista(0)">Archivo PDF</a>
                            <a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimirlista(1)">Archivo Excel</a>
                          </div>
                        </div>
					    <!--<button type="button" class="btn btn-success" v-on:click="pdf_creditos()">
					        <i class="fa fa-print"></i> CREDITOS PENDIENTES
					    </button>-->
				    </div><div class="col-md-5"></div>
					<div class="col-md-2"> 
						<select class="form-select" v-model="estado" v-on:change="phuyu_buscar()">
							<option value="">TODOS LAS COBRANZAS</option>
							<option value="0">ANULADOS</option>
							<option value="1">ACTIVOS</option>
						</select>
					</div>
				    <div class="col-md-3 col-xs-12">
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
			    </div><br>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>
				<div v-if="!cargando">
					<div class="table-responsive">
						<table class="table table-striped" style="font-size: 11px">
							<thead>
								<tr>
									<th>ID</th>
									<th>RAZON SOCIAL</th>
									<th>F.&nbsp;CREDITO</th>
									<th>F.&nbsp;&nbsp;&nbsp;VENCE</th>
									<th>COMPROBANTE</th>
									<th>TOTAL CRED.</th>
									<th>F.&nbsp;PAGO</th>
									<th>IMPORTE</th>
									<th width="5px">ESTADO</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in datos">
									<td>{{dato.codcredito}}</td>
									<td>{{dato.razonsocial}}</td>
									<td>{{dato.fechacredito}}</td>
									<td>{{dato.fechavencimientocredito}}</td>
									<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
									<td>{{dato.totalcredito}}</td>
									<td>{{dato.fechamovimiento}}</td>
									<td>{{dato.importe}}</td>
									<td>
										<span class="label label-danger" v-if="dato.estado==0">ANULADO</span>
										<span class="label label-success" v-if="dato.estado==1">ACTIVO</span>
									</td>
								</tr>
								<tr>
									<th colspan="5" style="text-align:right">TOTALES</th>	
									<th>{{total.totaltotal}}</th>	
									<th></th>	
									<th>{{total.totalpago}}</th>
									<th></th>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_creditos/listacobranza.js"> </script>