<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . "/third_party/phuyu_facturacion/xmlseclibs.php";

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class Sunat extends CI_Controller
{

    function phuyu_firmarXML($carpeta_phuyu, $phuyu)
    {

        // 1: CARGAMOS EL ARCHIVO XML A FIRMAR //
        $doc = new DOMDocument();
        $doc->load($carpeta_phuyu . ".xml");

        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $objDSig->addReference($doc, XMLSecurityDSig::SHA1, array("http://www.w3.org/2000/09/xmldsig#enveloped-signature"), array("force_uri" => true));

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array("type" => "private"));
        $objKey->loadKey("./sunat/certificados/private_key.pem", true);
        $objDSig->sign($objKey);

        $objDSig->add509Cert(file_get_contents("./sunat/certificados/public_key.pem"), true, false, array("subjectName" => true));

        $objDSig->appendSignature($doc->getElementsByTagName("ExtensionContent")->item($phuyu));

        // 2: GUARDAMOS EL XML FIRMADO //
        $doc->save($carpeta_phuyu . ".xml");
        chmod($carpeta_phuyu . ".xml", 0777);

        if (file_exists($carpeta_phuyu . ".xml")) {
            return 1;
        } else {
            return 0;
        }
    }

    function phuyu_sendBill($carpeta_phuyu, $archivo_phuyu, $credenciales)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>' . $credenciales[0] . $credenciales[1] . '</wsse:Username>
                        <wsse:Password>' . $credenciales[2] . '</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:sendBill>
                    <fileName>' . $archivo_phuyu . '.zip</fileName>
                    <contentFile>' . base64_encode(file_get_contents($carpeta_phuyu . "/" . $archivo_phuyu . ".zip")) . '</contentFile>
                </ser:sendBill>
            </soapenv:Body>
        </soapenv:Envelope>';
        return $xml;
    }

    function phuyu_sendSummary_original($carpeta_phuyu, $archivo_phuyu, $credenciales)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>' . $credenciales[0] . $credenciales[1] . '</wsse:Username>
                        <wsse:Password>' . $credenciales[2] . '</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:sendSummary>
                    <fileName>' . $archivo_phuyu . '.zip</fileName>
                    <contentFile>' . base64_encode(file_get_contents($carpeta_phuyu . "/" . $archivo_phuyu . ".zip")) . '</contentFile>
                </ser:sendSummary>
            </soapenv:Body>
        </soapenv:Envelope>';
        return $xml;
    }
    function phuyu_sendSummary($carpeta_phuyu, $archivo_phuyu, $credenciales)
    {
        // ✅ CORREGIDO: Extraer valores compatibles con ambos formatos
        $ruc = $credenciales['ruc'] ?? $credenciales[0] ?? '';
        $usuario = $credenciales['usuario'] ?? $credenciales[1] ?? '';
        $clave = $credenciales['clave'] ?? $credenciales[2] ?? '';

        // ✅ Validar que no estén vacíos
        if (empty($ruc) || empty($usuario) || empty($clave)) {
            error_log("ERROR phuyu_sendSummary: Credenciales vacías");
            throw new Exception("Credenciales vacías para envío SUNAT");
        }

        // ✅ DEBUG: Ver qué valores se están usando
        error_log("DEBUG phuyu_sendSummary - RUC: $ruc, Usuario: $usuario");

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                <soapenv:Header>
                    <wsse:Security>
                        <wsse:UsernameToken>
                            <wsse:Username>' . $ruc . $usuario . '</wsse:Username>
                            <wsse:Password>' . $clave . '</wsse:Password>
                        </wsse:UsernameToken>
                    </wsse:Security>
                </soapenv:Header>
                <soapenv:Body>
                    <ser:sendSummary>
                        <fileName>' . $archivo_phuyu . '.zip</fileName>
                        <contentFile>' . base64_encode(file_get_contents($carpeta_phuyu . "/" . $archivo_phuyu . ".zip")) . '</contentFile>
                    </ser:sendSummary>
                </soapenv:Body>
            </soapenv:Envelope>';
        return $xml;
    }
    function phuyu_getStatus($ticket, $credenciales): string
    {

        // DEBUG: Verificar estructura completa de credenciales
        error_log("DEBUG phuyu_getStatus - Credenciales completas: " . print_r($credenciales, true));
        // Validar credenciales - ahora esperamos 7 elementos pero solo usamos los 3 primeros
        if (!is_array($credenciales) || count($credenciales) < 3) {
            $error_msg = 'phuyu_getStatus: credenciales inválidas. 
            Esperado array con al menos 3 elementos, recibido: ' . (is_array($credenciales) ? count($credenciales) : 'no-array');
            log_message('error', $error_msg);
            throw new Exception($error_msg);
        }
        // Verificar que los primeros 3 elementos no estén vacíos
        if (
            empty($credenciales['ruc'] ?? $credenciales[0] ?? null) ||
            empty($credenciales['usuario'] ?? $credenciales[1] ?? null) ||
            empty($credenciales['clave'] ?? $credenciales[2] ?? null)
        ) {
            $error_msg = 'phuyu_getStatus: credenciales vacías. RUC, usuario o clave vacíos';
            log_message('error', $error_msg);
            throw new Exception($error_msg);
        }
        // Normalizar/escapar valores (solo los 3 primeros para autenticación)
        // $ruc     = htmlspecialchars($credenciales[0], ENT_XML1);
        // $usuario = htmlspecialchars($credenciales[1], ENT_XML1);
        // $pass    = htmlspecialchars($credenciales[2], ENT_XML1);
        $ruc     = htmlspecialchars($credenciales['ruc'] ?? $credenciales[0] ?? '', ENT_XML1);
        $usuario = htmlspecialchars($credenciales['usuario'] ?? $credenciales[1] ?? '', ENT_XML1);
        $pass    = htmlspecialchars($credenciales['clave'] ?? $credenciales[2] ?? '', ENT_XML1);

        // Si la intención era concatenar ruc|usuario como username:
        $wsseUsername = $ruc . '|' . $usuario;

        // ✅ CORREGIDO: Agregar namespace wsse
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
                                    xmlns:ser="http://service.sunat.gob.pe"
                                    xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                    <soapenv:Header>
                        <wsse:Security>
                            <wsse:UsernameToken>
                                <wsse:Username>' . $wsseUsername . '</wsse:Username>
                                <wsse:Password>' . $pass . '</wsse:Password>
                            </wsse:UsernameToken>
                        </wsse:Security>
                    </soapenv:Header>
                    <soapenv:Body>
                        <ser:getStatus>
                            <ticket>' . htmlspecialchars($ticket, ENT_XML1) . '</ticket>
                        </ser:getStatus>
                    </soapenv:Body>
                    </soapenv:Envelope>';

        return $xml;
    }

    function phuyu_getStatus_original($ticket, $credenciales)
    {
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>' . $credenciales[0] . $credenciales[1] . '</wsse:Username>
                        <wsse:Password>' . $credenciales[2] . '</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:getStatus>
                    <ticket>' . $ticket . '</ticket>
                </ser:getStatus>
            </soapenv:Body>
        </soapenv:Envelope>';
        return $xml;
    }

    function phuyu_getStatusCDR($informacion, $credenciales)
    {
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>' . $credenciales[0] . $credenciales[1] . '</wsse:Username>
                        <wsse:Password>' . $credenciales[2] . '</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:getStatus>
                    <rucComprobante>' . $credenciales[0] . '</rucComprobante>
                    <tipoComprobante>' . $informacion[0] . '</tipoComprobante>
                    <serieComprobante>' . $informacion[1] . '</serieComprobante>
                    <numeroComprobante>' . $informacion[2] . '</numeroComprobante>
                </ser:getStatus>
            </soapenv:Body>
        </soapenv:Envelope>';
        return $xml;
    }

    function phuyu_getCDR($informacion, $credenciales)
    {
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>' . $credenciales[0] . $credenciales[1] . '</wsse:Username>
                        <wsse:Password>' . $credenciales[2] . '</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:getStatusCdr>
                    <rucComprobante>' . $credenciales[0] . '</rucComprobante>
                    <tipoComprobante>' . $informacion[0] . '</tipoComprobante>
                    <serieComprobante>' . $informacion[1] . '</serieComprobante>
                    <numeroComprobante>' . $informacion[2] . '</numeroComprobante>
                </ser:getStatusCdr>
            </soapenv:Body>
        </soapenv:Envelope>';
        return $xml;
    }

    function phuyu_enviarSUNAT($send, $carpeta_phuyu, $archivo_phuyu, $credenciales, $tipo = "electronico")
    {

        // 1: CREAMOS EL ARCHIVO ZIP CON EL XML DEL COMPROBANTE // 
        $this->load->library("zip");
        $this->zip->read_file($carpeta_phuyu . "/" . $archivo_phuyu . ".xml");
        $this->zip->archive($carpeta_phuyu . "/" . $archivo_phuyu . ".zip");
        chmod($carpeta_phuyu . "/" . $archivo_phuyu . ".zip", 0777);
        $webservice = $this->db->query("select * from public.webservice")->result_array();
        // NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
        $camposervice = "servicesunat";
        if ($webservice[0]["sunatose"] == 1) {
            $camposervice = "serviceose";
        }
        if ($tipo != "electronico") {
            $camposervice = $camposervice . $tipo;
        }

        // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
        if ($webservice[0]["serviceweb"] == 1) {
            $camposervice = $camposervice . "_demo";
        }
        $wsdlURL = $webservice[0][$camposervice];

        // 2: ESTRUCTURA DEL XML PARA LA CONEXION //

        if ($send == "sendSummary") {
            $XMLString = $this->phuyu_sendSummary($carpeta_phuyu, $archivo_phuyu, $credenciales);
            $result = $this->soapCall($wsdlURL, $callFunction = $send, $XMLString);

            //    print_r($result);

            if ($result["error"] == "si") {
                $estado = 0;
                $mensaje = $result["mensaje"];
            } else {
                // ✅ CORRECCIÓN: EXTRAER VARIABLES COMPATIBLES (AGREGAR ESTO AL INICIO)
                $codresumentipo = $credenciales['tipo'] ?? $credenciales[3] ?? null;
                $periodo = $credenciales['periodo'] ?? $credenciales[4] ?? null;
                $nrocorrelativo = $credenciales['nro'] ?? $credenciales[5] ?? null;
                $codempresa = $credenciales['codempresa'] ?? $credenciales[6] ?? null;

                // Validar que no estén vacíos
                if (empty($codresumentipo) || empty($periodo) || empty($nrocorrelativo) || empty($codempresa)) {
                    error_log("ERROR: Datos de resumen incompletos en credenciales");
                    return ["estado" => 0, "mensaje" => "Datos de resumen incompletos"];
                }
                // 3: DESCARGAMOS EL ARCHIVO RESPUESTA DE SUNAT //
                $archivoresponse = fopen($carpeta_phuyu . "/R-" . $archivo_phuyu . ".xml", "w+");
                fputs($archivoresponse, $result["mensaje"]);
                fclose($archivoresponse);

                // 4: LEEMOS EL ARCHIVO XML RESPONSE //
                $xml = simplexml_load_file($carpeta_phuyu . "/R-" . $archivo_phuyu . ".xml");
                foreach ($xml->xpath('//ticket') as $response) {
                    $ticket = $response;
                }

                // print_r('jola '.$ticket);exit;

                if (isset($ticket) && $ticket !== "" && $ticket !== null) {
                    // El ticket NO está vacío, null, 0, false, etc.
                    // 5: CONSULTAMOS EL TICKET //
                    $update = array("fechaenvio" => date("Y-m-d"),    "ticket" => $ticket);
                    $this->db->where("codresumentipo", $codresumentipo);
                    $this->db->where("periodo", $periodo);
                    $this->db->where("nrocorrelativo", $nrocorrelativo);
                    $this->db->where("codempresa", $codempresa);
                    $actualizarkardex = $this->db->update("sunat.resumenes", $update);

                    // 5: SI ES RESUMEN DE BOLETAS //

                    if ($codresumentipo == 3) {
                        $detalle = $this->db->query("select codkardex 
                        from sunat.kardexsunatdetalle 
                        where codresumentipo=" . $codresumentipo .
                            " and periodo='" . $periodo . "' and nrocorrelativo=" . $nrocorrelativo .
                            " and codempresa=" . $codempresa)->result_array();

                        foreach ($detalle as $value) {
                            $update = array("fechaenvio" => date("Y-m-d"));
                            $this->db->where("codkardex", $value["codkardex"]);
                            $actualizarkardex = $this->db->update("sunat.kardexsunat", $update);
                        }

                        $update = array("fechaenvio" => date("Y-m-d"));
                        $this->db->where("codresumentipo", $codresumentipo);
                        $this->db->where("periodo", $periodo);
                        $this->db->where("nrocorrelativo", $nrocorrelativo);
                        $this->db->where("codempresa", $codempresa);
                        $actualizarkardex = $this->db->update("sunat.kardexsunatdetalle", $update);
                    }

                    // 6: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //

                    foreach (glob($carpeta_phuyu . "/*") as $archivos_carpeta) {
                        if (is_dir($archivos_carpeta)) {
                            rmdir($carpeta_phuyu . "/dummy");
                        } else {
                            unlink($archivos_carpeta);
                        }
                    }
                    rmdir($carpeta_phuyu);

                    // 7: CONSULTAMOS EL TICKET //

                    $estado = $this->phuyu_consultarTICKET($archivo_phuyu, $ticket, $credenciales);
                    $mensaje = $estado["mensaje"];
                    $estado = $estado["estado"];
                } else {
                    $estado = 0;
                    $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
                }
            }
        }

        if ($send == "sendBill") {
            $XMLString = $this->phuyu_sendBill($carpeta_phuyu, $archivo_phuyu, $credenciales);
            $result = $this->soapCall($wsdlURL, $callFunction = $send, $XMLString);
            //print_r($result);exit;
            if ($result["error"] == "si") {
                $estado = 0;
                $mensaje = $result["mensaje"];
                $comprobante = $this->db->query("select *from kardex.kardex where codkardex=" . $credenciales[3])->result_array();
                $fechacomprobante = $comprobante[0]["fechacomprobante"];
                $fechacomprobante = explode("-", $fechacomprobante);
                $year = $fechacomprobante[0];
                $month = $fechacomprobante[1];
                $tipocomprobante = $this->db->query("select *from caja.comprobantetipos where codcomprobantetipo=" . $comprobante[0]["codcomprobantetipo"])->result_array();
                $informacion = [$tipocomprobante[0]["oficial"], $comprobante[0]["seriecomprobante"], $comprobante[0]["nrocomprobante"]];
                $consultarCDR = $this->phuyu_consultarSUNATCDR($informacion, $carpeta_phuyu, $archivo_phuyu, $credenciales, $tipo);

                if ($consultarCDR["estado"] != 0) {
                    $archivoresponse = fopen($carpeta_phuyu . "/C-" . $archivo_phuyu . ".xml", "w+");
                    fputs($archivoresponse, $consultarCDR["mensaje"]);
                    fclose($archivoresponse);

                    // 4: LEEMOS EL ARCHIVO XML RESPONSE //
                    $xml = simplexml_load_file($carpeta_phuyu . "/C-" . $archivo_phuyu . ".xml");

                    foreach ($xml->xpath('//content') as $response) {
                    }

                    if ($response != "") {
                        // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR AÑO//
                        $carpeta_year  = "./sunat/comprobantes/" . $year;
                        if (!file_exists($carpeta_year)) {
                            mkdir($carpeta_year, 0777);
                            chmod($carpeta_year, 0777);
                        }

                        // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR MES //
                        $carpeta_month = $carpeta_year . "/" . $month;
                        if (!file_exists($carpeta_month)) {
                            mkdir($carpeta_month, 0777);
                            chmod($carpeta_month, 0777);
                        }

                        // 5: DESCARGAMOS EL ARCHIVO CDR (CONSTANCIA DE RECEPCIÓN) //
                        $cdr = base64_decode($response);
                        $archivoresponse = fopen($carpeta_month . "/R-" . $archivo_phuyu . ".zip", "w+");
                        fputs($archivoresponse, $cdr);
                        fclose($archivoresponse);
                        // chmod($carpeta_month."/R-".$archivo_phuyu.".zip", 0777);

                        //print_r("hola");exit;

                        // 6: EXTRAEMOS EL ARCHIVO RESPUESTA //
                        $zip = new ZipArchive;
                        if ($zip->open($carpeta_month . "/R-" . $archivo_phuyu . ".zip") === TRUE) {
                            $zip->extractTo($carpeta_phuyu . "/");
                            $zip->close();
                        }

                        // 7: LEEMOS EL CDR Y ACTUALIZAMOS EN LA BASE DE DATOS EN KARDEXSUNAT //
                        $xml_respuesta = simplexml_load_file($carpeta_phuyu . "/R-" . $archivo_phuyu . '.xml');
                        foreach ($xml_respuesta->xpath('//cbc:ResponseCode') as $responsecode) {
                            $responsecode_texto = $responsecode;
                        }
                        foreach ($xml_respuesta->xpath('//cbc:Description') as $description) {
                            $description_texto = $description;
                        }

                        //print_r($responsecode_texto);

                        $descripcion_explode = explode("-", $description_texto);
                        if ($responsecode_texto == 0) {
                            $estado = 1;
                            $mensaje =  (string)($description_texto);
                        } elseif ($responsecode_texto >= 100 and $responsecode_texto <= 1999) {
                            $estado = 2;
                            $mensaje = (string)($descripcion_explode[1]);
                        } elseif ($responsecode_texto >= 2000 and $responsecode_texto <= 3999) {
                            $estado = 3;
                            $mensaje = (string)($descripcion_explode[1]);
                        } else {
                            $estado = 4;
                            $mensaje = (string)($descripcion_explode[1]);
                        }

                        $update = array(
                            "fechaenvio" => date("Y-m-d"),
                            "codigorespuesta" => $responsecode_texto,
                            "ruta_cdr" => $carpeta_month . "/R-" . $archivo_phuyu,
                            "descripcion_cdr" => $mensaje,
                            "estado" => $estado
                        );
                        $this->db->where("codkardex", $credenciales[3]);
                        $actualizarkardex = $this->db->update("sunat.kardexsunat", $update);

                        // 8: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //

                        foreach (glob($carpeta_phuyu . "/*") as $archivos_carpeta) {
                            if (is_dir($archivos_carpeta)) {
                                rmdir($carpeta_phuyu . "/dummy");
                            } else {
                                unlink($archivos_carpeta);
                            }
                        }
                        rmdir($carpeta_phuyu);
                    } else {
                        $estado = 0;
                        $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
                    }
                }
            } else {
                // 3: DESCARGAMOS EL ARCHIVO RESPUESTA DE SUNAT //
                $archivoresponse = fopen($carpeta_phuyu . "/C-" . $archivo_phuyu . ".xml", "w+");
                fputs($archivoresponse, $result["mensaje"]);
                fclose($archivoresponse);

                // 4: LEEMOS EL ARCHIVO XML RESPONSE //
                $xml = simplexml_load_file($carpeta_phuyu . "/C-" . $archivo_phuyu . ".xml");
                foreach ($xml->xpath('//applicationResponse') as $response) {
                }

                if ($response != "") {
                    // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR AÑO//
                    $carpeta_year  = "./sunat/comprobantes/" . date("Y");
                    if (!file_exists($carpeta_year)) {
                        mkdir($carpeta_year, 0777);
                        chmod($carpeta_year, 0777);
                    }

                    // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR MES //
                    $carpeta_month = $carpeta_year . "/" . date("m");
                    if (!file_exists($carpeta_month)) {
                        mkdir($carpeta_month, 0777);
                        chmod($carpeta_month, 0777);
                    }

                    // 5: DESCARGAMOS EL ARCHIVO CDR (CONSTANCIA DE RECEPCIÓN) //
                    $cdr = base64_decode($response);
                    $archivoresponse = fopen($carpeta_month . "/R-" . $archivo_phuyu . ".zip", "w+");
                    fputs($archivoresponse, $cdr);
                    fclose($archivoresponse);
                    // chmod($carpeta_month."/R-".$archivo_phuyu.".zip", 0777);

                    // 6: EXTRAEMOS EL ARCHIVO RESPUESTA //
                    $zip = new ZipArchive;
                    if ($zip->open($carpeta_month . "/R-" . $archivo_phuyu . ".zip") === TRUE) {
                        $zip->extractTo($carpeta_phuyu . "/");
                        $zip->close();
                    }

                    // 7: LEEMOS EL CDR Y ACTUALIZAMOS EN LA BASE DE DATOS EN KARDEXSUNAT //
                    $xml_respuesta = simplexml_load_file($carpeta_phuyu . "/R-" . $archivo_phuyu . '.xml');
                    foreach ($xml_respuesta->xpath('//cbc:ResponseCode') as $responsecode) {
                        $responsecode_texto = $responsecode;
                    }
                    foreach ($xml_respuesta->xpath('//cbc:Description') as $description) {
                        $description_texto = $description;
                    }

                    //print_r($responsecode_texto);

                    $descripcion_explode = explode("-", $description_texto);
                    if ($responsecode_texto == 0) {
                        $estado = 1;
                        $mensaje =  (string)($description_texto);
                    } elseif ($responsecode_texto >= 100 and $responsecode_texto <= 1999) {
                        $estado = 2;
                        $mensaje = (string)($descripcion_explode[1]);
                    } elseif ($responsecode_texto >= 2000 and $responsecode_texto <= 3999) {
                        $estado = 3;
                        $mensaje = (string)($descripcion_explode[1]);
                    } else {
                        $estado = 4;
                        $mensaje = (string)($descripcion_explode[1]);
                    }

                    $update = array(
                        "fechaenvio" => date("Y-m-d"),
                        "codigorespuesta" => $responsecode_texto,
                        "ruta_cdr" => $carpeta_month . "/R-" . $archivo_phuyu,
                        "descripcion_cdr" => $mensaje,
                        "estado" => $estado
                    );
                    $this->db->where("codkardex", $credenciales[3]);
                    $actualizarkardex = $this->db->update("sunat.kardexsunat", $update);

                    // 8: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //

                    foreach (glob($carpeta_phuyu . "/*") as $archivos_carpeta) {
                        if (is_dir($archivos_carpeta)) {
                            rmdir($carpeta_phuyu . "/dummy");
                        } else {
                            unlink($archivos_carpeta);
                        }
                    }
                    rmdir($carpeta_phuyu);
                } else {
                    $estado = 0;
                    $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
                }
            }
        }

        $data["estado"] = $estado;
        $data["mensaje"] = $mensaje;
        return $data;
    }

    function phuyu_enviarSUNATGUIA($send, $carpeta_phuyu, $archivo_phuyu, $credenciales, $tipo = "electronico")
    {

        // 1: CREAMOS EL ARCHIVO ZIP CON EL XML DEL COMPROBANTE //

        $this->load->library("zip");
        $this->zip->read_file($carpeta_phuyu . "/" . $archivo_phuyu . ".xml");
        $this->zip->archive($carpeta_phuyu . "/" . $archivo_phuyu . ".zip");
        chmod($carpeta_phuyu . "/" . $archivo_phuyu . ".zip", 0777);

        $webservice = $this->db->query("select * from public.webservice")->result_array();

        // NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
        $camposervice = "servicesunatguia";
        if ($webservice[0]["sunatose"] == 1) {
            $camposervice = "serviceose";
        }

        if ($tipo != "electronico") {
            $camposervice = $camposervice . $tipo;
        }

        // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
        if ($webservice[0]["serviceweb"] == 1) {
            $camposervice = $camposervice . "_demo";
        }
        $wsdlURL = $webservice[0][$camposervice];

        // 2: ESTRUCTURA DEL XML PARA LA CONEXION //

        if ($send == "sendSummary") {
            $XMLString = $this->phuyu_sendSummary($carpeta_phuyu, $archivo_phuyu, $credenciales);
            $result = $this->soapCall($wsdlURL, $callFunction = $send, $XMLString);

            if ($result["error"] == "si") {
                $estado = 0;
                $mensaje = $result["mensaje"];
            } else {
                // 3: DESCARGAMOS EL ARCHIVO RESPUESTA DE SUNAT //
                $archivoresponse = fopen($carpeta_phuyu . "/R-" . $archivo_phuyu . ".xml", "w+");
                fputs($archivoresponse, $result["mensaje"]);
                fclose($archivoresponse);

                // 4: LEEMOS EL ARCHIVO XML RESPONSE //
                $xml = simplexml_load_file($carpeta_phuyu . "/R-" . $archivo_phuyu . ".xml");
                foreach ($xml->xpath('//ticket') as $response) {
                    $ticket = $response;
                }

                //print_r('jola '.$ticket);exit;

                if ($ticket != "") {

                    // 5: CONSULTAMOS EL TICKET //

                    $update = array(
                        "fechaenvio" => date("Y-m-d"),
                        "ticket" => $ticket
                    );
                    $this->db->where("codresumentipo", $credenciales[3]);
                    $this->db->where("periodo", $credenciales[4]);
                    $this->db->where("nrocorrelativo", $credenciales[5]);
                    $this->db->where("codempresa", $credenciales[6]);
                    $actualizarkardex = $this->db->update("sunat.resumenes", $update);

                    // 5: SI ES RESUMEN DE BOLETAS //

                    if ($credenciales[3] == 3) {
                        $detalle = $this->db->query("select codkardex from sunat.kardexsunatdetalle where codresumentipo=" . $credenciales[3] . " and periodo='" . $credenciales[4] . "' and nrocorrelativo=" . $credenciales[5] . " and codempresa=" . $credenciales[6])->result_array();
                        foreach ($detalle as $value) {
                            $update = array(
                                "fechaenvio" => date("Y-m-d")
                            );
                            $this->db->where("codkardex", $value["codkardex"]);
                            $actualizarkardex = $this->db->update("sunat.kardexsunat", $update);
                        }

                        $update = array(
                            "fechaenvio" => date("Y-m-d")
                        );
                        $this->db->where("codresumentipo", $credenciales[3]);
                        $this->db->where("periodo", $credenciales[4]);
                        $this->db->where("nrocorrelativo", $credenciales[5]);
                        $this->db->where("codempresa", $credenciales[6]);
                        $actualizarkardex = $this->db->update("sunat.kardexsunatdetalle", $update);
                    }

                    // 6: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //

                    foreach (glob($carpeta_phuyu . "/*") as $archivos_carpeta) {
                        if (is_dir($archivos_carpeta)) {
                            rmdir($carpeta_phuyu . "/dummy");
                        } else {
                            unlink($archivos_carpeta);
                        }
                    }
                    rmdir($carpeta_phuyu);

                    // 7: CONSULTAMOS EL TICKET //

                    $estado = $this->phuyu_consultarTICKET($archivo_phuyu, $ticket, $credenciales);
                    $mensaje = $estado["mensaje"];
                    $estado = $estado["estado"];
                } else {
                    $estado = 0;
                    $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
                }
            }
        }

        if ($send == "sendBill") {
            $XMLString = $this->phuyu_sendBill($carpeta_phuyu, $archivo_phuyu, $credenciales);
            $result = $this->soapCall($wsdlURL, $callFunction = $send, $XMLString);
            //print_r($result);exit;
            if ($result["error"] == "si") {
                $estado = 0;
                $mensaje = $result["mensaje"];
            } else {
                // 3: DESCARGAMOS EL ARCHIVO RESPUESTA DE SUNAT //
                $archivoresponse = fopen($carpeta_phuyu . "/C-" . $archivo_phuyu . ".xml", "w+");
                fputs($archivoresponse, $result["mensaje"]);
                fclose($archivoresponse);

                // 4: LEEMOS EL ARCHIVO XML RESPONSE //
                $xml = simplexml_load_file($carpeta_phuyu . "/C-" . $archivo_phuyu . ".xml");
                foreach ($xml->xpath('//applicationResponse') as $response) {
                }

                if ($response != "") {
                    // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR AÑO//
                    $carpeta_year  = "./sunat/comprobantes/" . date("Y");
                    if (!file_exists($carpeta_year)) {
                        mkdir($carpeta_year, 0777);
                        chmod($carpeta_year, 0777);
                    }

                    // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR MES //
                    $carpeta_month = $carpeta_year . "/" . date("m");
                    if (!file_exists($carpeta_month)) {
                        mkdir($carpeta_month, 0777);
                        chmod($carpeta_month, 0777);
                    }

                    // 5: DESCARGAMOS EL ARCHIVO CDR (CONSTANCIA DE RECEPCIÓN) //
                    $cdr = base64_decode($response);
                    $archivoresponse = fopen($carpeta_month . "/R-" . $archivo_phuyu . ".zip", "w+");
                    fputs($archivoresponse, $cdr);
                    fclose($archivoresponse);
                    // chmod($carpeta_month."/R-".$archivo_phuyu.".zip", 0777);

                    // 6: EXTRAEMOS EL ARCHIVO RESPUESTA //
                    $zip = new ZipArchive;
                    if ($zip->open($carpeta_month . "/R-" . $archivo_phuyu . ".zip") === TRUE) {
                        $zip->extractTo($carpeta_phuyu . "/");
                        $zip->close();
                    }

                    // 7: LEEMOS EL CDR Y ACTUALIZAMOS EN LA BASE DE DATOS EN KARDEXSUNAT //
                    $xml_respuesta = simplexml_load_file($carpeta_phuyu . "/R-" . $archivo_phuyu . '.xml');
                    foreach ($xml_respuesta->xpath('//cbc:ResponseCode') as $responsecode) {
                        $responsecode_texto = $responsecode;
                    }
                    foreach ($xml_respuesta->xpath('//cbc:Description') as $description) {
                        $description_texto = $description;
                    }

                    //print_r($responsecode_texto);

                    $descripcion_explode = explode("-", $description_texto);
                    if ($responsecode_texto == 0) {
                        $estado = 1;
                        $mensaje =  (string)($description_texto);
                    } elseif ($responsecode_texto >= 100 and $responsecode_texto <= 1999) {
                        $estado = 2;
                        $mensaje = (string)($descripcion_explode[1]);
                    } elseif ($responsecode_texto >= 2000 and $responsecode_texto <= 3999) {
                        $estado = 3;
                        $mensaje = (string)($descripcion_explode[1]);
                    } else {
                        $estado = 4;
                        $mensaje = (string)($descripcion_explode[1]);
                    }

                    $update = array(
                        "fechaenvio" => date("Y-m-d"),
                        "codigorespuesta" => $responsecode_texto,
                        "ruta_cdr" => $carpeta_month . "/R-" . $archivo_phuyu,
                        "descripcion_cdr" => $mensaje,
                        "estado" => $estado
                    );
                    $this->db->where("codguiar", $credenciales[3]);
                    $actualizarkardex = $this->db->update("sunat.guiasunat", $update);

                    // 8: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //

                    foreach (glob($carpeta_phuyu . "/*") as $archivos_carpeta) {
                        if (is_dir($archivos_carpeta)) {
                            rmdir($carpeta_phuyu . "/dummy");
                        } else {
                            unlink($archivos_carpeta);
                        }
                    }
                    rmdir($carpeta_phuyu);
                } else {
                    $estado = 0;
                    $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
                }
            }
        }

        $data["estado"] = $estado;
        $data["mensaje"] = $mensaje;
        return $data;
    }

    function phuyu_consultarTICKET_original($nombre_xml, $ticket, $credenciales, $tipo = "electronico")
    {

        $webservice = $this->db->query("select * from public.webservice")->result_array();
        // NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
        $camposervice = "servicesunat";
        if ($webservice[0]["sunatose"] == 1) {
            $camposervice = "serviceose";
        }
        if ($tipo != "electronico") {
            $camposervice = $camposervice . $tipo;
        }
        // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
        if ($webservice[0]["serviceweb"] == 1) {
            $camposervice = $camposervice . "_demo";
        }
        $wsdlURL = $webservice[0][$camposervice];

        // DEBUG: Verificar credenciales
        error_log("DEBUG - Credenciales recibidas: " . print_r($credenciales, true));
        error_log("DEBUG - Número de elementos: " . count($credenciales));

        if (!is_array($credenciales) || count($credenciales) < 3) {
            error_log("ERROR: Credenciales inválidas o insuficientes");
            return ["estado" => 0, "mensaje" => "Credenciales inválidas"];
        }
        // 1: ESTRUCTURA PARA LA CONEXION //
        $XMLString = $this->phuyu_getStatus($ticket, $credenciales);
        //print_r($XMLString);exit;
        $result = $this->soapCall($wsdlURL, $callFunction = "getStatus", $XMLString);
        //print_r($result);exit;
        if ($result["error"] == "si") {
            $estado = 0;
            $mensaje = $result["mensaje"];
        } else {
            // 3: DESCARGAMOS EL ARCHIVO RESPUESTA DE SUNAT //
            //echo $ticket;exit;
            $carpeta_phuyu  = "./sunat/webphuyu/" . $ticket;
            if (!file_exists($carpeta_phuyu)) {
                mkdir($carpeta_phuyu, 0777);
                chmod($carpeta_phuyu, 0777);
            }

            $archivoresponse = fopen($carpeta_phuyu . "/R-" . $ticket . ".xml", "w+");
            fputs($archivoresponse, $result["mensaje"]);
            fclose($archivoresponse);

            // 4: LEEMOS EL ARCHIVO XML //
            $xml = simplexml_load_file($carpeta_phuyu . "/R-" . $ticket . ".xml");
            //print_r($xml);exit;
            foreach ($xml->xpath('//content') as $response) {
            }
            //print_r($response);exit;
            if ($response != "") {
                // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS TICKETS POR AÑO//
                $carpeta_year  = "./sunat/resumenes/" . date("Y");
                if (!file_exists($carpeta_year)) {
                    mkdir($carpeta_year, 0777);
                    chmod($carpeta_year, 0777);
                }

                // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR MES //
                $carpeta_month = $carpeta_year . "/" . date("m");
                if (!file_exists($carpeta_month)) {
                    mkdir($carpeta_month, 0777);
                    chmod($carpeta_month, 0777);
                }

                // 5: DESCARGAMOS EL ARCHIVO CDR (CONSTANCIA DE RECEPCIÓN) //
                $cdr = base64_decode($response);
                //print_r($cdr);exit;
                $archivoresponse = fopen($carpeta_month . "/R-" . $ticket . ".zip", "w+");
                fputs($archivoresponse, $cdr);
                fclose($archivoresponse);
                chmod($carpeta_month . "/R-" . $ticket . ".zip", 0777);

                // 6: EXTRAEMOS EL ARCHIVO RESPUESTA //
                $zip = new ZipArchive;
                if ($zip->open($carpeta_month . "/R-" . $ticket . ".zip") === TRUE) {
                    $zip->extractTo($carpeta_phuyu . "/");
                    $zip->close();
                }

                //echo $nombre_xml;exit;

                /*if(file_exists($carpeta_phuyu."/R-".$nombre_xml.'.xml')){
                    echo 'si';
                }else{
                    echo 'no';
                }
                exit;*/

                // 7: LEEMOS EL CDR Y ACTUALIZAMOS EN LA BASE DE DATOS EN RESUMENES //
                $xml_respuesta = simplexml_load_file($carpeta_phuyu . "/R-" . $nombre_xml . '.xml');
                foreach ($xml_respuesta->xpath('//cbc:ResponseCode') as $responsecode) {
                    $responsecode_texto = $responsecode;
                }
                foreach ($xml_respuesta->xpath('//cbc:Description') as $description) {
                    $description_texto = $description;
                }

                $descripcion_explode = explode("-", $description_texto);
                if ($responsecode_texto == 0) {
                    $estado = 1;
                    $mensaje =  (string)($description_texto);
                } elseif ($responsecode_texto >= 100 and $responsecode_texto <= 1999) {
                    $estado = 2;
                    $mensaje = (string)($descripcion_explode[1]);
                } elseif ($responsecode_texto >= 2000 and $responsecode_texto <= 3999) {
                    $estado = 3;
                    $mensaje = (string)($descripcion_explode[1]);
                } else {
                    $estado = 4;
                    $mensaje = (string)($descripcion_explode[1]);
                }

                $update = array(
                    "codigorespuesta" => $responsecode_texto,
                    "ruta_cdr" => $carpeta_month . "/R-" . $ticket,
                    "descripcion_cdr" => $mensaje,
                    "estado" => $estado
                );
                $this->db->where("codresumentipo", $credenciales[3]);
                $this->db->where("periodo", $credenciales[4]);
                $this->db->where("nrocorrelativo", $credenciales[5]);
                $this->db->where("codempresa", $credenciales[6]);
                $actualizarkardex = $this->db->update("sunat.resumenes", $update);

                // 7: ACTUALIZAMOS LOS CAMPOS DE LAS TALAS DE SUNAT //

                if ($credenciales[3] == 1 || $credenciales[3] == 4) {
                    $detalle = $this->db->query("select codkardex from sunat.kardexsunatanulados where codresumentipo=" . $credenciales[3] . " and periodo='" . $credenciales[4] . "' and nrocorrelativo=" . $credenciales[5] . " and codempresa=" . $credenciales[6])->result_array();
                    foreach ($detalle as $value) {
                        $update = array(
                            "estado" => $estado
                        );
                        $this->db->where("codkardex", $value["codkardex"]);
                        $actualizarkardex = $this->db->update("sunat.kardexsunatanulados", $update);
                    }
                }

                if ($credenciales[3] == 3) {
                    $detalle = $this->db->query("select codkardex from sunat.kardexsunatdetalle where codresumentipo=" . $credenciales[3] . " and periodo='" . $credenciales[4] . "' and nrocorrelativo=" . $credenciales[5] . " and codempresa=" . $credenciales[6])->result_array();
                    foreach ($detalle as $value) {
                        $update = array(
                            "codigorespuesta" => $responsecode_texto,
                            "ruta_cdr" => $carpeta_month . "/R-" . $ticket,
                            "descripcion_cdr" => $mensaje,
                            "estado" => $estado
                        );
                        $this->db->where("codkardex", $value["codkardex"]);
                        $actualizarkardex = $this->db->update("sunat.kardexsunat", $update);
                    }

                    $update = array(
                        "descripcion_cdr" => $mensaje,
                        "estado" => $estado
                    );
                    $this->db->where("codresumentipo", $credenciales[3]);
                    $this->db->where("periodo", $credenciales[4]);
                    $this->db->where("nrocorrelativo", $credenciales[5]);
                    $this->db->where("codempresa", $credenciales[6]);
                    $actualizarkardex = $this->db->update("sunat.kardexsunatdetalle", $update);
                }

                // 8: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //

                foreach (glob($carpeta_phuyu . "/*") as $archivos_carpeta) {
                    if (is_dir($archivos_carpeta)) {
                        rmdir($carpeta_phuyu . "/dummy");
                    } else {
                        unlink($archivos_carpeta);
                    }
                }
                rmdir($carpeta_phuyu);
            } else {
                $estado = 0;
                $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
            }
        }

        $data["estado"] = $estado;
        $data["mensaje"] = $mensaje;
        return $data;
    }

    function phuyu_consultarTICKET_nuevo_error_lectura($nombre_xml, $ticket, $credenciales, $tipo = "electronico", $log = null)
    {

        $webservice = $this->db->query("select * from public.webservice")->result_array();
        //  print_r($webservice);
        // NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
        $camposervice = "servicesunat";
        //valida si es ose para reasignar sino continua como sunat
        if ($webservice[0]["sunatose"] == 1) {
            $camposervice = "serviceose";
        }
        if ($tipo != "electronico") {
            $camposervice = $camposervice . $tipo;
        }
        // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
        if ($webservice[0]["serviceweb"] == 1) {
            $camposervice = $camposervice . "_demo";
        }

        $wsdlURL = $webservice[0][$camposervice];

        // DEBUG: Verificar credenciales
        error_log("DEBUG phuyu_consultarTICKET - Credenciales recibidas: " . print_r($credenciales, true));
        error_log("DEBUG - Número de elementos: " . count($credenciales));

        if (!is_array($credenciales) || count($credenciales) < 7) {
            error_log("ERROR: Credenciales inválidas o insuficientes. Se esperaban 7 elementos");
            return ["estado" => 0, "mensaje" => "Credenciales inválidas"];
        }

        // DEBUG: Verificar cada elemento individual
        error_log("DEBUG - Credenciales[0] RUC: " . ($credenciales['ruc'] ?? $credenciales[0] ?? "VACÍO"));
        error_log("DEBUG - Credenciales[1] Usuario: " . ($credenciales['usuario'] ?? $credenciales[1] ?? "VACÍO"));
        error_log("DEBUG - Credenciales[2] Clave: " . ($credenciales['clave'] ?? $credenciales[2] ??  "VACÍO"));

        // 1: ESTRUCTURA PARA LA CONEXION //
        $XMLString = $this->phuyu_getStatus($ticket, $credenciales);
        $result = $this->soapCall($wsdlURL, $callFunction = "getStatus", $XMLString);
        //echo $XMLString;
        if ($result["error"] == "si") {
            $estado = 0;
            $mensaje = $result["mensaje"];
            // ✅ NUEVO: Si el ticket no existe, retornar inmediatamente
            if (strpos($mensaje, 'ticket no existe') !== false) {
                error_log("TICKET NO EXISTE: " . $ticket);
                return ["estado" => 0, "mensaje" => "El ticket no existe o ha expirado"];
            }
        } else {
            // 3: DESCARGAMOS EL ARCHIVO RESPUESTA DE SUNAT //
            $carpeta_phuyu  = "./sunat/webphuyu/" . $ticket;
            //print($carpeta_phuyu );

            // CORREGIDO: Crear directorio de forma segura
            if (!is_dir($carpeta_phuyu)) {
                if (!mkdir($carpeta_phuyu, 0755, true)) {
                    error_log("ERROR: No se pudo crear directorio: " . $carpeta_phuyu);
                    return ["estado" => 0, "mensaje" => "Error creando directorio temporal"];
                }
                // chmod no es necesario si mkdir usa 0755
            }
            // ✅ CORREGIDO: Usar $nombre_xml
            $archivoresponse = fopen($carpeta_phuyu . "/R-" . $nombre_xml . ".xml", "w+");

            // print_r( $ticket) ;
            if ($archivoresponse === false) {
                error_log("ERROR: No se pudo crear archivo XML");
                return ["estado" => 0, "mensaje" => "Error creando archivo temporal"];
            }
            fputs($archivoresponse, $result["mensaje"]);
            fclose($archivoresponse);

            // 4: LEEMOS EL ARCHIVO XML //
            $xml = simplexml_load_file($carpeta_phuyu . "/R-" . $nombre_xml . ".xml");
            //print_r($xml);exit;
            foreach ($xml->xpath('//content') as $response) {
            }
            //print_r($response);exit;
            if ($response != "") {
                // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS TICKETS POR AÑO//
                $carpeta_year  = "./sunat/resumenes/" . date("Y");
                if (!file_exists($carpeta_year)) {
                    mkdir($carpeta_year, 0755, true);
                }

                // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR MES //
                $carpeta_month = $carpeta_year . "/" . date("m");
                if (!file_exists($carpeta_month)) {
                    mkdir($carpeta_month, 0755, true);
                }

                // 5: DESCARGAMOS EL ARCHIVO CDR (CONSTANCIA DE RECEPCIÓN) //
                $cdr = base64_decode($response);
                //print_r($cdr);exit;
                $archivoresponse = fopen($carpeta_month . "/R-" . $ticket . ".zip", "w+");
                fputs($archivoresponse, $cdr);
                fclose($archivoresponse);

                // 6: EXTRAEMOS EL ARCHIVO RESPUESTA //
                $zip = new ZipArchive;
                if ($zip->open($carpeta_month . "/R-" . $ticket . ".zip") === TRUE) {
                    $zip->extractTo($carpeta_phuyu . "/");
                    $zip->close();
                }
                // 7: LEEMOS EL CDR Y ACTUALIZAMOS EN LA BASE DE DATOS EN RESUMENES //

                $xml_respuesta = simplexml_load_file($carpeta_phuyu . "/R-" . $nombre_xml . '.xml');
                // ✅ DEBUG: Verificar que el XML se cargó
                error_log("=== DEBUG phuyu_consultarTICKET ===");
                error_log("XML cargado: " . ($xml_respuesta ? "SÍ" : "NO"));
                error_log("Buscando en archivo: " . $carpeta_phuyu . "/R-" . $nombre_xml . '.xml');

                if ($log && is_callable($log)) {
                    $log("XML cargado: " . ($xml_respuesta ? "SÍ" : "NO"));
                }
                error_log("XML cargado: " . ($xml_respuesta ? "SÍ" : "NO"));



                // ✅ AGREGAR ESTAS 3 LÍNEAS (REGISTRO DE NAMESPACES):
                $xml_respuesta->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
                $xml_respuesta->registerXPathNamespace('sac', 'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');
                $xml_respuesta->registerXPathNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

                // ✅ INICIALIZAR VARIABLES (IMPORTANTE)
                $responsecode_texto = '';
                $description_texto = '';

                // ✅ AHORA SÍ PUEDES USAR XPATH
                foreach ($xml_respuesta->xpath('//cbc:ResponseCode') as $responsecode) {
                    $responsecode_texto = (string)$responsecode;
                }
                foreach ($xml_respuesta->xpath('//cbc:Description') as $description) {
                    $description_texto = (string)$description;
                }

                // ✅ SI NO ENCUENTRA, BUSCAR SIN NAMESPACE
                if (empty($responsecode_texto)) {
                    error_log("Buscando ResponseCode sin namespace...");
                    foreach ($xml_respuesta->xpath('//ResponseCode') as $responsecode) {
                        $responsecode_texto = (string)$responsecode;
                    }
                }

                if (empty($description_texto)) {
                    error_log("Buscando Description sin namespace...");
                    foreach ($xml_respuesta->xpath('//Description') as $description) {
                        $description_texto = (string)$description;
                    }
                }

                // ✅ DEBUG: VER QUÉ SE ENCONTRÓ
                error_log("ResponseCode encontrado: " . $responsecode_texto);
                error_log("Description encontrado: " . $description_texto);

                // ✅ VALIDAR QUE NO ESTÉN VACÍOS
                if (empty($responsecode_texto) || empty($description_texto)) {
                    error_log("ERROR: No se pudieron extraer datos del CDR");
                    return ["estado" => 0, "mensaje" => "Error leyendo respuesta de SUNAT"];
                }


                $descripcion_explode = explode("-", $description_texto);
                if ($responsecode_texto == 0) {
                    $estado = 1;
                    $mensaje =  (string)($description_texto);
                } elseif ($responsecode_texto >= 100 and $responsecode_texto <= 1999) {
                    $estado = 2;
                    $mensaje = (string)($descripcion_explode[1]);
                } elseif ($responsecode_texto >= 2000 and $responsecode_texto <= 3999) {
                    $estado = 3;
                    $mensaje = (string)($descripcion_explode[1]);
                } else {
                    $estado = 4;
                    $mensaje = (string)($descripcion_explode[1]);
                }

                $update = array(
                    "codigorespuesta" => $responsecode_texto,
                    "ruta_cdr" => $carpeta_month . "/R-" . $ticket,
                    "descripcion_cdr" => $mensaje,
                    "estado" => $estado
                );

                // ✅ CORRECCIÓN: EXTRAER VARIABLES COMPATIBLES (AGREGAR ESTO AL INICIO)
                $codresumentipo = $credenciales['tipo'] ?? $credenciales[3] ?? null;
                $periodo = $credenciales['periodo'] ?? $credenciales[4] ?? null;
                $nrocorrelativo = $credenciales['nro'] ?? $credenciales[5] ?? null;
                $codempresa = $credenciales['codempresa'] ?? $credenciales[6] ?? null;
                $actualizarkardex = $this->db->update("sunat.resumenes", $update);

                // 7: ACTUALIZAMOS LOS CAMPOS DE LAS TALAS DE SUNAT //
                // ✅ CORREGIR: Usar $codresumentipo en lugar de $credenciales[3]
                if ($codresumentipo == 1 || $codresumentipo == 4) {
                    $detalle = $this->db->query("select codkardex from sunat.kardexsunatanulados where codresumentipo=" . $codresumentipo . " and periodo='" . $periodo . "' and nrocorrelativo=" . $nrocorrelativo . " and codempresa=" . $codempresa)->result_array();
                    foreach ($detalle as $value) {
                        $update = array(
                            "estado" => $estado
                        );
                        $this->db->where("codkardex", $value["codkardex"]);
                        $actualizarkardex = $this->db->update("sunat.kardexsunatanulados", $update);
                    }
                }

                if ($codresumentipo == 3) {
                    $detalle = $this->db->query("select codkardex from sunat.kardexsunatdetalle where codresumentipo=" . $codresumentipo . " and periodo='" . $periodo . "' and nrocorrelativo=" . $nrocorrelativo . " and codempresa=" . $codempresa)->result_array();
                    foreach ($detalle as $value) {
                        $update = array(
                            "codigorespuesta" => $responsecode_texto,
                            "ruta_cdr" => $carpeta_month . "/R-" . $ticket,
                            "descripcion_cdr" => $mensaje,
                            "estado" => $estado
                        );
                        $this->db->where("codkardex", $value["codkardex"]);
                        $actualizarkardex = $this->db->update("sunat.kardexsunat", $update);
                    }

                    $update = array(
                        "descripcion_cdr" => $mensaje,
                        "estado" => $estado
                    );
                    // ✅ CORREGIR: Usar las variables extraídas
                    $this->db->where("codresumentipo", $codresumentipo);
                    $this->db->where("periodo", $periodo);
                    $this->db->where("nrocorrelativo", $nrocorrelativo);
                    $this->db->where("codempresa", $codempresa);
                    $actualizarkardex = $this->db->update("sunat.kardexsunatdetalle", $update);
                }

                // 8: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //

                foreach (glob($carpeta_phuyu . "/*") as $archivos_carpeta) {
                    if (is_dir($archivos_carpeta)) {
                        rmdir($carpeta_phuyu . "/dummy");
                    } else {
                        unlink($archivos_carpeta);
                    }
                }
                rmdir($carpeta_phuyu);
            } else {
                $estado = 0;
                $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
            }
        }

        $data["estado"] = $estado;
        $data["mensaje"] = $mensaje;
        return $data;
    }
    function phuyu_consultarTICKET($nombre_xml, $ticket, $credenciales, $tipo = "electronico", $log = null)
    {
        // 1. CONFIGURACIÓN DEL WEBSERVICE
        $webservice = $this->db->query("SELECT * FROM public.webservice")->result_array();

        $camposervice = "servicesunat";
        if ($webservice[0]["sunatose"] == 1) {
            $camposervice = "serviceose";
        }
        if ($tipo != "electronico") {
            $camposervice = $camposervice . $tipo;
        }
        if ($webservice[0]["serviceweb"] == 1) {
            $camposervice = $camposervice . "_demo";
        }

        $wsdlURL = $webservice[0][$camposervice];

        // 2. VALIDACIÓN DE CREDENCIALES
        if (!is_array($credenciales) || count($credenciales) < 7) {
            error_log("ERROR: Credenciales inválidas");
            return ["estado" => 0, "mensaje" => "Credenciales inválidas"];
        }

        // 3. CONSULTA DEL TICKET A SUNAT
        $XMLString = $this->phuyu_getStatus($ticket, $credenciales);
        $result = $this->soapCall($wsdlURL, "getStatus", $XMLString);

        if ($result["error"] == "si") {
            $mensaje = $result["mensaje"];
            if (strpos($mensaje, 'ticket no existe') !== false) {
                return ["estado" => 0, "mensaje" => "El ticket no existe o ha expirado"];
            }
            return ["estado" => 0, "mensaje" => $mensaje];
        }

        // 4. PROCESAMIENTO DE RESPUESTA EXITOSA
        $carpeta_phuyu = "./sunat/webphuyu/" . $ticket;

        // Crear directorio temporal
        if (!is_dir($carpeta_phuyu) && !mkdir($carpeta_phuyu, 0755, true)) {
            return ["estado" => 0, "mensaje" => "Error creando directorio temporal"];
        }

        // Guardar respuesta XML
        $archivo_respuesta = $carpeta_phuyu . "/R-" . $nombre_xml . ".xml";
        if (!file_put_contents($archivo_respuesta, $result["mensaje"])) {
            return ["estado" => 0, "mensaje" => "Error creando archivo temporal"];
        }

        // Leer respuesta
        $xml = simplexml_load_file($archivo_respuesta);

        print($xml); return exit;

        foreach ($xml->xpath('//content') as $response) {
        }

        if (empty($response)) {
            return ["estado" => 0, "mensaje" => "NO HAY RESPUESTA DE LA SUNAT"];
        }

        // 5. PROCESAR CDR (CONSTANCIA DE RECEPCIÓN)
        $carpeta_year = "./sunat/resumenes/" . date("Y");
        $carpeta_month = $carpeta_year . "/" . date("m");

        if (!is_dir($carpeta_year)) mkdir($carpeta_year, 0755, true);
        if (!is_dir($carpeta_month)) mkdir($carpeta_month, 0755, true);

        // Guardar CDR ZIP
        $cdr = base64_decode($response);
        $archivo_zip = $carpeta_month . "/R-" . $ticket . ".zip";
        file_put_contents($archivo_zip, $cdr);

        // Extraer ZIP
        $zip = new ZipArchive;
        if ($zip->open($archivo_zip) === TRUE) {
            $zip->extractTo($carpeta_phuyu . "/");
            $zip->close();
        }

        // 6. LEER Y PROCESAR CDR XML
        $xml_respuesta = simplexml_load_file($carpeta_phuyu . "/R-" . $nombre_xml . '.xml');

        // Registrar namespaces
        $xml_respuesta->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml_respuesta->registerXPathNamespace('sac', 'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');

        // Extraer datos del CDR
        $responsecode_texto = '';
        $description_texto = '';

        foreach ($xml_respuesta->xpath('//cbc:ResponseCode') as $responsecode) {
            $responsecode_texto = (string)$responsecode;
        }
        foreach ($xml_respuesta->xpath('//cbc:Description') as $description) {
            $description_texto = (string)$description;
        }

        // Fallback sin namespace
        if (empty($responsecode_texto)) {
            foreach ($xml_respuesta->xpath('//ResponseCode') as $responsecode) {
                $responsecode_texto = (string)$responsecode;
            }
        }
        if (empty($description_texto)) {
            foreach ($xml_respuesta->xpath('//Description') as $description) {
                $description_texto = (string)$description;
            }
        }

        if (empty($responsecode_texto) || empty($description_texto)) {
            return ["estado" => 0, "mensaje" => "Error leyendo respuesta de SUNAT"];
        }

        // 7. DETERMINAR ESTADO Y MENSAJE
        $descripcion_explode = explode("-", $description_texto);
        $mensaje_final = $descripcion_explode[1] ?? $description_texto;

        switch (true) {
            case $responsecode_texto == 0:
                $estado = 1;
                $mensaje = (string)$description_texto;
                break;
            case $responsecode_texto >= 100 && $responsecode_texto <= 1999:
                $estado = 2;
                $mensaje = (string)$mensaje_final;
                break;
            case $responsecode_texto >= 2000 && $responsecode_texto <= 3999:
                $estado = 3;
                $mensaje = (string)$mensaje_final;
                break;
            default:
                $estado = 4;
                $mensaje = (string)$mensaje_final;
        }

        // 8. ACTUALIZAR BASE DE DATOS
        $codresumentipo = $credenciales['tipo'] ?? $credenciales[3] ?? null;
        $periodo = $credenciales['periodo'] ?? $credenciales[4] ?? null;
        $nrocorrelativo = $credenciales['nro'] ?? $credenciales[5] ?? null;
        $codempresa = $credenciales['codempresa'] ?? $credenciales[6] ?? null;

        $update = [
            "codigorespuesta" => $responsecode_texto,
            "ruta_cdr" => $carpeta_month . "/R-" . $ticket,
            "descripcion_cdr" => $mensaje,
            "estado" => $estado
        ];

        $this->db->where("codresumentipo", $codresumentipo)
            ->where("periodo", $periodo)
            ->where("nrocorrelativo", $nrocorrelativo)
            ->where("codempresa", $codempresa)
            ->update("sunat.resumenes", $update);

        // 9. LIMPIAR ARCHIVOS TEMPORALES
        array_map('unlink', glob($carpeta_phuyu . "/*"));
        rmdir($carpeta_phuyu);

        return ["estado" => $estado, "mensaje" => $mensaje];
    }





    function phuyu_consultarSUNAT($informacion, $credenciales, $tipo = "electronico")
    {
        $webservice = $this->db->query("select * from public.webservice")->result_array();

        // NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
        $camposervice = "servicesunat";
        if ($webservice[0]["sunatose"] == 1) {
            $camposervice = "serviceose";
        }

        if ($tipo != "electronico") {
            $camposervice = $camposervice . $tipo;
        }

        // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
        if ($webservice[0]["serviceweb"] == 1) {
            $camposervice = $camposervice . "_demo";
        }

        if ($tipo == "electronico") {
            if ($webservice[0][$camposervice] == "./sunat/billService.wsdl") {
                $wsdlURL = "https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl";
            } else {
                $wsdlURL = "https://www.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl";
            }
        } else {
            $wsdlURL = $webservice[0][$camposervice];
        }

        $XMLString = $this->phuyu_getStatusCDR($informacion, $credenciales);
        $result = $this->soapCall($wsdlURL, $callFunction = "getStatus", $XMLString);
        //print_r($result);
        if ($result["error"] == "si") {
            $estado = 0;
            $mensaje = $result["mensaje"];
        } else {
            $estado = 1;
            $mensaje = $result["mensaje"];
        }

        $data["estado"] = $estado;
        $data["mensaje"] = $mensaje;
        return $data;
    }

    function phuyu_consultarSUNATCDR($informacion, $carpeta_phuyu, $archivo_phuyu, $credenciales, $tipo = "electronico")
    {
        $webservice = $this->db->query("select * from public.webservice")->result_array();

        // NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
        $camposervice = "servicesunat";
        if ($webservice[0]["sunatose"] == 1) {
            $camposervice = "serviceose";
        }

        if ($tipo != "electronico") {
            $camposervice = $camposervice . $tipo;
        }

        // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
        if ($webservice[0]["serviceweb"] == 1) {
            $camposervice = $camposervice . "_demo";
        }

        if ($tipo == "electronico") {
            if ($webservice[0][$camposervice] == "./sunat/billService.wsdl") {
                $wsdlURL = "https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl";
            } else {
                $wsdlURL = "https://www.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl";
            }
        } else {
            $wsdlURL = $webservice[0][$camposervice];
        }

        $XMLString = $this->phuyu_getCDR($informacion, $credenciales);
        $result = $this->soapCall($wsdlURL, $callFunction = "getStatus", $XMLString);
        //print_r($result);
        if ($result["error"] == "si") {
            $estado = 0;
            $mensaje = $result["mensaje"];
        } else {
            $estado = 1;
            $mensaje = $result["mensaje"];
        }

        $data["estado"] = $estado;
        $data["mensaje"] = $mensaje;
        return $data;
    }

    function soapCall($wsdlURL, $callFunction = "", $XMLString)
    {
        $client = new funcionSoap($wsdlURL, array("trace" => true));
        try {
            $reply  = $client->SoapClientCall($XMLString);
            $client->__call("$callFunction", array(), array());
            return array("error" => "no", "mensaje" => $client->__getLastResponse());
        } catch (Exception $e) {
            return array("error" => "si", "mensaje" => $client->__getLastResponse());
        }
    }

    function phuyu_qrcode($textoqr)
    {
        $this->load->library('ciqrcode');
        $params['data'] = $textoqr;
        $params['level'] = 'H';
        $params['size'] = 5;
        $params['savename'] = "./sunat/webphuyu/qrcode.png";
        $this->ciqrcode->generate($params);
        // chmod("./sunat/webphuyu/qrcode.png", 0777);

        $archivo_error = APPPATH . "/logs/qrcode.png-errors.txt";
        unlink($archivo_error);

        return 1;
    }
}

class funcionSoap extends SoapClient
{
    public $XMLStr = "";

    public function setXMLStr($value)
    {
        $this->XMLStr = $value;
    }

    public function getXMLStr()
    {
        return $this->XMLStr;
    }

    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $request = $this->XMLStr;
        $dom = new DOMDocument("1.0");
        try {
            $dom->loadXML($request);
        } catch (DOMException $e) {
            die($e->code);
        }
        $request = $dom->saveXML();
        //Para la solicitud //
        return parent::__doRequest($request, $location, $action, $version, $one_way = 0);
    }

    public function SoapClientCall($SOAPXML)
    {
        return $this->setXMLStr($SOAPXML);
    }
}
