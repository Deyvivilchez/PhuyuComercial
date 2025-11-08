<?php defined('BASEPATH') or exit('No direct script access allowed');

class Formato extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model("Facturacion_model");
	}

	public function formato_guia($codguiar)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right";
		$marco = 'padding:10px';
		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial,email from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$principal = $this->db->query("select *from public.sucursales where principal=1 and estado=1")->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$guia = $this->db->query("select k.constancia,k.observaciones,k.licenciaconductor,k.razonsocialconductor,k.documentoconductor ,k.codmodalidadtraslado,k.fechaguia,k.fechatraslado,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.documento,p.razonsocial AS cliente, mod.modalidadtraslado AS modalidad, mot.descripcion as motivo,k.direccionpartida,k.direccionllegada,k.codempleado,k.nroplaca, up.distrito AS distpartida,up.provincia AS provpartida, up.departamento AS deppartida,ud.distrito AS distdestino,ud.provincia AS provdestino,ud.departamento AS depdestino,k.codpersona,peso,nropaquetes,documentotransportista, razonsocialtransportista from almacen.guiasr as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) inner join almacen.modalidadtraslado as mod on(k.codmodalidadtraslado=mod.codmodalidadtraslado) inner join almacen.motivotraslado as mot on(k.codmotivotraslado=mot.codmotivotraslado) inner join public.ubigeo as up on(k.codubigeopartida=up.codubigeo) inner join public.ubigeo as ud on(k.codubigeollegada=ud.codubigeo) where k.codguiar=" . $codguiar)->result_array();

		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.peso from almacen.guiasrdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codguiar=" . $codguiar . " order by kd.item")->result_array();

		$formato = $this->db->query("select *from caja.comprobantes where codcomprobantetipo=" . $guia[0]["codcomprobantetipo"] . " AND seriecomprobante = '" . $guia[0]["seriecomprobante"] . "' AND codsucursal= " . $_SESSION['phuyu_codsucursal'])->result_array();

		$logo = "empresa/default.png";
		if ($formato[0]["logo"] != "") {
			$logo = "empresa/" . $formato[0]["logo"];
		}
		$logo = base_url() . 'public/img/' . $logo;
		if (!file_exists($logo)) {
			$logo = '';
		}

		if ($empresa[0]["nombrecomercial"] == '') {
			$nombre = $empresa[0]['razonsocial'];
		} else {
			$nombre = $empresa[0]["nombrecomercial"];
		}

		$ubigeopartida = $guia[0]["distpartida"] . ' - ' . $guia[0]['provpartida'] . ' - ' . $guia[0]['deppartida'];
		$ubigeodestino = $guia[0]["distdestino"] . ' - ' . $guia[0]['provdestino'] . ' - ' . $guia[0]['depdestino'];

		$html = '<table width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:20%">';
		$html .= '<h4></h4> <img src="' . $logo . '" style="height:100px">';
		$html .= '</th>';
		$html .= '<th style="width:50%">';
		$html .= '<h3>' . $nombre . '</h3>';
		if (count($principal) > 0) {
			if ($_SESSION["phuyu_codsucursal"] != $principal[0]["codsucursal"]) {
				$html .= '<b style="font-size:8px">PRINCIPAL: ' . $principal[0]["direccion"] . '</b>';

				$html .= '<b style="font-size:8px">SUCURSAL: ' . $sucursal[0]["direccion"] . '</b>';
			} else {
				$html .= '<b style="font-size:8px">' . $sucursal[0]["direccion"] . '</b>';
			}
		} else {
			$html .= '<b style="font-size:8px">' . $sucursal[0]["direccion"] . '</b>';
		}
		$html .= '<br><b style="font-size:8px">Teléfono: ' . $sucursal[0]["telefonos"] . '</b>';
		$html .= '<br><b style="font-size:8px">Email: ' . $empresa[0]["email"] . '</b>';
		$html .= '<p>' . $parametros[0]["slogan"] . '</p>';
		$html .= '</th>';
		$html .= '<th style="width:30%;border:1px solid #000;color:#000;">';
		$html .= '<h3 style="padding-top:10px">RUC: ' . $empresa[0]["documento"] . '</h3> <h3>' . $guia[0]["comprobante"] . '</h3>';
		$html .= '<h3>' . $guia[0]["seriecomprobante"] . ' - ' . $guia[0]["nrocomprobante"] . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="0" width="100%"> <tr> <th style="height:5px;"></th> </tr> </table>';

		$html .= '<table cellpadding="3" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;font-size:9px;' . $marco . '"> <b>FECHA EMISION</b> </td>';
		$html .= '<td style="width:84%;font-size:9px;' . $marco . '">: ' . $guia[0]["fechaguia"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>RAZON SOCIAL</b> </td>';
		$html .= '<td style="font-size:9px">: ' . $guia[0]["cliente"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>DNI / RUC</b> </td>';
		$html .= '<td style="font-size:9px">: ' . $guia[0]["documento"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$html .= '<table cellpadding="3" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;font-size:9px;"> <b>MOT. TRASLADO</b> </td>';
		$html .= '<td style="width:34%;font-size:9px;">: ' . $guia[0]["motivo"] . '</td>';
		$html .= '<td style="width:20%;font-size:9px;"> <b>FECHA TRASLADO</b> </td>';
		$html .= '<td style="width:30%;font-size:9px;">: ' . $guia[0]["fechatraslado"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>PESO TOTAL BRUTO</b> </td>';
		$html .= '<td style="font-size:9px">: ' . number_format($guia[0]["peso"], 2, '.', '') . '</td>';
		$html .= '<td style="font-size:9px"> <b>N° TOTAL DE BULTOS</b> </td>';
		$html .= '<td style="font-size:9px">: ' . number_format($guia[0]["nropaquetes"], 2, '.', '') . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>PUNTO DE PARTIDA</b> </td>';
		$html .= '<td style="font-size:9px" colspan="3">: ' . $guia[0]["direccionpartida"] . ', ' . $ubigeopartida . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>PUNTO DE LLEGADA</b> </td>';
		$html .= '<td style="font-size:9px" colspan="3">: ' . $guia[0]["direccionllegada"] . ', ' . $ubigeodestino . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$html .= '<table cellpadding="3" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;font-size:9px;' . $marco . '"> <b>MOD. TRANSPORTE</b> </td>';
		$html .= '<td style="width:84%;font-size:9px;' . $marco . '">: ' . $guia[0]["modalidad"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>TRANSPORTISTA</b> </td>';
		$html .= '<td style="font-size:9px">: ' . $guia[0]["razonsocialtransportista"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>DNI / RUC</b> </td>';
		$html .= '<td style="font-size:9px">: ' . $guia[0]["documentotransportista"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>CONDUCTOR</b> </td>';
		$html .= '<td style="font-size:9px">: ' . $guia[0]["razonsocialconductor"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>DNI / RUC</b> </td>';
		$html .= '<td style="font-size:9px">: ' . $guia[0]["documentoconductor"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>LICENCIA CONDUCTOR</b> </td>';
		$html .= '<td style="font-size:9px">: ' . $guia[0]["licenciaconductor"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>VEHICULO PLACA</b> </td>';
		$html .= '<td style="font-size:9px">: ' . $guia[0]["nroplaca"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:9px"> <b>MTC VEHICULO </b> </td>';
		$html .= '<td style="font-size:9px">: ' . $guia[0]["constancia"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:6%;font-size:9px"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:53%;font-size:9px"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:15%;font-size:9px"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;font-size:9px"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;font-size:9px"> <b>PESO</b> </td>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="1" width="100%" style="border:1px solid #000;">';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:6%;font-size:9px"> 0' . $value["item"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:53%;font-size:10px;font-size:9px"> ' . $value["producto"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:15%;font-size:9px"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;font-size:9px;text-align:right"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;font-size:9px;text-align:right"> ' . number_format($value["peso"], 2) . ' </td>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';
		$html .= '<table cellpadding="1" width="100%" style="border:1px solid #000;"> <tr><td style="' . $estilo . 'width:6%;font-size:9px">Observacion</td><td style="width:94%;font-size:9px">' . $guia[0]["observaciones"] . '</td></tr></table>';

		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';
		$html .= '<table width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:100%">';
		$html .= '<h5 style="color:#000;" align="center">' . $parametros[0]["publicidad"] . '</h5>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$this->load->library('Pdf');

		$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR VENTA");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage("A");
		$pdf->writeHTML($html, true, 0, true, 0);
		//$pdf->writeHTML($html, true, false, true, false, '');

		$nombre_archivo = utf8_decode("ImprimirVenta.pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function consulta($codkardex)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$principal = $this->db->query("select *from public.sucursales where principal=1 and estado=1")->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("select k.fechacomprobante,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, k.codsucursal ,p.documento,k.cliente,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.nroplaca, k.codpersona, k.icbper from kardex.kardex as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=" . $codkardex)->result_array();
		$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.kardexdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardex=" . $codkardex . " order by kd.item")->result_array();


		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $venta[0]["codsucursal"])->result_array();

		if ($empresa[0]["nombrecomercial"] == '') {
			$nombre = $empresa[0]['razonsocial'];
		} else {
			$nombre = $empresa[0]["nombrecomercial"];
		}

		$html = '<table cellpadding="6" width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:30%">';
		$html .= '<h4></h4> <img src="' . base_url() . 'public/img/empresa/logo_o.png" style="height:100px">';
		$html .= '</th>';
		$html .= '<th style="width:35%">';
		$html .= '<h3>' . $nombre . '</h3>';
		$html .= '<p>' . $parametros[0]["slogan"] . '</p>';
		$html .= '</th>';
		$html .= '<th style="width:35%;border:1px solid #000;color:#000;padding:10px !important;">';
		$html .= '<h2>RUC: ' . $empresa[0]["documento"] . '</h2> <h6></h6> <h3>' . $venta[0]["comprobante"] . '</h3>';
		$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%">';
		if (count($principal) > 0) {
			if ($venta[0]["codsucursal"] != $principal[0]["codsucursal"]) {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>PRINCIPAL: ' . $principal[0]["direccion"] . '</b> </td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>SUCURSAL: ' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			} else {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr>';
			$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
			//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
			$html .= '</tr>';
		}

		$html .= '<tr>';
		$html .= '<td> <b>' . $sucursal[0]["telefonos"] . '</b> </td>';
		// $html .= '<td> E-MAIL: '.$empresa[0]["email"].' </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="0" width="100%"> <tr> <th style="height:5px;"></th> </tr> </table>';

		$html .= '<table cellpadding="6" width="100%" style="border:1px solid #000;border-radius:50%">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;font-size:10px"> <b>CODIGO CLIENTE</b> </td>';
		$html .= '<td style="width:54%;font-size:10px">: 000' . $venta[0]["codpersona"] . '</td>';
		$html .= '<td style="width:18%;font-size:10px"> <b>CONDICION PAGO</b> </td>';
		if ($venta[0]["condicionpago"] == 1) {
			$html .= '<td style="width:12%;font-size:10px">: CONTADO</td>';
		} else {
			$html .= '<td style="width:12%;font-size:10px">: CREDITO</td>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>RAZON SOCIAL</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["cliente"] . '</td>';
		$html .= '<td style="font-size:10px"> <b>GUIA N°</b> </td>';
		$html .= '<td>: </td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>DIRECCION</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '<td style="font-size:10px"> <b>MONEDA</b> </td>';
		$html .= '<td style="font-size:10px">: SOLES</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DNI / RUC</b> </td>';
		$html .= '<td>: ' . $venta[0]["documento"] . '</td>';
		$html .= '<td> <b>FECHA</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechacomprobante"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:6%;"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:40%;"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:15%;"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>P.UNITARIO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>IMPORTE</b> </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$c = 1;
		$html .= '<table cellpadding="1" width="100%" style="border:1px solid #000;">';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:6%;"> 0' . $c . ' </td>';
			$html .= '<td style="' . $estilo . 'width:40%;font-size:10px;"> ' . $value["producto"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:15%;font-size:10px;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["preciounitario"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["subtotal"], 2) . ' </td>';
			$html .= '</tr>';
			$c++;
		}
		/* for ($i=0; $i < 7 - count($detalle); $i++) { 
            	$html .= '<tr>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	            $html .= '</tr>';
            } */
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$this->load->library("Number");
		$number = new Number();
		$total_texto = $number->convertirNumeroEnLetras(round($venta[0]["importe"], 2));

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . ' width:65%" rowspan="10" align="center"> <br> ';
		$html .= '<h3> SON: ' . strtoupper($total_texto) . ' Y 00/100 SOLES</h3> <br> ';
		$html .= '<img src="' . base_url() . 'sunat/webphuyu/qrcode.png" style="height:100px">';
		$html .= '<p>Para ver el documento visita: http://ceramicasm.erpperu.com/consultacomprobantes</p>';
		$html .= '</td>';
		$html .= '<td style="' . $estilo . ' width:20%;text-align:right"> <b>OP.GRAVADAS S/</b> </td>';
		$html .= '<td style="' . $estilo . ' width:15%;text-align:right">' . number_format($totales[0]["gravado"] - $venta[0]["igv"], 2) . ' </td>';
		$html .= '</tr>';

		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.INAFECTAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["inafecto"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.EXONERADAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["exonerado"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>O.GRATUITAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["gratuito"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OTROS CARGOS S/</b> </td> <td style="' . $estilo1 . '">0.00</td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OTROS TRIBUTOS S/</b> </td> <td style="' . $estilo1 . '">0.00</td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>DESCUENTO S/</b>  </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["descglobal"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>IGV S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["igv"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>ICBPER S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["icbper"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>TOTAL S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["importe"], 2) . ' </td> </tr>';
		$html .= '</table><br><br>';

		$textoqr = $empresa[0]["razonsocial"] . "|" . $venta[0]["seriecomprobante"] . "|" . $venta[0]["nrocomprobante"] . "|" . number_format($venta[0]["igv"], 2) . "|" . number_format($venta[0]["importe"], 2) . "|" . $venta[0]["fechacomprobante"] . "|" . $venta[0]["documento"];

		$this->load->library('ciqrcode');
		$params['data'] = $textoqr;
		$params['level'] = 'H';
		$params['size'] = 5;
		$params['savename'] = "./sunat/webphuyu/qrcode.png";
		$this->ciqrcode->generate($params);

		$archivo_error = APPPATH . "/logs/qrcode.png-errors.txt";
		unlink($archivo_error);

		$html .= '<table><tr><td>BIENES TRANSFERIDOS EN LA AMAZONIA REGION SELVA PARA SER CONSUMIDOS EN LA MISMA</td></tr><tr><td>SERVICIOS PRESTADOS EN LA AMAZONIA REGION SELVA PARA SER CONSUMIDOS EN LA MISMA</td></tr> </table>';

		$this->load->library('Pdf');

		$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR VENTA");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage("A");
		$pdf->writeHTML($html, true, 0, true, 0);
		//$pdf->writeHTML($html, true, false, true, false, '');

		$nombre_archivo = utf8_decode("ImprimirVenta.pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function a4movimiento($codkardex)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$principal = $this->db->query("select *from public.sucursales where principal=1 and estado=1")->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("select k.codkardex,k.fechacomprobante,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.*,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.nroplaca, k.codpersona, k.icbper from kardex.kardex as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=" . $codkardex)->result_array();
		$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,p.codigo,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.kardexdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardex=" . $codkardex . " order by kd.item")->result_array();

		if ($empresa[0]["nombrecomercial"] == '') {
			$nombre = $empresa[0]['razonsocial'];
		} else {
			$nombre = $empresa[0]["nombrecomercial"];
		}

		$logo = base_url() . 'public/img/' . $_SESSION['phuyu_logo'];
		if (!file_exists($logo)) {
			$logo = '';
		}

		$html = '<table cellpadding="6" width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:30%">';
		$html .= '<h4></h4> <img src="' . $logo . '" style="height:100px">';
		$html .= '</th>';
		$html .= '<th style="width:35%">';
		$html .= '<h3>' . $nombre . '</h3>';
		$html .= '<p>' . $parametros[0]["slogan"] . '</p>';
		$html .= '</th>';
		$html .= '<th style="width:35%;border:1px solid #000;color:#000;padding:10px !important;">';
		$html .= '<h2>RUC: ' . $empresa[0]["documento"] . '</h2> <h6></h6> <h3>' . $venta[0]["comprobante"] . '</h3>';
		$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%">';
		if (count($principal) > 0) {
			if ($_SESSION["phuyu_codsucursal"] != $principal[0]["codsucursal"]) {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>PRINCIPAL: ' . $principal[0]["direccion"] . '</b> </td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>SUCURSAL: ' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			} else {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr>';
			$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
			//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
			$html .= '</tr>';
		}

		$html .= '<tr>';
		$html .= '<td> <b>' . $sucursal[0]["telefonos"] . '</b> </td>';
		// $html .= '<td> E-MAIL: '.$empresa[0]["email"].' </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="0" width="100%"> <tr> <th style="height:5px;"></th> </tr> </table>';

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #000;border-radius:50%">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;font-size:10px"> <b>NRO INGRESO</b> </td>';
		$html .= '<td style="width:84%;font-size:10px">: ' . $venta[0]["codkardex"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>PROVEEDOR</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["razonsocial"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>DIRECCION</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>FECHA</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechacomprobante"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:6%;"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>CODIGO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:33%;"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>P.UNITARIO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>IMPORTE</b> </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$c = 1;
		$html .= '<table cellpadding="1" width="100%" style="border:1px solid #000;">';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:6%;"> 0' . $c . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;">' . $value["codigo"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:33%;font-size:10px;"> ' . $value["producto"] . ' ' . $value["descripcion"] . '</td>';
			$html .= '<td style="' . $estilo . 'width:12%;font-size:10px;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;text-align:right;font-size:10px;"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;text-align:right;font-size:10px;"> ' . number_format($value["preciounitario"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;text-align:right;font-size:10px;"> ' . number_format($value["subtotal"], 2) . ' </td>';
			$html .= '</tr>';
			$c++;
		}
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>TOTAL S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["importe"], 2) . ' </td> </tr>';
		$html .= '</table>';

		$this->load->library('Pdf');

		$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR MOVIMIENTO");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage("A");
		$pdf->writeHTML($html, true, 0, true, 0);
		//$pdf->writeHTML($html, true, false, true, false, '');

		$nombre_archivo = utf8_decode("ImprimirMovimiento.pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function a4($codkardex)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right;";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$principal = $this->db->query("select *from public.sucursales where principal=1 and estado=1")->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("select k.codkardex,k.fechacomprobante,k.conleyendaamazonia ,ct.descripcion as comprobante, ct.oficial, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.documento,k.cliente,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.nroplaca, k.codpersona, k.icbper from kardex.kardex as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=" . $codkardex)->result_array();

		$fechavencimiento = $venta[0]["fechacomprobante"];

		$empleado = $this->db->query("select p.razonsocial from public.empleados e inner join public.personas p ON p.codpersona = e.codpersona where e.codpersona=" . $venta[0]["codempleado"])->result_array();

		if ($venta[0]["condicionpago"] == 2) {
			$credito = $this->db->query("SELECT *FROM kardex.creditos WHERE codkardex=" . $venta[0]["codkardex"])->result_array();
			$fechavencimiento = $credito[0]["fechavencimiento"];
		}
		$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.kardexdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardex=" . $codkardex . " order by kd.item")->result_array();

		$cuentascorrientes = $this->db->query("select ct.*,b.descripcion as banco from caja.ctasctes ct inner join caja.bancos b ON(ct.codbanco=b.codbanco) where ct.codpersona=1")->result_array();

		$formato = $this->db->query("select nombrecomercial,logo,slogan,publicidad,agradecimiento,tipoconleyendaamazonia,impresionlogo from caja.comprobantes where codcomprobantetipo=" . $venta[0]["codcomprobantetipo"] . " AND seriecomprobante = '" . $venta[0]["seriecomprobante"] . "' AND codsucursal= " . $_SESSION['phuyu_codsucursal'])->result_array();

		//NOMBRE COMERCIAL
		$nombre = $formato[0]["nombrecomercial"];
		if ($nombre == "") {
			if ($empresa[0]["nombrecomercial"] == '') {

				$nombre = $empresa[0]['razonsocial'];
			} else {
				$nombre = $empresa[0]["nombrecomercial"];
			}
		}
		if ($formato[0]["impresionlogo"] == "") {
			$formato[0]["impresionlogo"] = 1;
		}
		// LOGO EN EL COMPROBANTE
		$logo = $formato[0]["logo"];
		if ($logo == "") {
			$direccionlogo = base_url() . "public/img/" . $_SESSION["phuyu_logo"];
		} else {
			$direccionlogo = base_url() . "public/img/empresa/" . $logo;
		}

		//SLOGAN Y AGRADECIMIENTO

		$slogan = $formato[0]["slogan"];
		if ($slogan == "") {
			$slogan = $parametros[0]["slogan"];
		}
		$publicidad = $formato[0]["publicidad"];
		if ($publicidad == "") {
			$publicidad = $parametros[0]["publicidad"];
		}

		$html = '<table cellpadding="6" width="100%" align="center">';
		$html .= '<tr>';
		if ($formato[0]["impresionlogo"] == 1) {
			$html .= '<th style="width:30%">';
			$html .= '<h4></h4> <img src="' . $direccionlogo . '" style="height:100px">';
			$html .= '</th>';
			$html .= '<th style="width:35%">';
			$html .= '<h2>' . $nombre . '</h2>';
			$html .= '<p>' . $slogan . '</p>';
			$html .= '<p>' . $publicidad . '</p>';
			$html .= '</th>';
			$html .= '<th style="width:35%;border:1px solid #000;color:#000;padding:10px !important;">';
			$html .= '<h2>RUC: ' . $empresa[0]["documento"] . '</h2> <h6></h6> <h3>' . $venta[0]["comprobante"] . '</h3>';
			$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
			$html .= '</th>';
		} else {
			$html .= '<th style="width:55%;text-align:left;padding:0px !important;">';
			$html .= '<img src="' . $direccionlogo . '" style="width:340px;height:120px">';
			$html .= '</th>';
			$html .= '<th style="width:45%;border:1px solid #000;color:#000;padding:10px !important;">';
			$html .= '<h4></h4><h2>RUC: ' . $empresa[0]["documento"] . '</h2> <h6></h6> <h3>' . $venta[0]["comprobante"] . '</h3>';
			$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
			$html .= '</th>';
		}
		$html .= '</tr>';
		$html .= '</table>';
		if ($formato[0]["impresionlogo"] == 1) {
			$html .= '<table cellpadding="2" width="100%">';
			if (count($principal) > 0) {
				if ($_SESSION["phuyu_codsucursal"] != $principal[0]["codsucursal"]) {
					$html .= '<tr>';
					$html .= '<td style="width:100%;"> <b>PRINCIPAL: ' . $principal[0]["direccion"] . '</b> </td>';
					$html .= '</tr>';

					$html .= '<tr>';
					$html .= '<td style="width:100%;"> <b>SUCURSAL: ' . $sucursal[0]["direccion"] . '</b> </td>';
					//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
					$html .= '</tr>';
				} else {
					$html .= '<tr>';
					$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
					//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
					$html .= '</tr>';
				}
			} else {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			}

			$html .= '<tr>';
			$html .= '<td> <b>' . $sucursal[0]["telefonos"] . '</b> </td>';
			// $html .= '<td> E-MAIL: '.$empresa[0]["email"].' </td>';
			$html .= '</tr>';
			$html .= '</table>';
		}
		$html .= '<table cellpadding="0" width="100%"> <tr> <th style="height:5px;"></th> </tr> </table>';

		$html .= '<table cellpadding="3" width="100%" style="border:1px solid #000;border-radius:50%">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;font-size:10px"> <b>CODIGO CLIENTE</b> </td>';
		$html .= '<td style="width:45%;font-size:10px">: 000' . $venta[0]["codpersona"] . '</td>';
		$html .= '<td style="width:18%;font-size:10px"> <b>CONDICION PAGO</b> </td>';
		if ($venta[0]["condicionpago"] == 1) {
			$html .= '<td style="width:21%;font-size:10px">: CONTADO</td>';
		} else {
			$html .= '<td style="width:21%;font-size:10px">: CREDITO: ' . $credito[0]["nrodias"] . ' dias</td>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>RAZON SOCIAL</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["cliente"] . '</td>';
		$html .= '<td style="font-size:10px"> <b>FECHA EMISION</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechacomprobante"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>DIRECCION</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '<td style="font-size:10px"> <b>FECHA VENCIMIENTO</b> </td>';
		$html .= '<td>: ' . $fechavencimiento . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DNI / RUC</b> </td>';
		$html .= '<td>: ' . $venta[0]["documento"] . '</td>';
		$html .= '<td style="font-size:10px"> <b>MONEDA</b> </td>';
		$html .= '<td style="font-size:10px">: SOLES</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>VENDEDOR</b> </td>';
		$html .= '<td>: ' . $empleado[0]["razonsocial"] . '</td>';
		$html .= '<td> <b>USUARIO</b> </td>';
		$html .= '<td>: ' . $_SESSION["phuyu_usuario"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:6%;font-size:10px"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:40%;font-size:10px"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:15%;font-size:10px"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;font-size:10px"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;font-size:10px"> <b>P.UNITARIO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;font-size:10px"> <b>IMPORTE</b> </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$c = 1;
		$html .= '<table cellpadding="1" width="100%" style="border:1px solid #000;">';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:6%;"> 0' . $c . ' </td>';
			$html .= '<td style="' . $estilo . 'width:40%;font-size:10px;"> ' . $value["producto"] . ' ' . $value["descripcion"] . '</td>';
			$html .= '<td style="' . $estilo . 'width:15%;font-size:10px;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["preciounitario"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["subtotal"], 2) . ' </td>';
			$html .= '</tr>';
			$c++;
		}
		/* for ($i=0; $i < 7 - count($detalle); $i++) { 
            	$html .= '<tr>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	            $html .= '</tr>';
            } */
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$this->load->library("Number");
		$number = new Number();
		$total_texto = $number->convertirNumeroEnLetras(round($venta[0]["importe"], 2));

		$html .= '<table cellpadding="1" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . ' width:65%" rowspan="10" align="center"> ';
		$html .= '<p style="font-size:10px"> SON: ' . strtoupper($total_texto) . ' Y 00/100 SOLES</p><br> ';

		$html .= '<img src="' . base_url() . 'sunat/webphuyu/qrcode.png" style="height:80px">';
		$html .= '<p>' . $parametros[0]["urlconsultacomprobantes"] . '</p>';
		$html .= '</td>';
		$html .= '<td style="' . $estilo . ' width:20%;text-align:right;font-size:10px"> <b>OP.GRAVADAS S/</b> </td>';
		$html .= '<td style="' . $estilo . ' width:15%;text-align:right">' . number_format($totales[0]["gravado"] - $venta[0]["igv"], 2) . ' </td>';
		$html .= '</tr>';

		$html .= '<tr> <td style="' . $estilo1 . ' font-size:10px"> <b>OP.INAFECTAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["inafecto"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . ' font-size:10px"> <b>OP.EXONERADAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["exonerado"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . ' font-size:10px"> <b>O.GRATUITAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["gratuito"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . ' font-size:10px"> <b>OTROS CARGOS S/</b> </td> <td style="' . $estilo1 . '">0.00</td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . ' font-size:10px"> <b>OTROS TRIBUTOS S/</b> </td> <td style="' . $estilo1 . '">0.00</td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . ' font-size:10px"> <b>DESCUENTO S/</b>  </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["descglobal"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . ' font-size:10px"> <b>IGV S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["igv"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . ' font-size:10px"> <b>ICBPER S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["icbper"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . ' font-size:10px"> <b>TOTAL S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["importe"], 2) . ' </td> </tr>';
		$html .= '</table>';

		foreach ($cuentascorrientes as $key => $value) {
			$html .= '<p style="text-align:left !important;font-size:9px"><b>' . $value["banco"] . '</b> <br>CC: &nbsp;' . $value["nroctacte"] . '<br>
			CCI: ' . $value["descripcion"] . '</p><br>';
		}
		$html .= '<p>' . $formato[0]["agradecimiento"] . '</p>';
		if ($venta[0]["conleyendaamazonia"] == 1) {
			$html .= '<p align="center" style="font-size:8px">';
			if ($formato[0]["tipoconleyendaamazonia"] == 1) {
				$html .= $parametros[0]["codleyendapamazonia"] . ' - ' . $parametros[0]["leyendapamazonia"];
			} else if ($formato[0]["tipoconleyendaamazonia"] == 2) {
				$html .= $parametros[0]["codleyendasamazonia"] . ' - ' . $parametros[0]["leyendasamazonia"];
			} else {
				$html .= $parametros[0]["codleyendapamazonia"] . ' - ' . $parametros[0]["leyendapamazonia"] . '<br>' . $parametros[0]["codleyendasamazonia"] . ' - ' . $parametros[0]["leyendasamazonia"];
			}
			$html .= '</p>';
		}

		$textoqr = $empresa[0]["razonsocial"] . "|" . $venta[0]["seriecomprobante"] . "|" . $venta[0]["nrocomprobante"] . "|" . number_format($venta[0]["igv"], 2) . "|" . number_format($venta[0]["importe"], 2) . "|" . $venta[0]["fechacomprobante"] . "|" . $venta[0]["documento"];

		$this->load->library('ciqrcode');
		$params['data'] = $textoqr;
		$params['level'] = 'H';
		$params['size'] = 5;
		$params['savename'] = "./sunat/webphuyu/qrcode.png";
		$this->ciqrcode->generate($params);

		$archivo_error = APPPATH . "/logs/qrcode.png-errors.txt";
		unlink($archivo_error);

		$this->load->library('Pdf');

		$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR VENTA");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage("A");
		$pdf->writeHTML($html, true, 0, true, 0);
		//$pdf->writeHTML($html, true, false, true, false, '');

		$nombre_archivo = utf8_decode($empresa[0]["documento"] . '-' . $venta[0]["oficial"] . '-' . $venta[0]["seriecomprobante"] . '-' . $venta[0]["nrocomprobante"] . ".pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function a4despacho($codkardex)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right;";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$principal = $this->db->query("select *from public.sucursales where principal=1 and estado=1")->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("SELECT kd.codkardex, kd.fechakardex, kd.movimiento, (CASE k.codmovimientotipo WHEN 20 THEN 'NOTA DE SALIDA' ELSE 'NOTA DE ENTRADA' END) as comprobante, k.codcomprobantetipo, k.codpersona,
       kd.seriecomprobante, kd.nrocomprobante, kd.estado, kd.observacion, k.seriecomprobante as serieref,
       k.nrocomprobante as nroref,p.documento,k.cliente,k.direccion
  FROM kardex.kardexalmacen kd inner join kardex.kardex k on(kd.codkardex = k.codkardex)
  inner join public.personas as p on(k.codpersona=p.codpersona) where kd.codkardexalmacen=" . $codkardex)->result_array();
		//echo json_encode($venta);exit;
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad from kardex.kardexalmacendetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardexalmacen=" . $codkardex . " order by kd.item")->result_array();

		$cuentascorrientes = $this->db->query("select ct.*,b.descripcion as banco from caja.ctasctes ct inner join caja.bancos b ON(ct.codbanco=b.codbanco) where ct.codpersona=1")->result_array();

		if ($empresa[0]["nombrecomercial"] == '') {
			$nombre = $empresa[0]['razonsocial'];
		} else {
			$nombre = $empresa[0]["nombrecomercial"];
		}

		$formato = $this->db->query("select *from caja.comprobantes where codcomprobantetipo=" . $venta[0]["codcomprobantetipo"] . " AND seriecomprobante = '" . $venta[0]["serieref"] . "' AND codsucursal= " . $_SESSION['phuyu_codsucursal'])->result_array();

		// echo json_encode($venta[0]["codcomprobantetipo"]." - ".$venta[0]["serieref"]);

		$logo = "empresa/default.png";
		if (count($formato)) {
			if ($formato[0]["logo"] != "") {
				$logo = "empresa/" . $formato[0]["logo"];
			}
		} else {
			$formato[0]["slogan"] = '';
			$formato[0]["publicidad"] = '';
		}

		$html = '<table cellpadding="6" width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:30%">';
		$html .= '<h4></h4> <img src="' . base_url() . 'public/img/' . $logo . '" style="height:100px">';
		$html .= '</th>';
		$html .= '<th style="width:35%">';
		$html .= '<h2>' . $nombre . '</h2>';
		$html .= '<p>' . $formato[0]["slogan"] . '</p>';
		$html .= '<p>' . $formato[0]["publicidad"] . '</p>';
		$html .= '</th>';
		$html .= '<th style="width:35%;border:1px solid #000;color:#000;padding:10px !important;">';
		$html .= '<h2>RUC: ' . $empresa[0]["documento"] . '</h2> <h6></h6> <h3>' . $venta[0]["comprobante"] . '</h3>';
		$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%">';
		if (count($principal) > 0) {
			if ($_SESSION["phuyu_codsucursal"] != $principal[0]["codsucursal"]) {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>PRINCIPAL: ' . $principal[0]["direccion"] . '</b> </td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>SUCURSAL: ' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			} else {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr>';
			$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
			//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
			$html .= '</tr>';
		}

		$html .= '<tr>';
		$html .= '<td> <b>' . $sucursal[0]["telefonos"] . '</b> </td>';
		// $html .= '<td> E-MAIL: '.$empresa[0]["email"].' </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="0" width="100%"> <tr> <th style="height:5px;"></th> </tr> </table>';

		$html .= '<table cellpadding="3" width="100%" style="border:1px solid #000;border-radius:50%">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;font-size:10px"> <b>CODIGO CLIENTE</b> </td>';
		$html .= '<td style="width:50%;font-size:10px">: 000' . $venta[0]["codpersona"] . '</td>';

		$html .= '<td style="width:16%"> <b>DNI / RUC</b> </td>';
		$html .= '<td style="width:18%">: ' . $venta[0]["documento"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>RAZON SOCIAL</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["cliente"] . '</td>';
		$html .= '<td> <b>FECHA</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechakardex"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>DIRECCION</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '<td style="font-size:10px"> <b>MONEDA</b> </td>';
		$html .= '<td style="font-size:10px">: SOLES</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>REFERENCIA</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["serieref"] . '-' . $venta[0]["nroref"] . ' </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$html .= '<table cellpadding="5" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:6%;font-size:10px"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:66%;font-size:10px"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:15%;font-size:10px"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;font-size:10px"> <b>DESPACHO</b> </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$c = 1;
		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:6%;"> 0' . $c . ' </td>';
			$html .= '<td style="' . $estilo . 'width:66%;font-size:10px;"> ' . $value["producto"] . '</td>';
			$html .= '<td style="' . $estilo . 'width:15%;font-size:10px;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '</tr>';
			$c++;
		}
		/* for ($i=0; $i < 7 - count($detalle); $i++) { 
            	$html .= '<tr>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	            $html .= '</tr>';
            } */
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$this->load->library('Pdf');

		$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("phuyu SOFT | IMPRIMIR DESPACHO");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage("A");
		$pdf->writeHTML($html, true, 0, true, 0);
		//$pdf->writeHTML($html, true, false, true, false, '');

		$nombre_archivo = utf8_decode($empresa[0]["documento"] . '-' . $venta[0]["seriecomprobante"] . '-' . $venta[0]["nrocomprobante"] . ".pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function a5movimiento($codkardex)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("select k.codkardex,k.fechacomprobante,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.*,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.nroplaca from kardex.kardex as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=" . $codkardex)->result_array();

		$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,p.codigo,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.kardexdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardex=" . $codkardex . " order by kd.item")->result_array();

		//$html = $this->load->view("facturacion/formato/a5",compact("empresa","parametros","venta"),true);

		if ($empresa[0]["nombrecomercial"] == '') {
			$nombre = $empresa[0]['razonsocial'];
		} else {
			$nombre = $empresa[0]["nombrecomercial"];
		}

		$logo = base_url() . 'public/img/' . $_SESSION["phuyu_logo"];
		if (!file_exists($logo)) {
			$logo = '';
		}

		$vendedor = $this->db->query("select razonsocial from public.personas where codpersona=" . $venta[0]["codempleado"])->result_array();
		$html = '<table width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:20%">';
		$html .= '<img src="' . $logo . '" style="height:100px;">';
		$html .= '<h6>DE: ' . $nombre . '</h6>';
		$html .= '</th>';
		$html .= '<th style="width:40%">';
		$html .= '<h4>' . $parametros[0]["slogan"] . '</h4>';
		$html .= '</th>';
		$html .= '<th style="width:2%;"></th>';
		$html .= '<th style="width:38%;border:1px solid #000;color:#000;">';
		$html .= '<h3>RUC: ' . $empresa[0]["documento"] . '</h3> <h3>' . $venta[0]["comprobante"] . '</h3>';
		$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%">';
		$html .= '<tr>';
		$html .= '<td style="width:100%;"><b>' . $sucursal[0]["direccion"] . '</b> </td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>TELF: ' . $sucursal[0]["telefonos"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;font-size:9px;">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;"> <b>NRO INGRESO</b> </td>';
		$html .= '<td style="width:84%;">: ' . $venta[0]["codkardex"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="width:16%;"> <b>PROVEEDOR</b> </td>';
		$html .= '<td style="width:54%;">: ' . $venta[0]["razonsocial"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DIRECCION</b> </td>';
		$html .= '<td>: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>FECHA</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechacomprobante"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #000;font-size:8px;margin-top:-5px">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:7%;"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>CODIGO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:33%;"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>P.UNITARIO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>IMPORTE</b> </td>';
		$html .= '</tr>';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:7%;"> 0' . $value["item"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;">' . $value["codigo"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:33%;"> ' . $value["producto"] . ' ' . $value["descripcion"] . '</td>';
			$html .= '<td style="' . $estilo . 'width:12%;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;text-align:right"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;text-align:right"> ' . number_format($value["preciounitario"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;text-align:right"> ' . number_format($value["subtotal"], 2) . ' </td>';
			$html .= '</tr>';
		}
		$html .= '</table>';

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #000;font-size:8px">';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>TOTAL S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["importe"], 2) . ' </td> </tr>';
		$html .= '</table>';

		$this->load->library('Pdf');

		$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR MOVIMIENTO");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage('L', 'A5');
		//$pdf->AddPage('L', 'A5');
		// $pdf->SetLeftMargin(0);
		$pdf->writeHTML($html, true, 0, true, 0);

		$nombre_archivo = utf8_decode("ImprimirMovimiento.pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function a5($codkardex)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("select k.codkardex,k.fechacomprobante,k.conleyendaamazonia ,ct.descripcion as comprobante,ct.oficial, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.documento,k.cliente,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.nroplaca from kardex.kardex as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=" . $codkardex)->result_array();
		$fechavencimiento = $venta[0]["fechacomprobante"];
		$empleado = $this->db->query("select p.razonsocial from public.empleados e inner join public.personas p ON p.codpersona = e.codpersona where e.codpersona=" . $venta[0]["codempleado"])->result_array();

		if ($venta[0]["condicionpago"] == 2) {
			$credito = $this->db->query("SELECT *FROM kardex.creditos WHERE codkardex=" . $venta[0]["codkardex"])->result_array();
			$fechavencimiento = $credito[0]["fechavencimiento"];
		}
		$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.kardexdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardex=" . $codkardex . " order by kd.item")->result_array();

		$cuentascorrientes = $this->db->query("select ct.*,b.descripcion as banco from caja.ctasctes ct inner join caja.bancos b ON(ct.codbanco=b.codbanco) where ct.codpersona=1")->result_array();

		//$html = $this->load->view("facturacion/formato/a5",compact("empresa","parametros","venta"),true);

		$formato = $this->db->query("select *from caja.comprobantes where codcomprobantetipo=" . $venta[0]["codcomprobantetipo"] . " AND seriecomprobante = '" . $venta[0]["seriecomprobante"] . "' AND codsucursal= " . $_SESSION['phuyu_codsucursal'])->result_array();

		$nombre = $formato[0]["nombrecomercial"];
		if ($nombre == "") {
			if ($empresa[0]["nombrecomercial"] == '') {

				$nombre = $empresa[0]['razonsocial'];
			} else {
				$nombre = $empresa[0]["nombrecomercial"];
			}
		}

		if ($formato[0]["impresionlogo"] == "") {
			$formato[0]["impresionlogo"] = 1;
		}

		$logo = $formato[0]["logo"];
		if ($logo == "") {
			$direccionlogo = base_url() . "public/img/" . $_SESSION["phuyu_logo"];
		} else {
			$direccionlogo = base_url() . "public/img/empresa/" . $logo;
		}

		//SLOGAN Y AGRADECIMIENTO

		$slogan = $formato[0]["slogan"];
		if ($slogan == "") {
			$slogan = $parametros[0]["slogan"];
		}
		$publicidad = $formato[0]["publicidad"];
		if ($publicidad == "") {
			$publicidad = $parametros[0]["publicidad"];
		}

		$vendedor = $this->db->query("select razonsocial from public.personas where codpersona=" . $venta[0]["codempleado"])->result_array();
		$html = '<table width="100%" align="center">';
		$html .= '<tr>';
		if ($formato[0]["impresionlogo"] == 1) {
			$html .= '<th style="width:20%">';
			$html .= '<img src="' . $direccionlogo . '" style="height:100px;">';
			$html .= '</th>';
			$html .= '<th style="width:40%">';
			$html .= '<h4>' . $nombre . '</h4>';
			$html .= '<h6>' . $slogan . '</h6>';
			$html .= '<h6>' . $publicidad . '</h6>';
			$html .= '</th>';
			$html .= '<th style="width:2%;"></th>';
			$html .= '<th style="width:38%;border:1px solid #000;color:#000;">';
			$html .= '<h4>RUC: ' . $empresa[0]["documento"] . '</h4> <h4>' . $venta[0]["comprobante"] . '</h4>';
			$html .= '<h4>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h4>';
			$html .= '</th>';
		} else {
			$html .= '<th style="width:60%">';
			$html .= '<img src="' . $direccionlogo . '" style="width:240px;height:80px;">';
			$html .= '</th>';
			$html .= '<th style="width:2%;"></th>';
			$html .= '<th style="width:38%;border:1px solid #000;color:#000;">';
			$html .= '<h4>RUC: ' . $empresa[0]["documento"] . '</h4> <h4>' . $venta[0]["comprobante"] . '</h4>';
			$html .= '<h4>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h4>';
			$html .= '</th>';
		}
		$html .= '</tr>';
		$html .= '</table>';
		if ($formato[0]["impresionlogo"] == 1) {
			$html .= '<table cellpadding="2" width="100%">';
			$html .= '<tr>';
			$html .= '<td style="width:100%;font-size:9px"><b>' . $sucursal[0]["direccion"] . '</b> </td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td>TELF: ' . $sucursal[0]["telefonos"] . '</td>';
			$html .= '</tr>';
			$html .= '</table>';
		} else {
			$html .= '<table cellpadding="2" width="100%">';
			$html .= '<tr><td></td></tr>';
			$html .= '</table>';
		}

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;font-size:9px;">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;"> <b>CLIENTE</b> </td>';
		$html .= '<td style="width:54%;">: ' . $venta[0]["cliente"] . '</td>';
		$html .= '<td style="width:15%"> <b>PAGO AL</b> </td>';
		if ($venta[0]["condicionpago"] == 1) {
			$html .= '<td style="width:15%;">: CONTADO</td>';
		} else {
			$html .= '<td style="width:15%;">: CREDITO: ' . $credito[0]["nrodias"] . ' dias</td>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DIRECCION</b> </td>';
		$html .= '<td>: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '<td> <b>MONEDA</b> </td>';
		$html .= '<td>: SOLES</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DNI / RUC</b> </td>';
		$html .= '<td>: ' . $venta[0]["documento"] . '</td>';
		$html .= '<td> <b>FECHA</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechacomprobante"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>VENDEDOR</b> </td>';
		$html .= '<td>: ' . $empleado[0]["razonsocial"] . '</td>';
		$html .= '<td> <b>FECHA VENC.</b> </td>';
		$html .= '<td>: ' . $fechavencimiento . '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		$this->load->library("Number");
		$number = new Number();
		$total_texto = $number->convertirNumeroEnLetras(round($venta[0]["importe"], 2));

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;font-size:7px;margin-top:-5px">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:7%;"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:40%;"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:15%;"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>P.UNITARIO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>IMPORTE</b> </td>';
		$html .= '</tr>';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:7%;"> 0' . $value["item"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:40%;"> ' . $value["producto"] . ' ' . $value["descripcion"] . '</td>';
			$html .= '<td style="' . $estilo . 'width:15%;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;text-align:right"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right"> ' . number_format($value["preciounitario"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right"> ' . number_format($value["subtotal"], 2) . ' </td>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		$textoqr = $empresa[0]["razonsocial"] . "|" . $venta[0]["seriecomprobante"] . "|" . $venta[0]["nrocomprobante"] . "|" . number_format($venta[0]["igv"], 2) . "|" . number_format($venta[0]["importe"], 2) . "|" . $venta[0]["fechacomprobante"] . "|" . $venta[0]["documento"];

		$this->load->library('ciqrcode');
		$params['data'] = $textoqr;
		$params['level'] = 'H';
		$params['size'] = 5;
		$params['savename'] = "./sunat/webphuyu/qrcode.png";
		$this->ciqrcode->generate($params);

		$archivo_error = APPPATH . "/logs/qrcode.png-errors.txt";
		unlink($archivo_error);

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #000;font-size:8px">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . ' width:62%" rowspan="7" align="center">';
		$html .= '<h4> SON: ' . strtoupper($total_texto) . ' Y 00/100 SOLES</h4><br>';
		$html .= '<img src="' . base_url() . 'sunat/webphuyu/qrcode.png" style="height:80px">';
		$html .= '<p></p><p>' . $parametros[0]["urlconsultacomprobantes"] . '</p>';
		$html .= '</td>';
		$html .= '<td style="' . $estilo . ' width:25%;text-align:right"> <b>OP.GRAVADAS S/</b> </td>';
		$html .= '<td style="' . $estilo . ' width:13%;text-align:right">' . number_format($totales[0]["gravado"], 2) . ' </td>';
		$html .= '</tr>';

		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.INAFECTAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["inafecto"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.EXONERADAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["exonerado"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>O.GRATUITAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["gratuito"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>DESCUENTO S/</b>  </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["descglobal"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>IGV S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["igv"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>TOTAL S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["importe"], 2) . ' </td> </tr>';
		$html .= '</table>';
		foreach ($cuentascorrientes as $key => $value) {
			$html .= '<p style="text-align:left !important;font-size:8px"><b>' . $value["banco"] . '</b> <br>CC: &nbsp;' . $value["nroctacte"] . '<br>
			CCI: ' . $value["descripcion"] . '</p><br>';
		}

		//$html .= '<p style="color:#000;" align="center">'.$parametros[0]["publicidad"].'</p>';
		if ($venta[0]["conleyendaamazonia"] == 1) {
			$html .= '<p align="center" style="font-size:8px">';
			if ($formato[0]["tipoconleyendaamazonia"] == 1) {
				$html .= $parametros[0]["codleyendapamazonia"] . ' - ' . $parametros[0]["leyendapamazonia"];
			} else if ($formato[0]["tipoconleyendaamazonia"] == 2) {
				$html .= $parametros[0]["codleyendasamazonia"] . ' - ' . $parametros[0]["leyendasamazonia"];
			} else {
				$html .= $parametros[0]["codleyendapamazonia"] . ' - ' . $parametros[0]["leyendapamazonia"] . '<br>' . $parametros[0]["codleyendasamazonia"] . ' - ' . $parametros[0]["leyendasamazonia"];
			}
			$html .= '</p>';
		}

		$this->load->library('Pdf');

		$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR VENTA");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage('P', 'A5');
		//$pdf->AddPage('L', 'A5');
		// $pdf->SetLeftMargin(0);
		$pdf->writeHTML($html, true, 0, true, 0);

		$nombre_archivo = utf8_decode($empresa[0]["documento"] . '-' . $venta[0]["oficial"] . '-' . $venta[0]["seriecomprobante"] . '-' . $venta[0]["nrocomprobante"] . ".pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function ticketmovimiento($codkardex)
	{
		if (isset($_SESSION["phuyu_codusuario"])) {
			$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
			$sucursal = $this->db->query("select sucursal.*,empresa.publicidad,empresa.agradecimiento from public.sucursales as sucursal inner join public.empresas as empresa on(sucursal.codempresa=empresa.codempresa) where sucursal.codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();

			$venta = $this->db->query("select k.fechacomprobante,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.*,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.nroplaca from kardex.kardex as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=" . $codkardex)->result_array();
			$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
			$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,p.codigo,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.kardexdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardex=" . $codkardex . " order by kd.item")->result_array();

			$ticket = "ticketmovimiento";

			$this->load->view("facturacion/formato/" . $ticket, compact("empresa", "sucursal", "venta", "totales", "detalle"));
		} else {
			$this->load->view("phuyu/404");
		}
	}

	public function ticket($codkardex)
	{
		if (isset($_SESSION["phuyu_codusuario"])) {
			$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
			$sucursal = $this->db->query("select sucursal.*,empresa.* from public.sucursales as sucursal inner join public.empresas as empresa on(sucursal.codempresa=empresa.codempresa) where sucursal.codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();

			$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();
			$venta = $this->db->query("select k.fechacomprobante,k.conleyendaamazonia,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.documento,k.cliente,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.nroplaca from kardex.kardex as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=" . $codkardex)->result_array();
			$fechavencimiento = $venta[0]["fechacomprobante"];
			$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
			$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.kardexdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardex=" . $codkardex . " order by kd.item")->result_array();

			$movimiento = $this->db->query("select codmovimiento from caja.movimientos where codkardex=" . $codkardex)->result_array();

			$detallemovimiento = $this->db->query("select importeentregado, vuelto from caja.movimientosdetalle where codtipopago=1 AND codmovimiento=" . $movimiento[0]["codmovimiento"])->result_array();

			$efectivo = 0;
			if (count($detallemovimiento) > 0) {
				$efectivo = 1;
			}

			$vendedor = $this->db->query("select razonsocial,telefono from public.personas where codpersona=" . $venta[0]["codempleado"])->result_array();
			if ($venta[0]["condicionpago"] == 2) {
				$credito = $this->db->query("select fechavencimiento from kardex.creditos where codkardex=" . $codkardex)->result_array();
				$fechavencimiento = $credito[0]["fechavencimiento"];
			} else {
				$credito = [];
			}

			$formato = $this->db->query("select *from caja.comprobantes where codcomprobantetipo=" . $venta[0]["codcomprobantetipo"] . " AND seriecomprobante = '" . $venta[0]["seriecomprobante"] . "' AND codsucursal= " . $_SESSION['phuyu_codsucursal'])->result_array();

			$nombre = $formato[0]["nombrecomercial"];
			if ($nombre == "") {
				if ($empresa[0]["nombrecomercial"] == '') {

					$nombre = $empresa[0]['razonsocial'];
				} else {
					$nombre = $empresa[0]["nombrecomercial"];
				}
			}

			$logo = $formato[0]["logo"];
			if ($logo == "") {
				$direccionlogo = base_url() . "public/img/" . $_SESSION["phuyu_logo"];
			} else {
				$direccionlogo = base_url() . "public/img/empresa/" . $logo;
			}

			if (!file_exists($direccionlogo)) {
				$direccionlogo = '';
			}

			//SLOGAN Y AGRADECIMIENTO

			$slogan = $formato[0]["slogan"];
			if ($slogan == "") {
				$slogan = $parametros[0]["slogan"];
			}
			$publicidad = $formato[0]["publicidad"];
			if ($publicidad == "") {
				$publicidad = $parametros[0]["publicidad"];
			}

			$textoqr = $empresa[0]["razonsocial"] . "|" . $venta[0]["seriecomprobante"] . "|" . $venta[0]["nrocomprobante"] . "|" . number_format($venta[0]["igv"], 2) . "|" . number_format($venta[0]["importe"], 2) . "|" . $venta[0]["fechacomprobante"] . "|" . $venta[0]["documento"];

			$this->load->library('ciqrcode');
			$params['data'] = $textoqr;
			$params['level'] = 'H';
			$params['size'] = 5;
			$params['savename'] = "./sunat/webphuyu/qrcode.png";
			$this->ciqrcode->generate($params);

			$archivo_error = APPPATH . "/logs/qrcode.png-errors.txt";
			unlink($archivo_error);

			$this->load->library("Number");
			$number = new Number();
			$tot_total = (string)(number_format($venta[0]["importe"], 2, ".", ""));
			$imptotaltexto = explode(".", $tot_total);
			$det_imptotaltexto = $number->convertirNumeroEnLetras($imptotaltexto[0]);

			$texto_importe = "SON " . strtoupper($det_imptotaltexto) . " Y " . $imptotaltexto[1] . "/100 SOLES";

			$ticket = "ticket";
			if ($empresa[0]["documento"] == "20209000831") {
				$ticket = "ticket_20209000831";
			}
			if ($empresa[0]["documento"] == "20602165869") {
				$ticket = "ticket_20602165869";
			}
			if ($empresa[0]["documento"] == "20570793986") {
				$ticket = "ticket_20570793986";
			}

			$this->load->view("facturacion/formato/" . $ticket, compact("empresa", "sucursal", "venta", "totales", "detalle", "vendedor", "credito", "texto_importe", "direccionlogo", "formato", "nombre", "slogan", "publicidad", "fechavencimiento", "detallemovimiento", "efectivo"));
		} else {
			$this->load->view("phuyu/404");
		}
	}

	public function a4proforma($codproforma)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$principal = $this->db->query("select *from public.sucursales where principal=1 and estado=1")->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("select k.fechaproforma,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.documento,k.razonsocial,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.codpersona from kardex.proformas as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codproforma=" . $codproforma)->result_array();
		$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codproforma . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codproforma . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codproforma . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codproforma . " and codafectacionigv='21') as gratuito")->result_array();
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion,kd.preciobruto,kd.descuento from kardex.proformasdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codproforma=" . $codproforma . " order by kd.item")->result_array();

		$formato = $this->db->query("select *from caja.comprobantes where codcomprobantetipo=" . $venta[0]["codcomprobantetipo"] . " AND seriecomprobante = '" . $venta[0]["seriecomprobante"] . "' AND codsucursal= " . $_SESSION['phuyu_codsucursal'])->result_array();

		$nombre = $formato[0]["nombrecomercial"];
		if ($nombre == "") {
			if ($empresa[0]["nombrecomercial"] == '') {

				$nombre = $empresa[0]['razonsocial'];
			} else {
				$nombre = $empresa[0]["nombrecomercial"];
			}
		}

		$logo = $formato[0]["logo"];
		if ($logo == "") {
			$direccionlogo = base_url() . "public/img/" . $_SESSION["phuyu_logo"];
		} else {
			$direccionlogo = base_url() . "public/img/empresa/" . $logo;
		}

		if (!file_exists($direccionlogo)) {
			$direccionlogo = '';
		}

		$html = '<table cellpadding="6" width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:30%">';
		$html .= '<h4></h4> <img src="' . $direccionlogo . '" style="height:100px">';
		$html .= '</th>';
		$html .= '<th style="width:35%">';
		$html .= '<h3>' . $nombre . '</h3>';
		$html .= '<p>' . $parametros[0]["slogan"] . '</p>';
		$html .= '</th>';
		$html .= '<th style="width:35%;border:1px solid #000;color:#000;padding:10px !important;">';
		$html .= '<h2>RUC: ' . $empresa[0]["documento"] . '</h2> <h6></h6> <h3>' . $venta[0]["comprobante"] . '</h3>';
		$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%">';
		if (count($principal) > 0) {
			if ($_SESSION["phuyu_codsucursal"] != $principal[0]["codsucursal"]) {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>PRINCIPAL: ' . $principal[0]["direccion"] . '</b> </td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>SUCURSAL: ' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			} else {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr>';
			$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
			//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
			$html .= '</tr>';
		}

		$html .= '<tr>';
		$html .= '<td> <b>' . $sucursal[0]["telefonos"] . '</b> </td>';
		// $html .= '<td> E-MAIL: '.$empresa[0]["email"].' </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="0" width="100%"> <tr> <th style="height:5px;"></th> </tr> </table>';

		$html .= '<table cellpadding="6" width="100%" style="border:1px solid #000;border-radius:50%">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;font-size:10px"> <b>CODIGO CLIENTE</b> </td>';
		$html .= '<td style="width:54%;font-size:10px">: 000' . $venta[0]["codpersona"] . '</td>';
		$html .= '<td style="width:18%;font-size:10px"> <b>CONDICION PAGO</b> </td>';
		if ($venta[0]["condicionpago"] == 1) {
			$html .= '<td style="width:12%;font-size:10px">: CONTADO</td>';
		} else {
			$html .= '<td style="width:12%;font-size:10px">: CREDITO</td>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>RAZON SOCIAL</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["razonsocial"] . '</td>';
		$html .= '<td style="font-size:10px"> <b>GUIA N°</b> </td>';
		$html .= '<td>: </td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>DIRECCION</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '<td style="font-size:10px"> <b>MONEDA</b> </td>';
		$html .= '<td style="font-size:10px">: SOLES</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DNI / RUC</b> </td>';
		$html .= '<td>: ' . $venta[0]["documento"] . '</td>';
		$html .= '<td> <b>FECHA</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechaproforma"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;font-size:11px">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:6%;"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:32%;"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:10%;"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>P.UNITARIO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>DESCUENTO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>IMPORTE</b> </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$c = 1;
		$html .= '<table cellpadding="1" width="100%" style="border:1px solid #000;">';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:6%;"> 0' . $c . ' </td>';
			$html .= '<td style="' . $estilo . 'width:32%;font-size:10px;"> ' . $value["producto"] . ' ' . $value["descripcion"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:10%;font-size:10px;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["preciobruto"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["descuento"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["subtotal"], 2) . ' </td>';
			$html .= '</tr>';
			$c++;
		}
		/* for ($i=0; $i < 7 - count($detalle); $i++) { 
            	$html .= '<tr>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	            $html .= '</tr>';
            } */
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$this->load->library("Number");
		$number = new Number();
		$total_texto = $number->convertirNumeroEnLetras(round($venta[0]["importe"], 2));

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . ' width:65%" rowspan="10" align="center"> <br> ';
		$html .= '<h3> SON: ' . strtoupper($total_texto) . ' Y 00/100 SOLES</h3> <br> ';
		$html .= '</td>';
		$html .= '<td style="' . $estilo . ' width:20%;text-align:right"> <b>OP.GRAVADAS S/</b> </td>';
		$html .= '<td style="' . $estilo . ' width:15%;text-align:right">' . number_format($totales[0]["gravado"] - $venta[0]["igv"], 2) . ' </td>';
		$html .= '</tr>';

		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.INAFECTAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["inafecto"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.EXONERADAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["exonerado"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>O.GRATUITAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["gratuito"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OTROS CARGOS S/</b> </td> <td style="' . $estilo1 . '">0.00</td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OTROS TRIBUTOS S/</b> </td> <td style="' . $estilo1 . '">0.00</td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>DESCUENTO S/</b>  </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["descglobal"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>IGV S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["igv"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>TOTAL S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["importe"], 2) . ' </td> </tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';
		$html .= '<table width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:100%">';
		$html .= '<h6></h6> <h4 style="color:#000;" align="center">' . $parametros[0]["publicidad"] . '</h4>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$this->load->library('Pdf');

		$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR VENTA");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage("A");
		$pdf->writeHTML($html, true, 0, true, 0);
		//$pdf->writeHTML($html, true, false, true, false, '');

		$nombre_archivo = utf8_decode("ImprimirVenta.pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function a5proforma($codkardex)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("select k.fechaproforma,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.documento,k.razonsocial as cliente, k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago from kardex.proformas as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codproforma=" . $codkardex)->result_array();
		$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.proformasdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codproforma=" . $codkardex . " order by kd.item")->result_array();

		//$html = $this->load->view("facturacion/formato/a5",compact("empresa","parametros","venta"),true);

		$formato = $this->db->query("select *from caja.comprobantes where codcomprobantetipo=" . $venta[0]["codcomprobantetipo"] . " AND seriecomprobante = '" . $venta[0]["seriecomprobante"] . "' AND codsucursal= " . $_SESSION['phuyu_codsucursal'])->result_array();

		$nombre = $formato[0]["nombrecomercial"];
		if ($nombre == "") {
			if ($empresa[0]["nombrecomercial"] == '') {

				$nombre = $empresa[0]['razonsocial'];
			} else {
				$nombre = $empresa[0]["nombrecomercial"];
			}
		}

		$logo = $formato[0]["logo"];
		if ($logo == "") {
			$direccionlogo = base_url() . "public/img/" . $_SESSION["phuyu_logo"];
		} else {
			$direccionlogo = base_url() . "public/img/empresa/" . $logo;
		}

		if (!file_exists($direccionlogo)) {
			$direccionlogo = '';
		}

		$vendedor = $this->db->query("select razonsocial from public.personas where codpersona=" . $venta[0]["codempleado"])->result_array();
		$html = '<table width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:20%">';
		$html .= '<img src="' . $direccionlogo . '" style="height:100px;">';
		$html .= '</th>';
		$html .= '<th style="width:40%">';

		$html .= '<h2>' . $nombre . '</h2>';
		$html .= '<h4>' . $parametros[0]["slogan"] . '</h4>';
		$html .= '</th>';
		$html .= '<th style="width:2%;"></th>';
		$html .= '<th style="width:38%;border:1px solid #000;color:#000;">';
		$html .= '<h3>RUC: ' . $empresa[0]["documento"] . '</h3> <h3>' . $venta[0]["comprobante"] . '</h3>';
		$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%">';
		$html .= '<tr>';
		$html .= '<td style="width:100%;"><b>' . $sucursal[0]["direccion"] . '</b> </td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>TELEFONOS: ' . $sucursal[0]["telefonos"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;font-size:9px;">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;"> <b>CLIENTE</b> </td>';
		$html .= '<td style="width:54%;">: ' . $venta[0]["cliente"] . '</td>';
		$html .= '<td style="width:15%"> <b>PAGO AL</b> </td>';
		if ($venta[0]["condicionpago"] == 1) {
			$html .= '<td style="width:15%;">: CONTADO</td>';
		} else {
			$html .= '<td style="width:15%;">: CREDITO: ' . $credito[0]["nrodias"] . ' dias</td>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DIRECCION</b> </td>';
		$html .= '<td>: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '<td> <b>MONEDA</b> </td>';
		$html .= '<td>: SOLES</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DNI / RUC</b> </td>';
		$html .= '<td>: ' . $venta[0]["documento"] . '</td>';
		$html .= '<td> <b>FECHA</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechaproforma"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		$this->load->library("Number");
		$number = new Number();
		$total_texto = $number->convertirNumeroEnLetras(round($venta[0]["importe"], 2));

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #000;font-size:8px;margin-top:-5px">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:7%;"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:40%;"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:15%;"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>P.UNITARIO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>IMPORTE</b> </td>';
		$html .= '</tr>';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:7%;"> 0' . $value["item"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:40%;"> ' . $value["producto"] . ' ' . $value["descripcion"] . '</td>';
			$html .= '<td style="' . $estilo . 'width:15%;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;text-align:right"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right"> ' . number_format($value["preciounitario"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right"> ' . number_format($value["subtotal"], 2) . ' </td>';
			$html .= '</tr>';
		}
		$html .= '</table>';

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #000;font-size:8px">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . ' width:62%" rowspan="7" align="center">';
		$html .= '<h4> SON: ' . strtoupper($total_texto) . ' Y 00/100 SOLES</h4>';
		$html .= '</td>';
		$html .= '<td style="' . $estilo . ' width:25%;text-align:right"> <b>OP.GRAVADAS S/</b> </td>';
		$html .= '<td style="' . $estilo . ' width:13%;text-align:right">' . number_format($totales[0]["gravado"], 2) . ' </td>';
		$html .= '</tr>';

		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.INAFECTAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["inafecto"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.EXONERADAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["exonerado"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>O.GRATUITAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["gratuito"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>DESCUENTO S/</b>  </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["descglobal"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>IGV S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["igv"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>TOTAL S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["importe"], 2) . ' </td> </tr>';
		$html .= '</table>';

		$html .= '<h5 style="color:#000;" align="center">' . $parametros[0]["publicidad"] . '</h5>';

		$this->load->library('Pdf');

		$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR PROFORMA");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage('L', 'A5');
		//$pdf->AddPage('L', 'A5');
		// $pdf->SetLeftMargin(0);
		$pdf->writeHTML($html, true, 0, true, 0);

		$nombre_archivo = utf8_decode($venta[0]["seriecomprobante"] . "|" . $venta[0]["nrocomprobante"] . ".pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function ticketproforma($codkardex)
	{
		if (isset($_SESSION["phuyu_codusuario"])) {
			$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
			$sucursal = $this->db->query("select sucursal.*,empresa.publicidad,empresa.agradecimiento from public.sucursales as sucursal inner join public.empresas as empresa on(sucursal.codempresa=empresa.codempresa) where sucursal.codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();

			$venta = $this->db->query("select k.fechaproforma,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.documento,k.razonsocial, k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago from kardex.proformas as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codproforma=" . $codkardex)->result_array();
			$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.proformasdetalle where codproforma=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
			$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.proformasdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codproforma=" . $codkardex . " order by kd.item")->result_array();

			$vendedor = $this->db->query("select razonsocial,telefono from public.personas where codpersona=" . $venta[0]["codempleado"])->result_array();
			if ($venta[0]["condicionpago"] == 2) {
				$credito = $this->db->query("select fechavencimiento from kardex.creditos where codkardex=" . $codkardex)->result_array();
			} else {
				$credito = [];
			}

			$textoqr = $empresa[0]["razonsocial"] . "|" . $venta[0]["seriecomprobante"] . "|" . $venta[0]["nrocomprobante"] . "|" . number_format($venta[0]["igv"], 2) . "|" . number_format($venta[0]["importe"], 2) . "|" . $venta[0]["fechaproforma"] . "|" . $venta[0]["documento"];

			/*$this->load->library('ciqrcode');
	        $params['data'] = $textoqr; $params['level'] = 'H'; $params['size'] = 5;
	        $params['savename'] = "./sunat/webphuyu/qrcode.png";
	        $this->ciqrcode->generate($params);

	        $archivo_error = APPPATH."/logs/qrcode.png-errors.txt";
        	unlink($archivo_error);*/

			$this->load->library("Number");
			$number = new Number();
			$tot_total = (string)(number_format($venta[0]["importe"], 2, ".", ""));
			$imptotaltexto = explode(".", $tot_total);
			$det_imptotaltexto = $number->convertirNumeroEnLetras($imptotaltexto[0]);

			$texto_importe = "SON " . strtoupper($det_imptotaltexto) . " Y " . $imptotaltexto[1] . "/100 SOLES";

			$ticket = "ticketproforma";
			if ($empresa[0]["documento"] == "20209000831") {
				$ticket = "ticket_20209000831";
			}
			if ($empresa[0]["documento"] == "20602165869") {
				$ticket = "ticket_20602165869";
			}
			if ($empresa[0]["documento"] == "20570793986") {
				$ticket = "ticket_20570793986";
			}

			$this->load->view("facturacion/formato/" . $ticket, compact("empresa", "sucursal", "venta", "totales", "detalle", "vendedor", "credito", "texto_importe"));
		} else {
			$this->load->view("phuyu/404");
		}
	}

	function a4_nota($codkardex)
	{
		if (isset($_SESSION["phuyu_codusuario"])) {
			$empresa = $this->db->query("select documento,razonsocial from public.personas where codpersona=1")->result_array();
			$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();

			$this->load->library("Pdf2");
			$pdf = new Pdf2();
			$pdf->AddPage();

			$pdf->Image('./public/img/' . $_SESSION['phuyu_logo'], 10, 8, 35);
			$pdf->SetFont('Arial', 'B', 12);

			$pdf->Cell(35, 5, "", 0, 0, 'C');
			$pdf->Cell(100, 5, utf8_decode(substr($_SESSION["phuyu_empresa"], 0, 35)), 0, 0, 'L');
			$pdf->Cell(100, 5, utf8_decode($_SESSION["phuyu_sucursal"]));
			$pdf->Ln(8);
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->Cell(35, 5, "", 0, 0, 'C');
			$pdf->Cell(100, 5, utf8_decode("sdkjdsjksd"), 0, 0, 'L');
			$pdf->Cell(100, 5, utf8_decode($_SESSION["phuyu_caja"]));
			$pdf->Ln(5);


			$pdf->SetFont('Arial', 'B', 9);
			$pdf->Cell(0, 7, utf8_decode("KARDEX PRODUCTO DETALLADO - DESDE "), 0, 1, 'C');
			$pdf->SetFillColor(230, 230, 230);
			$pdf->Cell(0, 7, utf8_decode("sdkjjksd | UNIDAD: jdsjhsdjhsd"), 1, 1, 'C', True);
			$pdf->Ln();

			$pdf->SetFont('Arial', 'B', 8);
			$pdf->Cell(25, 5, ' ', 'LTR', 0, 'L', 0);   // empty cell with left,top, and right borders
			$pdf->Cell(50, 5, "DOCUMENTO QUE SE MODIFICA", 1, 0, 'L', 0);
			$pdf->Ln();

			$columnas = array("FECHA", "T.DOC", "N°DOC", "DOC.IDEN", "RAZON SOCIAL", "VALOR VENTA", "IGV", "TOTAL");
			$w = array(15, 15, 20, 20, 20, 23, 10, 15);
			$pdf->pdf_tabla_head($columnas, $w, 8);

			$pdf->SetTitle("phuyu Peru - Nota de Credito");
			$pdf->Output();
		}
	}

	function descargarxml($nombrexml)
	{
		if (!file_exists("sunat/webphuyu/" . $nombrexml . "/" . $nombrexml . ".xml")) {
			$codkardex = $_GET["codkardex"];
			$info = $this->db->query("select *from kardex.kardex where codkardex=" . $codkardex)->result_array();
			$comprobantetipo = $this->db->query("select *from caja.comprobantetipos where codcomprobantetipo=" . $info[0]["codcomprobantetipo"])->result_array();
			$estado = $this->Facturacion_model->phuyu_crearXML($comprobantetipo[0]["oficial"], $codkardex);

			$nombrexml = $estado["archivo_phuyu"];
		}
		header("Content-disposition: attachment; filename=" . $nombrexml . ".xml");
		header("Content-type: application/xml");
		readfile("sunat/webphuyu/" . $nombrexml . "/" . $nombrexml . ".xml");
	}

	function descargarzip()
	{
		$ruta = $_GET["ruta"];
		$nombre = $_GET["nombre"];
		header("Content-disposition: attachment; filename=" . $nombre . ".zip");
		header("Content-type: application/zip");
		readfile($ruta . ".zip");
	}

	public function a4pedido($codkardex)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$principal = $this->db->query("select *from public.sucursales where principal=1 and estado=1")->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("select k.codpedido,k.fechapedido,ct.descripcion as comprobante, k.codcomprobantetipo, v.razonsocial as vendedor,pu.razonsocial as usuario ,
k.seriecomprobante,k.nrocomprobante, p.documento,k.cliente,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.descripcion, 
k.codpersona from kardex.pedidos as k inner join public.personas as p on(k.codpersona=p.codpersona)
			inner join public.personas as v on(k.codempleado=v.codpersona)
			inner join seguridad.usuarios as u on(k.codusuario=u.codusuario)
			inner join public.personas as pu on(u.codempleado=pu.codpersona)
		    inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codpedido=" . $codkardex)->result_array();

		$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.pedidosdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codpedido=" . $codkardex . " order by kd.item")->result_array();

		if ($empresa[0]["nombrecomercial"] == '') {
			$nombre = $empresa[0]['razonsocial'];
		} else {
			$nombre = $empresa[0]["nombrecomercial"];
		}

		$formato = $this->db->query("select *from caja.comprobantes where codcomprobantetipo=" . $venta[0]["codcomprobantetipo"] . " AND seriecomprobante = '" . $venta[0]["seriecomprobante"] . "' AND codsucursal= " . $_SESSION['phuyu_codsucursal'])->result_array();

		$logo = "empresa/default.png";
		if ($formato[0]["logo"] != "") {
			$logo = "empresa/" . $formato[0]["logo"];
		}

		$html = '<table cellpadding="6" width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:30%">';
		$html .= '<h4></h4> <img src="' . base_url() . 'public/img/' . $logo . '" style="height:100px">';
		$html .= '</th>';
		$html .= '<th style="width:35%">';
		$html .= '<h2>' . $nombre . '</h2>';
		$html .= '<p>' . $parametros[0]["slogan"] . '</p>';
		$html .= '<p>' . $parametros[0]["publicidad"] . '</p>';
		$html .= '</th>';
		$html .= '<th style="width:35%;border:1px solid #000;color:#000;padding:10px !important;">';
		$html .= '<h2>RUC: ' . $empresa[0]["documento"] . '</h2> <h6></h6> <h3>' . $venta[0]["comprobante"] . '</h3>';
		$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%">';
		if (count($principal) > 0) {
			if ($_SESSION["phuyu_codsucursal"] != $principal[0]["codsucursal"]) {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>PRINCIPAL: ' . $principal[0]["direccion"] . '</b> </td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>SUCURSAL: ' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			} else {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr>';
			$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
			//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
			$html .= '</tr>';
		}

		$html .= '<tr>';
		$html .= '<td> <b>' . $sucursal[0]["telefonos"] . '</b> </td>';
		// $html .= '<td> E-MAIL: '.$empresa[0]["email"].' </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="0" width="100%"> <tr> <th style="height:5px;"></th> </tr> </table>';

		$html .= '<table cellpadding="6" width="100%" style="border:1px solid #000;border-radius:50%">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;font-size:10px"> <b>CODIGO CLIENTE</b> </td>';
		$html .= '<td style="width:50%;font-size:10px">: 000' . $venta[0]["codpersona"] . '</td>';
		$html .= '<td style="width:16%;font-size:10px"> <b>CONDICION PAGO</b> </td>';
		if ($venta[0]["condicionpago"] == 1) {
			$html .= '<td style="width:18%;font-size:10px">: CONTADO</td>';
		} else {
			$html .= '<td style="width:18%;font-size:10px">: CREDITO</td>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>RAZON SOCIAL</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["cliente"] . '</td>';
		$html .= '<td style="font-size:10px"> <b>GUIA N°</b> </td>';
		$html .= '<td>: </td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>DIRECCION</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '<td style="font-size:10px"> <b>MONEDA</b> </td>';
		$html .= '<td style="font-size:10px">: SOLES</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DNI / RUC</b> </td>';
		$html .= '<td>: ' . $venta[0]["documento"] . '</td>';
		$html .= '<td> <b>FECHA</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechapedido"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>VENDEDOR</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["vendedor"] . '</td>';
		$html .= '<td style="font-size:10px"> <b>USUARIO</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["usuario"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>GLOSA</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["descripcion"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:6%;"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:40%;"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:15%;"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>P.UNITARIO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>IMPORTE</b> </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$c = 1;
		$html .= '<table cellpadding="1" width="100%" style="border:1px solid #000;">';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:6%;"> 0' . $c . ' </td>';
			$html .= '<td style="' . $estilo . 'width:40%;font-size:10px;"> ' . $value["producto"] . ' ' . $value["descripcion"] . '</td>';
			$html .= '<td style="' . $estilo . 'width:15%;font-size:10px;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["preciounitario"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["subtotal"], 2) . ' </td>';
			$html .= '</tr>';
			$c++;
		}
		/* for ($i=0; $i < 7 - count($detalle); $i++) { 
            	$html .= '<tr>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	            $html .= '</tr>';
            } */
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$this->load->library("Number");
		$number = new Number();
		$total_texto = $number->convertirNumeroEnLetras(round($venta[0]["importe"], 2));

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . ' width:65%" rowspan="4" align="center"> <br> ';
		$html .= '<h3> SON: ' . strtoupper($total_texto) . ' Y 00/100 SOLES</h3> <br> ';
		$html .= '<p>' . $parametros[0]["agradecimiento"] . '</p>';
		$html .= '</td>';
		$html .= '<td style="' . $estilo . ' width:20%;text-align:right"> <b>SUBTOTAL S/</b> </td>';
		$html .= '<td style="' . $estilo . ' width:15%;text-align:right">' . number_format($venta[0]["valorventa"], 2) . ' </td>';
		$html .= '</tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>DESCUENTO S/</b>  </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["descglobal"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>IGV S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["igv"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>TOTAL S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["importe"], 2) . ' </td> </tr>';
		$html .= '</table>';

		$this->load->library('Pdf');

		$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR PEDIDO");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage("A");
		$pdf->writeHTML($html, true, 0, true, 0);
		//$pdf->writeHTML($html, true, false, true, false, '');

		$nombre_archivo = utf8_decode($venta[0]["comprobante"] . ' ' . $venta[0]["seriecomprobante"] . '-' . $venta[0]["nrocomprobante"] . ".pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function a5pedido($codkardex)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("select k.codpedido,k.fechapedido,ct.descripcion as comprobante, k.codcomprobantetipo, v.razonsocial as vendedor,pu.razonsocial as usuario ,
k.seriecomprobante,k.nrocomprobante, p.documento,k.cliente,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,
k.codpersona,k.descripcion from kardex.pedidos as k inner join public.personas as p on(k.codpersona=p.codpersona)
			inner join public.personas as v on(k.codempleado=v.codpersona)
			inner join seguridad.usuarios as u on(k.codusuario=u.codusuario)
			inner join public.personas as pu on(u.codempleado=pu.codpersona)
		    inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codpedido=" . $codkardex)->result_array();

		$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.pedidosdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codpedido=" . $codkardex . " order by kd.item")->result_array();

		//$html = $this->load->view("facturacion/formato/a5",compact("empresa","parametros","venta"),true);

		if ($empresa[0]["nombrecomercial"] == '') {
			$nombre = $empresa[0]['razonsocial'];
		} else {
			$nombre = $empresa[0]["nombrecomercial"];
		}

		$vendedor = $this->db->query("select razonsocial from public.personas where codpersona=" . $venta[0]["codempleado"])->result_array();
		$html = '<table width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:20%">';
		$html .= '<img src="' . base_url() . 'public/img/' . $_SESSION['phuyu_logo'] . '" style="height:100px;">';
		$html .= '</th>';
		$html .= '<th style="width:40%">';
		$html .= '<h2>' . $nombre . '</h2>';
		$html .= '<h4>' . $parametros[0]["slogan"] . '</h4>';
		$html .= '</th>';
		$html .= '<th style="width:2%;"></th>';
		$html .= '<th style="width:38%;border:1px solid #000;color:#000;">';
		$html .= '<h3>RUC: ' . $empresa[0]["documento"] . '</h3> <h3>' . $venta[0]["comprobante"] . '</h3>';
		$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%">';
		$html .= '<tr>';
		$html .= '<td style="width:100%;"><b>' . $sucursal[0]["direccion"] . '</b> </td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>TELF: ' . $sucursal[0]["telefonos"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;font-size:9px;">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;"> <b>CLIENTE</b> </td>';
		$html .= '<td style="width:54%;">: ' . $venta[0]["cliente"] . '</td>';
		$html .= '<td style="width:15%"> <b>PAGO AL</b> </td>';
		if ($venta[0]["condicionpago"] == 1) {
			$html .= '<td style="width:15%;">: CONTADO</td>';
		} else {
			$html .= '<td style="width:15%;">: CREDITO</td>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DIRECCION</b> </td>';
		$html .= '<td>: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '<td> <b>MONEDA</b> </td>';
		$html .= '<td>: SOLES</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DNI / RUC</b> </td>';
		$html .= '<td>: ' . $venta[0]["documento"] . '</td>';
		$html .= '<td> <b>FECHA</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechapedido"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>VENDEDOR</b> </td>';
		$html .= '<td>: ' . $venta[0]["vendedor"] . '</td>';
		$html .= '<td> <b>USUARIO</b> </td>';
		$html .= '<td>: ' . $venta[0]["usuario"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>GLOSA</b> </td>';
		$html .= '<td>: ' . $venta[0]["descripcion"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		$this->load->library("Number");
		$number = new Number();
		$total_texto = $number->convertirNumeroEnLetras(round($venta[0]["importe"], 2));

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #000;font-size:8px;margin-top:-5px">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:7%;"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:40%;"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:15%;"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>P.UNITARIO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>IMPORTE</b> </td>';
		$html .= '</tr>';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:7%;"> 0' . $value["item"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:40%;"> ' . $value["producto"] . ' ' . $value["descripcion"] . '</td>';
			$html .= '<td style="' . $estilo . 'width:15%;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;text-align:right"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right"> ' . number_format($value["preciounitario"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right"> ' . number_format($value["subtotal"], 2) . ' </td>';
			$html .= '</tr>';
		}
		$html .= '</table>';

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #000;font-size:8px">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . ' width:62%" rowspan="5" align="center">';
		$html .= '<h4> SON: ' . strtoupper($total_texto) . ' Y 00/100 SOLES</h4>';
		$html .= '</td>';
		$html .= '<td style="' . $estilo . ' width:25%;text-align:right"> <b>SUBTOTAL S/</b> </td>';
		$html .= '<td style="' . $estilo . ' width:13%;text-align:right">' . number_format($venta[0]["valorventa"], 2) . ' </td>';
		$html .= '</tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>DESCUENTO S/</b>  </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["descglobal"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>IGV S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["igv"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>TOTAL S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["importe"], 2) . ' </td> </tr>';
		$html .= '</table>';

		$html .= '<h5 style="color:#000;" align="center">' . $parametros[0]["publicidad"] . '</h5>';

		$this->load->library('Pdf');

		$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR PEDIDO");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage('L', 'A5');
		//$pdf->AddPage('L', 'A5');
		// $pdf->SetLeftMargin(0);
		$pdf->writeHTML($html, true, 0, true, 0);

		$nombre_archivo = utf8_decode($venta[0]["comprobante"] . ' ' . $venta[0]["seriecomprobante"] . '-' . $venta[0]["nrocomprobante"] . ".pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function ticketpedido($codkardex)
	{
		if (isset($_SESSION["phuyu_codusuario"])) {
			$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
			$sucursal = $this->db->query("select sucursal.*,empresa.publicidad,empresa.agradecimiento from public.sucursales as sucursal inner join public.empresas as empresa on(sucursal.codempresa=empresa.codempresa) where sucursal.codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();

			$venta = $this->db->query("select k.codpedido,k.fechapedido,ct.descripcion as comprobante, k.codcomprobantetipo, v.razonsocial as vendedor,pu.razonsocial as usuario ,
k.seriecomprobante,k.nrocomprobante, p.documento,k.cliente,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,
k.codpersona from kardex.pedidos as k inner join public.personas as p on(k.codpersona=p.codpersona)
			inner join public.personas as v on(k.codempleado=v.codpersona)
			inner join seguridad.usuarios as u on(k.codusuario=u.codusuario)
			inner join public.personas as pu on(u.codempleado=pu.codpersona)
		    inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codpedido=" . $codkardex)->result_array();

			$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.pedidosdetalle where codpedido=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
			$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.pedidosdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codpedido=" . $codkardex . " order by kd.item")->result_array();

			$vendedor = $this->db->query("select razonsocial,telefono from public.personas where codpersona=" . $venta[0]["codempleado"])->result_array();

			$this->load->library("Number");
			$number = new Number();
			$tot_total = (string)(number_format($venta[0]["importe"], 2, ".", ""));
			$imptotaltexto = explode(".", $tot_total);
			$det_imptotaltexto = $number->convertirNumeroEnLetras($imptotaltexto[0]);

			$texto_importe = "SON " . strtoupper($det_imptotaltexto) . " Y " . $imptotaltexto[1] . "/100 SOLES";

			$ticket = "ticketpedido";

			$this->load->view("facturacion/formato/" . $ticket, compact("empresa", "sucursal", "venta", "totales", "detalle", "vendedor", "texto_importe"));
		} else {
			$this->load->view("phuyu/404");
		}
	}

	public function a4notacredito($codkardex)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$principal = $this->db->query("select *from public.sucursales where principal=1 and estado=1")->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("select k.codkardex,k.fechacomprobante,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante,( CASE WHEN  codcomprobantetipo_ref = 10 THEN 'FACT' ELSE 'BOL' END) AS comprobante_ref,k.seriecomprobante_ref,k.nrocomprobante_ref, mn.descripcion as motivo, p.documento,k.cliente,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.nroplaca, k.codpersona, k.icbper from kardex.kardex as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) inner join kardex.motivonotas as mn on(k.codmotivonota=mn.codmotivonota) where k.codkardex=" . $codkardex)->result_array();
		if ($venta[0]["condicionpago"] == 2) {
			$credito = $this->db->query("SELECT *FROM kardex.creditos WHERE codkardex=" . $venta[0]["codkardex"])->result_array();
		}
		$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.kardexdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardex=" . $codkardex . " order by kd.item")->result_array();

		if ($empresa[0]["nombrecomercial"] == '') {
			$nombre = $empresa[0]['razonsocial'];
		} else {
			$nombre = $empresa[0]["nombrecomercial"];
		}

		$html = '<table cellpadding="6" width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:30%">';
		$html .= '<h4></h4> <img src="' . base_url() . 'public/img/' . $_SESSION['phuyu_logo'] . '" style="height:100px">';
		$html .= '</th>';
		$html .= '<th style="width:35%">';
		$html .= '<h3>' . $nombre . '</h3>';
		$html .= '<p>' . $parametros[0]["slogan"] . '</p>';
		$html .= '<p>' . $parametros[0]["publicidad"] . '</p>';
		$html .= '</th>';
		$html .= '<th style="width:35%;border:1px solid #000;color:#000;padding:10px !important;">';
		$html .= '<h2>RUC: ' . $empresa[0]["documento"] . '</h2> <h6></h6> <h3>' . $venta[0]["comprobante"] . '</h3>';
		$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%">';
		if (count($principal) > 0) {
			if ($_SESSION["phuyu_codsucursal"] != $principal[0]["codsucursal"]) {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>PRINCIPAL: ' . $principal[0]["direccion"] . '</b> </td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>SUCURSAL: ' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			} else {
				$html .= '<tr>';
				$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
				//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr>';
			$html .= '<td style="width:100%;"> <b>' . $sucursal[0]["direccion"] . '</b> </td>';
			//$html .= '<td style="width:40%;"> <b> SOPORTE TECNICO: 997644742 </b> </td>';
			$html .= '</tr>';
		}

		$html .= '<tr>';
		$html .= '<td> <b>' . $sucursal[0]["telefonos"] . '</b> </td>';
		// $html .= '<td> E-MAIL: '.$empresa[0]["email"].' </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="0" width="100%"> <tr> <th style="height:5px;"></th> </tr> </table>';

		$html .= '<table cellpadding="3" width="100%" style="border:1px solid #000;border-radius:50%">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;font-size:10px"> <b>CODIGO CLIENTE</b> </td>';
		$html .= '<td style="width:44%;font-size:10px">: 000' . $venta[0]["codpersona"] . '</td>';
		$html .= '<td style="width:18%;font-size:10px"> <b>CONDICION PAGO</b> </td>';
		if ($venta[0]["condicionpago"] == 1) {
			$html .= '<td style="width:22%;font-size:10px">: CONTADO</td>';
		} else {
			$html .= '<td style="width:22%;font-size:10px">: CREDITO: ' . $credito[0]["nrodias"] . ' dias</td>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>RAZON SOCIAL</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["cliente"] . '</td>';
		$html .= '<td style="font-size:10px"> <b>GUIA N°</b> </td>';
		$html .= '<td>: </td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td style="font-size:10px"> <b>DIRECCION</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '<td style="font-size:10px"> <b>MONEDA</b> </td>';
		$html .= '<td style="font-size:10px">: SOLES</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DNI / RUC</b> </td>';
		$html .= '<td>: ' . $venta[0]["documento"] . '</td>';
		$html .= '<td> <b>FECHA</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechacomprobante"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>REFERENCIA</b> </td>';
		$html .= '<td>: ' . $venta[0]["comprobante_ref"] . ': ' . $venta[0]["seriecomprobante_ref"] . '-' . $venta[0]["nrocomprobante_ref"] . '</td>';
		$html .= '<td> <b>MOTIVO</b> </td>';
		$html .= '<td style="font-size:10px">: ' . $venta[0]["motivo"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:6%;"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:40%;"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:15%;"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>P.UNITARIO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>IMPORTE</b> </td>';
		$html .= '</tr>';
		$html .= '</table>';
		$c = 1;
		$html .= '<table cellpadding="1" width="100%" style="border:1px solid #000;">';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:6%;"> 0' . $c . ' </td>';
			$html .= '<td style="' . $estilo . 'width:40%;font-size:10px;"> ' . $value["producto"] . ' ' . $value["descripcion"] . '</td>';
			$html .= '<td style="' . $estilo . 'width:15%;font-size:10px;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["preciounitario"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right;font-size:10px;"> ' . number_format($value["subtotal"], 2) . ' </td>';
			$html .= '</tr>';
			$c++;
		}
		/* for ($i=0; $i < 7 - count($detalle); $i++) { 
            	$html .= '<tr>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	                $html .= '<td style="'.$estilo.'"> </td>';
	            $html .= '</tr>';
            } */
		$html .= '</table>';
		$html .= '<table cellpadding="1" width="100%"> <tr> <th style="height:10px;"></th> </tr> </table>';

		$this->load->library("Number");
		$number = new Number();
		$total_texto = $number->convertirNumeroEnLetras(round($venta[0]["importe"], 2));

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . ' width:65%" rowspan="8" align="center"> ';
		$html .= '<h4> SON: ' . strtoupper($total_texto) . ' Y 00/100 SOLES</h4> <br> ';
		$html .= '<img src="' . base_url() . 'sunat/webphuyu/qrcode.png" style="height:100px">';
		$html .= '</td>';
		$html .= '<td style="' . $estilo . ' width:20%;text-align:right"> <b>OP.GRAVADAS S/</b> </td>';
		$html .= '<td style="' . $estilo . ' width:15%;text-align:right">' . number_format($totales[0]["gravado"] - $venta[0]["igv"], 2) . ' </td>';
		$html .= '</tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.INAFECTAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["inafecto"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.EXONERADAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["exonerado"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>O.GRATUITAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["gratuito"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>DESCUENTO S/</b>  </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["descglobal"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>IGV S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["igv"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>ICBPER S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["icbper"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>TOTAL S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["importe"], 2) . ' </td> </tr>';
		$html .= '</table>';

		$html .= '<p>CONSULTE SU COMPROBANTE EN: ' . $parametros[0]["urlconsultacomprobantes"] . '</p>';
		$html .= '<p>' . $parametros[0]["agradecimiento"] . '</p>';

		$textoqr = $empresa[0]["razonsocial"] . "|" . $venta[0]["seriecomprobante"] . "|" . $venta[0]["nrocomprobante"] . "|" . number_format($venta[0]["igv"], 2) . "|" . number_format($venta[0]["importe"], 2) . "|" . $venta[0]["fechacomprobante"] . "|" . $venta[0]["documento"];

		$this->load->library('ciqrcode');
		$params['data'] = $textoqr;
		$params['level'] = 'H';
		$params['size'] = 5;
		$params['savename'] = "./sunat/webphuyu/qrcode.png";
		$this->ciqrcode->generate($params);

		$archivo_error = APPPATH . "/logs/qrcode.png-errors.txt";
		unlink($archivo_error);

		$this->load->library('Pdf');

		$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR VENTA");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage("A");
		$pdf->writeHTML($html, true, 0, true, 0);
		//$pdf->writeHTML($html, true, false, true, false, '');

		$nombre_archivo = utf8_decode($venta[0]["comprobante"] . ' ' . $venta[0]["seriecomprobante"] . '-' . $venta[0]["nrocomprobante"] . ".pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function a5notacredito($codkardex)
	{
		$estilo = "border-left:1px solid #000; border-right:1px solid #000;";
		$estilo1 = "border-top:1px solid #000; border-left:1px solid #000; border-right:1px solid #000;text-align:right";

		$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
		$sucursal = $this->db->query("select *from public.sucursales where codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->result_array();

		$venta = $this->db->query("select k.codkardex,k.fechacomprobante,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante,( CASE WHEN  codcomprobantetipo_ref = 10 THEN 'FACT' ELSE 'BOL' END) AS comprobante_ref,k.seriecomprobante_ref,k.nrocomprobante_ref, mn.descripcion as motivo, p.documento,k.cliente,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.nroplaca, k.codpersona, k.icbper from kardex.kardex as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) inner join kardex.motivonotas as mn on(k.codmotivonota=mn.codmotivonota) where k.codkardex=" . $codkardex)->result_array();
		if ($venta[0]["condicionpago"] == 2) {
			$credito = $this->db->query("SELECT *FROM kardex.creditos WHERE codkardex=" . $venta[0]["codkardex"])->result_array();
		}
		$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
		$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.kardexdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardex=" . $codkardex . " order by kd.item")->result_array();

		//$html = $this->load->view("facturacion/formato/a5",compact("empresa","parametros","venta"),true);

		if ($empresa[0]["nombrecomercial"] == '') {
			$nombre = $empresa[0]['razonsocial'];
		} else {
			$nombre = $empresa[0]["nombrecomercial"];
		}

		$vendedor = $this->db->query("select razonsocial from public.personas where codpersona=" . $venta[0]["codempleado"])->result_array();
		$html = '<table width="100%" align="center">';
		$html .= '<tr>';
		$html .= '<th style="width:20%">';
		$html .= '<img src="' . base_url() . 'public/img/' . $_SESSION['phuyu_logo'] . '" style="height:80px;">';
		$html .= '</th>';
		$html .= '<th style="width:40%">';
		$html .= '<h2>' . $nombre . '</h2>';
		$html .= '<h4>' . $parametros[0]["slogan"] . '</h4>';
		$html .= '</th>';
		$html .= '<th style="width:2%;"></th>';
		$html .= '<th style="width:38%;border:1px solid #000;color:#000;">';
		$html .= '<h3>RUC: ' . $empresa[0]["documento"] . '</h3> <h3>' . $venta[0]["comprobante"] . '</h3>';
		$html .= '<h3>' . $venta[0]["seriecomprobante"] . ' - ' . $venta[0]["nrocomprobante"] . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%">';
		$html .= '<tr>';
		$html .= '<td style="width:100%;"><b>' . $sucursal[0]["direccion"] . '</b> </td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>TELF: ' . $sucursal[0]["telefonos"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '<table cellpadding="2" width="100%" style="border:1px solid #000;font-size:9px;">';
		$html .= '<tr>';
		$html .= '<td style="width:16%;"> <b>CLIENTE</b> </td>';
		$html .= '<td style="width:54%;">: ' . $venta[0]["cliente"] . '</td>';
		$html .= '<td style="width:15%"> <b>PAGO AL</b> </td>';
		if ($venta[0]["condicionpago"] == 1) {
			$html .= '<td style="width:15%;">: CONTADO</td>';
		} else {
			$html .= '<td style="width:15%;">: CREDITO: ' . $credito[0]["nrodias"] . ' dias</td>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DIRECCION</b> </td>';
		$html .= '<td>: ' . $venta[0]["direccion"] . ' </td>';
		$html .= '<td> <b>MONEDA</b> </td>';
		$html .= '<td>: SOLES</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>DNI / RUC</b> </td>';
		$html .= '<td>: ' . $venta[0]["documento"] . '</td>';
		$html .= '<td> <b>FECHA</b> </td>';
		$html .= '<td>: ' . $venta[0]["fechacomprobante"] . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td> <b>REFERENCIA</b> </td>';
		$html .= '<td>: ' . $venta[0]["comprobante_ref"] . ': ' . $venta[0]["seriecomprobante_ref"] . '-' . $venta[0]["nrocomprobante_ref"] . '</td>';
		$html .= '<td> <b>MOTIVO</b> </td>';
		$html .= '<td>: ' . $venta[0]["motivo"] . '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		$this->load->library("Number");
		$number = new Number();
		$total_texto = $number->convertirNumeroEnLetras(round($venta[0]["importe"], 2));

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #000;font-size:8px;margin-top:-5px">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . 'width:7%;"> <b>ITEM</b> </td>';
		$html .= '<td style="' . $estilo . 'width:40%;"> <b>DESCRIPCION</b> </td>';
		$html .= '<td style="' . $estilo . 'width:15%;"> <b>UND MEDIDA</b> </td>';
		$html .= '<td style="' . $estilo . 'width:12%;"> <b>CANTIDAD</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>P.UNITARIO</b> </td>';
		$html .= '<td style="' . $estilo . 'width:13%;"> <b>IMPORTE</b> </td>';
		$html .= '</tr>';
		foreach ($detalle as $value) {
			$html .= '<tr>';
			$html .= '<td style="' . $estilo . 'width:7%;"> 0' . $value["item"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:40%;"> ' . $value["producto"] . ' ' . $value["descripcion"] . '</td>';
			$html .= '<td style="' . $estilo . 'width:15%;"> ' . $value["unidad"] . ' </td>';
			$html .= '<td style="' . $estilo . 'width:12%;text-align:right"> ' . number_format($value["cantidad"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right"> ' . number_format($value["preciounitario"], 2) . ' </td>';
			$html .= '<td style="' . $estilo . 'width:13%;text-align:right"> ' . number_format($value["subtotal"], 2) . ' </td>';
			$html .= '</tr>';
		}
		$html .= '</table>';

		$textoqr = $empresa[0]["razonsocial"] . "|" . $venta[0]["seriecomprobante"] . "|" . $venta[0]["nrocomprobante"] . "|" . number_format($venta[0]["igv"], 2) . "|" . number_format($venta[0]["importe"], 2) . "|" . $venta[0]["fechacomprobante"] . "|" . $venta[0]["documento"];

		$this->load->library('ciqrcode');
		$params['data'] = $textoqr;
		$params['level'] = 'H';
		$params['size'] = 5;
		$params['savename'] = "./sunat/webphuyu/qrcode.png";
		$this->ciqrcode->generate($params);

		$archivo_error = APPPATH . "/logs/qrcode.png-errors.txt";
		unlink($archivo_error);

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #000;font-size:8px">';
		$html .= '<tr>';
		$html .= '<td style="' . $estilo . ' width:62%" rowspan="7" align="center">';
		$html .= '<h4> SON: ' . strtoupper($total_texto) . ' Y 00/100 SOLES</h4>';
		$html .= '<img src="' . base_url() . 'sunat/webphuyu/qrcode.png" style="height:80px">';
		$html .= '</td>';
		$html .= '<td style="' . $estilo . ' width:25%;text-align:right"> <b>OP.GRAVADAS S/</b> </td>';
		$html .= '<td style="' . $estilo . ' width:13%;text-align:right">' . number_format($totales[0]["gravado"], 2) . ' </td>';
		$html .= '</tr>';

		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.INAFECTAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["inafecto"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>OP.EXONERADAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["exonerado"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>O.GRATUITAS S/</b> </td> <td style="' . $estilo1 . '">' . number_format($totales[0]["gratuito"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>DESCUENTO S/</b>  </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["descglobal"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>IGV S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["igv"], 2) . ' </td> </tr>';
		$html .= '<tr> <td style="' . $estilo1 . '"> <b>TOTAL S/</b> </td> <td style="' . $estilo1 . '">' . number_format($venta[0]["importe"], 2) . ' </td> </tr>';
		$html .= '</table>';

		$html .= '<h5 style="color:#000;" align="center">' . $parametros[0]["publicidad"] . '</h5>';

		$this->load->library('Pdf');

		$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("WEB phuyu");
		$pdf->SetTitle("WEB phuyu | IMPRIMIR VENTA");
		$pdf->SetSubject("WEB phuyu");

		$pdf->setPrintHeader(false);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage('L', 'A5');
		//$pdf->AddPage('L', 'A5');
		// $pdf->SetLeftMargin(0);
		$pdf->writeHTML($html, true, 0, true, 0);

		$nombre_archivo = utf8_decode("ImprimirVenta.pdf");
		$pdf->Output($nombre_archivo, 'I');
	}

	public function ticketnotacredito($codkardex)
	{
		if (isset($_SESSION["phuyu_codusuario"])) {
			$empresa = $this->db->query("select documento,razonsocial,nombrecomercial from public.personas where codpersona=1")->result_array();
			$sucursal = $this->db->query("select sucursal.*,empresa.publicidad,empresa.agradecimiento from public.sucursales as sucursal inner join public.empresas as empresa on(sucursal.codempresa=empresa.codempresa) where sucursal.codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();

			$venta = $this->db->query("select k.fechacomprobante,ct.descripcion as comprobante, k.codcomprobantetipo, k.seriecomprobante,k.nrocomprobante, p.documento,k.cliente,k.direccion,k.valorventa,k.descglobal,k.igv,k.importe, k.codempleado,k.condicionpago,k.nroplaca from kardex.kardex as k inner join public.personas as p on(k.codpersona=p.codpersona) inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=" . $codkardex)->result_array();
			$totales = $this->db->query("select (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='30') as inafecto, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=" . $codkardex . " and codafectacionigv='21') as gratuito")->result_array();
			$detalle = $this->db->query("select kd.item,kd.cantidad,p.descripcion as producto,u.descripcion as unidad,kd.preciounitario,kd.subtotal, kd.descripcion from kardex.kardexdetalle as kd inner join almacen.productos as p on(p.codproducto=kd.codproducto) inner join almacen.unidades as u on(u.codunidad=kd.codunidad) where kd.codkardex=" . $codkardex . " order by kd.item")->result_array();

			$vendedor = $this->db->query("select razonsocial,telefono from public.personas where codpersona=" . $venta[0]["codempleado"])->result_array();
			if ($venta[0]["condicionpago"] == 2) {
				$credito = $this->db->query("select fechavencimiento from kardex.creditos where codkardex=" . $codkardex)->result_array();
			} else {
				$credito = [];
			}

			$textoqr = $empresa[0]["razonsocial"] . "|" . $venta[0]["seriecomprobante"] . "|" . $venta[0]["nrocomprobante"] . "|" . number_format($venta[0]["igv"], 2) . "|" . number_format($venta[0]["importe"], 2) . "|" . $venta[0]["fechacomprobante"] . "|" . $venta[0]["documento"];

			$this->load->library('ciqrcode');
			$params['data'] = $textoqr;
			$params['level'] = 'H';
			$params['size'] = 5;
			$params['savename'] = "./sunat/webphuyu/qrcode.png";
			$this->ciqrcode->generate($params);

			$archivo_error = APPPATH . "/logs/qrcode.png-errors.txt";
			unlink($archivo_error);

			$this->load->library("Number");
			$number = new Number();
			$tot_total = (string)(number_format($venta[0]["importe"], 2, ".", ""));
			$imptotaltexto = explode(".", $tot_total);
			$det_imptotaltexto = $number->convertirNumeroEnLetras($imptotaltexto[0]);

			$texto_importe = "SON " . strtoupper($det_imptotaltexto) . " Y " . $imptotaltexto[1] . "/100 SOLES";

			$ticket = "ticket";
			if ($empresa[0]["documento"] == "20209000831") {
				$ticket = "ticket_20209000831";
			}
			if ($empresa[0]["documento"] == "20602165869") {
				$ticket = "ticket_20602165869";
			}
			if ($empresa[0]["documento"] == "20570793986") {
				$ticket = "ticket_20570793986";
			}

			$this->load->view("facturacion/formato/" . $ticket, compact("empresa", "sucursal", "venta", "totales", "detalle", "vendedor", "credito", "texto_importe"));
		} else {
			$this->load->view("phuyu/404");
		}
	}
}
