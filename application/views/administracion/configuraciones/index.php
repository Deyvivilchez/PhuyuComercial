<!--
Phuyu – Configuración de Empresa (UI renovada v2)
- Mantiene los mismos name/id/v-model y eventos para no romper lógica.
- Estilo completamente distinto: encabezado glass + gradientes, tarjetas neumórficas,
  tipografía compacta, campos con bordes fluidos, switch y file-drop custom.
- Paleta corporativa tomada del logo (azules/navy) y aplicada con CSS vars.
- Responsive mejorado y sticky bar con botón Guardar siempre visible.
-->



<div id="phuyu_datos" class="phuyu-theme">
	<!-- Top bar -->


	<main class="container-xxl py-4">
		<header class="phuyu-topbar">
			<div class="phuyu-topbar__inner container-xxl">
				<div class="d-flex align-items-center gap-3">
					<div class="brand d-flex align-items-center gap-2">
						<img src="<?php echo base_url(); ?>public/img/phuyu2024-bk.png" alt="Phuyu System" class="brand-logo" />
						<div class="brand-title">
							<span class="kicker">Panel</span>
							<h1 class="h6 mb-0">Configuraciones de tu empresa</h1>
						</div>
					</div>

				</div>
			</div>
		</header>
		<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
			<input type="hidden" name="codpersona" v-model="campos.codpersona" />
			<input type="hidden" name="codempresa" v-model="campos.codempresa" />
			<input type="hidden" name="itemrepetircomprobante" v-model="campos.itemrepetircomprobante" />

			<div class="row g-4">
				<!-- Izquierda -->
				<div class="col-12 col-lg-6">
					<section class="phy-card">
						<div class="phy-card__head">
							<h2 class="title">Datos de la empresa</h2>
						</div>
						<div class="phy-card__body">
							<div class="row g-3 row-ruc">
  <div class="col-12 col-md-7">
    <label class="form-label">RUC empresa</label>
    <input type="text" class="phy-input" name="documento" v-model="campos.documento" 
           id="documento" placeholder="11 dígitos" required autocomplete="off" 
           minlength="11" maxlength="11" />
    <div class="hint">Solo números</div>
  </div>
  <div class="col-12 col-md-5">
    <label class="form-label d-none d-md-block">&nbsp;</label> <!-- espacio para alinear -->
    <button type="button"
            class="btn phy-btn-sunat w-100"
            v-on:click="phuyu_consultar()">
      <i data-acorn-icon="search"></i>
      <span class="ms-1">Consultar SUNAT</span>
    </button>
  </div>
</div>


							<div class="mt-3">
								<label class="form-label">Razón social</label>
								<input type="text" class="phy-input" name="razonsocial" v-model="campos.razonsocial" placeholder="Razón social" required autocomplete="off" />
							</div>

							<div class="mt-3">
								<label class="form-label">Nombre comercial</label>
								<input type="text" class="phy-input" name="nombrecomercial" v-model="campos.nombrecomercial" placeholder="Nombre comercial" autocomplete="off" />
							</div>

							<div class="row g-3 mt-1">
								<div class="col-12 col-md-8">
									<label class="form-label">Dirección</label>
									<input type="text" class="phy-input" name="direccion" v-model="campos.direccion" placeholder="Av./Jr./Mz./Lt." required autocomplete="off" />
								</div>
								<div class="col-12 col-md-4">
									<label class="form-label">Clave seguridad</label>
									<input type="password" class="phy-input" name="claveseguridad" v-model="campos.claveseguridad" placeholder="••••••" autocomplete="off" maxlength="50" />
								</div>
							</div>

							<div class="row g-3 mt-1">
								<div class="col-12 col-md-4">
									<label class="form-label">Departamento</label>
									<select class="phy-select" name="departamento" v-model="campos.departamento" required v-on:change="phuyu_provincias()">
										<option value="">Seleccione</option>
										<?php foreach ($departamentos as $key => $value) { ?>
											<option value="<?php echo $value['ubidepartamento']; ?>"><?php echo $value['departamento']; ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="col-12 col-md-4">
									<label class="form-label">Provincia</label>
									<select class="phy-select" name="provincia" v-model="campos.provincia" id="provincia" required v-on:change="phuyu_distritos()">
										<option value="">Seleccione</option>
									</select>
								</div>
								<div class="col-12 col-md-4">
									<label class="form-label">Distrito</label>
									<select class="phy-select" name="codubigeo" v-model="campos.codubigeo" id="codubigeo" required>
										<option value="">Seleccione</option>
									</select>
								</div>
							</div>

							<div class="row g-3 mt-1">
								<div class="col-12 col-md-6">
									<label class="form-label">Email empresa</label>
									<input type="email" class="phy-input" name="email" v-model="campos.email" placeholder="correo@empresa.com" autocomplete="off" />
								</div>
								<div class="col-12 col-md-6">
									<label class="form-label">Telf./Cel.</label>
									<input type="text" class="phy-input" name="telefono" v-model="campos.telefono" placeholder="Ej. 942 000 000" autocomplete="off" maxlength="100" />
								</div>
							</div>

							<div class="mt-3">
								<label class="form-label">Slogan empresa</label>
								<textarea class="phy-input" name="slogan" v-model="campos.slogan" placeholder="Una frase corta que te identifique" rows="2"></textarea>
							</div>

							<div class="row g-3 mt-1">
								<div class="col-12 col-md-4">
									<label class="form-label">Código (Bienes)</label>
									<input type="text" class="phy-input" v-model="campos.codleyendapamazonia" name="codleyendapamazonia" placeholder="Código" />
								</div>
								<div class="col-12 col-md-8">
									<label class="form-label">Leyenda de bienes</label>
									<textarea class="phy-input" name="leyendapamazonia" v-model="campos.leyendapamazonia" placeholder="Leyenda de bienes..." rows="1"></textarea>
								</div>
								<div class="col-12 col-md-4">
									<label class="form-label">Código (Servicios)</label>
									<input type="text" class="phy-input" v-model="campos.codleyendasamazonia" name="codleyendasamazonia" placeholder="Código" />
								</div>
								<div class="col-12 col-md-8">
									<label class="form-label">Leyenda de servicios</label>
									<textarea class="phy-input" name="leyendasamazonia" v-model="campos.leyendasamazonia" placeholder="Leyenda de servicios..." rows="1"></textarea>
								</div>
							</div>
						</div>
					</section>
				</div>

				<!-- Derecha -->
				<div class="col-12 col-lg-6">
					<section class="phy-card">
						<div class="phy-card__head">
							<h2 class="title">Parámetros SUNAT</h2>
						</div>
						<div class="phy-card__body">
							<div class="row g-3">
								<div class="col-12 col-md-4">
									<label class="form-label">IGV SUNAT (%)</label>
									<input type="number" step="0.01" class="phy-input text-end" name="igvsunat" v-model.number="campos.igvsunat" required />
								</div>
								<div class="col-12 col-md-4">
									<label class="form-label">ICBPER SUNAT (%)</label>
									<input type="number" step="0.01" class="phy-input text-end" name="icbpersunat" v-model.number="campos.icbpersunat" required />
								</div>
								<div class="col-12 col-md-4">
									<label class="form-label">ISC SUNAT (%)</label>
									<input type="number" step="0.01" class="phy-input text-end" name="iscsunat" v-model.number="campos.iscsunat" required />
								</div>
							</div>

							<div class="mt-3">
								<label class="form-label d-block mb-1">Repetir ítem en comprobante</label>
								<label class="phy-switch">
									<input type="checkbox" :checked="campos.itemrepetircomprobante==1" @click="phuyu_itemrepetir()" />
									<span class="track"></span>
									<span class="label ms-2">Activar</span>
								</label>
								<div class="hint">Repite el último ítem al agregar más productos/servicios.</div>
							</div>
						</div>
					</section>

					<section class="phy-card mt-4">
						<div class="phy-card__head">
							<h2 class="title">Logos y mensajes</h2>
						</div>
						<div class="phy-card__body">
							<div class="row g-3">
								<div class="col-12 col-md-6">
									<label class="form-label">Logo empresa</label>
									<label class="phy-drop">
										<input type="file" name="logo" accept="image/*" />
										<span class="ico" aria-hidden>🖼️</span>
										<span class="txt">Arrastra o <u>selecciona</u> (PNG/JPG 400×400)</span>
									</label>
								</div>
								<div class="col-12 col-md-6">
									<label class="form-label">Logo auspiciador</label>
									<label class="phy-drop">
										<input type="file" name="auspiciador" accept="image/*" />
										<span class="ico" aria-hidden>🏷️</span>
										<span class="txt">Arrastra o <u>selecciona</u></span>
									</label>
								</div>
							</div>

							<div class="mt-3">
								<label class="form-label">Publicidad</label>
								<textarea class="phy-input" name="publicidad" v-model="campos.publicidad" placeholder="Texto de banner o pie de ticket" rows="1"></textarea>
							</div>

							<div class="mt-3">
								<label class="form-label">Agradecimiento</label>
								<textarea class="phy-input" name="agradecimiento" v-model="campos.agradecimiento" placeholder="Mensaje de agradecimiento" rows="1"></textarea>
							</div>

							<div class="mt-3">
								<label class="form-label">URL consulta comprobantes</label>
								<textarea class="phy-input" name="urlconsultacomprobantes" v-model="campos.urlconsultacomprobantes" placeholder="https://tu-dominio.com/consultas" rows="1"></textarea>
							</div>
						</div>
					</section>
					<section class="phy-card mt-4">
						<div class="phy-card__head">
							<h2 class="title">Actualizar Datos</h2>
						</div>
						<div class="phy-card__body">
							<div class="text-end mt-4">
								<button form="formulario"
									type="submit"
									class="btn phy-btn-primary btn-lg">
									<i data-acorn-icon="save"></i>
									<span class="ms-1">Guardar configuración</span>
								</button>
							</div>

						</div>

					</section>
				</div>
			</div>
		</form>
	</main>
</div>

<!-- ICONS INIT (igual que antes) -->
<script>
	if (typeof AcornIcons !== 'undefined') {
		new AcornIcons().replace();
	}
	if (typeof Icons !== 'undefined') {
		const icons = new Icons();
	}
</script>

<!-- DATA (sin cambios) -->
<script>
	var campos = {
		codpersona: "<?php echo $info[0]["codpersona"]; ?>",
		codempresa: "<?php echo $empresa[0]["codempresa"]; ?>",
		documento: "<?php echo $info[0]["documento"]; ?>",
		razonsocial: "<?php echo $info[0]["razonsocial"]; ?>",
		nombrecomercial: "<?php echo $info[0]["nombrecomercial"]; ?>",
		direccion: "<?php echo $info[0]["direccion"]; ?>",
		claveseguridad: "<?php echo $empresa[0]["claveseguridad"]; ?>",
		email: "<?php echo $info[0]["email"]; ?>",
		telefono: "<?php echo $info[0]["telefono"]; ?>",
		slogan: "<?php echo $empresa[0]["slogan"]; ?>",
		igvsunat: "<?php echo $empresa[0]["igvsunat"]; ?>",
		icbpersunat: "<?php echo $empresa[0]["icbpersunat"]; ?>",
		iscsunat: "<?php echo $empresa[0]["iscsunat"]; ?>",
		itemrepetircomprobante: "<?php echo $empresa[0]["itemrepetircomprobante"]; ?>",
		publicidad: "<?php echo $empresa[0]["publicidad"]; ?>",
		agradecimiento: "<?php echo $empresa[0]["agradecimiento"]; ?>",
		departamento: "<?php echo $info[0]["departamento"]; ?>",
		provincia: "<?php echo $info[0]["provincia"]; ?>",
		codubigeo: "<?php echo $info[0]["distrito"]; ?>",
		provinciacod: "<?php echo $info[0]["provincia"]; ?>",
		codubigeocod: "<?php echo $info[0]["codubigeo"]; ?>",
		leyendapamazonia: "<?php echo $empresa[0]["leyendapamazonia"]; ?>",
		codleyendapamazonia: "<?php echo $empresa[0]["codleyendapamazonia"]; ?>",
		leyendasamazonia: "<?php echo $empresa[0]["leyendasamazonia"]; ?>",
		codleyendasamazonia: "<?php echo $empresa[0]["codleyendasamazonia"]; ?>",
		urlconsultacomprobantes: "<?php echo $empresa[0]["urlconsultacomprobantes"]; ?>"
	};
</script>
<script src="<?php echo base_url(); ?>phuyu/phuyu_empresa/configuraciones.js"></script>

<style>


/* Igualar altura de input y botón dentro del grupo RUC */
.row-ruc .phy-input,
.row-ruc .phy-btn-sunat {
  height: 48px; /* mismo alto */
  display: flex;
  align-items: center;
}

/* Ajuste visual del botón */
.phy-btn-sunat {
  line-height: 1.2;
}

	/* Botón principal (Guardar) */
	.phy-btn-primary {
		background: linear-gradient(135deg, var(--phy-iris), var(--phy-navy));
		color: #fff !important;
		border: none;
		border-radius: 10px;
		padding: .7rem 1.3rem;
		font-weight: 600;
		box-shadow: 0 4px 12px rgba(7, 16, 74, .25);
		transition: all .2s ease-in-out;
	}

	.phy-btn-primary:hover {
		background: linear-gradient(135deg, var(--phy-navy), var(--phy-indigo));
		transform: translateY(-1px);
		box-shadow: 0 6px 16px rgba(7, 16, 74, .35);
	}

	/* Botón Consultar SUNAT */
	.phy-btn-sunat {
		background: linear-gradient(135deg, var(--phy-sky), var(--phy-iris));
		color: #fff !important;
		border: none;
		border-radius: 10px;
		padding: .55rem 1rem;
		font-weight: 600;
		box-shadow: 0 3px 8px rgba(75, 99, 255, .25);
		transition: all .2s ease-in-out;
	}

	.phy-btn-sunat:hover {
		filter: brightness(1.1);
		transform: translateY(-1px);
		box-shadow: 0 4px 12px rgba(75, 99, 255, .35);
	}

	/* =====================
   PALETA CORPORATIVA
   ===================== */
	.phuyu-theme {
		--phy-navy: #07104a;
		/* azul profundo del logotipo */
		--phy-indigo: #2a37a5;
		/* acento principal */
		--phy-iris: #4b63ff;
		/* botón principal */
		--phy-sky: #7fb3ff;
		/* detalles suaves */
		--phy-cyan: #74d3ff;
		/* realces */
		--phy-bg: #f6f8ff;
		/* fondo */
		--phy-card: #ffffff;
		/* tarjeta */
		--phy-text: #0f172a;
		/* texto principal */
		--phy-muted: #5b6475;
		/* texto secundario */
		--radius-lg: 18px;
		--radius-md: 12px;
		--shadow-soft: 0 12px 30px rgba(7, 16, 74, .08);
		--shadow-float: 0 18px 40px rgba(7, 16, 74, .12);
	}

	/* ===== Topbar glass ===== */
	.phuyu-topbar {
		position: sticky;
		top: 0;
		z-index: 1040;
		backdrop-filter: saturate(1.2) blur(10px);
		background: linear-gradient(90deg, rgba(7, 16, 74, .85), rgba(43, 56, 168, .7));
		border-bottom: 1px solid rgba(255, 255, 255, .18);
	}

	.phuyu-topbar__inner {
		padding: .9rem 1rem;
	}

	.phuyu-topbar .brand-logo {
		height: 28px;
		filter: drop-shadow(0 2px 10px rgba(0, 0, 0, .2));
	}

	.phuyu-topbar .brand-title .kicker {
		display: block;
		font-size: .7rem;
		letter-spacing: .14em;
		text-transform: uppercase;
		color: #cfe3ff;
		opacity: .85
	}

	.phuyu-topbar h1 {
		color: #fff;
		font-weight: 700
	}

	.phyu-btn-primary {
		background: linear-gradient(135deg, var(--phy-iris), var(--phy-indigo));
		color: #fff;
		border: none;
		border-radius: 999px;
		padding: .55rem 1rem;
		box-shadow: var(--shadow-soft);
	}

	.phyu-btn-primary:hover {
		filter: brightness(1.08);
		box-shadow: var(--shadow-float);
	}

	/* ===== Layout ===== */
	.phuyu-theme main {
		background: radial-gradient(1200px 500px at 10% -10%, rgba(127, 179, 255, .25), transparent 60%),
			radial-gradient(900px 400px at 110% -20%, rgba(116, 211, 255, .18), transparent 50%), var(--phy-bg);
	}

	/* ===== Card ===== */
	.phy-card {
		background: var(--phy-card);
		border-radius: var(--radius-lg);
		box-shadow: var(--shadow-soft);
		border: 1px solid rgba(7, 16, 74, .06);
		overflow: hidden
	}

	.phy-card__head {
		padding: .9rem 1.1rem;
		background: linear-gradient(90deg, rgba(43, 56, 168, .08), rgba(127, 179, 255, .08));
		border-bottom: 1px dashed rgba(7, 16, 74, .12)
	}

	.phy-card__head .title {
		margin: 0;
		font-size: .92rem;
		letter-spacing: .08em;
		text-transform: uppercase;
		color: var(--phy-navy);
		font-weight: 800
	}

	.phy-card__body {
		padding: 1.25rem;
	}

	/* ===== Inputs ===== */
	.form-label {
		font-weight: 700;
		color: var(--phy-navy);
		font-size: .86rem;
		letter-spacing: .02em
	}

	.hint {
		font-size: .78rem;
		color: var(--phy-muted);
		margin-top: .25rem
	}

	.phy-input,
	.phy-select {
		width: 100%;
		border: 1.2px solid rgba(7, 16, 74, .18);
		border-radius: 12px;
		padding: .62rem .75rem;
		outline: none;
		background: #fff;
		color: var(--phy-text);
		transition: box-shadow .2s, border-color .2s
	}

	.phy-input:focus,
	.phy-select:focus {
		border-color: var(--phy-iris);
		box-shadow: 0 0 0 4px rgba(75, 99, 255, .15)
	}

	.phy-input.text-end {
		text-align: right
	}

	/* Quitar spinners numéricos */
	input[type=number]::-webkit-outer-spin-button,
	input[type=number]::-webkit-inner-spin-button {
		-webkit-appearance: none;
		margin: 0
	}

	input[type=number] {
		-moz-appearance: textfield
	}

	/* ===== Ghost button ===== */
	.phy-btn-ghost {
		background: rgba(75, 99, 255, .08);
		color: var(--phy-indigo);
		border: 1px solid rgba(75, 99, 255, .15);
		border-radius: 12px;
		padding: .62rem .8rem;
		font-weight: 600
	}

	.phy-btn-ghost:hover {
		background: rgba(75, 99, 255, .12)
	}

	/* ===== Switch ===== */
	.phy-switch {
		display: inline-flex;
		align-items: center;
		user-select: none;
		cursor: pointer
	}

	.phy-switch input {
		appearance: none;
		width: 44px;
		height: 26px;
		background: rgba(7, 16, 74, .2);
		border-radius: 999px;
		position: relative;
		outline: none;
		transition: background .2s
	}

	.phy-switch input::after {
		content: "";
		position: absolute;
		top: 3px;
		left: 3px;
		width: 20px;
		height: 20px;
		background: #fff;
		border-radius: 50%;
		box-shadow: 0 2px 6px rgba(7, 16, 74, .25);
		transition: transform .2s
	}

	.phy-switch input:checked {
		background: linear-gradient(135deg, var(--phy-iris), var(--phy-cyan))
	}

	.phy-switch input:checked::after {
		transform: translateX(18px)
	}

	.phy-switch .track {
		display: none
	}

	.phy-switch .label {
		font-weight: 600;
		color: var(--phy-indigo)
	}

	/* ===== File drop ===== */
	.phy-drop {
		position: relative;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		gap: .35rem;
		height: 110px;
		border: 2px dashed rgba(7, 16, 74, .2);
		border-radius: 14px;
		background: linear-gradient(180deg, rgba(127, 179, 255, .08), transparent);
		color: var(--phy-muted);
		font-weight: 600
	}

	.phy-drop input {
		position: absolute;
		inset: 0;
		opacity: 0;
		cursor: pointer
	}

	.phy-drop .ico {
		font-size: 1.3rem
	}

	.phy-drop:hover {
		border-color: rgba(75, 99, 255, .4);
		background: linear-gradient(180deg, rgba(127, 179, 255, .14), rgba(116, 211, 255, .08))
	}

	/* ===== Utilities ===== */
	.text-end {
		text-align: right
	}

	.container-xxl {
		max-width: 1280px
	}
</style>