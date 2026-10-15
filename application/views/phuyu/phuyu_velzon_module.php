<style>
	.phuyu-velzon-list .phuyu-page-title,
	.phuyu-velzon-form .phuyu-page-title,
	.phuyu-velzon-form .phuyu-form-title {
		display: flex;
		align-items: center;
		gap: .75rem;
		margin-bottom: 1rem;
	}

	.phuyu-velzon-list .phuyu-page-icon,
	.phuyu-velzon-form .phuyu-page-icon,
	.phuyu-velzon-form .phuyu-form-icon {
		width: 44px;
		height: 44px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .1);
		color: #405189;
		font-size: 1.2rem;
		flex: 0 0 auto;
	}

	.phuyu-velzon-list .phuyu-card,
	.phuyu-velzon-form .phuyu-card {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	.phuyu-velzon-list .phuyu-toolbar {
		display: flex;
		flex-wrap: wrap;
		gap: .65rem;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 1rem;
	}

	.phuyu-velzon-list .phuyu-search {
		position: relative;
		flex: 1 1 280px;
		max-width: 380px;
	}

	.phuyu-velzon-list .phuyu-search .form-control {
		min-height: 40px;
		padding-left: 2.4rem;
		border-radius: 10px;
		border-color: rgba(64, 81, 137, .18);
	}

	.phuyu-velzon-list .phuyu-search i {
		position: absolute;
		left: .9rem;
		top: 50%;
		transform: translateY(-50%);
		color: #878a99;
	}

	.phuyu-velzon-list .phuyu-actions,
	.phuyu-velzon-form .phuyu-form-actions {
		display: flex;
		flex-wrap: wrap;
		gap: .5rem;
	}

	.phuyu-velzon-list .phuyu-actions {
		justify-content: flex-end;
	}

	.phuyu-velzon-form .phuyu-form-actions {
		justify-content: flex-end;
		padding-top: 1rem;
		margin-top: 1rem;
		border-top: 1px solid rgba(64, 81, 137, .12);
	}

	.phuyu-velzon-list .phuyu-table-wrap {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .75rem;
		overflow: auto;
	}

	.phuyu-velzon-list table {
		margin-bottom: 0;
		min-width: 720px;
	}

	.phuyu-velzon-list table thead th {
		background: #f3f6f9;
		color: #343a40;
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
		vertical-align: middle;
	}

	.phuyu-velzon-list table tbody td {
		font-size: .86rem;
		vertical-align: middle;
	}

	.phuyu-velzon-form .card-body {
		padding: 1rem;
	}

	.phuyu-velzon-form .form-label,
	.phuyu-velzon-form label {
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .4rem;
	}

	.phuyu-velzon-form .form-control,
	.phuyu-velzon-form .form-select {
		min-height: 40px;
		border-radius: 9px;
		border-color: rgba(64, 81, 137, .18);
	}

	.phuyu-velzon-form .phuyu-section-title {
		font-size: .82rem;
		font-weight: 800;
		color: #405189;
		text-transform: uppercase;
		letter-spacing: .04em;
		margin: 1rem 0 .75rem;
	}

	.phuyu-velzon-form .phuyu-table-wrap {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .75rem;
		overflow: auto;
	}

	.phuyu-velzon-form .phuyu-table-wrap table {
		margin-bottom: 0;
	}

	.phuyu-reportes-velzon .card,
	.phuyu-creditos-velzon .card {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	.phuyu-reportes-velzon .card .card,
	.phuyu-creditos-velzon .card .card {
		box-shadow: none;
		background: #f8f9fa;
	}

	.phuyu-reportes-velzon .card-body,
	.phuyu-reportes-velzon .card-header,
	.phuyu-creditos-velzon .card-body,
	.phuyu-creditos-velzon .card-header {
		padding: 1rem;
	}

	.phuyu-reportes-velzon h4,
	.phuyu-reportes-velzon h5,
	.phuyu-reportes-velzon p[style*="font-size"],
	.phuyu-creditos-velzon h4,
	.phuyu-creditos-velzon h5 {
		color: #212529;
		font-weight: 800;
		letter-spacing: 0 !important;
		margin-bottom: .75rem;
	}

	.phuyu-reportes-velzon label,
	.phuyu-creditos-velzon label {
		font-size: .72rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .35rem;
	}

	.phuyu-reportes-velzon .form-control,
	.phuyu-reportes-velzon .form-select,
	.phuyu-creditos-velzon .form-control,
	.phuyu-creditos-velzon .form-select {
		min-height: 40px;
		border-radius: 9px;
		border-color: rgba(64, 81, 137, .18);
	}

	.phuyu-reportes-velzon .btn,
	.phuyu-creditos-velzon .btn {
		border-radius: 9px;
		font-weight: 700;
	}

	.phuyu-reportes-velzon .btn-icon,
	.phuyu-creditos-velzon .btn-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: .35rem;
	}

	.phuyu-reportes-velzon .table-responsive,
	.phuyu-reportes-velzon .phuyu-table-wrap,
	.phuyu-creditos-velzon .table-responsive,
	.phuyu-creditos-velzon .phuyu-table-wrap {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .75rem;
		overflow: auto;
	}

	.phuyu-reportes-velzon table,
	.phuyu-creditos-velzon table {
		margin-bottom: 0;
	}

	.phuyu-reportes-velzon table thead th,
	.phuyu-reportes-velzon table th,
	.phuyu-creditos-velzon table thead th,
	.phuyu-creditos-velzon table th {
		background: #f3f6f9;
		color: #343a40;
		font-size: .72rem;
		font-weight: 800;
		text-transform: uppercase;
		vertical-align: middle;
		white-space: nowrap;
	}

	.phuyu-reportes-velzon table td,
	.phuyu-creditos-velzon table td {
		vertical-align: middle;
	}

	.phuyu-reportes-velzon .input-group-addon,
	.phuyu-creditos-velzon .input-group-addon {
		display: flex;
		align-items: center;
		gap: .35rem;
		padding: .45rem .65rem;
		border: 1px solid rgba(64, 81, 137, .18);
		border-right: 0;
		border-radius: 9px 0 0 9px;
		background: #f3f6f9;
		font-size: .72rem;
		font-weight: 800;
		color: #495057;
		text-transform: uppercase;
	}

	.phuyu-reportes-velzon .label,
	.phuyu-creditos-velzon .label {
		display: inline-flex;
		align-items: center;
		border-radius: 999px;
		padding: .25rem .55rem;
		font-size: .68rem;
		font-weight: 800;
		line-height: 1;
	}

	.phuyu-reportes-velzon .label-danger,
	.phuyu-creditos-velzon .label-danger {
		background: rgba(240, 101, 72, .12);
		color: #f06548;
	}

	.phuyu-reportes-velzon .label-warning,
	.phuyu-creditos-velzon .label-warning {
		background: rgba(247, 184, 75, .16);
		color: #b7791f;
	}

	.phuyu-reportes-velzon .label-success,
	.phuyu-creditos-velzon .label-success {
		background: rgba(10, 179, 156, .12);
		color: #0ab39c;
	}

	.phuyu-reportes-velzon .label-info,
	.phuyu-creditos-velzon .label-info,
	.phuyu-reportes-velzon .label-primary,
	.phuyu-creditos-velzon .label-primary {
		background: rgba(64, 81, 137, .1);
		color: #405189;
	}

	.phuyu-reportes-velzon .modal-header,
	.phuyu-creditos-velzon .modal-header {
		background: #f3f6f9;
		border-bottom: 1px solid rgba(64, 81, 137, .12);
	}

	.phuyu-reportes-velzon .modal-title,
	.phuyu-creditos-velzon .modal-title {
		font-weight: 800;
		color: #212529;
	}

	.phuyu-cpe-velzon .card,
	.phuyu-agricola-velzon .card {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	.phuyu-cpe-velzon .card-body,
	.phuyu-cpe-velzon .card-header,
	.phuyu-agricola-velzon .card-body,
	.phuyu-agricola-velzon .card-header {
		padding: 1rem;
	}

	.phuyu-cpe-velzon h4,
	.phuyu-cpe-velzon h5,
	.phuyu-agricola-velzon h4,
	.phuyu-agricola-velzon h5 {
		color: #212529;
		font-weight: 800;
		letter-spacing: 0 !important;
	}

	.phuyu-cpe-velzon label,
	.phuyu-agricola-velzon label {
		font-size: .72rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .35rem;
	}

	.phuyu-cpe-velzon .form-control,
	.phuyu-cpe-velzon .form-select,
	.phuyu-agricola-velzon .form-control,
	.phuyu-agricola-velzon .form-select {
		min-height: 40px;
		border-radius: 9px;
		border-color: rgba(64, 81, 137, .18);
	}

	.phuyu-cpe-velzon .btn,
	.phuyu-agricola-velzon .btn {
		border-radius: 9px;
		font-weight: 700;
	}

	.phuyu-cpe-velzon .btn-block,
	.phuyu-agricola-velzon .btn-block {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 100%;
		gap: .35rem;
	}

	.phuyu-cpe-velzon .btn-xs,
	.phuyu-agricola-velzon .btn-xs {
		padding: .25rem .5rem;
		font-size: .72rem;
		line-height: 1.2;
	}

	.phuyu-cpe-velzon .table-responsive,
	.phuyu-cpe-velzon .phuyu-table-wrap,
	.phuyu-agricola-velzon .table-responsive,
	.phuyu-agricola-velzon .phuyu-table-wrap {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .75rem;
		overflow: auto;
	}

	.phuyu-cpe-velzon table,
	.phuyu-agricola-velzon table {
		margin-bottom: 0;
	}

	.phuyu-cpe-velzon table thead th,
	.phuyu-cpe-velzon table th,
	.phuyu-agricola-velzon table thead th,
	.phuyu-agricola-velzon table th {
		background: #f3f6f9;
		color: #343a40;
		font-size: .72rem;
		font-weight: 800;
		text-transform: uppercase;
		vertical-align: middle;
		white-space: nowrap;
	}

	.phuyu-cpe-velzon table td,
	.phuyu-agricola-velzon table td {
		vertical-align: middle;
	}

	.phuyu-cpe-velzon .label,
	.phuyu-agricola-velzon .label {
		display: inline-flex;
		align-items: center;
		border-radius: 999px;
		padding: .25rem .55rem;
		font-size: .68rem;
		font-weight: 800;
		line-height: 1;
	}

	.phuyu-cpe-velzon .label-danger,
	.phuyu-agricola-velzon .label-danger {
		background: rgba(240, 101, 72, .12);
		color: #f06548;
	}

	.phuyu-cpe-velzon .label-warning,
	.phuyu-agricola-velzon .label-warning {
		background: rgba(247, 184, 75, .16);
		color: #b7791f;
	}

	.phuyu-cpe-velzon .label-success,
	.phuyu-agricola-velzon .label-success {
		background: rgba(10, 179, 156, .12);
		color: #0ab39c;
	}

	.phuyu-cpe-velzon .badge-danger,
	.phuyu-agricola-velzon .badge-danger {
		background: #f06548;
		color: #fff;
	}

	.phuyu-cpe-velzon .badge-warning,
	.phuyu-agricola-velzon .badge-warning {
		background: #f7b84b;
		color: #212529;
	}

	.phuyu-cpe-velzon .badge-info,
	.phuyu-cpe-velzon .badge-teal,
	.phuyu-agricola-velzon .badge-info,
	.phuyu-agricola-velzon .badge-teal {
		background: #0ab39c;
		color: #fff;
	}

	.phuyu-cpe-velzon .badge-secondary,
	.phuyu-agricola-velzon .badge-secondary {
		background: #74788d;
		color: #fff;
	}

	.phuyu-cpe-velzon .x_panel,
	.phuyu-agricola-velzon .x_panel {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .75rem;
		background: #fff;
		padding: 1rem;
		margin-bottom: 1rem;
	}

	.phuyu-cpe-velzon .modal-header,
	.phuyu-agricola-velzon .modal-header {
		background: #f3f6f9;
		border-bottom: 1px solid rgba(64, 81, 137, .12);
	}

	.phuyu-cpe-velzon .modal-title,
	.phuyu-agricola-velzon .modal-title {
		font-weight: 800;
		color: #212529;
	}

	@media (max-width: 575.98px) {
		.phuyu-velzon-list .phuyu-search,
		.phuyu-velzon-list .phuyu-actions .btn,
		.phuyu-velzon-form .phuyu-form-actions .btn {
			width: 100%;
			max-width: 100%;
		}
	}
</style>
