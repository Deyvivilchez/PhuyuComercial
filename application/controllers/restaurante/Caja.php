<?php defined('BASEPATH') or exit('No direct script access allowed');

class Caja extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model("phuyu_model");
		$this->load->model("Caja_model");
	}

	public function index()
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$ambientes = $this->db->query("select *from restaurante.ambientes where codsucursal=" . $_SESSION["phuyu_codsucursal"] . " and estado=1 order by codambiente asc")->result_array();
				$lineas = $this->db->query("select *from almacen.lineas where estado=1 order by descripcion asc")->result_array();

				$comprobantes = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=" . $_SESSION["phuyu_codsucursal"] . " and c.codcomprobantetipo>=5 and c.estado=1")->result_array();
				$conceptos = $this->db->query("select *from caja.conceptos where codconcepto=13 or codconcepto=15")->result_array();
				$tipopagos = $this->db->query("select *from caja.tipopagos where ingreso=1 and estado=1 order by codtipopago")->result_array();
				$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 and empleado.codcargo=4")->result_array();
				$configuracion = $this->db->query("select sinstockventa,itemrepetirventa from public.empresas where codempresa=" . $_SESSION["phuyu_codempresa"])->result_array();
				$sucursal = $this->db->query("select codcomprobantetipo, seriecomprobante from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();

				$this->load->view("restaurante/atender/index", compact("ambientes", "lineas", "comprobantes", "conceptos", "tipopagos", "vendedores", "configuracion", "sucursal"));
			} else {
				$this->load->view("phuyu/505");
			}
		} else {
			$this->load->view("phuyu/404");
		}
	}

	public function avance_pedido($codpedido)
	{
		if (!isset($_SESSION["phuyu_usuario"])) {
			show_404();
			return;
		}

		$codpedido = (int)$codpedido;

		// Escape seguro
		$esc = function ($v) {
			return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
		};

		// Empresa (encabezado)
		$empresa = $this->db->query(
			"SELECT nombrecomercial, razonsocial FROM public.personas WHERE codpersona = 1 LIMIT 1"
		)->result_array();

		// Info del pedido (NOW() como fecha de impresión)
		$info = $this->db->query(
			"SELECT pe.razonsocial,
                (SELECT COALESCE(string_agg(nromesa::text,' - '), '—')
                   FROM restaurante.mesaspedido
                  WHERE codpedido = ?) AS mesa,
                p.valorventa, p.igv, p.importe,
                NOW() AS fecharegistro
           FROM kardex.pedidos p
           INNER JOIN public.personas pe ON p.codempleado = pe.codpersona
          WHERE p.codpedido = ?
          LIMIT 1",
			[$codpedido, $codpedido]
		)->result_array();

		// Detalle (con nota en kd.descripcion)
		$detalle = $this->db->query(
			"SELECT
            kd.cantidad,
            kd.descripcion AS notapedido,
            p.descripcion  AS producto,
            u.descripcion  AS unidad,
            kd.preciounitario,
            kd.subtotal
         FROM kardex.pedidosdetalle kd
         INNER JOIN almacen.productos p ON p.codproducto = kd.codproducto
         INNER JOIN almacen.unidades  u ON u.codunidad  = kd.codunidad
        WHERE kd.codpedido = ?
        ORDER BY p.descripcion ASC",
			[$codpedido]
		)->result_array();

		if (empty($info)) {
			echo "Pedido no encontrado";
			return;
		}

		// Encabezado empresa
		$nombrecomercial = $empresa[0]['nombrecomercial'] ?? '';
		$razonsocial     = $empresa[0]['razonsocial'] ?? '';
		$tituloEmpresa   = $nombrecomercial !== '' ? $nombrecomercial : ($razonsocial !== '' ? $razonsocial : '—');

		// Totales y textos
		$valorventa = (float)($info[0]['valorventa'] ?? 0);
		$igv        = (float)($info[0]['igv'] ?? 0);
		$importe    = (float)($info[0]['importe'] ?? 0);
		$mesaTxt    = (string)($info[0]['mesa'] ?? '—');
		$mozoTxt    = (string)($info[0]['razonsocial'] ?? '—');
		$fecha      = date('d/m/Y H:i', strtotime($info[0]['fecharegistro'] ?? 'now'));

		// Monto en letras
		$this->load->library("Number");
		$number      = new Number();
		$tot_total   = number_format($importe, 2, '.', '');
		$partes      = explode('.', $tot_total);
		$entero      = $partes[0] ?? '0';
		$centimos    = $partes[1] ?? '00';
		$totalLetras = strtoupper($number->convertirNumeroEnLetras($entero)) . " Y {$centimos}/100 SOLES";

		// Render seguro
		ob_start(); ?>
		<!DOCTYPE html>
		<html lang="es">

		<head>
			<meta charset="utf-8">
			<title>Pre Cuenta #<?= $esc($codpedido) ?></title>
			<style>
				.ticket {
					width: 260px;
					margin: 0 auto;
					padding: 6px 8px;
					font: 12px/1.25 "Courier New", ui-monospace, Menlo, Consolas, monospace;
					color: #000;
				}

				.center {
					text-align: center
				}

				.right {
					text-align: right
				}

				.bold {
					font-weight: 700
				}

				.sep {
					margin: 6px 0;
					border: 0;
					border-top: 1px dashed #000
				}

				.title {
					font-size: 14px;
					letter-spacing: .5px;
					margin: 1px 0 0
				}

				.sub {
					font-size: 11px;
					color: #111;
					margin: 0
				}

				.row {
					display: flex
				}

				.row>div {
					padding: 2px 0
				}

				.c-cant {
					width: 54px
				}

				.c-desc {
					flex: 1;
					padding-left: 4px
				}

				.c-pu {
					width: 60px;
					text-align: right
				}

				.c-imp {
					width: 66px;
					text-align: right
				}

				.prod {
					display: block;
					font-size: 12px;
					text-transform: uppercase
				}

				.note {
					display: block;
					font-size: 10px;
					margin-top: 1px
				}

				img.logo {
					display: block;
					margin: 0 auto 4px;
					max-width: 130px;
					max-height: 40px;
					object-fit: contain
				}

				.tot-lbl {
					flex: 1
				}

				@media print {
					@page {
						margin: 0
					}

					body {
						margin: 0
					}

					.ticket {
						width: 260px;
						padding: 6px 8px
					}

					.no-print {
						display: none !important
					}

					.subbold {
						font-size: 11px;
						font-weight: 700;
						margin: 0;
					}

					.no-print button {
						padding: 6px 10px;
						border: 1px solid #000;
						background: #fff;
						cursor: pointer
					}

					.no-print button:active {
						transform: scale(0.98)
					}


				}
			</style>
		</head>

		<body>
			<!-- <?= $esc(base_url() . 'public/img/' . $_SESSION['phuyu_logo']) ?> -->
			<div class="ticket">
				<?php if (!empty($_SESSION['phuyu_logo'])): ?>
					<img class="logo" src="<?= $esc(base_url() . 'public/img/' . $_SESSION['phuyu_logo']) ?>" alt="logo">
				<?php endif; ?>

				<div class="center bold"><?= $esc($tituloEmpresa) ?></div>
				<div class="center title">PRE CUENTA</div>
				<div class="center sub">COMPROBANTE NO AUTORIZADO</div>
				<div class="center sub">PEDIDO N° <?= str_pad($codpedido, 5, '0', STR_PAD_LEFT) ?></div>
				<hr class="sep">

				<div class="row">
					<div class="c-desc">MESA</div>
					<div class="c-imp right"><?= $esc($mesaTxt) ?></div>
				</div>
				<div class="row">
					<div class="c-desc">MOZO</div>
					<div class="c-imp right"><?= $esc($mozoTxt) ?></div>
				</div>
				<div class="row">
					<div class="c-desc">FECHA</div>
					<div class="c-imp right"><?= $esc($fecha) ?></div>
				</div>

				<hr class="sep">

				<!-- Cabecera -->
				<div class="row bold">
					<div class="c-cant">CANT</div>
					<div class="c-desc">DESCRIPCIÓN</div>
					<div class="c-pu">P.U.</div>
					<div class="c-imp">IMP.</div>
				</div>

				<?php if (!empty($detalle)): ?>
					<?php foreach ($detalle as $it):
						$qty      = (float)($it['cantidad'] ?? 0);
						$cant     = (fmod($qty, 1.0) === 0.0) ? (int)$qty : round($qty, 1);
						$unidad   = (string)($it['unidad'] ?? '');
						$producto = (string)($it['producto'] ?? '');
						$nota     = trim((string)($it['notapedido'] ?? ''));
						$pu       = (float)($it['preciounitario'] ?? 0);
						$imp      = (float)($it['subtotal'] ?? 0);
					?>
						<div class="row">
							<div class="c-cant"><?= $esc($cant) . " " . $esc($unidad) ?></div>
							<div class="c-desc">
								<span class="prod"><?= $esc($producto) ?></span>

							</div>
							<div class="c-pu"><?= number_format($pu, 2) ?></div>
							<div class="c-imp"><?= number_format($imp, 2) ?></div>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="row">
						<div class="c-desc">Sin ítems registrados.</div>
					</div>
				<?php endif; ?>

				<hr class="sep">

				<!-- Totales -->
				<div class="row">
					<div class="tot-lbl">SUBTOTAL</div>
					<div class="c-imp"><?= number_format($valorventa, 2) ?></div>
				</div>
				<div class="row">
					<div class="tot-lbl">I.G.V.</div>
					<div class="c-imp"><?= number_format($igv, 2) ?></div>
				</div>
				<div class="row bold">
					<div class="tot-lbl">TOTAL</div>
					<div class="c-imp"><?= number_format($importe, 2) ?></div>
				</div>
				<div class="center sub" style="margin-top:4px;">SON <?= $esc($totalLetras) ?></div>
				<hr class="sep">
				<!-- Datos para facturación -->
				<div class="center subbold">DATOS PARA FACTURACIÓN</div>
				<div class="sub">NOMBRE / RAZÓN SOCIAL: _______________________________</div>
				<div class="sub">DNI / RUC: ______________</div>
				<div class="sub">TELÉFONO: __________</div>


				<div class="no-print center" style="margin-top:6px">
					<button onclick="window.print()">Imprimir</button>
				</div>
				<div class="center sub">Gracias por su preferencia</div>
				<!-- <div class="center sub">Imp.: <?= date('d/m/Y H:i') ?></div> -->
				<div class="center sub">Impreso desde sistemas phuyusystem.com</div>

				<div class="no-print center" style="margin-top:6px">
					<button onclick="window.print()">Imprimir</button>
				</div>
			</div>

			<!-- <script>window.print();</script> -->
		</body>

		</html>
		<?php
		$html = ob_get_clean();
		// evitar headers duplicados si ya se enviaron
		if (!headers_sent()) {
			header('Content-Type: text/html; charset=UTF-8');
		}
		echo $html;
	}


	function cobrar_pedido($codkardex)
	{
		if (isset($_SESSION["phuyu_usuario"])) {
			$empresa = $this->db->query("select *from public.personas where codpersona=1")->result_array();
			$info = $this->db->query("select k.fechacomprobante,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.documento,k.cliente,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe from kardex.kardex as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=" . $codkardex)->result_array();
			$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
			$detalle = $this->db->query("select kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal from kardex.kardexdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardex=" . $codkardex)->result_array();

			$this->load->library("Ticket");
			$pdf = new Ticket();
			$pdf->AddPage();
			$padding_x = 2;
			$pdf->Image('./public/img/' . $_SESSION['phuyu_logo'], 13, 10, 50, 30);

			$pdf->SetFont('Arial', 'B', 14);
			$pdf->setY(50);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 4, utf8_decode($empresa[0]["nombrecomercial"]), 0, "C", false);
			$pdf->SetFont('Arial', 'B', 9);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 4, utf8_decode($empresa[0]["razonsocial"]), 0, "C", false);

			$pdf->SetFont('Arial', '', 7);
			$pdf->Ln(1);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 4, utf8_decode($empresa[0]["direccion"]), 0, "C", false);
			$pdf->SetFont('Arial', '', 9);
			$pdf->Ln(1);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 4, "RESERVACIONES Y DELIBERY", 0, "C", false);
			$pdf->SetFont('Arial', '', 7);
			$pdf->Ln(1);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 4, "TELF/CEL: " . utf8_decode($empresa[0]["telefono"]), 0, "C", false);
			$pdf->Ln(3);

			$pdf->SetFont('Arial', 'B', 8);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 3, '----------------------------------------------------------------------------', 0, "C", false);
			$pdf->SetFont('Arial', '', 8);

			$pdf->SetFont('Arial', 'B', 11);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 4, "RUC: " . utf8_decode($empresa[0]["documento"]), 0, "C", false);
			$pdf->Ln(1);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 3, utf8_decode($info[0]["comprobante"]), 0, "C", false);
			$pdf->Ln(1);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 3, $info[0]["seriecomprobante"] . "-" . $info[0]["nrocomprobante"], 0, "C", false);
			$pdf->Ln(2);

			$pdf->SetFont('Arial', 'B', 8);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 3, '----------------------------------------------------------------------------', 0, "C", false);

			$pdf->SetFont('Arial', '', 8);
			$pdf->Ln(2);
			$pdf->setX(4);
			$pdf->MultiCell(75, 3, "FECHA: " . $info[0]["fechacomprobante"] . " 		HORA: " . date("H:i:s"), 0, "L", false);
			$pdf->setX(4);
			$pdf->MultiCell(75, 4, utf8_decode("NOMBRE O RAZON SOCIAL: " . $info[0]["cliente"]), 0, "L", false);
			$pdf->setX(4);
			$pdf->MultiCell(75, 4, utf8_decode("DNI/RUC: " . $info[0]["documento"]), 0, "L", false);
			$pdf->setX(4);
			$pdf->MultiCell(75, 4, utf8_decode("DIRECCION: " . $info[0]["direccion"]), 0, "L", false);
			$pdf->Ln(1);

			$pdf->SetFont('Arial', 'B', 8);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 3, '----------------------------------------------------------------------------', 0, "C", false);
			$columnas = array("PRODUCTO", "CANT", "UNIDAD", "P.U.", "IMP.");
			$w = array(30, 10, 13, 10, 10);
			$pdf->setX(4);
			$pdf->pdf_tabla_head($columnas, $w, 8);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 3, '----------------------------------------------------------------------------', 0, "C", false);

			$pdf->SetWidths(array(30, 10, 13, 10, 10));
			$pdf->SetLineHeight(3);
			$pdf->SetFont('Arial', '', 7);
			foreach ($detalle as $key => $value) {
				$pdf->setX(4);
				$datos = array(utf8_decode($value["producto"]));
				array_push($datos, round("  " . $value["cantidad"], 3));
				array_push($datos, utf8_decode(substr($value["unidad"], 0, 7)));
				array_push($datos, number_format($value["preciounitario"], 2));
				array_push($datos, number_format($value["subtotal"], 2));
				$pdf->Row($datos);
			}
			$pdf->SetFont('Arial', 'B', 8);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 3, '----------------------------------------------------------------------------', 0, "C", false);
			$pdf->SetFont('Arial', '', 8);

			$pdf->SetWidths(array(30, 33, 10));
			$pdf->SetLineHeight(3);
			$pdf->SetFont('Arial', '', 7);
			$pdf->setX(4);
			if ($info[0]["codcomprobantetipo"] == 10 || $info[0]["codcomprobantetipo"] == 12) {
				$pdf->setX(4);
				$datos = array("");
				array_push($datos, "OP. GRAVADAS S/");
				array_push($datos, number_format($totales[0]["gravado"], 2));
				$pdf->Row($datos);
				$pdf->setX(4);
				$datos = array("");
				array_push($datos, "OP. INAFECTAS S/");
				array_push($datos, number_format($totales[0]["inafecto"], 2));
				$pdf->Row($datos);
				$pdf->setX(4);
				$datos = array("");
				array_push($datos, "OP. EXONERADAS S/");
				array_push($datos, number_format($totales[0]["exonerado"], 2));
				$pdf->Row($datos);
				$pdf->setX(4);
				$datos = array("");
				array_push($datos, "OP. GRATUITAS S/");
				array_push($datos, number_format($totales[0]["gratuito"], 2));
				$pdf->Row($datos);
			} else {
				$pdf->setX(4);
				$datos = array("");
				array_push($datos, "SUBTOTAL S/");
				array_push($datos, number_format($info[0]["valorventa"], 2));
				$pdf->Row($datos);
			}
			$pdf->setX(4);
			$datos = array("");
			array_push($datos, "DESCUENTOS S/");
			array_push($datos, number_format($info[0]["descglobal"], 2));
			$pdf->Row($datos);
			$pdf->setX(4);
			$datos = array("");
			array_push($datos, "I.G.V. S/");
			array_push($datos, number_format($info[0]["igv"], 2));
			$pdf->Row($datos);
			$pdf->setX(4);
			$datos = array("");
			array_push($datos, "TOTAL S/");
			array_push($datos, number_format($info[0]["importe"], 2));
			$pdf->Row($datos);

			$pdf->SetFont('Arial', 'B', 8);
			$pdf->setX($padding_x);
			$pdf->MultiCell(75, 3, '----------------------------------------------------------------------------', 0, "C", false);

			$pagos = $this->db->query("select tp.descripcion, md.* from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) inner join caja.tipopagos as tp on(tp.codtipopago=md.codtipopago) where m.codkardex=" . $codkardex . " order by tp.codtipopago desc")->result_array();
			foreach ($pagos as $vp) {
				$pdf->setX(4);
				$pdf->SetFont('Arial', '', 8);
				$datos = array("");
				array_push($datos, utf8_decode($vp["descripcion"] . "  S/ "));
				array_push($datos, number_format($vp["importeentregado"], 2));
				$pdf->Row($datos);

				// $pdf->setX(4); $pdf->MultiCell(75,4,utf8_decode($vp["descripcion"]."  S/ ".number_format($vp["importeentregado"],2)."    "),0,"R",false);
				if ($vp["codtipopago"] == 1) {
					$pdf->setX(4);
					$pdf->SetFont('Arial', '', 8);
					$datos = array("");
					array_push($datos, utf8_decode("VUELTO    S/ "));
					array_push($datos, number_format($vp["vuelto"], 2));
					$pdf->Row($datos);

					// $pdf->setX(4); $pdf->MultiCell(75,4,utf8_decode("VUELTO    S/ ".number_format($vp["vuelto"],2)."    "),0,"R",false);
				}
			}

			$this->load->library("Number");
			$number = new Number();
			$tot_total = (string)(number_format($info[0]["importe"], 2));
			$imptotaltexto = explode(".", $tot_total);
			$det_imptotaltexto = $number->convertirNumeroEnLetras($imptotaltexto[0]);

			$pdf->SetFont('Arial', 'B', 8);
			$pdf->setX(4);
			$pdf->MultiCell(75, 4, utf8_decode("SON " . strtoupper($det_imptotaltexto) . " Y " . $imptotaltexto[1] . "/100 SOLES"), 0, "L", false);

			$pedido = $this->db->query("select codpedido from kardex.pedidos where codkardex=" . $codkardex)->result_array();
			$infom = $this->db->query("select pe.razonsocial, (select COALESCE(string_agg(nromesa::text,' - ')) from restaurante.mesaspedido where codpedido=" . $pedido[0]["codpedido"] . ") as mesa, p.valorventa,p.igv,p.importe from kardex.pedidos as p inner join public.personas as pe on(p.codempleado=pe.codpersona) where p.codpedido=" . $pedido[0]["codpedido"])->result_array();

			$pdf->SetFont('Arial', '', 8);
			$pdf->Ln(4);
			$pdf->setX(4);
			$pdf->MultiCell(75, 4, "ITEMS: " . count($detalle) . " | MESA: " . $infom[0]["mesa"] . " | CAJA: " . $_SESSION["phuyu_caja"], 0, "L", false);
			$pdf->setX(4);
			$pdf->MultiCell(75, 4, utf8_decode("ATENDIDO POR: " . $infom[0]["razonsocial"]), 0, "L", false);
			$pdf->Ln(1);

			$textoqr = $empresa[0]["razonsocial"] . "|" . $info[0]["seriecomprobante"] . "|" . $info[0]["nrocomprobante"] . "|" . number_format($info[0]["igv"], 2) . "|" . number_format($info[0]["importe"], 2) . "|" . $info[0]["fechacomprobante"] . "|" . $info[0]["documento"];

			$this->load->library('ciqrcode');
			$params['data'] = $textoqr;
			$params['level'] = 'H';
			$params['size'] = 5;
			$params['savename'] = "./sunat/webphuyu/qrcode.png";
			$this->ciqrcode->generate($params);
			// chmod("./sunat/webphuyu/qrcode.png", 0777);

			$archivo_error = APPPATH . "/logs/qrcode.png-errors.txt";
			unlink($archivo_error);
			$altura = (count($detalle) * 6) + (31 * 6);
			$pdf->Image('./sunat/webphuyu/qrcode.png', 30, $altura, 20, 20);
			$pdf->setY($altura + 20);

			$pdf->setX(2);
			$pdf->MultiCell(75, 4, utf8_decode("CONSULTA TU COMPROBANTE EN"), 0, "C", false);
			$pdf->setX(2);
			$pdf->MultiCell(75, 4, utf8_decode("http://phuyuperu.com/sunat"), 0, "C", false);
			$pdf->Ln(3);
			$pdf->SetFont('Arial', 'B', 8);
			$pdf->setX(2);
			$pdf->MultiCell(75, 3, utf8_decode("BIENES TRANSFERIDOS / SERVICIOS PRESTADOS EN LA REGIÓN DE LA SELVA PARA SER CONSUMIDOS EN LA MISMA"), 0, "C", false);

			$pdf->setX(2);
			$pdf->MultiCell(75, 4, utf8_decode("___________________________________________"), 0, "C", false);
			$pdf->Ln();

			$pdf->AutoPrint();
			$pdf->Output();
		}
	}

	function venta_diaria($codcontroldiario = 0)
	{
		if (isset($_SESSION["phuyu_usuario"])) {
			$empresa = $this->db->query("select *from public.personas where codpersona=1")->result_array();

			$this->load->library("Ticket");
			$pdf = new Ticket();
			$pdf->AddPage();
			$pdf->Image('./public/img/' . $_SESSION['phuyu_logo'], 13, 10, 50, 30);

			$pdf->SetFont('Arial', 'B', 12);
			$pdf->setY(45);
			$pdf->setX(2);
			$pdf->MultiCell(75, 4, utf8_decode($empresa[0]["nombrecomercial"]), 0, "C", false);
			$pdf->SetFont('Arial', 'B', 9);
			$pdf->setX(2);
			$pdf->MultiCell(75, 4, "DE: " . utf8_decode($empresa[0]["razonsocial"]), 0, "C", false);
			$pdf->setX(2);
			$pdf->MultiCell(75, 4, "RUC: " . utf8_decode($empresa[0]["documento"]), 0, "C", false);

			$pdf->SetFont('Arial', 'B', 8);
			$pdf->setX(2);
			$pdf->MultiCell(75, 3, '-----------------------------------------------------------------------------', 0, "C", false);
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->setX(2);
			$pdf->MultiCell(75, 4, "VENTA DIARIA POR PRODUCTO", 0, "C", false);
			$pdf->SetFont('Arial', '', 8);
			$pdf->setX(2);
			$pdf->MultiCell(75, 3, "FECHA: " . date("d-m-Y") . " 		HORA: " . date("H:i:s"), 0, "C", false);
			$pdf->Ln(5);

			$columnas = array("PRODUCTO O SERVICIO", "CANTIDAD", "IMPORTE");
			$w = array(35, 20, 20);
			$pdf->setX(4);
			$pdf->pdf_tabla_head($columnas, $w, 8);

			if ($codcontroldiario == 0) {
				$codcontroldiario = $_SESSION["phuyu_codcontroldiario"];
			}

			$lineas = $this->db->query("select *from almacen.lineas")->result_array();

			$pdf->SetWidths(array(35, 20, 20));
			$pdf->SetLineHeight(3);
			$pdf->SetFont('Arial', '', 7);
			$total = 0;
			$cantidad = 0;
			foreach ($lineas as $v) {
				$lista = $this->db->query("select kd.codproducto,kd.codunidad,p.descripcion as producto,u.descripcion as unidad, coalesce(sum(kd.cantidad),0) as cantidad, coalesce(sum(kd.subtotal),0) as importe from kardex.kardex as k inner join caja.movimientos as mov on(k.codkardex=mov.codkardex) inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where k.codmovimientotipo=20 and k.codsucursal=" . $_SESSION["phuyu_codsucursal"] . " and mov.codcontroldiario=" . $codcontroldiario . " and p.codlinea=" . $v["codlinea"] . " and k.estado=1 group by kd.codproducto, kd.codunidad, p.descripcion, u.descripcion")->result_array();
				if (count($lista) > 0) {
					$pdf->SetFont('Arial', 'B', 8);
					$pdf->MultiCell(73, 5, "LINEA: " . $v["descripcion"], 0, "L", false);
					$pdf->SetFont('Arial', '', 7);
				}
				foreach ($lista as $key => $value) {
					$pdf->setX(4);
					$datos = array(utf8_decode($value["producto"] . "-" . $value["unidad"]));
					array_push($datos, number_format($value["cantidad"], 2));
					array_push($datos, number_format($value["importe"], 2));
					$pdf->Row($datos);
					$total = $total + $value["importe"];
					$cantidad = $cantidad + $value["cantidad"];
				}
			}

			$pdf->SetFont('Arial', '', 8);
			$pdf->setX(2);
			$pdf->MultiCell(75, 3, '-----------------------------------------------------------------------------', 0, "C", false);
			$columnas = array("TOTALES", number_format($cantidad, 2), number_format($total, 2));
			$w = array(35, 20, 20);
			$pdf->setX(4);
			$pdf->pdf_tabla_head($columnas, $w, 8);
			$pdf->AutoPrint();
			$pdf->Output();
		}
	}

	function balance_caja($codcontroldiario = 0)
	{
		if (isset($_SESSION["phuyu_usuario"])) {
			$empresa = $this->db->query("select *from public.personas where codpersona=1")->result_array();

			$this->load->library("Ticket");
			$pdf = new Ticket();
			$pdf->AddPage();
			$pdf->Image('./public/img/' . $_SESSION['phuyu_logo'], 13, 10, 50, 30);

			$pdf->SetFont('Arial', 'B', 12);
			$pdf->setY(45);
			$pdf->setX(2);
			$pdf->MultiCell(75, 4, utf8_decode($empresa[0]["nombrecomercial"]), 0, "C", false);
			$pdf->SetFont('Arial', 'B', 9);
			$pdf->setX(2);
			$pdf->MultiCell(75, 4, "DE: " . utf8_decode($empresa[0]["razonsocial"]), 0, "C", false);
			$pdf->setX(2);
			$pdf->MultiCell(75, 4, "RUC: " . utf8_decode($empresa[0]["documento"]), 0, "C", false);

			$pdf->SetFont('Arial', 'B', 8);
			$pdf->setX(2);
			$pdf->MultiCell(75, 3, '-----------------------------------------------------------------------------', 0, "C", false);
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->setX(2);
			$pdf->MultiCell(75, 4, "BALANCE DE CAJA", 0, "C", false);
			$pdf->SetFont('Arial', '', 8);
			$pdf->setX(2);
			$pdf->MultiCell(75, 3, "FECHA: " . date("d-m-Y") . " 		HORA: " . date("H:i:s"), 0, "C", false);
			$pdf->Ln(5);

			if ($codcontroldiario == 0) {
				$codcontroldiario = $_SESSION["phuyu_codcontroldiario"];
			}

			$fechas = $this->db->query("select distinct(fechamovimiento) from caja.movimientos where codcontroldiario=" . $codcontroldiario)->result_array();
			$total_efectivo_ingreso = 0;
			$total_efectivo_egreso = 0;

			foreach ($fechas as $key => $value) {
				$pdf->SetFillColor(230, 230, 230);
				$pdf->setX(2);
				$pdf->MultiCell(75, 6, "FECHA PROCESO: " . $value["fechamovimiento"], 0, "C", true);
				$pdf->Ln(5);

				$w = array(55, 20);
				$pdf->SetWidths($w);
				$pdf->SetLineHeight(3);
				$pdf->SetFont('Arial', '', 8);
				$pdf->setX(4);
				$tipopagos = $this->db->query("select *from caja.tipopagos where estado=1 order by codtipopago")->result_array();

				$otros = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=" . $codcontroldiario . " and m.codkardex=0 and m.fechamovimiento='" . $value["fechamovimiento"] . "' and m.tipomovimiento=1 and m.estado=1")->result_array();
				$datos = array("OTROS INGRESOS");
				array_push($datos, number_format($otros[0]["importe"], 2));
				$pdf->Row($datos);
				$pdf->Ln(2);
				$columnas = array("VENTAS", "IMPORTE");
				$pdf->setX(4);
				$pdf->pdf_tabla_head($columnas, $w, 8);
				$pdf->SetFont('Arial', '', 8);
				$pdf->Ln(1);

				$total_ventas = $otros[0]["importe"];
				foreach ($tipopagos as $key => $val) {
					$venta = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=" . $codcontroldiario . " and m.codkardex>0 and m.fechamovimiento='" . $value["fechamovimiento"] . "' and m.tipomovimiento=1 and md.codtipopago=" . $val["codtipopago"] . " and m.estado=1")->result_array();
					$total_ventas = $total_ventas + $venta[0]["importe"];

					$pdf->setX(4);
					$datos = array(utf8_decode($val["descripcion"]));
					array_push($datos, number_format($venta[0]["importe"], 2));
					$pdf->Row($datos);
					$pdf->Ln(1);
				}
				$columnas = array("TOTAL INGRESOS", number_format($total_ventas, 2));
				$pdf->setX(4);
				$pdf->pdf_tabla_head($columnas, $w, 8);
				$pdf->Ln(1);
				$pdf->SetFont('Arial', '', 8);
				$pdf->setX(2);
				$pdf->MultiCell(75, 3, '-------------------------------------------------------------------------', 0, "C", false);

				$totalingresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=" . $codcontroldiario . " and m.fechamovimiento='" . $value["fechamovimiento"] . "' and codkardex>0 and m.tipomovimiento=1 and md.codtipopago=1 and m.estado=1")->result_array();

				$totalegresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=" . $codcontroldiario . " and m.fechamovimiento='" . $value["fechamovimiento"] . "' and m.tipomovimiento=2 and md.codtipopago=1 and m.estado=1")->result_array();

				$pdf->setX(4);
				$datos = array("TOTAL INGRESOS EFECTIVO");
				array_push($datos, number_format($totalingresos[0]["importe"], 2));
				$pdf->Row($datos);
				$pdf->Ln(2);
				$pdf->setX(4);
				$datos = array("TOTAL EGRESOS EFECTIVO");
				array_push($datos, number_format($totalegresos[0]["importe"], 2));
				$pdf->Row($datos);
				$pdf->Ln(0);
				$pdf->setX(4);
				$pdf->SetFont('Arial', '', 8);
				$pdf->setX(2);
				$pdf->MultiCell(75, 3, '-------------------------------------------------------------------------', 0, "C", false);
				$pdf->setX(4);
				$datos = array("TOTAL EFECTIVO DISP.");
				array_push($datos, number_format($totalingresos[0]["importe"] - $totalegresos[0]["importe"], 2));
				$pdf->Row($datos);
				$pdf->Ln(2);
			}

			$pdf->Ln(5);
			$pdf->setX(2);
			$pdf->MultiCell(75, 3, '-------------------------------------------------------------------------', 0, "C", false);
			$pdf->SetFillColor(230, 230, 230);
			$pdf->setX(2);
			$pdf->MultiCell(75, 6, "TOTAL GENERAL CAJA", 0, "C", true);
			$pdf->Ln(5);

			$w = array(55, 20);
			$pdf->SetWidths($w);
			$pdf->SetLineHeight(3);
			$pdf->SetFont('Arial', '', 8);
			$pdf->setX(4);
			$tipopagos = $this->db->query("select *from caja.tipopagos where estado=1 order by codtipopago")->result_array();

			$otros = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=" . $codcontroldiario . " and m.codkardex=0 and m.tipomovimiento=1 and m.estado=1")->result_array();
			$datos = array("OTROS INGRESOS");
			array_push($datos, number_format($otros[0]["importe"], 2));
			$pdf->Row($datos);
			$pdf->Ln(2);
			$columnas = array("VENTAS", "IMPORTE");
			$pdf->setX(4);
			$pdf->pdf_tabla_head($columnas, $w, 8);
			$pdf->SetFont('Arial', '', 8);
			$pdf->Ln(1);

			$total_ventas = $otros[0]["importe"];
			$total_otros = $otros[0]["importe"];
			foreach ($tipopagos as $key => $val) {
				$venta = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=" . $codcontroldiario . " and m.codkardex>0 and m.tipomovimiento=1 and md.codtipopago=" . $val["codtipopago"] . " and m.estado=1")->result_array();
				if ($val["codtipopago"] == 1) {
					$total_otros = $total_otros + $venta[0]["importe"];
				}
				$total_ventas = $total_ventas + $venta[0]["importe"];

				$pdf->setX(4);
				$datos = array(utf8_decode($val["descripcion"]));
				array_push($datos, number_format($venta[0]["importe"], 2));
				$pdf->Row($datos);
				$pdf->Ln(1);
			}
			$columnas = array("TOTAL INGRESOS", number_format($total_ventas, 2));
			$pdf->setX(4);
			$pdf->pdf_tabla_head($columnas, $w, 8);
			$pdf->Ln(1);
			$pdf->SetFont('Arial', '', 8);
			$pdf->setX(2);
			$pdf->MultiCell(75, 3, '-------------------------------------------------------------------------', 0, "C", false);

			$totalingresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=" . $codcontroldiario . " and codkardex>0 and m.tipomovimiento=1 and md.codtipopago=1 and m.estado=1")->result_array();

			$totalegresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=" . $codcontroldiario . " and m.tipomovimiento=2 and md.codtipopago=1 and m.estado=1")->result_array();

			$pdf->setX(4);
			$datos = array("TOTAL INGRESOS EFECTIVO");
			array_push($datos, number_format($totalingresos[0]["importe"], 2));
			$pdf->Row($datos);
			$pdf->Ln(2);
			$pdf->setX(4);
			$datos = array("TOTAL EGRESOS EFECTIVO");
			array_push($datos, number_format($totalegresos[0]["importe"], 2));
			$pdf->Row($datos);
			$pdf->Ln(0);
			$pdf->setX(4);
			$pdf->SetFont('Arial', '', 8);
			$pdf->setX(2);
			$pdf->MultiCell(75, 3, '-------------------------------------------------------------------------', 0, "C", false);
			$pdf->setX(4);
			$datos = array("TOTAL EFECTIVO DISP.");
			array_push($datos, number_format($total_otros - $totalegresos[0]["importe"], 2));
			$pdf->Row($datos);
			$pdf->Ln(2);


			$pdf->AutoPrint();
			$pdf->Output();
		}
	}

	function pdf_vendedores_caja_directo($codcontroldiario = 0)
	{
		if ($codcontroldiario == 0) {
			$codcontroldiario = $_SESSION["phuyu_codcontroldiario"];
		}

		$productos = $this->db->query("select distinct(kd.codproducto),kd.codunidad,p.descripcion as producto,u.descripcion as unidad, round(avg(kd.preciounitario),2) as preciounitario from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) inner join caja.movimientos as m on(k.codkardex=m.codkardex) where k.codmovimientotipo=20 and p.codlinea=3 and m.codcontroldiario=" . $codcontroldiario . " and k.estado=1 group by kd.codproducto, kd.codunidad, p.descripcion, u.descripcion order by preciounitario desc, kd.codproducto")->result_array();
		$detalle = $productos;

		$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 and empleado.codcargo=4 order by persona.razonsocial asc")->result_array();
		foreach ($vendedores as $key => $value) {
			$total = 0;
			$cantidad = 0;
			$cantidad_descontar = 2;
			$item = 0;
			foreach ($detalle as $k => $v) {
				$suventa = $this->db->query("select kd.codproducto,kd.codunidad,p.descripcion as producto,u.descripcion as unidad, coalesce(sum(kd.cantidad),0) as cantidad, avg(kd.preciounitario) as preciounitario, coalesce(sum(kd.subtotal),0) as importe from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) inner join caja.movimientos as m on(k.codkardex=m.codkardex) where k.codmovimientotipo=20 and k.codempleado=" . $value["codpersona"] . " and kd.codproducto=" . $v["codproducto"] . " and kd.codunidad=" . $v["codunidad"] . " and m.codcontroldiario=" . $codcontroldiario . " and k.estado=1 group by kd.codproducto, kd.codunidad, p.descripcion, u.descripcion order by preciounitario desc")->result_array();
				if (count($suventa) == 0) {
					$detalle[$k]["cantidad"] = 0;
					$detalle[$k]["primeros"] = 0;
					$detalle[$k]["preciounitario"] = 0;
					$detalle[$k]["importe"] = 0;
					$detalle[$k]["dia"] = 0;
				} else {
					$cantidad = $suventa[0]["cantidad"];
					if ($cantidad_descontar > 0) {
						if ($suventa[0]["cantidad"] >= $cantidad_descontar) {
							$cantidad = $suventa[0]["cantidad"] - $cantidad_descontar;
							$cantidad_descontar = 0;
						} else {
							$cantidad = $suventa[0]["cantidad"] - 1;
							$cantidad_descontar = $cantidad_descontar - 1;
						}
					}

					$dia = ($suventa[0]["preciounitario"] * $cantidad * 0.40) - ($cantidad * 1);
					$total = $total + $dia;
					$detalle[$k]["cantidad"] = number_format($suventa[0]["cantidad"], 0);
					$detalle[$k]["primeros"] = number_format($cantidad, 0);
					$detalle[$k]["preciounitario"] = number_format($suventa[0]["preciounitario"], 2);
					$detalle[$k]["importe"] = number_format($suventa[0]["preciounitario"] * $cantidad, 2);
					$detalle[$k]["dia"] = number_format($dia, 2);
				}
			}
			$vendedores[$key]["detalle"] = $detalle;
			$vendedores[$key]["total"] = number_format($total, 2);
		}
		$this->load->view("restaurante/atender/ventas", compact("productos", "vendedores"));
	}

	function pdf_vendedores_caja($codcontroldiario = 0)
	{
		$this->load->library('Pdf2');
		$pdf = new Pdf2();
		$pdf->AddPage();
		$pdf->pdf_header("REPORTE VENTAS EMPLEADOS", "");

		if ($codcontroldiario == 0) {
			$codcontroldiario = $_SESSION["phuyu_codcontroldiario"];
		}

		// $caja = $this->db->query("select *from caja.controldiario where codcontroldiario=".$codcontroldiario)->result_array();

		$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 and empleado.codcargo=4 order by persona.razonsocial asc")->result_array();
		foreach ($vendedores as $key => $value) {
			$pdf->SetFont('Arial', 'B', 11);
			$pdf->Cell(0, 7, utf8_decode($value["razonsocial"]), 0, 1, 'C', 0);
			$pdf->Ln(2);

			$productos = $this->db->query("select kd.codproducto,kd.codunidad,p.descripcion as producto,u.descripcion as unidad, coalesce(sum(kd.cantidad),0) as cantidad, avg(kd.preciounitario) as preciounitario, coalesce(sum(kd.subtotal),0) as importe from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) inner join caja.movimientos as m on(k.codkardex=m.codkardex) where k.codmovimientotipo=20 and p.codlinea=3 and k.codempleado=" . $value["codpersona"] . " and m.codcontroldiario=" . $codcontroldiario . " and k.estado=1 group by kd.codproducto, kd.codunidad, p.descripcion, u.descripcion order by preciounitario desc")->result_array();

			$columnas = array("DESCRIPCION", "CANTIDAD", "CANTIDAD - 2", "PRECIO UNI.", "TOTAL", "COMISION 40%", "DESC", "PAGAR");
			$w = array(60, 17, 22, 20, 20, 25, 10, 18);
			$pdf->pdf_tabla_head($columnas, $w, 8);

			$pdf->SetWidths($w);
			$pdf->SetLineHeight(5);
			$pdf->SetFont('Arial', '', 8);
			$total = 0;
			$cantidad = 0;
			$cantidad_descontar = 2;
			foreach ($productos as $val) {
				$cantidad = $val["cantidad"];
				if ($cantidad_descontar > 0) {
					if ($val["cantidad"] >= $cantidad_descontar) {
						$cantidad = $val["cantidad"] - $cantidad_descontar;
						$cantidad_descontar = 0;
					} else {
						$cantidad = $val["cantidad"] - 1;
						$cantidad_descontar = $cantidad_descontar - 1;
					}
				}

				$datos = array(utf8_decode($val["producto"] . " - " . $val["unidad"]));
				array_push($datos, number_format($val["cantidad"], 2));
				array_push($datos, number_format($cantidad, 2));
				array_push($datos, number_format($val["preciounitario"], 2));
				array_push($datos, number_format($val["preciounitario"] * $cantidad, 2));
				array_push($datos, number_format($val["preciounitario"] * $cantidad * 0.40, 2));
				array_push($datos, number_format($cantidad * 1, 2));
				array_push($datos, number_format(($val["preciounitario"] * $cantidad * 0.40) - ($cantidad * 1), 2));
				$pdf->Row($datos);

				$total = $total + (($val["preciounitario"] * $cantidad * 0.40) - ($cantidad * 1));
			}
			$w = array(99, 75, 18);
			$pdf->SetWidths($w);
			$pdf->SetLineHeight(5);
			$pdf->SetFont('Arial', 'B', 8);

			$datos = array("");
			array_push($datos, "TOTAL A PAGAR POR EL DIA: S/.");
			array_push($datos, number_format($total, 2));
			$pdf->Row($datos);
		}

		$pdf->SetTitle("phuyu Peru - Ventas Empleados");
		$pdf->Output();
	}

	public function comanda($codpedido)
	{
		if (!isset($_SESSION["phuyu_usuario"])) {
			show_404();
			return;
		}

		$codpedido = (int)$codpedido; // Sanitizar id de pedido

		// Helper para escapar HTML
		$esc = function ($v) {
			return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
		};

		// Empresa (encabezado)
		$empresa = $this->db->query(
			"SELECT * FROM public.personas WHERE codpersona = 1"
		)->result_array();

		// Info del pedido (usa NOW() como fecha para evitar columna inexistente)
		$info = $this->db->query(
			"SELECT pe.razonsocial,  
                (SELECT COALESCE(string_agg(nromesa::text, ' - '), '—')
                 FROM restaurante.mesaspedido
                 WHERE codpedido = ?) AS mesa,
                p.valorventa, p.igv, p.importe,
                NOW() AS fecharegistro
         FROM kardex.pedidos p
         INNER JOIN public.personas pe ON p.codempleado = pe.codpersona
         WHERE p.codpedido = ?
         LIMIT 1",
			[$codpedido, $codpedido]
		)->result_array();

		// Detalle de ítems (IMPORTANTE: incluir cantidad y llaves para el subquery)
		$detalle = $this->db->query(
			"SELECT
            kd.cantidad,
            kd.codproducto,
            kd.codunidad,
            kd.item,
            kd.descripcion AS notapedido,
            p.descripcion  AS producto,
            u.descripcion  AS unidad,
            kd.preciounitario,
            kd.subtotal,
            (
              SELECT ROUND(COALESCE(SUM(a.cantidad),0))
              FROM restaurante.atendidos a
              WHERE a.codpedido  = kd.codpedido
                AND a.codproducto = kd.codproducto
                AND a.codunidad   = kd.codunidad
                AND a.item        = kd.item
            ) AS atendido
         FROM kardex.pedidosdetalle kd
         INNER JOIN almacen.productos p ON p.codproducto = kd.codproducto
         INNER JOIN almacen.unidades  u ON u.codunidad  = kd.codunidad
         WHERE kd.codpedido = ?
         ORDER BY p.descripcion ASC",
			[$codpedido]
		)->result_array();

		if (empty($info)) {
			echo "Pedido no encontrado";
			return;
		}

		// Total en letras
		$this->load->library("Number");
		$number    = new Number();
		$tot_total = number_format((float)$info[0]["importe"], 2, '.', '');
		$partes    = explode('.', $tot_total);
		$entero    = $partes[0] ?? '0';
		$centimos  = $partes[1] ?? '00';
		$totalLetras = strtoupper($number->convertirNumeroEnLetras($entero)) . " Y {$centimos}/100 SOLES";

		// Fecha/hora
		$fecha = date('d/m/Y H:i', strtotime($info[0]['fecharegistro'] ?: 'now'));

		// Encabezado empresa
		$tituloEmpresa = !empty($empresa[0]['nombrecomercial'])
			? $empresa[0]['nombrecomercial']
			: (!empty($empresa[0]['razonsocial']) ? $empresa[0]['razonsocial'] : '—');

		// Render HTML
		ob_start(); ?>
		<!DOCTYPE html>
		<html lang="es">

		<head>
			<meta charset="utf-8">
			<title>Comanda #<?= $esc($codpedido) ?></title>
			<style>
				.ticket {
					width: 260px;
					/* ~58mm de papel térmico */
					margin: 0 auto;
					padding: 6px 8px;
					font: 12px/1.25 "Courier New", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
					color: #000;
				}

				.center {
					text-align: center;
				}

				.right {
					text-align: right;
				}

				.bold {
					font-weight: 700;
				}

				.sep {
					margin: 6px 0;
					border: 0;
					border-top: 1px dashed #000;
				}

				.title {
					font-size: 14px;
					letter-spacing: 1px;
					margin: 2px 0 0;
				}

				.sub {
					font-size: 11px;
					color: #111;
					margin: 0;
				}

				.note {
					display: block;
					font-size: 10px;
					color: #333;
					margin-top: 1px;
				}

				.row {
					display: flex;
				}

				.row>div {
					padding: 2px 0;
				}

				.row .c-cant {
					width: 74px;
				}

				/* CANT + unidad */
				.row .c-desc {
					flex: 1;
				}

				/* DESCRIPCIÓN */
				.row .c-est {
					width: 78px;
					text-align: right;
				}

				/* ESTADO */
				.badge {
					display: inline-block;
					border: 1px solid #000;
					padding: 0 4px;
					border-radius: 4px;
					font-size: 10px;
				}

				.ok {
					background: #e6ffe6;
				}

				/* ATEND. */
				.warn {
					background: #fff0f0;
				}

				/* PEND.  */
				@media print {
					@page {
						margin: 0;
					}

					body {
						margin: 0;
					}

					.ticket {
						width: 260px;
						padding: 6px 8px;
					}

					.no-print {
						display: none !important;
					}
	
				}

								.note {
  font-weight: bold;
  text-transform: uppercase;
  color: #333;
  font-size: 0.95em;
}
			</style>
		</head>

		<body>
			<div class="ticket">

				<!-- Encabezado -->
				<div class="center bold"><?= $esc($tituloEmpresa) ?></div>
				<div class="center title">DETALLE DE COMANDA</div>
				<div class="center sub">COMPROBANTE NO AUTORIZADO</div>
				<hr class="sep">

				<!-- Pedido / Mesa / Fecha / Mozo -->
				<div class="row">
					<div class="c-desc bold">PEDIDO</div>
					<div class="c-est right bold">#<?= str_pad($codpedido, 5, '0', STR_PAD_LEFT) ?></div>
				</div>
				<div class="row">
					<div class="c-desc">MESA</div>
					<div class="c-est right"><?= $esc($info[0]['mesa'] ?? '—') ?></div>
				</div>
				<div class="row">
					<div class="c-desc">MOZO</div>
					<div class="c-est right"><?= $esc($info[0]['razonsocial'] ?? '—') ?></div>
				</div>
				<div class="row">
					<div class="c-desc">FECHA</div>
					<div class="c-est right"><?= $esc($fecha) ?></div>
				</div>

				<hr class="sep">

				<!-- Cabecera items -->
				<div class="row bold">
					<div class="c-cant">CANT</div>
					<div class="c-desc">DESCRIPCIÓN</div>
					<div class="c-est">ESTADO</div>
				</div>

				<?php foreach ($detalle as $it):
					$qty         = isset($it['cantidad'])  ? (float)$it['cantidad']  : 0.0;
					$cant        = round($qty, 1);
					$unidad      = isset($it['unidad'])    ? (string)$it['unidad']   : '';
					$producto    = isset($it['producto'])  ? (string)$it['producto'] : '';
					$notaProducto = trim((string)($it['notapedido'] ?? ''));
					$atendidoVal = isset($it['atendido'])  ? (float)$it['atendido']  : 0.0;
					$estadoOk    = ($qty == $atendidoVal);
					$estadoTxt   = $estadoOk ? 'ATEND.' : 'PEND.';
					$estadoCls   = $estadoOk ? 'ok' : 'warn';
				?>
					<div class="row">
						<div class="c-cant"><?= $esc($cant) . " " . $esc($unidad) ?></div>
						<div class="c-desc">
							<?= $esc($producto) ?>
							<?php if ($notaProducto !== ''): ?>
							<span class="note bold" style="font-weight: 900; text-transform: uppercase;">
    (<?= $esc($notaProducto) ?>)
</span>

							<?php endif; ?>
						</div>
						<div class="c-est"><span class="badge <?= $estadoCls ?>"><?= $estadoTxt ?></span></div>
					</div>
				<?php endforeach; ?>

				<hr class="sep">

				<!-- Total (opcional en comanda; útil si cocina lo revisa) -->
				<!-- <div class="row">
					<div class="c-desc bold">TOTAL</div>
					<div class="c-est right bold">S/ <?= number_format((float)$info[0]['importe'], 2) ?></div>
				</div>
				<div class="center sub">SON <?= $esc($totalLetras) ?></div>

				<hr class="sep">
				<div class="center sub">Gracias por su pedido</div> -->
				<div class="center sub">Impreso: <?= date('d/m/Y H:i') ?></div>

				<div class="no-print center" style="margin-top:6px">
					<button onclick="window.print()">Imprimir</button>
				</div>
			</div>

			<!-- Auto-print para kiosko (si lo necesitas, descomenta) -->
			<!-- <script>window.print();</script> -->
		</body>

		</html>
<?php
		$html = ob_get_clean();
		header('Content-Type: text/html; charset=UTF-8');
		echo $html;
	}
}
