<?php
$esc = function($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Orden de mantenimiento OM-<?php echo $esc($orden["numero_orden"]); ?></title>
	<style>
		@page{size:80mm auto;margin:5mm}
		*{box-sizing:border-box}
		body{font-family:Arial,Helvetica,sans-serif;color:#111;margin:0;font-size:12px}
		.ticket{width:100%;max-width:80mm;margin:0 auto}
		h1{font-size:16px;text-align:center;margin:0 0 4px;font-weight:800}
		.sub{text-align:center;font-size:12px;margin-bottom:8px}
		.row{display:flex;justify-content:space-between;gap:8px;border-top:1px dashed #999;padding:5px 0}
		.label{font-weight:700}
		.value{text-align:right}
		.block{border-top:1px dashed #999;padding:6px 0}
		.sign{margin-top:24px;text-align:center}
		.line{border-top:1px solid #111;padding-top:5px}
		@media print{.no-print{display:none}body{margin:0}}
		@media screen{body{background:#f2f3f5;padding:18px}.ticket{background:#fff;padding:12px;box-shadow:0 8px 30px rgba(0,0,0,.12)}.no-print{text-align:center;margin-top:12px}}
	</style>
</head>
<body onload="window.print()">
	<div class="ticket">
		<h1>ORDEN DE MANTENIMIENTO</h1>
		<div class="sub">OM-<?php echo $esc($orden["numero_orden"]); ?></div>
		<div class="row"><div class="label">Sucursal</div><div class="value"><?php echo $esc($orden["sucursal"]); ?></div></div>
		<div class="row"><div class="label">Ambiente</div><div class="value"><?php echo $esc($orden["ambiente"]); ?></div></div>
		<div class="row"><div class="label">Habitacion</div><div class="value"><?php echo $esc($orden["numero"]); ?></div></div>
		<div class="row"><div class="label">Fecha/hora</div><div class="value"><?php echo $esc($orden["fecha_inicio"]." ".$orden["hora_inicio"]); ?></div></div>
		<div class="row"><div class="label">Fin estimado</div><div class="value"><?php echo $esc($orden["fecha_fin"]); ?></div></div>
		<div class="row"><div class="label">Responsable</div><div class="value"><?php echo $esc($orden["responsable"]); ?></div></div>
		<div class="row"><div class="label">Prioridad</div><div class="value"><?php echo $esc(strtoupper($orden["prioridad"])); ?></div></div>
		<div class="row"><div class="label">Tipo</div><div class="value"><?php echo $esc($orden["tipo_mantenimiento_texto"]); ?></div></div>
		<div class="block">
			<div class="label">Descripcion del problema</div>
			<div><?php echo nl2br($esc($orden["descripcion_problema"])); ?></div>
		</div>
		<div class="block">
			<div class="label">Trabajos a realizar</div>
			<div><?php echo nl2br($esc($orden["trabajos_realizados"])); ?></div>
		</div>
		<div class="block">
			<div class="label">Materiales / repuestos</div>
			<div><?php echo nl2br($esc($orden["materiales"])); ?></div>
		</div>
		<div class="block">
			<div class="label">Observacion</div>
			<div><?php echo nl2br($esc($orden["observacion"])); ?></div>
		</div>
		<div class="sign"><div class="line">Firma del responsable</div></div>
	</div>
	<div class="no-print"><button onclick="window.print()">Imprimir</button></div>
</body>
</html>
