<?php


class Facturacion_model extends CI_Model {

    public function __construct(){
        parent::__construct();
    }

    function phuyu_crearXML($codcomprobante,$codkardex){
        $empresa = $this->db->query("select p.documento,p.razonsocial,p.direccion, e.ubigeo, e.departamento, e.provincia, e.distrito from public.personas as p inner join public.empresas as e on(p.codpersona=e.codpersona) where e.codempresa=1")->result_array();

        $info = $this->db->query("select k.seriecomprobante,k.condicionpago ,k.descripcion,k.nrocomprobante,k.fechacomprobante,k.valorventa,k.porcdescuento, k.descglobal, k.porcigv, k.igv, k.icbper ,k.importe, k.codmotivonota, k.codcomprobantetipo_ref, k.seriecomprobante_ref, k.nrocomprobante_ref, ks.nombre_xml, dt.oficial as coddocumento, p.documento, k.cliente,k.direccion from kardex.kardex as k inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) inner join public.personas as p on(k.codpersona=p.codpersona) inner join public.documentotipos as dt on(p.coddocumentotipo=dt.coddocumentotipo) where k.codkardex=".$codkardex)->result_array();
        $totales = $this->db->query("select (select coalesce(sum(valorventa),0) from kardex.kardexdetalle where codkardex=".$codkardex." and codafectacionigv='10') as gravado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=".$codkardex." and codafectacionigv='20') as exonerado, (select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=".$codkardex." and codafectacionigv='30') as inafecto, (select coalesce(sum(preciorefunitario * cantidad),0) from kardex.kardexdetalle where codkardex=".$codkardex." and codafectacionigv='21') as gratuito")->result_array();
        $detalle = $this->db->query("select kd.*,p.descripcion as producto,u.oficial as unidad from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$codkardex." and kd.estado=1 order by kd.item asc")->result_array();

        $credito = $this->db->query("select *from kardex.creditos WHERE codkardex=".$codkardex)->result_array();
        $cuotas = [];
        if(count($credito) > 0){
            $cuotas = $this->db->query("select *from kardex.cuotas WHERE codcredito=".$credito[0]["codcredito"])->result_array();
        }

        // 0: CREAMOS UNA CARPETA PARA ALMACENAR EL XML DEL COMPROBANTE TEMPORALMENTE //

        //chmod("./sunat/webphuyu", 0777);

        $carpeta_phuyu  = "./sunat/webphuyu/".$info[0]["nombre_xml"];
        //print_r($carpeta_phuyu);exit;
        if (!file_exists($carpeta_phuyu)) {
            mkdir($carpeta_phuyu,0777); chmod($carpeta_phuyu, 0777);
        }

        // 1: CREAMOS EL DOCUMENTO XML //

        $xml = new DomDocument("1.0","ISO-8859-1"); $xml->standalone = false; $xml->preserveWhiteSpace = false;

        if($codcomprobante <> "07" && $codcomprobante <> "08"){
            $Invoice = $xml->createElement("Invoice"); $Invoice = $xml->appendChild($Invoice);
            $Invoice->setAttribute("xmlns","urn:oasis:names:specification:ubl:schema:xsd:Invoice-2");
        }elseif($codcomprobante == "07"){
            $Invoice = $xml->createElement("CreditNote"); $Invoice = $xml->appendChild($Invoice);
            $Invoice->setAttribute("xmlns","urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2");
        }else{
            $Invoice = $xml->createElement("DebitNote"); $Invoice = $xml->appendChild($Invoice);
            $Invoice->setAttribute("xmlns","urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2");
        }

        $Invoice->setAttribute("xmlns:cac", "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
        $Invoice->setAttribute("xmlns:cbc", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
        $Invoice->setAttribute("xmlns:ccts", "urn:un:unece:uncefact:documentation:2");
        $Invoice->setAttribute("xmlns:ds", "http://www.w3.org/2000/09/xmldsig#");
        $Invoice->setAttribute("xmlns:ext", "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
        $Invoice->setAttribute("xmlns:qdt", "urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2");
        $Invoice->setAttribute("xmlns:sac", "urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1");
        $Invoice->setAttribute("xmlns:udt", "urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2");
        $Invoice->setAttribute("xmlns:xsd", "http://www.w3.org/2001/XMLSchema");
        $Invoice->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");

        $UBLExtension = $xml->createElement("ext:UBLExtensions"); $UBLExtension = $Invoice->appendChild($UBLExtension);
            
            // 2: FIRMA ELECTRONICA //

            $ext = $xml->createElement("ext:UBLExtension"); $ext = $UBLExtension->appendChild($ext);
                $contents = $xml->createElement("ext:ExtensionContent"," "); $contents = $ext->appendChild($contents);
            
            // 3: VERSION DEL XML Y DEL COMPROBANTE //

            $cbc = $xml->createElement("cbc:UBLVersionID","2.1"); $cbc = $Invoice->appendChild($cbc);
            $cbc = $xml->createElement("cbc:CustomizationID","2.0"); $cbc = $Invoice->appendChild($cbc);
                $cbc->setAttribute("schemeAgencyName","PE:SUNAT");

            // 4. SERIE Y NRO DEL COMPROBANTE F001-00000001 - FECHA Y HORA DE EMISION //

            $cbc = $xml->createElement("cbc:ID",$info[0]["seriecomprobante"]."-".$info[0]["nrocomprobante"]); $cbc = $Invoice->appendChild($cbc);
            $cbc = $xml->createElement("cbc:IssueDate",$info[0]["fechacomprobante"]); $cbc = $Invoice->appendChild($cbc);

            // 5. CODIGO TIPO DOCUMENTO CATALOGO 01 //

            if($codcomprobante <> "07" && $codcomprobante <> "08"){
                $cbc = $xml->createElement("cbc:InvoiceTypeCode",$codcomprobante); $cbc = $Invoice->appendChild($cbc);
                    $cbc->setAttribute("listAgencyName","PE:SUNAT");
                    $cbc->setAttribute("listID","0101");
                    $cbc->setAttribute("listName","Tipo de Documento");
                    $cbc->setAttribute("name","Tipo de Operacion");
                    $cbc->setAttribute("listURI","urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01");
                    $cbc->setAttribute("listSchemeURI","urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51");
            }

            // 6. LEYENDA DEL MONTO TOTAL - TIPO MONEDA - CANTIDAD DE ITEMS //

            $this->load->library("Number"); $number = new Number();
            $tot_total = (String)(number_format($info[0]["importe"],2,".","")); $imptotaltexto = explode(".", $tot_total);
            $det_imptotaltexto = $number->convertirNumeroEnLetras($imptotaltexto[0]);

            $texto_importe = "SON ".strtoupper($det_imptotaltexto)." Y ".$imptotaltexto[1]."/100 SOLES";

            $cbc =$xml->createElement("cbc:Note",$texto_importe); $cbc = $Invoice->appendChild($cbc);
                $cbc->setAttribute("languageLocaleID","1000");
      
            $cbc = $xml->createElement("cbc:DocumentCurrencyCode","PEN"); $cbc = $Invoice->appendChild($cbc);
                $cbc->setAttribute("listAgencyName","United Nations Economic Commission for Europe");
                $cbc->setAttribute("listID","ISO 4217 Alpha");
                $cbc->setAttribute("listName","Currency");
            
            $cbc = $xml->createElement("cbc:LineCountNumeric",count($detalle)); $cbc = $Invoice->appendChild($cbc);

            //PARA LAS NOTAS DE CREDITOS

            if($codcomprobante == "08" || $codcomprobante == "07"){
                $consulta_motivo = $this->db->query("SELECT oficial FROM kardex.motivonotas WHERE codmotivonota=".$info[0]["codmotivonota"])->result_array();
                $codcomprobantetipo_ofi = $this->db->query("SELECT oficial FROM caja.comprobantetipos WHERE codcomprobantetipo=".$info[0]["codcomprobantetipo_ref"])->result_array();

                $cac_discre = $xml->createElement('cac:DiscrepancyResponse'); $cac_discre = $Invoice->appendChild($cac_discre);
                    //verificar la letra S  
                    $cbc = $xml->createElement('cbc:ReferenceID', $info[0]["seriecomprobante_ref"]."-".trim($info[0]["nrocomprobante_ref"])); $cbc = $cac_discre->appendChild($cbc);
                    $cbc = $xml->createElement('cbc:ResponseCode', $consulta_motivo[0]["oficial"]); $cbc = $cac_discre->appendChild($cbc);
                    //el espacio vacio es para cuando no aya datos
                    $cbc = $xml->createElement('cbc:Description', $info[0]["descripcion"].' '); $cbc = $cac_discre->appendChild($cbc);

                    //
                $cac_billin = $xml->createElement('cac:BillingReference'); $cac_billin = $Invoice->appendChild($cac_billin);
                    $cac = $xml->createElement('cac:InvoiceDocumentReference'); $cac = $cac_billin->appendChild($cac);
                    $cbc = $xml->createElement('cbc:ID', $info[0]["seriecomprobante_ref"]."-".trim($info[0]["nrocomprobante_ref"])); $cbc = $cac->appendChild($cbc);
                    $cbc = $xml->createElement('cbc:DocumentTypeCode', $codcomprobantetipo_ofi[0]["oficial"]); $cbc = $cac->appendChild($cbc);
            }

            // 7: DATOS DE LA EMPRESA - REFERENCIA FIRMA DIGITAL //

            $cac_signature = $xml->createElement("cac:Signature"); $cac_signature = $Invoice->appendChild($cac_signature);
                $cbc = $xml->createElement("cbc:ID","SignSUNAT"); $cbc = $cac_signature->appendChild($cbc);

                $cac_signatureparty = $xml->createElement("cac:SignatoryParty"); 
                $cac_signatureparty = $cac_signature->appendChild($cac_signatureparty);

                    $cac_partyidentification = $xml->createElement("cac:PartyIdentification"); 
                    $cac_partyidentification = $cac_signatureparty->appendChild($cac_partyidentification);
                        $cbc = $xml->createElement("cbc:ID",$empresa[0]["documento"]); $cbc = $cac_partyidentification->appendChild($cbc);
                    $cac_partyname = $xml->createElement("cac:PartyName"); 
                    $cac_partyname = $cac_signatureparty->appendChild($cac_partyname);
                        $cbc = $xml->createElement("cbc:Name",htmlspecialchars($empresa[0]["razonsocial"]) ); 
                        $cbc = $cac_partyname->appendChild($cbc);

                $cac_digital = $xml->createElement("cac:DigitalSignatureAttachment"); 
                $cac_digital=$cac_signature->appendChild($cac_digital);
                    $cac_externalreference = $xml->createElement("cac:ExternalReference"); 
                    $cac_externalreference = $cac_digital->appendChild($cac_externalreference);
                        $cbc = $xml->createElement("cbc:URI","#SignSUNAT"); $cbc = $cac_externalreference->appendChild($cbc);

            // 8: DATOS DE LA EMPRESA QUE EMITE //

            $cac_supplierparty = $xml->createElement("cac:AccountingSupplierParty"); 
            $cac_supplierparty = $Invoice->appendChild($cac_supplierparty);

                $cac_party = $xml->createElement("cac:Party"); $cac_party = $cac_supplierparty->appendChild($cac_party);

                    $cac_partyidentification = $xml->createElement("cac:PartyIdentification"); 
                    $cac_partyidentification = $cac_party->appendChild($cac_partyidentification);
                        $cbc = $xml->createElement("cbc:ID",$empresa[0]["documento"]); $cbc = $cac_partyidentification->appendChild($cbc);
                            $cbc->setAttribute("schemeID","6");
                            $cbc->setAttribute("schemeName","Documento de Identidad");
                            $cbc->setAttribute("schemeAgencyName","PE:SUNAT");
                            $cbc->setAttribute("schemeURI","urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06");

                    $cac_legal = $xml->createElement("cac:PartyLegalEntity"); $cac_legal = $cac_party->appendChild($cac_legal);
                        $cbc = $xml->createElement("cbc:RegistrationName",htmlspecialchars($empresa[0]["razonsocial"]) ); 
                        $cbc = $cac_legal->appendChild($cbc);

                        $cac_adres = $xml->createElement("cac:RegistrationAddress"); 
                        $cac_adres = $cac_legal->appendChild($cac_adres);

                            $cbc = $xml->createElement("cbc:ID",$empresa[0]["ubigeo"]); $cbc = $cac_adres->appendChild($cbc);
                                $cbc->setAttribute("schemeName","Ubigeos");
                                $cbc->setAttribute("schemeAgencyName","PE:INEI");

                            $cbc = $xml->createElement("cbc:AddressTypeCode","0000"); $cbc = $cac_adres->appendChild($cbc);
                                $cbc->setAttribute("listAgencyName","PE:SUNAT");
                                $cbc->setAttribute("listName","Establecimientos anexos");

                            $cbc = $xml->createElement("cbc:CitySubdivisionName","-"); $cbc = $cac_adres->appendChild($cbc);
                            $cbc = $xml->createElement("cbc:CityName",$empresa[0]["departamento"]); $cbc = $cac_adres->appendChild($cbc);
                            $cbc = $xml->createElement("cbc:CountrySubentity",$empresa[0]["provincia"]); $cbc = $cac_adres->appendChild($cbc);
                            $cbc = $xml->createElement("cbc:District",$empresa[0]["distrito"]); $cbc = $cac_adres->appendChild($cbc);

                            $cac_dir = $xml->createElement("cac:AddressLine"); $cac_dir = $cac_adres->appendChild($cac_dir);
                                $cbc = $xml->createElement("cbc:Line",$empresa[0]["direccion"]); $cbc = $cac_dir->appendChild($cbc);

                            $cac_ciu = $xml->createElement("cac:Country"); $cac_ciu = $cac_adres->appendChild($cac_ciu);
                                $cbc = $xml->createElement("cbc:IdentificationCode","PE"); $cbc = $cac_ciu->appendChild($cbc);
                                    $cbc->setAttribute("listID","ISO 3166-1");
                                    $cbc->setAttribute("listAgencyName","United Nations Economic Commission for Europe");
                                    $cbc->setAttribute("listName","Country");

            // 9: DATOS DEL CLIENTE //

            $cac_customerparty = $xml->createElement("cac:AccountingCustomerParty"); 
            $cac_customerparty = $Invoice->appendChild($cac_customerparty);

                $cac_party = $xml->createElement("cac:Party"); $cac_party = $cac_customerparty->appendChild($cac_party);

                    $cac_partyidentification = $xml->createElement("cac:PartyIdentification"); 
                    $cac_partyidentification = $cac_party->appendChild($cac_partyidentification);
                        $cbc = $xml->createElement("cbc:ID",$info[0]["documento"]); $cbc = $cac_partyidentification->appendChild($cbc);
                            $cbc->setAttribute("schemeID",$info[0]["coddocumento"]);
                            $cbc->setAttribute("schemeName","Documento de Identidad");
                            $cbc->setAttribute("schemeAgencyName","PE:SUNAT");
                            $cbc->setAttribute("schemeURI","urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06");

                    $cac_legal = $xml->createElement("cac:PartyLegalEntity"); $cac_legal = $cac_party->appendChild($cac_legal);
                        $cbc = $xml->createElement("cbc:RegistrationName",htmlspecialchars($info[0]["cliente"]) ); 
                        $cbc = $cac_legal->appendChild($cbc);

                        $cac_adres = $xml->createElement("cac:RegistrationAddress"); 
                        $cac_adres = $cac_legal->appendChild($cac_adres);

                            /* $cbc = $xml->createElement('cbc:ID','203234'); $cbc = $cac_adres->appendChild($cbc);
                                $cbc->setAttribute('schemeName', 'Ubigeos');
                                $cbc->setAttribute('schemeAgencyName', 'PE:INEI');

                            $cbc = $xml->createElement('cbc:AddressTypeCode','0000'); $cbc = $cac_adres->appendChild($cbc);
                                $cbc->setAttribute('listAgencyName','PE:SUNAT');
                                $cbc->setAttribute('listName','Establecimientos anexos');

                            $cbc = $xml->createElement('cbc:CitySubdivisionName','-'); $cbc = $cac_adres->appendChild($cbc);
                            $cbc = $xml->createElement('cbc:CityName','SAN MARTIN'); $cbc = $cac_adres->appendChild($cbc);
                            $cbc = $xml->createElement('cbc:CountrySubentity','TARAPOTO'); $cbc = $cac_adres->appendChild($cbc);
                            $cbc = $xml->createElement('cbc:District','TARAPOTO'); $cbc = $cac_adres->appendChild($cbc); */

                            $cac_dir = $xml->createElement("cac:AddressLine"); $cac_dir = $cac_adres->appendChild($cac_dir);
                                $cbc = $xml->createElement("cbc:Line",$info[0]["direccion"]); $cbc = $cac_dir->appendChild($cbc);

                            $cac_ciu = $xml->createElement("cac:Country"); $cac_ciu = $cac_adres->appendChild($cac_ciu);
                                $cbc = $xml->createElement("cbc:IdentificationCode","PE"); $cbc = $cac_ciu->appendChild($cbc);
                                    $cbc->setAttribute("listID","ISO 3166-1");
                                    $cbc->setAttribute("listAgencyName","United Nations Economic Commission for Europe");
                                    $cbc->setAttribute("listName","Country");


            // DESCUENTOS GLOBALES

            /* if($info[0]["det_impdsctoglobal"] > 0)  {
                $cac_global = $xml->createElement("cac:AllowanceCharge"); $cac_global = $Invoice->appendChild($cac_global);
                    $cbc = $xml->createElement("cbc:ChargeIndicator", "false"); $cbc = $cac_global->appendChild($cbc);            
                    $cbc = $xml->createElement("cbc:AllowanceChargeReasonCode", "00"); $cbc = $cac_global->appendChild($cbc);
                    $cbc = $xml->createElement("cbc:MultiplierFactorNumeric", $info[0]["det_porcdescuento"]); $cbc=$cac_global->appendChild($cbc);
                    $cbc = $xml->createElement("cbc:Amount", $info[0]["det_impdsctoglobal"]); $cbc = $cac_global->appendChild($cbc);
                    $cbc = $xml->createElement("cbc:BaseAmount", $info[0]["det_impsubtotal"]); $cbc = $cac_global->appendChild($cbc);
            } */

            //CONDICION PAGO
            
            if($codcomprobante <> "07" && $codcomprobante <> "08"){
                $textcondicion = ($info[0]["condicionpago"] == 1) ? "Contado" : "Credito";

                $cac_condicion = $xml->createElement("cac:PaymentTerms"); $cac_condicion = $Invoice->appendChild($cac_condicion);
                    $cbc = $xml->createElement("cbc:ID", "FormaPago"); $cbc = $cac_condicion->appendChild($cbc);
                    $cbc = $xml->createElement("cbc:PaymentMeansID", $textcondicion); $cbc = $cac_condicion->appendChild($cbc);
                    $cbc = $xml->createElement("cbc:Amount", number_format($info[0]["importe"],2,".","")); $cbc = $cac_condicion->appendChild($cbc);
                         $cbc->setAttribute("currencyID","PEN");

                if($info[0]["condicionpago"] == 2){
                    $c = 1;
                    foreach ($cuotas as $key => $value) {
                        $c = str_pad($c, 3, "0", STR_PAD_LEFT);
                        $item = 'Cuota'.$c;
                        $cac_cuotas = $xml->createElement("cac:PaymentTerms"); $cac_cuotas = $Invoice->appendChild($cac_cuotas);
                            $cbc = $xml->createElement("cbc:ID", "FormaPago"); $cbc = $cac_cuotas->appendChild($cbc);
                            $cbc = $xml->createElement("cbc:PaymentMeansID", $item); $cbc = $cac_cuotas->appendChild($cbc);
                            $cbc = $xml->createElement("cbc:Amount", number_format($value["importe"],2,".","")); $cbc = $cac_cuotas->appendChild($cbc);
                                $cbc->setAttribute("currencyID","PEN");
                            $cbc = $xml->createElement("cbc:PaymentDueDate", $value["fechavence"]); $cbc = $cac_cuotas->appendChild($cbc);
                            $c++;
                    }
                }
            }

            // 10: SUBTOTALES DEL COMPROBANTE (IGV + IVAP + ICBPER + OTROS) //

            $cac_total = $xml->createElement("cac:TaxTotal"); $cac_total = $Invoice->appendChild($cac_total);
                $cbc = $xml->createElement("cbc:TaxAmount", number_format(($info[0]["igv"] + $info[0]["icbper"]),2,".","") ); $cbc = $cac_total->appendChild($cbc);
                    $cbc->setAttribute("currencyID","PEN");

                // 10.1: SUBTOTAL OPERACIONES GRAVADAS //

                if($totales[0]["gravado"]>0){
                    $cac_subigv = $xml->createElement("cac:TaxSubtotal"); $cac_subigv = $cac_total->appendChild($cac_subigv);
                        $cbc = $xml->createElement("cbc:TaxableAmount",number_format($totales[0]["gravado"],2,".","") ); $cbc = $cac_subigv->appendChild($cbc);
                            $cbc->setAttribute("currencyID","PEN");
                        $cbc = $xml->createElement('cbc:TaxAmount',number_format($info[0]["igv"],2,".","") ); $cbc = $cac_subigv->appendChild($cbc);
                            $cbc->setAttribute('currencyID','PEN');

                        $cac_cat = $xml->createElement("cac:TaxCategory"); $cac_cat = $cac_subigv->appendChild($cac_cat);
                            $cac_esq = $xml->createElement("cac:TaxScheme"); $cac_esq = $cac_cat->appendChild($cac_esq);
                                $cbc = $xml->createElement("cbc:ID","1000"); $cbc = $cac_esq->appendChild($cbc);
                                    $cbc->setAttribute("schemeAgencyName", "PE:SUNAT");
                                    $cbc->setAttribute("schemeID", "UN/ECE 5153");
                                    $cbc->setAttribute("schemeName", "Codigo de tributos");

                                $cbc = $xml->createElement("cbc:Name","IGV"); $cbc = $cac_esq->appendChild($cbc);
                                $cbc = $xml->createElement("cbc:TaxTypeCode","VAT"); $cbc = $cac_esq->appendChild($cbc);
                }

                // 10.2: SUBTOTAL OPERACIONES EXONERADAS //

                if ($totales[0]["exonerado"]>0) {
                    $cac_subtal = $xml->createElement("cac:TaxSubtotal"); $cac_subtal = $cac_total->appendChild($cac_subtal);
                        $cbc = $xml->createElement("cbc:TaxableAmount", number_format($totales[0]["exonerado"],2,".","") ); $cbc = $cac_subtal->appendChild($cbc);
                            $cbc->setAttribute("currencyID", "PEN");
                        $cbc = $xml->createElement("cbc:TaxAmount","0.00"); $cbc = $cac_subtal->appendChild($cbc);
                            $cbc->setAttribute("currencyID", "PEN");
                        
                        $cac_cat = $xml->createElement("cac:TaxCategory"); $cac_cat = $cac_subtal->appendChild($cac_cat);
                            $cac_esq = $xml->createElement("cac:TaxScheme"); $cac_esq = $cac_cat->appendChild($cac_esq);
                                $cbc = $xml->createElement("cbc:ID","9997"); $cbc = $cac_esq->appendChild($cbc);
                                    $cbc->setAttribute("schemeAgencyName", "PE:SUNAT");
                                    $cbc->setAttribute("schemeID", "UN/ECE 5153");
                                    $cbc->setAttribute("schemeName", "Codigo de tributos");

                                $cbc = $xml->createElement("cbc:Name","EXO"); $cbc = $cac_esq->appendChild($cbc);
                                $cbc = $xml->createElement("cbc:TaxTypeCode","VAT"); $cbc = $cac_esq->appendChild($cbc);
                }

                // 10.3: SUBTOTAL OPERACIONES GRATUITAS //

                if ($totales[0]["gratuito"]>0) {
                    $cac_subtal = $xml->createElement("cac:TaxSubtotal"); $cac_subtal = $cac_total->appendChild($cac_subtal);
                        $cbc = $xml->createElement("cbc:TaxableAmount", number_format($totales[0]["gratuito"],2,".","") ); $cbc = $cac_subtal->appendChild($cbc);
                            $cbc->setAttribute("currencyID", "PEN");
                        $cbc = $xml->createElement("cbc:TaxAmount","0.00"); $cbc = $cac_subtal->appendChild($cbc);
                            $cbc->setAttribute("currencyID", "PEN");
                        
                        $cac_cat = $xml->createElement("cac:TaxCategory"); $cac_cat = $cac_subtal->appendChild($cac_cat);
                            $cac_esq = $xml->createElement("cac:TaxScheme"); $cac_esq = $cac_cat->appendChild($cac_esq);
                                $cbc = $xml->createElement("cbc:ID","9996"); $cbc = $cac_esq->appendChild($cbc);
                                    $cbc->setAttribute("schemeAgencyName", "PE:SUNAT");
                                    $cbc->setAttribute("schemeID", "UN/ECE 5153");
                                    $cbc->setAttribute("schemeName", "Codigo de tributos");
                                $cbc = $xml->createElement("cbc:Name","GRA"); $cbc = $cac_esq->appendChild($cbc);
                                $cbc = $xml->createElement("cbc:TaxTypeCode","FRE"); $cbc = $cac_esq->appendChild($cbc);
                }

                // 10.4: NUEVO IMPUESTO ICBPER //

                if ($info[0]["icbper"]>0) {
                    $cac_icbper = $xml->createElement("cac:TaxSubtotal"); $cac_icbper = $cac_total->appendChild($cac_icbper);
                        $cbc = $xml->createElement("cbc:TaxAmount", number_format($info[0]["icbper"],2,".","") ); $cbc=$cac_icbper->appendChild($cbc);
                            $cbc->setAttribute("currencyID", "PEN");

                        $cac_cat = $xml->createElement("cac:TaxCategory"); $cac_cat = $cac_icbper->appendChild($cac_cat);
                            $cac_esq = $xml->createElement("cac:TaxScheme"); $cac_esq = $cac_cat->appendChild($cac_esq);
                                $cbc = $xml->createElement("cbc:ID","7152"); $cbc = $cac_esq->appendChild($cbc);
                                $cbc = $xml->createElement("cbc:Name","ICBPER"); $cbc = $cac_esq->appendChild($cbc);
                                $cbc = $xml->createElement("cbc:TaxTypeCode","OTH"); $cbc = $cac_esq->appendChild($cbc);
                }

            // 11: TOTALES DEL COMPROBANTE //

            if($codcomprobante <> '07' && $codcomprobante <> '08'){
                $cac_total = $xml->createElement("cac:LegalMonetaryTotal"); $cac_total = $Invoice->appendChild($cac_total);
                $cbc = $xml->createElement("cbc:LineExtensionAmount",number_format($info[0]["valorventa"],2,".","") ); $cbc = $cac_total->appendChild($cbc);
                $cbc->setAttribute("currencyID","PEN");

                $cbc = $xml->createElement("cbc:TaxInclusiveAmount",number_format($info[0]["importe"] ,2,'.','') ); $cbc = $cac_total->appendChild($cbc);
                    $cbc->setAttribute("currencyID","PEN");

                $cbc = $xml->createElement("cbc:AllowanceTotalAmount",number_format($info[0]["descglobal"],2,".","") ); $cbc = $cac_total->appendChild($cbc);
                    $cbc->setAttribute("currencyID","PEN");
                $cbc = $xml->createElement("cbc:ChargeTotalAmount","0.00"); $cbc = $cac_total->appendChild($cbc);
                    $cbc->setAttribute("currencyID","PEN");
                $cbc = $xml->createElement("cbc:PrepaidAmount","0.00"); $cbc = $cac_total->appendChild($cbc);
                    $cbc->setAttribute("currencyID","PEN");
                $cbc = $xml->createElement("cbc:PayableAmount",number_format($info[0]["importe"],2,".","") ); $cbc = $cac_total->appendChild($cbc);
                    $cbc->setAttribute("currencyID","PEN");
            }else{
                $cac_total = $xml->createElement("cac:LegalMonetaryTotal"); $cac_total = $Invoice->appendChild($cac_total);

                $cbc = $xml->createElement("cbc:PayableAmount",number_format($info[0]["importe"],2,".","") ); $cbc = $cac_total->appendChild($cbc);
                    $cbc->setAttribute("currencyID","PEN");
            }

            

            // 12: ITEMS DEL COMPROBANTE //

            foreach ($detalle as $key => $value) {
                if($codcomprobante <> "07" && $codcomprobante <> "08"){
                    $line = $xml->createElement("cac:InvoiceLine"); $line = $Invoice->appendChild($line);
                }elseif($codcomprobante == "07"){
                    $line = $xml->createElement("cac:CreditNoteLine"); $line = $Invoice->appendChild($line);
                }else{
                    $line = $xml->createElement("cac:DebitNoteLine"); $line = $Invoice->appendChild($line);
                }

                // 12.1: NRO DE ITEM / CANTIDAD Y UNIDAD DE MEDIDA //

                $cbc = $xml->createElement("cbc:ID",$value["item"]); $cbc = $line->appendChild($cbc);
                if($codcomprobante <> "07" && $codcomprobante <> "08"){
                    $cbc = $xml->createElement("cbc:InvoicedQuantity",number_format($value["cantidad"],2,".","") ); $cbc = $line->appendChild($cbc);
                        $cbc->setAttribute("unitCode",$value["unidad"]);
                        $cbc->setAttribute("unitCodeListID",'UN/ECE rec 20');
                        $cbc->setAttribute("unitCodeListAgencyName","United Nations Economic Commission for Europe");
                }elseif($codcomprobante == "07"){
                    $cbc = $xml->createElement("cbc:CreditedQuantity",number_format($value["cantidad"],2,".","") ); $cbc = $line->appendChild($cbc);
                        $cbc->setAttribute("unitCode",$value["unidad"]);
                }else{
                    $cbc = $xml->createElement("cbc:DebitedQuantity",number_format($value["cantidad"],2,".","") ); $cbc = $line->appendChild($cbc); 
                        $cbc->setAttribute("unitCode",$value["unidad"]);
                }

                // 12.2: SUBTOTAL DEL ITEM / MONTOS UNITARIOS DEL ITEM //

                $cbc = $xml->createElement("cbc:LineExtensionAmount",number_format($value["valorventa"],2,".","") ); $cbc = $line->appendChild($cbc);
                    $cbc->setAttribute("currencyID","PEN");

                $precios = $xml->createElement("cac:PricingReference"); $precios = $line->appendChild($precios);
                    $precios_al = $xml->createElement("cac:AlternativeConditionPrice"); 
                    $precios_al = $precios->appendChild($precios_al);

                        $precioref = (double)($value["subtotal"] / $value["cantidad"]);
                        $cbc = $xml->createElement("cbc:PriceAmount",number_format($precioref,2,".","") ); $cbc = $precios_al->appendChild($cbc);
                            $cbc->setAttribute("currencyID","PEN");

                        if ($value["codafectacionigv"]==21) {
                            $cbc = $xml->createElement("cbc:PriceTypeCode","02"); $cbc = $precios_al->appendChild($cbc);
                                $cbc->setAttribute("listAgencyName","PE:SUNAT");
                                $cbc->setAttribute("listName","Tipo de Precio");
                                $cbc->setAttribute("listURI","urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16");
                        }else{
                            $cbc = $xml->createElement("cbc:PriceTypeCode","01"); $cbc = $precios_al->appendChild($cbc);
                                $cbc->setAttribute("listAgencyName","PE:SUNAT");
                                $cbc->setAttribute("listName","Tipo de Precio");
                                $cbc->setAttribute("listURI","urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16");
                        }

                // 12.3: DESCUENTOS POR ITEM //

                if ($value["porcdescuento"]!=0) {
                    $descuentos = $xml->createElement("cac:AllowanceCharge"); $descuentos = $line->appendChild($descuentos);
                        $cbc = $xml->createElement("cbc:ChargeIndicator","false"); $cbc = $descuentos->appendChild($cbc);
                        $cbc = $xml->createElement("cbc:AllowanceChargeReasonCode","00"); $cbc = $descuentos->appendChild($cbc);

                        $cbc = $xml->createElement("cbc:Amount",number_format($value["descuento"],2,".","") ); $cbc = $descuentos->appendChild($cbc);
                            $cbc->setAttribute("currencyID","PEN");

                        $cbc = $xml->createElement("cbc:BaseAmount",number_format($value["importe"],2,".","") ); $cbc = $descuentos->appendChild($cbc);
                            $cbc->setAttribute("currencyID","PEN");
                }

                // 12.4: SUBTOTALES DEL ITEM //

                $cac_tot = $xml->createElement("cac:TaxTotal"); $cac_tot = $line->appendChild($cac_tot);
                    $cbc = $xml->createElement("cbc:TaxAmount",number_format(($value["igv"] + $value["icbper"]),2,".","") ); $cbc = $cac_tot->appendChild($cbc);
                        $cbc->setAttribute("currencyID","PEN");

                        // 12.4.1 ITEM GRAVADO //

                        if ($value["codafectacionigv"]==10) {
                            $cac_ig = $xml->createElement("cac:TaxSubtotal"); $cac_ig = $cac_tot->appendChild($cac_ig);
                                $cbc = $xml->createElement("cbc:TaxableAmount",number_format($value["valorventa"],2,".","") ); $cbc = $cac_ig->appendChild($cbc);
                                    $cbc->setAttribute("currencyID","PEN");
                                $cbc = $xml->createElement("cbc:TaxAmount",number_format($value["igv"],2,".","") ); $cbc = $cac_ig->appendChild($cbc);
                                    $cbc->setAttribute("currencyID","PEN");

                                $cac_ct = $xml->createElement("cac:TaxCategory"); $cac_ct = $cac_ig->appendChild($cac_ct);

                                    $cbc = $xml->createElement("cbc:Percent","18.00"); $cbc = $cac_ct->appendChild($cbc);
                                    $cbc = $xml->createElement("cbc:TaxExemptionReasonCode","10"); $cbc = $cac_ct->appendChild($cbc);
                                        $cbc->setAttribute("listAgencyName", "PE:SUNAT");
                                        $cbc->setAttribute("listName", "Afectacion del IGV");
                                        $cbc->setAttribute("listURI", "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07");

                                    $cac_sq = $xml->createElement("cac:TaxScheme"); $cac_sq = $cac_ct->appendChild($cac_sq);
                                        $cbc = $xml->createElement("cbc:ID","1000"); $cbc = $cac_sq->appendChild($cbc);
                                            $cbc->setAttribute("schemeAgencyName", "PE:SUNAT");
                                            $cbc->setAttribute("schemeID", "UN/ECE 5153");
                                            $cbc->setAttribute("schemeName", "Codigo de tributos");

                                        $cbc = $xml->createElement("cbc:Name","IGV"); $cbc = $cac_sq->appendChild($cbc);
                                        $cbc = $xml->createElement("cbc:TaxTypeCode","VAT"); $cbc = $cac_sq->appendChild($cbc);
                        }

                        // 12.4.2 ITEM EXONERADO //

                        if ($value["codafectacionigv"]==20) {
                            $cac_ig = $xml->createElement("cac:TaxSubtotal"); $cac_ig = $cac_tot->appendChild($cac_ig);
                                $cbc = $xml->createElement("cbc:TaxableAmount",number_format($value["subtotal"],2,".","") ); $cbc = $cac_ig->appendChild($cbc);
                                    $cbc->setAttribute("currencyID","PEN");
                                $cbc = $xml->createElement("cbc:TaxAmount","0.00"); $cbc = $cac_ig->appendChild($cbc);
                                    $cbc->setAttribute("currencyID","PEN");

                                $cac_ct = $xml->createElement("cac:TaxCategory"); $cac_ct = $cac_ig->appendChild($cac_ct);

                                    $cbc = $xml->createElement("cbc:Percent","0.00"); $cbc = $cac_ct->appendChild($cbc);
                                    $cbc = $xml->createElement("cbc:TaxExemptionReasonCode","20"); $cbc = $cac_ct->appendChild($cbc);
                                        $cbc->setAttribute("listAgencyName", "PE:SUNAT");
                                        $cbc->setAttribute("listName", "Afectacion del IGV");
                                        $cbc->setAttribute("listURI", "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07");

                                    $cac_sq = $xml->createElement("cac:TaxScheme"); $cac_sq = $cac_ct->appendChild($cac_sq);
                                        $cbc = $xml->createElement("cbc:ID","9997"); $cbc = $cac_sq->appendChild($cbc);
                                            $cbc->setAttribute("schemeAgencyName", "PE:SUNAT");
                                            $cbc->setAttribute("schemeID", "UN/ECE 5153");
                                            $cbc->setAttribute("schemeName", "Codigo de tributos");

                                        $cbc = $xml->createElement("cbc:Name","EXO"); $cbc = $cac_sq->appendChild($cbc);
                                        $cbc = $xml->createElement("cbc:TaxTypeCode","VAT"); $cbc = $cac_sq->appendChild($cbc);
                        }

                        // 12.4.3 ITEM GRATUITO //

                        if ($value["codafectacionigv"]==21) {
                            $cac_ig = $xml->createElement("cac:TaxSubtotal"); $cac_ig = $cac_tot->appendChild($cac_ig);
                                $cbc = $xml->createElement("cbc:TaxableAmount",number_format($value["preciorefunitario"] * $value["cantidad"],2,".","") ); $cbc = $cac_ig->appendChild($cbc);
                                    $cbc->setAttribute("currencyID","PEN");
                                $cbc = $xml->createElement("cbc:TaxAmount","0.00"); $cbc = $cac_ig->appendChild($cbc);
                                    $cbc->setAttribute("currencyID","PEN");

                                $cac_ct = $xml->createElement("cac:TaxCategory"); $cac_ct = $cac_ig->appendChild($cac_ct);

                                    $cbc = $xml->createElement("cbc:Percent","0.00"); $cbc = $cac_ct->appendChild($cbc);
                                    $cbc = $xml->createElement("cbc:TaxExemptionReasonCode","21"); $cbc = $cac_ct->appendChild($cbc);
                                        $cbc->setAttribute("listAgencyName", "PE:SUNAT");
                                        $cbc->setAttribute("listName", "Afectacion del IGV");
                                        $cbc->setAttribute("listURI", "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07");

                                    $cac_sq = $xml->createElement("cac:TaxScheme"); $cac_sq = $cac_ct->appendChild($cac_sq);
                                        $cbc = $xml->createElement("cbc:ID","9996"); $cbc = $cac_sq->appendChild($cbc);
                                            $cbc->setAttribute("schemeAgencyName", "PE:SUNAT");
                                            $cbc->setAttribute("schemeID", "UN/ECE 5153");
                                            $cbc->setAttribute("schemeName", "Codigo de tributos");

                                        $cbc = $xml->createElement("cbc:Name","GRA"); $cbc = $cac_sq->appendChild($cbc);
                                        $cbc = $xml->createElement("cbc:TaxTypeCode","FRE"); $cbc = $cac_sq->appendChild($cbc);
                        }

                        // 12.4.4 ITEM ICBPER //

                        if ($value["icbper"]>0) {
                            $cac_ig = $xml->createElement("cac:TaxSubtotal"); $cac_ig = $cac_tot->appendChild($cac_ig);
                                $cbc = $xml->createElement("cbc:TaxAmount",number_format($value["subtotal"],2,".","") ); $cbc = $cac_ig->appendChild($cbc);
                                    $cbc->setAttribute("currencyID","PEN");
                                    $cbc = $xml->createElement("cbc:BaseUnitMeasure",(int)$value["cantidad"] ); $cbc = $cac_ig->appendChild($cbc);
                                    $cbc->setAttribute("unitCode",$value["unidad"]);
                                    $cbc = $xml->createElement("cbc:PerUnitAmount",number_format($value["icbper"],2,".","") ); $cbc = $cac_ig->appendChild($cbc);
                                    $cbc->setAttribute("currencyID","PEN");

                                $cac_ct = $xml->createElement("cac:TaxCategory"); $cac_ct = $cac_ig->appendChild($cac_ct);
                                    $cac_sq = $xml->createElement("cac:TaxScheme"); $cac_sq = $cac_ct->appendChild($cac_sq);
                                        $cbc = $xml->createElement("cbc:ID","7152"); $cbc = $cac_sq->appendChild($cbc);
                                        $cbc = $xml->createElement("cbc:Name","ICBPER"); $cbc = $cac_sq->appendChild($cbc);
                                        $cbc = $xml->createElement("cbc:TaxTypeCode","OTH"); $cbc = $cac_sq->appendChild($cbc);
                        }
                            
                // 12.5: ITEM DEL PRODUCTO / PRECIO DEL PRODUCTO //

                // CDATA: $cbc = $item->appendChild($xml->createElement("cbc:Description")); $cbc->appendChild($xml->createCDATASection($value["producto"]));

                $item = $xml->createElement("cac:Item"); $item = $line->appendChild($item);
                    $descripcion_item = $value["producto"]." ".$value["descripcion"];
                    $descripcion_item = preg_replace("/[\n|\r|\n\r]/i","",$descripcion_item);
                    
                    if(strlen($descripcion_item) > 250){
                        $cbc = $xml->createElement("cbc:Description", htmlspecialchars(substr($descripcion_item, 0, 250)) );
                        $cbc = $item->appendChild($cbc);

                        $cbc = $xml->createElement("cbc:Description", htmlspecialchars(substr($descripcion_item, 250, strlen($descripcion_item))) );
                        $cbc = $item->appendChild($cbc);
                    }else{
                        $cbc = $xml->createElement("cbc:Description",htmlspecialchars($descripcion_item) );
                        $cbc = $item->appendChild($cbc);
                    }
                    $sellers = $xml->createElement("cac:SellersItemIdentification"); $sellers = $item->appendChild($sellers);
                        $cbc = $xml->createElement("cbc:ID",$value["codproducto"]); $cbc = $sellers->appendChild($cbc);

                $price = $xml->createElement("cac:Price"); $price = $line->appendChild($price);
                    $cbc = $xml->createElement("cbc:PriceAmount",number_format($value["preciosinigv"],4,".","") ); $cbc = $price->appendChild($cbc);
                        $cbc->setAttribute("currencyID", "PEN");
            }

        // FIN DE LA CREACION DEL XML //
        
        $xml->formatOutput = true;
        //echo $carpeta_phuyu."/".$info[0]["nombre_xml"].".xml";
        $xml->save($carpeta_phuyu."/".$info[0]["nombre_xml"].".xml"); chmod($carpeta_phuyu."/".$info[0]["nombre_xml"].".xml", 0777);

        if (file_exists($carpeta_phuyu."/".$info[0]["nombre_xml"].".xml")) {
            $data["estado"] = 1; $data["carpeta_phuyu"] = $carpeta_phuyu; $data["archivo_phuyu"] = $info[0]["nombre_xml"];
        }else{
            $data["estado"] = 0;
        }
        return $data;
    }

    //GUIAS DE REMISION XML

    function phuyu_crearXMLGUIAS($codcomprobante,$codguiar){
        $empresa = $this->db->query("select p.documento,p.razonsocial,p.direccion, e.ubigeo, e.departamento, e.provincia, e.distrito from public.personas as p inner join public.empresas as e on(p.codpersona=e.codpersona) where e.codempresa=1")->result_array();

        $info = $this->db->query("select guiasr.licenciaconductor, guiasr.documentoconductor,guiasr.codproveedor,guiasr.fechatraslado,guiasr.codmodalidadtraslado,dt.oficial, documentotipos.oficial AS oficialdocumento,motivos.descripcion AS motivo,motivos.oficial AS oficialmotivo ,guiasr.codmotivotraslado,personas.documento, guiasr.destinatario, guiasr.codguiar, guiasr.seriecomprobante, guiasr.peso, guiasr.nropaquetes, guiasr.documentotransportista, guiasr.razonsocialtransportista, guiasr.codubigeollegada, guiasr.direccionllegada, guiasr.codubigeopartida, guiasr.direccionpartida, guiasr.nrocomprobante,guiasr.fechaguia,guiasrs.estado,guiasr.observaciones,guiasrs.nombre_xml from almacen.guiasr as guiasr inner join sunat.guiasunat as guiasrs on(guiasr.codguiar=guiasrs.codguiar) inner join public.personas as personas on (guiasr.codpersona=personas.codpersona) inner join public.documentotipos as documentotipos on (guiasr.coddocumentotipoconductor=documentotipos.coddocumentotipo) inner join almacen.motivotraslado as motivos on(guiasr.codmotivotraslado=motivos.codmotivotraslado) inner join public.documentotipos as dt on(personas.coddocumentotipo=dt.coddocumentotipo) where guiasr.codguiar=".$codguiar)->result_array();

        $detalle = $this->db->query("select kd.*,p.descripcion as producto,u.oficial as unidad,l.codlinea from almacen.guiasrdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) inner join almacen.lineas as l on(p.codlinea=l.codlinea) where kd.codguiar=".$codguiar." and kd.estado=1 order by kd.item asc")->result_array();

        // 0: CREAMOS UNA CARPETA PARA ALMACENAR EL XML DEL COMPROBANTE TEMPORALMENTE //

        $carpeta_phuyu  = "./sunat/webphuyu/".$info[0]["nombre_xml"];
        if (!file_exists($carpeta_phuyu)) {
            mkdir($carpeta_phuyu,0777); chmod($carpeta_phuyu, 0777);
        }

        // 1: CREAMOS EL DOCUMENTO XML //

       $xml = new DomDocument("1.0","UTF-8"); $xml->standalone = false; $xml->preserveWhiteSpace = false;

        $Invoice = $xml->createElement("DespatchAdvice"); $Invoice = $xml->appendChild($Invoice);
        $Invoice->setAttribute("xmlns","urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2");

        $Invoice->setAttribute("xmlns:cac","urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
        $Invoice->setAttribute("xmlns:cbc", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
        $Invoice->setAttribute("xmlns:ds", "http://www.w3.org/2000/09/xmldsig#");
        $Invoice->setAttribute("xmlns:ext", "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");

        $UBLExtension = $xml->createElement("ext:UBLExtensions"); $UBLExtension = $Invoice->appendChild($UBLExtension);
            
            // 2: FIRMA ELECTRONICA //

            $ext = $xml->createElement("ext:UBLExtension"); $ext = $UBLExtension->appendChild($ext);
                $contents = $xml->createElement("ext:ExtensionContent",""); $contents = $ext->appendChild($contents);
            
            // 3: VERSION DEL XML Y DEL COMPROBANTE //

            $cbc = $xml->createElement("cbc:UBLVersionID","2.1"); $cbc = $Invoice->appendChild($cbc);
            $cbc = $xml->createElement("cbc:CustomizationID","1.0"); $cbc = $Invoice->appendChild($cbc);

            // 4. SERIE Y NRO DEL COMPROBANTE F001-00000001 - FECHA Y HORA DE EMISION //

            $cbc = $xml->createElement("cbc:ID",$info[0]["seriecomprobante"]."-".$info[0]["nrocomprobante"]); $cbc = $Invoice->appendChild($cbc);
            $cbc = $xml->createElement("cbc:IssueDate",$info[0]["fechaguia"]); $cbc = $Invoice->appendChild($cbc);

            $cbc = $xml->createElement("cbc:IssueTime", '00:00:00'); $cbc = $Invoice->appendChild($cbc);


            // 5. CODIGO TIPO DOCUMENTO CATALOGO 01 //

            $cbc = $xml->createElement("cbc:DespatchAdviceTypeCode",$codcomprobante); $cbc = $Invoice->appendChild($cbc);

            // 6. OBSERVACION

            $cbc = $xml->createElement("cbc:Note", trim($info[0]["observaciones"])); $cbc = $Invoice->appendChild($cbc);

           
            // 7: DATOS DE LA EMPRESA - REFERENCIA FIRMA DIGITAL //

               $cac_signature = $xml->createElement("cac:Signature"); $cac_signature = $Invoice->appendChild($cac_signature);
                $cbc = $xml->createElement("cbc:ID","SignSUNAT"); $cbc = $cac_signature->appendChild($cbc);

                $cac_signatureparty = $xml->createElement("cac:SignatoryParty"); 
                $cac_signatureparty = $cac_signature->appendChild($cac_signatureparty);

                    $cac_partyidentification = $xml->createElement("cac:PartyIdentification"); 
                    $cac_partyidentification = $cac_signatureparty->appendChild($cac_partyidentification);
                        $cbc = $xml->createElement("cbc:ID",$empresa[0]["documento"]); $cbc = $cac_partyidentification->appendChild($cbc);
                    $cac_partyname = $xml->createElement("cac:PartyName"); 
                    $cac_partyname = $cac_signatureparty->appendChild($cac_partyname);
                        $cbc = $xml->createElement("cbc:Name",htmlspecialchars($empresa[0]["razonsocial"]) ); 
                        $cbc = $cac_partyname->appendChild($cbc);

                $cac_digital = $xml->createElement("cac:DigitalSignatureAttachment"); 
                $cac_digital=$cac_signature->appendChild($cac_digital);
                    $cac_externalreference = $xml->createElement("cac:ExternalReference"); 
                    $cac_externalreference = $cac_digital->appendChild($cac_externalreference);
                        $cbc = $xml->createElement("cbc:URI","#SignSUNAT"); $cbc = $cac_externalreference->appendChild($cbc);

            // 8: DATOS DE LA EMPRESA QUE EMITE //

            $cac_supplierparty = $xml->createElement("cac:DespatchSupplierParty"); 
            $cac_supplierparty = $Invoice->appendChild($cac_supplierparty);

                $cbc_customer = $xml->createElement("cbc:CustomerAssignedAccountID", $empresa[0]["documento"]);
                $cbc_customer = $cac_supplierparty->appendChild($cbc_customer);
                $cbc_customer->setAttribute("schemeID","6");

                $cac_party = $xml->createElement("cac:Party"); $cac_party = $cac_supplierparty->appendChild($cac_party);

                    $cac_legal = $xml->createElement("cac:PartyLegalEntity"); $cac_legal = $cac_party->appendChild($cac_legal);
                        $cbc = $xml->createElement("cbc:RegistrationName",htmlspecialchars($empresa[0]["razonsocial"]) ); 
                        $cbc = $cac_legal->appendChild($cbc);

            // 9: DATOS DEL CLIENTE //

            $cac_customerparty = $xml->createElement("cac:DeliveryCustomerParty"); 
            $cac_customerparty = $Invoice->appendChild($cac_customerparty);

                $cbc_customer = $xml->createElement("cbc:CustomerAssignedAccountID", $info[0]["documento"]);
                $cbc_customer = $cac_customerparty->appendChild($cbc_customer);
                $cbc_customer->setAttribute("schemeID",$info[0]["oficial"]);

                $cac_party = $xml->createElement("cac:Party"); $cac_party = $cac_customerparty->appendChild($cac_party);

                    $cac_legal = $xml->createElement("cac:PartyLegalEntity"); $cac_legal = $cac_party->appendChild($cac_legal);
                        $cbc = $xml->createElement("cbc:RegistrationName",htmlspecialchars($info[0]["destinatario"]) ); 
                        $cbc = $cac_legal->appendChild($cbc);

            if($info[0]["codmotivotraslado"] == 2){

                $proveedor = $this->db->query("select *from personas where codpersona=".$info[0]["codproveedor"])->result_array();

                $cac_proveedor = $xml->createElement("cac:SellerSupplierParty"); 
                $cac_proveedor = $Invoice->appendChild($cac_proveedor);

                    $cbc_proveedor = $xml->createElement("cbc:CustomerAssignedAccountID", $proveedor[0]["documento"]);
                    $cbc_proveedor = $cac_proveedor->appendChild($cbc_proveedor);
                    $cbc_proveedor->setAttribute("schemeID","6");

                    $cac_party = $xml->createElement("cac:Party"); $cac_party = $cac_proveedor->appendChild($cac_party);

                        $cac_legal = $xml->createElement("cac:PartyLegalEntity"); $cac_legal = $cac_party->appendChild($cac_legal);
                            $cbc = $xml->createElement("cbc:RegistrationName",htmlspecialchars($proveedor[0]["razonsocial"]) ); 
                            $cbc = $cac_legal->appendChild($cbc);
            }


            // DESCUENTOS GLOBALES

            /* if($info[0]["det_impdsctoglobal"] > 0)  {
                $cac_global = $xml->createElement("cac:AllowanceCharge"); $cac_global = $Invoice->appendChild($cac_global);
                    $cbc = $xml->createElement("cbc:ChargeIndicator", "false"); $cbc = $cac_global->appendChild($cbc);            
                    $cbc = $xml->createElement("cbc:AllowanceChargeReasonCode", "00"); $cbc = $cac_global->appendChild($cbc);
                    $cbc = $xml->createElement("cbc:MultiplierFactorNumeric", $info[0]["det_porcdescuento"]); $cbc=$cac_global->appendChild($cbc);
                    $cbc = $xml->createElement("cbc:Amount", $info[0]["det_impdsctoglobal"]); $cbc = $cac_global->appendChild($cbc);
                    $cbc = $xml->createElement("cbc:BaseAmount", $info[0]["det_impsubtotal"]); $cbc = $cac_global->appendChild($cbc);
            } */

            // 11: TOTALES DEL COMPROBANTE //

            $cac_sepelier = $xml->createElement("cac:Shipment"); $cac_sepelier = $Invoice->appendChild($cac_sepelier);

               $cbc = $xml->createElement("cbc:ID","1"); $cbc = $cac_sepelier->appendChild($cbc);

               $cbc = $xml->createElement("cbc:HandlingCode", $info[0]["oficialmotivo"]); $cbc = $cac_sepelier->appendChild($cbc);
               $cbc = $xml->createElement("cbc:Information", $info[0]["motivo"]); $cbc = $cac_sepelier->appendChild($cbc);

               $cbc = $xml->createElement("cbc:GrossWeightMeasure",number_format($info[0]["peso"],2,".","")); $cbc = $cac_sepelier->appendChild($cbc);
                    $cbc->setAttribute("unitCode","KGM");

                $cbc = $xml->createElement("cbc:TotalTransportHandlingUnitQuantity", $info[0]["nropaquetes"]); $cbc = $cac_sepelier->appendChild($cbc);

                $cac_shipment = $xml->createElement("cac:ShipmentStage"); $cac_shipment = $cac_sepelier->appendChild($cac_shipment);

                     $cbc = $xml->createElement("cbc:TransportModeCode", '0'.$info[0]["codmodalidadtraslado"]); $cbc = $cac_shipment->appendChild($cbc);

                     $cac = $xml->createElement("cac:TransitPeriod"); $cac = $cac_shipment->appendChild($cac);

                         $cbc = $xml->createElement("cbc:StartDate", $info[0]["fechatraslado"]); $cbc = $cac->appendChild($cbc);

                    if($info[0]["codmodalidadtraslado"] == 1){

                        //EMPRESA TRANSPORTISTA
                        $cac_transporte = $xml->createElement("cac:CarrierParty"); $cac_transporte = $cac_shipment->appendChild($cac_transporte);
                           $cac_partyidentification = $xml->createElement("cac:PartyIdentification"); $cac_partyidentification = $cac_transporte->appendChild($cac_partyidentification);
                               $cbc = $xml->createElement("cbc:ID", $info[0]["documentotransportista"]); $cbc = $cac_partyidentification->appendChild($cbc);
                               $cbc->setAttribute("schemeID","6");

                            $cac_partyName = $xml->createElement("cac:PartyName"); $cac_partyName = $cac_transporte->appendChild($cac_partyName);
                               $cbc = $xml->createElement("cbc:Name", htmlspecialchars($info[0]["razonsocialtransportista"])); $cbc = $cac_partyName->appendChild($cbc);
                    }else{

                        //VEHICULO 
                        $cac_vehiculo = $xml->createElement("cac:TransportMeans"); $cac_vehiculo = $cac_shipment->appendChild($cac_vehiculo);
                            $cac_transport = $xml->createElement("cac:RoadTransport"); $cac_transport = $cac_vehiculo->appendChild($cac_transport);

                                $cbc = $xml->createElement("cbc:LicensePlateID", $info[0]["licenciaconductor"]); $cbc = $cac_transport->appendChild($cbc);


                        //CONDUCTOR
                        $cac_conductor = $xml->createElement("cac:DriverPerson"); $cac_conductor = $cac_shipment->appendChild($cac_conductor);
                            $cbc = $xml->createElement("cbc:ID", $info[0]["documentoconductor"]); $cbc = $cac_conductor->appendChild($cbc);
                            $cbc->setAttribute("schemeID",$info[0]["oficialdocumento"]);
                    }

                $ubigeollegada = $this->db->query("select *from ubigeo WHERE codubigeo=".$info[0]["codubigeollegada"])->result_array();

                $cac_delivery = $xml->createElement("cac:Delivery"); $cac_delivery = $cac_sepelier->appendChild($cac_delivery);
                    $cac_deliveryadress = $xml->createElement("cac:DeliveryAddress"); $cac_delivery->appendChild($cac_deliveryadress);
                        $cbc = $xml->createElement("cbc:ID", $ubigeollegada[0]["ubidepartamento"].$ubigeollegada[0]["ubiprovincia"].$ubigeollegada[0]["ubidistrito"]); $cbc = $cac_deliveryadress->appendChild($cbc);
                        $cbc = $xml->createElement("cbc:StreetName", htmlspecialchars((string)$info[0]["direccionllegada"])); $cbc = $cac_deliveryadress->appendChild($cbc);

                $ubigeopartida = $this->db->query("select *from ubigeo WHERE codubigeo=".$info[0]["codubigeopartida"])->result_array();

                $cac_origin = $xml->createElement("cac:OriginAddress"); $cac_origin = $cac_sepelier->appendChild($cac_origin);
                        $cbc = $xml->createElement("cbc:ID", $ubigeopartida[0]["ubidepartamento"].$ubigeopartida[0]["ubiprovincia"].$ubigeopartida[0]["ubidistrito"]); $cbc = $cac_origin->appendChild($cbc);
                        $cbc = $xml->createElement("cbc:StreetName", htmlspecialchars($info[0]["direccionpartida"])); $cbc = $cac_origin->appendChild($cbc);

            // 12: ITEMS DEL COMPROBANTE //

            foreach ($detalle as $key => $value) {
                $line = $xml->createElement("cac:DespatchLine"); $line = $Invoice->appendChild($line);

                // 12.1: NRO DE ITEM / CANTIDAD Y UNIDAD DE MEDIDA //

                $cbc = $xml->createElement("cbc:ID",$value["item"]); $cbc = $line->appendChild($cbc);

                    $cbc = $xml->createElement("cbc:DeliveredQuantity",number_format($value["cantidad"],2,".","") ); $cbc = $line->appendChild($cbc);
                    $cbc->setAttribute("unitCode",$value["unidad"]);

                    $cac_orden = $xml->createElement("cac:OrderLineReference"); $cac_orden = $line->appendChild($cac_orden);

                       $cbc = $xml->createElement("cbc:LineID", $value["item"]); $cbc = $cac_orden->appendChild($cbc);

                    $cac_item = $xml->createElement("cac:Item"); $cac_item = $line->appendChild($cac_item);
                       $cbc = $xml->createElement("cbc:Name", htmlspecialchars($value["producto"])); $cbc = $cac_item->appendChild($cbc);

                       $cac_seller = $xml->createElement("cac:SellersItemIdentification"); $cac_seller = $cac_item->appendChild($cac_seller);
                           $cbc = $xml->createElement("cbc:ID",$value["codproducto"]); $cbc = $cac_seller->appendChild($cbc);

                
            }

        // FIN DE LA CREACION DEL XML //
        
        $xml->formatOutput = true;
        $xml->save($carpeta_phuyu."/".$info[0]["nombre_xml"].".xml"); chmod($carpeta_phuyu."/".$info[0]["nombre_xml"].".xml", 0777);

        if (file_exists($carpeta_phuyu."/".$info[0]["nombre_xml"].".xml")) {
            $data["estado"] = 1; $data["carpeta_phuyu"] = $carpeta_phuyu; $data["archivo_phuyu"] = $info[0]["nombre_xml"];
        }else{
            $data["estado"] = 0;
        }
        return $data;
    }

    // RESUMEN DE FACTURAS ANULADAS // 

    function phuyu_rf_crearXML($periodo,$nrocorrelativo){
        $empresa = $this->db->query("select p.documento,p.razonsocial,p.direccion,u.* from public.personas as p inner join public.ubigeo as u on(p.codubigeo=u.codubigeo) where p.codpersona=".$_SESSION["phuyu_codempresa"])->result_array();

        $resumen = $this->db->query("select *from sunat.resumenes where codresumentipo=1 and periodo='".$periodo."' and nrocorrelativo=".$nrocorrelativo." and codempresa=".$_SESSION["phuyu_codempresa"])->result_array();
        $detalle = $this->db->query("select ksa.*,k.seriecomprobante,k.nrocomprobante from sunat.kardexsunatanulados as ksa inner join kardex.kardex as k on(ksa.codkardex=k.codkardex) where ksa.codresumentipo=1 and ksa.periodo='".$periodo."' and ksa.nrocorrelativo=".$nrocorrelativo." and ksa.codempresa=".$_SESSION["phuyu_codempresa"])->result_array();
        
        // 0: CREAMOS UNA CARPETA PARA ALMACENAR EL XML DEL COMPROBANTE TEMPORALMENTE //
        
        $carpeta_phuyu  = "./sunat/webphuyu/".$resumen[0]["nombre_xml"];
        if (!file_exists($carpeta_phuyu)) {
            mkdir($carpeta_phuyu,0777); chmod($carpeta_phuyu, 0777);
        }

        // 1: CREAMOS EL XML DEL COMPROBANTE //

        $xml = new DomDocument("1.0", "ISO-8859-1"); $xml->standalone = false; $xml->preserveWhiteSpace = false;
        $Invoice = $xml->createElement("VoidedDocuments"); $Invoice = $xml->appendChild($Invoice);
        $Invoice->setAttribute("xmlns","urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1");
        $Invoice->setAttribute("xmlns:cac","urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
        $Invoice->setAttribute("xmlns:cbc","urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
        $Invoice->setAttribute("xmlns:ccts","urn:un:unece:uncefact:documentation:2");
        $Invoice->setAttribute("xmlns:ds","http://www.w3.org/2000/09/xmldsig#");
        $Invoice->setAttribute("xmlns:ext","urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
        $Invoice->setAttribute("xmlns:qdt","urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2");
        $Invoice->setAttribute("xmlns:sac","urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1");
        $Invoice->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $Invoice->setAttribute("xmlns:udt","urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2");
    
        $UBLExtension = $xml->createElement("ext:UBLExtensions"); $UBLExtension = $Invoice->appendChild($UBLExtension);
            $ext = $xml->createElement("ext:UBLExtension"); $ext = $UBLExtension->appendChild($ext);
            $contents = $xml->createElement("ext:ExtensionContent"); $contents = $ext->appendChild($contents);

                $cbc = $xml->createElement("cbc:UBLVersionID", "2.0"); $cbc = $Invoice->appendChild($cbc);
                $cbc = $xml->createElement("cbc:CustomizationID", "1.0"); $cbc = $Invoice->appendChild($cbc);
                $cbc = $xml->createElement("cbc:ID","RA-".$resumen[0]["periodo"]."-".$resumen[0]["nrocorrelativo"]); 
                $cbc = $Invoice->appendChild($cbc);
                $cbc = $xml->createElement("cbc:ReferenceDate",$resumen[0]["fecharesumen"]); $cbc = $Invoice->appendChild($cbc);
                $cbc = $xml->createElement("cbc:IssueDate",$resumen[0]["fecharesumen"]); $cbc = $Invoice->appendChild($cbc);

                // 2: DATOS DE LA FIRMA ELECTRONICA //

                $cac_signature = $xml->createElement("cac:Signature"); $cac_signature = $Invoice->appendChild($cac_signature);
                $cbc = $xml->createElement("cbc:ID","SignSUNAT"); $cbc = $cac_signature->appendChild($cbc);
                $cac_signatory = $xml->createElement("cac:SignatoryParty"); $cac_signatory = $cac_signature->appendChild($cac_signatory);
                $cac = $xml->createElement("cac:PartyIdentification"); $cac = $cac_signatory->appendChild($cac);
                $cbc = $xml->createElement("cbc:ID",$empresa[0]["documento"]); $cbc = $cac->appendChild($cbc);
                $cac = $xml->createElement("cac:PartyName"); $cac = $cac_signatory->appendChild($cac);
                $cbc = $xml->createElement("cbc:Name",htmlspecialchars($empresa[0]["razonsocial"]) ); $cbc = $cac->appendChild($cbc);
                $cac_digital = $xml->createElement("cac:DigitalSignatureAttachment"); $cac_digital = $cac_signature->appendChild($cac_digital);
                $cac = $xml->createElement("cac:ExternalReference"); $cac = $cac_digital->appendChild($cac);
                $cbc = $xml->createElement("cbc:URI","#SignSUNAT"); $cbc = $cac->appendChild($cbc);

                // 3: DATOS DE LA EMPRESA QUE EMITE //

                $cac_accounting = $xml->createElement("cac:AccountingSupplierParty"); $cac_accounting = $Invoice->appendChild($cac_accounting);
                $cbc = $xml->createElement("cbc:CustomerAssignedAccountID",$empresa[0]["documento"]); $cbc = $cac_accounting->appendChild($cbc);
                $cbc = $xml->createElement("cbc:AdditionalAccountID","6"); $cbc = $cac_accounting->appendChild($cbc);
                $cac_party = $xml->createElement("cac:Party"); $cac_party = $cac_accounting->appendChild($cac_party);
                    $cac = $xml->createElement("cac:PartyName"); $cac = $cac_party->appendChild($cac);
                        $cbc = $xml->createElement("cbc:Name",htmlspecialchars($empresa[0]["razonsocial"]) ); $cbc = $cac->appendChild($cbc);
                    $legal = $xml->createElement("cac:PartyLegalEntity"); $legal = $cac_party->appendChild($legal);
                        $cbc = $xml->createElement("cbc:RegistrationName",htmlspecialchars($empresa[0]["razonsocial"]) ); $cbc = $legal->appendChild($cbc);

                // 4: COMPROBANTES DEL RESUMEN DE BAJA //

                $item = 0;
                foreach ($detalle as $key => $value) { $item = $item + 1;
                    $VoidedDocumentsLine = $xml->createElement("sac:VoidedDocumentsLine"); $VoidedDocumentsLine = $Invoice->appendChild($VoidedDocumentsLine);
                    $cbc = $xml->createElement("cbc:LineID",$item); $cbc = $VoidedDocumentsLine->appendChild($cbc);
                    $cbc = $xml->createElement("cbc:DocumentTypeCode","01"); $cbc = $VoidedDocumentsLine->appendChild($cbc);
                    $sac = $xml->createElement("sac:DocumentSerialID",$value["seriecomprobante"]); $sac = $VoidedDocumentsLine->appendChild($sac);
                    $sac = $xml->createElement("sac:DocumentNumberID",$value["nrocomprobante"]); $sac = $VoidedDocumentsLine->appendChild($sac);
                    $sac = $xml->createElement("sac:VoidReasonDescription",$value["motivobaja"]); $sac = $VoidedDocumentsLine->appendChild($sac);
                }

        // FIN DE LA CREACION DEL XML //
        
        $xml->formatOutput = true;
        $xml->save($carpeta_phuyu."/".$resumen[0]["nombre_xml"].".xml"); 
        chmod($carpeta_phuyu."/".$resumen[0]["nombre_xml"].".xml", 0777);

        if (file_exists($carpeta_phuyu."/".$resumen[0]["nombre_xml"].".xml")) {
            $data["estado"] = 1; $data["carpeta_phuyu"] = $carpeta_phuyu; $data["archivo_phuyu"] = $resumen[0]["nombre_xml"];
        }else{
            $data["estado"] = 0;
        }
        return $data;
    }

    function phuyu_rb_crearXML($periodo,$nrocorrelativo,$codresumentipo){

      //  echo "estamos donde se genera el xml resumenBoletas";
        $empresa = $this->db->query("select p.documento,p.razonsocial,p.direccion,u.* 
        from public.personas as p 
        inner join public.ubigeo as u on(p.codubigeo=u.codubigeo)
        where p.codpersona=".$_SESSION["phuyu_codempresa"])->result_array();

        $resumen = $this->db->query("select *from sunat.resumenes where codresumentipo=".$codresumentipo." and periodo='".$periodo."' and nrocorrelativo=".$nrocorrelativo)->result_array();

     //   print_r( $resumen);
        if ($codresumentipo==3) {
            $detalle = $this->db->query("select dt.oficial as coddocumento, p.documento, k.seriecomprobante,k.nrocomprobante, k.igv,k.icbper,k.importe,k.codkardex from sunat.kardexsunatdetalle as ksd inner join kardex.kardex as k on(ksd.codkardex=k.codkardex) inner join public.personas as p on(k.codpersona=p.codpersona) inner join public.documentotipos as dt on(p.coddocumentotipo=dt.coddocumentotipo) where ksd.codresumentipo=".$codresumentipo." and ksd.periodo='".$periodo."' and ksd.nrocorrelativo=".$nrocorrelativo." and ksd.codempresa=".$_SESSION["phuyu_codempresa"]." order by k.seriecomprobante,k.nrocomprobante")->result_array();
            $estado = 1;
        }else{
            $detalle = $this->db->query("select dt.oficial as coddocumento, p.documento, k.seriecomprobante,k.nrocomprobante, k.igv,k.icbper,k.importe,k.codkardex from sunat.kardexsunatanulados as ksa inner join kardex.kardex as k on(ksa.codkardex=k.codkardex) inner join public.personas as p on(k.codpersona=p.codpersona) inner join public.documentotipos as dt on(p.coddocumentotipo=dt.coddocumentotipo) where ksa.codresumentipo=".$codresumentipo." and ksa.periodo='".$periodo."' and ksa.nrocorrelativo=".$nrocorrelativo." and ksa.codempresa=".$_SESSION["phuyu_codempresa"])->result_array();
            $estado = 3;
        }
        
        // 0: CREAMOS UNA CARPETA PARA ALMACENAR EL XML DEL COMPROBANTE TEMPORALMENTE //
        
        $carpeta_phuyu  = "./sunat/webphuyu/".$resumen[0]["nombre_xml"];
        if (!file_exists($carpeta_phuyu)) {   mkdir($carpeta_phuyu,0777); chmod($carpeta_phuyu, 0777);  }

        // 1.- CREAR EL DOCUMENTO XML //

        $xml = new DomDocument("1.0", "ISO-8859-1"); $xml->standalone = false; $xml->preserveWhiteSpace = false;
        $Invoice = $xml->createElement("SummaryDocuments"); $Invoice = $xml->appendChild($Invoice);
        $Invoice->setAttribute("xmlns","urn:sunat:names:specification:ubl:peru:schema:xsd:SummaryDocuments-1");
        $Invoice->setAttribute("xmlns:cac","urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
        $Invoice->setAttribute("xmlns:cbc","urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
        $Invoice->setAttribute("xmlns:ccts","urn:un:unece:uncefact:documentation:2");
        $Invoice->setAttribute("xmlns:ds","http://www.w3.org/2000/09/xmldsig#");
        $Invoice->setAttribute("xmlns:ext","urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
        $Invoice->setAttribute("xmlns:qdt","urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2");
        $Invoice->setAttribute("xmlns:sac","urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1");
        $Invoice->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $Invoice->setAttribute("xmlns:udt","urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2");
    
        $UBLExtension = $xml->createElement("ext:UBLExtensions"); $UBLExtension = $Invoice->appendChild($UBLExtension);
            $ext = $xml->createElement("ext:UBLExtension"); $ext = $UBLExtension->appendChild($ext);
            $contents = $xml->createElement("ext:ExtensionContent"); $contents = $ext->appendChild($contents);

                $cbc = $xml->createElement("cbc:UBLVersionID", "2.0"); $cbc = $Invoice->appendChild($cbc);
                $cbc = $xml->createElement("cbc:CustomizationID", "1.1"); $cbc = $Invoice->appendChild($cbc);
                $cbc = $xml->createElement("cbc:ID", "RC-".$resumen[0]["periodo"]."-".$resumen[0]["nrocorrelativo"]); 
                $cbc = $Invoice->appendChild($cbc);

                $cbc = $xml->createElement("cbc:ReferenceDate",$resumen[0]["fecharesumen"]); $cbc = $Invoice->appendChild($cbc);
                $cbc = $xml->createElement("cbc:IssueDate",$resumen[0]["fecharesumen"]); $cbc = $Invoice->appendChild($cbc);

                // 2: REFERENCIA A LA FIRMA DIGITAL //

                $cac_signature = $xml->createElement("cac:Signature"); $cac_signature = $Invoice->appendChild($cac_signature);
                $cbc = $xml->createElement("cbc:ID","SignSUNAT"); $cbc = $cac_signature->appendChild($cbc);
                $cac_signatory = $xml->createElement("cac:SignatoryParty"); $cac_signatory = $cac_signature->appendChild($cac_signatory);
                $cac = $xml->createElement("cac:PartyIdentification"); $cac = $cac_signatory->appendChild($cac);
                $cbc = $xml->createElement("cbc:ID",$empresa[0]["documento"]); $cbc = $cac->appendChild($cbc);
                $cac = $xml->createElement("cac:PartyName"); $cac = $cac_signatory->appendChild($cac);
                $cbc = $xml->createElement("cbc:Name",htmlspecialchars($empresa[0]["razonsocial"]) ); $cbc = $cac->appendChild($cbc);
                $cac_digital = $xml->createElement("cac:DigitalSignatureAttachment"); $cac_digital = $cac_signature->appendChild($cac_digital);
                $cac = $xml->createElement("cac:ExternalReference"); $cac = $cac_digital->appendChild($cac);
                $cbc = $xml->createElement("cbc:URI","#SignSUNAT"); $cbc = $cac->appendChild($cbc);

                // 3: DATOS DE LA EMPRESA QUE EMITE //

                $cac_accounting = $xml->createElement("cac:AccountingSupplierParty"); $cac_accounting = $Invoice->appendChild($cac_accounting);
                $cbc = $xml->createElement("cbc:CustomerAssignedAccountID",$empresa[0]["documento"]); $cbc = $cac_accounting->appendChild($cbc);
                $cbc = $xml->createElement("cbc:AdditionalAccountID","6"); $cbc = $cac_accounting->appendChild($cbc);
                $cac_party = $xml->createElement("cac:Party"); $cac_party = $cac_accounting->appendChild($cac_party);
                    $cac = $xml->createElement("cac:PartyName"); $cac = $cac_party->appendChild($cac);
                        $cbc = $xml->createElement("cbc:Name",htmlspecialchars($empresa[0]["razonsocial"]) ); $cbc = $cac->appendChild($cbc);
                    $legal = $xml->createElement("cac:PartyLegalEntity"); $legal = $cac_party->appendChild($legal);
                        $cbc = $xml->createElement("cbc:RegistrationName",htmlspecialchars($empresa[0]["razonsocial"]) ); $cbc = $legal->appendChild($cbc);

                // 4: BOLETAS ELECTRONICAS A ENVIAR //
                $item = 0;
                foreach ($detalle as $key => $value) { $item = $item + 1;
                    $summary = $xml->createElement("sac:SummaryDocumentsLine"); $summary = $Invoice->appendChild($summary);
                        $cbc = $xml->createElement("cbc:LineID",$item); $cbc = $summary->appendChild($cbc);
                        $cbc = $xml->createElement("cbc:DocumentTypeCode","03"); $cbc = $summary->appendChild($cbc);
                        $cbc = $xml->createElement("cbc:ID",$value["seriecomprobante"]."-".$value["nrocomprobante"]); $cbc = $summary->appendChild($cbc);

                        $cac_cli = $xml->createElement("cac:AccountingCustomerParty"); $cac_cli = $summary->appendChild($cac_cli);
                            $cbc = $xml->createElement("cbc:CustomerAssignedAccountID",$value["documento"]); $cbc = $cac_cli->appendChild($cbc);
                            $cbc = $xml->createElement("cbc:AdditionalAccountID",$value["coddocumento"]); $cbc = $cac_cli->appendChild($cbc);

                        $cac_estado = $xml->createElement("cac:Status"); $cac_estado = $summary->appendChild($cac_estado);
                            $cbc = $xml->createElement("cbc:ConditionCode",$estado); $cbc = $cac_estado->appendChild($cbc);

                        $cbc = $xml->createElement("sac:TotalAmount", number_format($value["importe"],2,".","")  ); $cbc = $summary->appendChild($cbc);
                            $cbc->setAttribute("currencyID", "PEN");

                        $gravado = $this->db->query("select COALESCE(sum(valorventa),0) as importe from kardex.kardexdetalle where codkardex=".$value["codkardex"]." and codafectacionigv='10'")->result_array();
                        $exonerado = $this->db->query("select COALESCE(sum(subtotal),0) as importe from kardex.kardexdetalle where codkardex=".$value["codkardex"]." and codafectacionigv='20'")->result_array();
                        $inafecto = $this->db->query("select COALESCE(sum(subtotal),0) as importe from kardex.kardexdetalle where codkardex=".$value["codkardex"]." and codafectacionigv='30'")->result_array();
                        $gratuito = $this->db->query("select COALESCE(sum(preciorefunitario * cantidad),0) as importe from kardex.kardexdetalle where codkardex=".$value["codkardex"]." and codafectacionigv='21'")->result_array();
                        
                        if($gravado[0]["importe"] > 0){
                            $cac_pay = $xml->createElement("sac:BillingPayment"); $cac_pay = $summary->appendChild($cac_pay);
                                $cbc = $xml->createElement("cbc:PaidAmount", number_format($gravado[0]["importe"],2,".","")  ); $cbc = $cac_pay->appendChild($cbc);
                                    $cbc->setAttribute("currencyID", "PEN");
                                $cbc = $xml->createElement("cbc:InstructionID","01"); $cbc = $cac_pay->appendChild($cbc);
                        }

                        if($exonerado[0]["importe"] > 0){
                            $cac_pay = $xml->createElement("sac:BillingPayment"); $cac_pay = $summary->appendChild($cac_pay);
                                $cbc = $xml->createElement("cbc:PaidAmount", number_format($exonerado[0]["importe"],2,".","") ); $cbc = $cac_pay->appendChild($cbc);
                                    $cbc->setAttribute("currencyID", "PEN");
                                $cbc = $xml->createElement("cbc:InstructionID","02"); $cbc = $cac_pay->appendChild($cbc);
                        }

                        if($inafecto[0]["importe"] > 0){
                            $cac_pay = $xml->createElement("sac:BillingPayment"); $cac_pay = $summary->appendChild($cac_pay);
                                $cbc = $xml->createElement("cbc:PaidAmount", number_format($inafecto[0]["importe"],2,".","") ); $cbc = $cac_pay->appendChild($cbc);
                                    $cbc->setAttribute("currencyID", "PEN");
                                $cbc = $xml->createElement("cbc:InstructionID","03"); $cbc = $cac_pay->appendChild($cbc);
                        }

                        /* if( $gratuito[0]["importe"] > 0){
                            $cac_pay = $xml->createElement("sac:BillingPayment"); $cac_pay = $summary->appendChild($cac_pay);
                                $cbc = $xml->createElement("cbc:PaidAmount", number_format($gratuito[0]["importe"],2,".","") ); $cbc = $cac_pay->appendChild($cbc);
                                    $cbc->setAttribute("currencyID", "PEN");
                                $cbc = $xml->createElement("cbc:InstructionID","05"); $cbc = $cac_pay->appendChild($cbc);
                        } */

                        $cac_total = $xml->createElement("cac:TaxTotal"); $cac_total = $summary->appendChild($cac_total);
                            $cbc = $xml->createElement("cbc:TaxAmount", "0.00" ); $cbc = $cac_total->appendChild($cbc);
                            $cbc->setAttribute("currencyID", "PEN");

                            $cac_sub = $xml->createElement("cac:TaxSubtotal"); $cac_sub = $cac_total->appendChild($cac_sub);
                                $cbc = $xml->createElement("cbc:TaxAmount", "0.00" ); $cbc = $cac_sub->appendChild($cbc);
                                    $cbc->setAttribute("currencyID", "PEN");
                                $cac_cat = $xml->createElement("cac:TaxCategory"); $cac_cat = $cac_sub->appendChild($cac_cat);
                                    $cac_esq = $xml->createElement("cac:TaxScheme"); $cac_esq = $cac_cat->appendChild($cac_esq);
                                        $cbc = $xml->createElement("cbc:ID","2000"); $cbc = $cac_esq->appendChild($cbc);
                                        $cbc = $xml->createElement("cbc:Name","ISC"); $cbc = $cac_esq->appendChild($cbc);
                                        $cbc = $xml->createElement("cbc:TaxTypeCode","EXC"); $cbc = $cac_esq->appendChild($cbc);

                        $cac_total = $xml->createElement("cac:TaxTotal"); $cac_total = $summary->appendChild($cac_total);
                            $cbc = $xml->createElement("cbc:TaxAmount",number_format($value["igv"],2,".","")); $cbc = $cac_total->appendChild($cbc);
                                $cbc->setAttribute("currencyID", "PEN");

                            $cac_sub = $xml->createElement("cac:TaxSubtotal"); $cac_sub = $cac_total->appendChild($cac_sub);
                                $cbc = $xml->createElement("cbc:TaxAmount",number_format($value["igv"],2,".","")); $cbc = $cac_sub->appendChild($cbc);
                                    $cbc->setAttribute("currencyID", "PEN");
                                $cac_cat = $xml->createElement("cac:TaxCategory"); $cac_cat = $cac_sub->appendChild($cac_cat);
                                    $cac_esq = $xml->createElement("cac:TaxScheme"); $cac_esq = $cac_cat->appendChild($cac_esq);
                                        $cbc = $xml->createElement("cbc:ID","1000"); $cbc = $cac_esq->appendChild($cbc);
                                        $cbc = $xml->createElement("cbc:Name","IGV"); $cbc = $cac_esq->appendChild($cbc);
                                        $cbc = $xml->createElement("cbc:TaxTypeCode","VAT"); $cbc = $cac_esq->appendChild($cbc);

                        $cac_total = $xml->createElement("cac:TaxTotal"); $cac_total = $summary->appendChild($cac_total);
                            $cbc = $xml->createElement("cbc:TaxAmount",number_format($value["icbper"],2,".","")); $cbc = $cac_total->appendChild($cbc);
                                $cbc->setAttribute("currencyID", "PEN");

                            $cac_sub = $xml->createElement("cac:TaxSubtotal"); $cac_sub = $cac_total->appendChild($cac_sub);
                                $cbc = $xml->createElement("cbc:TaxAmount",number_format($value["icbper"],2,".","")); $cbc = $cac_sub->appendChild($cbc);
                                    $cbc->setAttribute("currencyID", "PEN");
                                $cac_cat = $xml->createElement("cac:TaxCategory"); $cac_cat = $cac_sub->appendChild($cac_cat);
                                    $cac_esq = $xml->createElement("cac:TaxScheme"); $cac_esq = $cac_cat->appendChild($cac_esq);
                                        $cbc = $xml->createElement("cbc:ID","9999"); $cbc = $cac_esq->appendChild($cbc);
                                        $cbc = $xml->createElement("cbc:Name","OTROS"); $cbc = $cac_esq->appendChild($cbc);
                                        $cbc = $xml->createElement("cbc:TaxTypeCode","OTH"); $cbc = $cac_esq->appendChild($cbc);

                        /* if($value["icbper"]>0){
                            $cac_total = $xml->createElement("cac:TaxTotal"); $cac_total = $summary->appendChild($cac_total);
                                $cbc = $xml->createElement("cbc:TaxAmount",number_format($value["icbper"],2,".","")); $cbc = $cac_total->appendChild($cbc);
                                    $cbc->setAttribute("currencyID", "PEN");

                                $cac_icbper = $xml->createElement("cac:TaxSubtotal"); $cac_icbper = $cac_total->appendChild($cac_icbper);
                                    $cbc = $xml->createElement("cbc:TaxAmount",number_format($value["icbper"],2,".","")); $cbc = $cac_icbper->appendChild($cbc);
                                        $cbc->setAttribute("currencyID", "PEN");
                                    $cac_cat = $xml->createElement("cac:TaxCategory"); $cac_cat = $cac_icbper->appendChild($cac_cat);
                                        $cac_esq = $xml->createElement("cac:TaxScheme"); $cac_esq = $cac_cat->appendChild($cac_esq);
                                            $cbc = $xml->createElement("cbc:ID","7152"); $cbc = $cac_esq->appendChild($cbc);
                                            $cbc = $xml->createElement("cbc:Name","ICBPER"); $cbc = $cac_esq->appendChild($cbc);
                                            $cbc = $xml->createElement("cbc:TaxTypeCode","OTH"); $cbc = $cac_esq->appendChild($cbc);
                        } */
                }

        $xml->formatOutput = true;
        $xml->save($carpeta_phuyu."/".$resumen[0]["nombre_xml"].".xml"); 
        chmod($carpeta_phuyu."/".$resumen[0]["nombre_xml"].".xml", 0777);

        if (file_exists($carpeta_phuyu."/".$resumen[0]["nombre_xml"].".xml")) {
            $data["estado"] = 1; $data["carpeta_phuyu"] = $carpeta_phuyu; $data["archivo_phuyu"] = $resumen[0]["nombre_xml"];
        }else{
            $data["estado"] = 0;
        }
        return $data;
    }
}