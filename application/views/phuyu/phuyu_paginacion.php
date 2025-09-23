<div class="row">
	<div class="col-md-6 hidden-xs">
		<p>TOTAL REGISTROS <b>{{paginacion.total}}</b> ENCONTRADOS</p>
	</div>
	<div class="col-md-6">
		<ul class="pagination bordered pull-right">
			<li class="page-item disabled" v-if="paginacion.actual <= 1">
		    	<a class="page-link"> <i data-acorn-icon="chevron-left"></i> </a> 
		    </li>
		    <li class="page-item" v-if="paginacion.actual > 1">
		    	<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(paginacion.actual - 1)"> 
		    		<i data-acorn-icon="chevron-left"></i> 
		    	</a> 
		    </li>

		    <li class="page-item" v-for="pag in phuyu_paginas" v-bind:class="[pag==phuyu_actual ? 'active':'']">
		    	<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(pag)">{{pag}}</a> 
		    </li>

		    <li class="page-item" v-if="paginacion.actual < paginacion.ultima">
		    	<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(paginacion.actual + 1)"> 
		    	 <i data-acorn-icon="chevron-right"></i> 
		    	</a> 
		    </li>
		    <li class="page-item disabled" v-if="paginacion.actual >= paginacion.ultima">
		    	<a class="page-link"> <i data-acorn-icon="chevron-right"></i> </a> 
		    </li>
		</ul>
	</div>
</div>