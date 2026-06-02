<style>
	:root {
		--ph-404-primary: #405189;
		--ph-404-primary-dark: #33416e;
		--ph-404-accent: #0ab39c;
		--ph-404-danger: #f06548;
		--ph-404-text: #1f2937;
		--ph-404-muted: #6b7280;
		--ph-404-border: #e5e7eb;
		--ph-404-soft: #f3f6f9;
	}

	html,
	body {
		min-height: 100%;
	}

	body {
		background: #f3f6f9;
		color: var(--ph-404-text);
		font-family: "Inter", "Public Sans", "Segoe UI", Arial, sans-serif;
		margin: 0;
	}

	.ph-404-page,
	.ph-404-page * {
		box-sizing: border-box;
	}

	.ph-404-page {
		align-items: center;
		display: flex;
		justify-content: center;
		min-height: 100vh;
		overflow: hidden;
		padding: 34px 18px;
		position: relative;
	}

	.ph-404-page::before {
		background: #5b342f;
		content: "";
		height: 12px;
		left: 0;
		position: fixed;
		right: 0;
		top: 0;
		z-index: 0;
	}

	.ph-404-card {
		background: #fff;
		border: 1px solid rgba(148, 163, 184, .22);
		border-radius: 18px;
		box-shadow: 0 24px 70px rgba(15, 23, 42, .12);
		display: grid;
		grid-template-columns: minmax(0, 1.05fr) minmax(320px, .95fr);
		max-width: 1080px;
		min-height: 520px;
		overflow: hidden;
		position: relative;
		width: 100%;
		z-index: 1;
	}

	.ph-404-content {
		display: flex;
		flex-direction: column;
		justify-content: center;
		padding: 52px;
	}

	.ph-404-brand {
		align-items: center;
		display: flex;
		gap: 14px;
		margin-bottom: 36px;
	}

	.ph-404-brand img {
		display: block;
		height: 38px;
		max-width: 180px;
		object-fit: contain;
		width: auto;
	}

	.ph-404-badge {
		background: rgba(64, 81, 137, .09);
		border: 1px solid rgba(64, 81, 137, .13);
		border-radius: 999px;
		color: var(--ph-404-primary);
		font-size: 12px;
		font-weight: 800;
		letter-spacing: .02em;
		padding: 8px 12px;
		text-transform: uppercase;
		white-space: nowrap;
	}

	.ph-404-kicker {
		align-items: center;
		color: var(--ph-404-danger);
		display: inline-flex;
		font-size: 13px;
		font-weight: 900;
		gap: 9px;
		letter-spacing: .08em;
		margin-bottom: 14px;
		text-transform: uppercase;
	}

	.ph-404-kicker::before {
		background: var(--ph-404-danger);
		border-radius: 999px;
		content: "";
		height: 8px;
		width: 8px;
	}

	.ph-404-title {
		color: #111827;
		font-size: clamp(42px, 7vw, 84px);
		font-weight: 900;
		letter-spacing: 0;
		line-height: .94;
		margin: 0 0 18px;
	}

	.ph-404-subtitle {
		color: #111827;
		font-size: clamp(22px, 3vw, 32px);
		font-weight: 850;
		letter-spacing: 0;
		line-height: 1.15;
		margin: 0;
		max-width: 620px;
	}

	.ph-404-text {
		color: var(--ph-404-muted);
		font-size: 15px;
		font-weight: 500;
		line-height: 1.65;
		margin: 18px 0 0;
		max-width: 560px;
	}

	.ph-404-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		margin-top: 30px;
	}

	.ph-404-btn {
		align-items: center;
		border: 1px solid transparent;
		border-radius: 10px;
		cursor: pointer;
		display: inline-flex;
		font-size: 14px;
		font-weight: 800;
		gap: 8px;
		justify-content: center;
		min-height: 42px;
		padding: 0 18px;
		text-decoration: none;
		transition: transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease;
	}

	.ph-404-btn:hover {
		transform: translateY(-1px);
	}

	.ph-404-btn-primary {
		background: var(--ph-404-primary);
		box-shadow: 0 12px 22px rgba(64, 81, 137, .22);
		color: #fff;
	}

	.ph-404-btn-primary:hover {
		background: var(--ph-404-primary-dark);
		color: #fff;
	}

	.ph-404-btn-light {
		background: #fff;
		border-color: #d7dce5;
		color: #374151;
	}

	.ph-404-btn-light:hover {
		background: #f8fafc;
		color: #111827;
	}

	.ph-404-links {
		display: grid;
		gap: 10px;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		margin-top: 24px;
		max-width: 620px;
	}

	.ph-404-link {
		align-items: center;
		background: #f8fafc;
		border: 1px solid var(--ph-404-border);
		border-radius: 12px;
		color: #374151;
		display: flex;
		gap: 10px;
		min-height: 58px;
		padding: 12px;
		text-decoration: none;
		transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
	}

	.ph-404-link:hover {
		border-color: rgba(64, 81, 137, .35);
		box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
		color: #111827;
		transform: translateY(-1px);
	}

	.ph-404-link-icon {
		align-items: center;
		background: #eef2ff;
		border-radius: 10px;
		color: var(--ph-404-primary);
		display: flex;
		flex: 0 0 34px;
		font-size: 17px;
		font-weight: 900;
		height: 34px;
		justify-content: center;
		width: 34px;
	}

	.ph-404-link strong {
		display: block;
		font-size: 13px;
		font-weight: 900;
		line-height: 1.2;
	}

	.ph-404-link span {
		color: var(--ph-404-muted);
		display: block;
		font-size: 11px;
		font-weight: 700;
		line-height: 1.25;
		margin-top: 2px;
	}

	.ph-404-visual {
		background: linear-gradient(155deg, #eef2ff 0%, #f8fafc 46%, #e8fbf7 100%);
		display: flex;
		flex-direction: column;
		justify-content: center;
		padding: 42px 38px;
		position: relative;
	}

	.ph-404-visual::before {
		background-image: radial-gradient(rgba(64, 81, 137, .18) 1px, transparent 1px);
		background-size: 18px 18px;
		bottom: 0;
		content: "";
		left: 0;
		opacity: .45;
		position: absolute;
		right: 0;
		top: 0;
	}

	.ph-404-illustration {
		align-items: center;
		display: flex;
		justify-content: center;
		position: relative;
		z-index: 1;
	}

	.ph-404-illustration img {
		display: block;
		filter: drop-shadow(0 24px 30px rgba(30, 41, 59, .12));
		height: auto;
		max-width: 100%;
		width: 440px;
	}

	.ph-404-note {
		background: rgba(255, 255, 255, .78);
		border: 1px solid rgba(255, 255, 255, .95);
		border-radius: 14px;
		box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
		margin-top: 28px;
		padding: 16px 18px;
		position: relative;
		z-index: 1;
	}

	.ph-404-note strong {
		color: #111827;
		display: block;
		font-size: 13px;
		font-weight: 900;
		margin-bottom: 5px;
	}

	.ph-404-note span {
		color: var(--ph-404-muted);
		display: block;
		font-size: 12px;
		font-weight: 600;
		line-height: 1.5;
	}

	.ph-404-footer {
		align-items: center;
		color: #94a3b8;
		display: flex;
		flex-wrap: wrap;
		font-size: 12px;
		font-weight: 700;
		gap: 8px;
		margin-top: 34px;
	}

	.ph-404-footer a {
		color: var(--ph-404-primary);
		font-weight: 900;
		text-decoration: none;
	}

	@media (max-width: 991.98px) {
		.ph-404-card {
			grid-template-columns: 1fr;
			max-width: 720px;
		}

		.ph-404-content {
			padding: 36px 28px;
		}

		.ph-404-visual {
			order: -1;
			padding: 30px 28px;
		}

		.ph-404-illustration img {
			max-width: 360px;
		}
	}

	@media (max-width: 575.98px) {
		.ph-404-page {
			align-items: stretch;
			padding: 20px 12px;
		}

		.ph-404-card {
			border-radius: 14px;
			min-height: 0;
		}

		.ph-404-brand {
			align-items: flex-start;
			flex-direction: column;
			margin-bottom: 24px;
		}

		.ph-404-actions,
		.ph-404-btn {
			width: 100%;
		}

		.ph-404-links {
			grid-template-columns: 1fr;
		}

		.ph-404-visual {
			padding: 24px 20px;
		}

		.ph-404-note {
			margin-top: 18px;
		}
	}
</style>

<div class="ph-404-page" role="main" aria-label="Pagina no encontrada">
	<section class="ph-404-card">
		<div class="ph-404-content">
			<div class="ph-404-brand">
				<img src="<?php echo base_url('public/img/phuyu2024-bk.png'); ?>" alt="Phuyu System" onerror="this.onerror=null; this.src='<?php echo base_url('public/img/phuyu.png'); ?>'">
				<span class="ph-404-badge">Phuyu Sistema</span>
			</div>

			<div class="ph-404-kicker">Error 404</div>
			<h1 class="ph-404-title">Pagina no encontrada</h1>
			<h2 class="ph-404-subtitle">La ruta que intentaste abrir no existe o fue movida.</h2>
			<p class="ph-404-text">
				No pasa nada, puedes volver al inicio del sistema o entrar directo a uno de los modulos principales.
			</p>

			<div class="ph-404-actions">
				<a href="<?php echo base_url('phuyu/w/'); ?>" class="ph-404-btn ph-404-btn-primary" data-force-nav>
					<span>Inicio</span>
				</a>
				<button type="button" class="ph-404-btn ph-404-btn-light" id="backButton">
					<span>Volver atras</span>
				</button>
			</div>

			<div class="ph-404-links" aria-label="Accesos rapidos">
				<a href="<?php echo base_url('phuyu/w/administracion/dashboard'); ?>" class="ph-404-link" data-force-nav>
					<span class="ph-404-link-icon">A</span>
					<span>
						<strong>Administracion</strong>
						<span>Configurar sistema</span>
					</span>
				</a>
				<a href="<?php echo base_url('phuyu/w/ventas/ventas'); ?>" class="ph-404-link" data-force-nav>
					<span class="ph-404-link-icon">V</span>
					<span>
						<strong>Ventas</strong>
						<span>Ir a operaciones</span>
					</span>
				</a>
				<a href="<?php echo base_url('phuyu/w/almacen/productos'); ?>" class="ph-404-link" data-force-nav>
					<span class="ph-404-link-icon">K</span>
					<span>
						<strong>Almacen</strong>
						<span>Productos y stock</span>
					</span>
				</a>
			</div>

			<div class="ph-404-footer">
				<span>&copy; <?php echo date('Y'); ?> Phuyu System</span>
				<span>|</span>
				<a href="<?php echo base_url('phuyu/w/'); ?>" data-force-nav>Ir al panel</a>
			</div>
		</div>

		<aside class="ph-404-visual" aria-hidden="true">
			<div class="ph-404-illustration">
				<img src="<?php echo base_url('public/img1/404.png'); ?>" alt="">
			</div>
			<div class="ph-404-note">
				<strong>Ruta no disponible</strong>
				<span>Si llegaste desde un menu, revisa permisos o que el modulo siga activo.</span>
			</div>
		</aside>
	</section>
</div>

<script>
	(function() {
		document.querySelectorAll("[data-force-nav]").forEach(function(el) {
			el.addEventListener("click", function(event) {
				var href = el.getAttribute("href");
				if (href) {
					event.preventDefault();
					window.location.href = href;
				}
			});
		});

		var backBtn = document.getElementById("backButton");
		if (backBtn) {
			backBtn.addEventListener("click", function() {
				if (window.history.length > 1) {
					window.history.back();
					return;
				}
				window.location.href = "<?php echo base_url('phuyu/w/'); ?>";
			});
		}
	})();
</script>
