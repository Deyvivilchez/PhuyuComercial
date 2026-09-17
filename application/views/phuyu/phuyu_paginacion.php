<style>
	.phuyu-pagination-wrap {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		flex-wrap: wrap;
		padding: 14px 18px;
		border: 1px solid #e8edf3;
		border-radius: 18px;
		background: #fff;
		box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
	}

	.phuyu-pagination-info {
		margin: 0;
		font-size: 0.92rem;
		color: #64748b;
	}

	.phuyu-pagination-info b {
		color: #0f172a;
		font-weight: 800;
	}

	.phuyu-pagination {
		margin: 0;
		gap: 6px;
		flex-wrap: wrap;
	}

	.phuyu-pagination .page-item .page-link {
		min-width: 40px;
		height: 40px;
		padding: 0 12px;
		border-radius: 12px;
		border: 1px solid #e2e8f0;
		background: #fff;
		color: #334155;
		font-weight: 700;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		box-shadow: none;
		transition: all .18s ease;
	}

	.phuyu-pagination .page-item .page-link:hover {
		background: #f8fafc;
		border-color: #cbd5e1;
		color: #0f172a;
	}

	.phuyu-pagination .page-item.active .page-link {
		background: linear-gradient(90deg, #0ea5e9 0%, #6366f1 100%);
		border-color: transparent;
		color: #fff;
		box-shadow: 0 10px 24px rgba(99, 102, 241, 0.22);
	}

	.phuyu-pagination .page-item.disabled .page-link {
		background: #f8fafc;
		color: #94a3b8;
		border-color: #e2e8f0;
		cursor: not-allowed;
	}

	@media (max-width: 767.98px) {
		.phuyu-pagination-wrap {
			flex-direction: column;
			align-items: stretch;
		}

		.phuyu-pagination-info {
			text-align: center;
		}

		.phuyu-pagination {
			justify-content: center;
		}
	}
</style>

<div class="phuyu-pagination-wrap">
	<p class="phuyu-pagination-info">
		Total de registros encontrados: <b>{{ paginacion.total }}</b>
	</p>

	<ul class="pagination phuyu-pagination">
		<li class="page-item disabled" v-if="paginacion.actual <= 1">
			<a class="page-link">
				<i class="bi bi-chevron-left"></i>
			</a>
		</li>

		<li class="page-item" v-if="paginacion.actual > 1">
			<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(paginacion.actual - 1)">
				<i class="bi bi-chevron-left"></i>
			</a>
		</li>

		<li class="page-item" v-for="pag in phuyu_paginas" :class="[pag == phuyu_actual ? 'active' : '']">
			<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(pag)">
				{{ pag }}
			</a>
		</li>

		<li class="page-item" v-if="paginacion.actual < paginacion.ultima">
			<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(paginacion.actual + 1)">
				<i class="bi bi-chevron-right"></i>
			</a>
		</li>

		<li class="page-item disabled" v-if="paginacion.actual >= paginacion.ultima">
			<a class="page-link">
				<i class="bi bi-chevron-right"></i>
			</a>
		</li>
	</ul>
</div>