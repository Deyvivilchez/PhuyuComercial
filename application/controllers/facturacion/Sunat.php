<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH."/third_party/phuyu_facturacion/xmlseclibs.php";
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class Sunat extends CI_Controller {

    protected function phuyu_respuesta_cpe($estado, $mensaje, $alerta = null){
        $data = array("estado" => $estado, "mensaje" => $mensaje);
        if ($alerta !== null) {
            $data["alerta"] = $alerta;
        }
        return $data;
    }

    protected function phuyu_descripcion_cdr($descripcion){
        $partes = explode("-", (string)$descripcion, 2);
        return isset($partes[1]) ? trim($partes[1]) : (string)$descripcion;
    }

    protected function phuyu_normalizar_error_sunat($mensaje, $respuesta = "", $operacion = ""){
        $texto = trim((string)$mensaje);
        $respuesta = trim((string)$respuesta);
        $base = $respuesta !== "" ? $respuesta : $texto;

        $faultcode = "";
        $faultstring = "";
        if ($base !== "" && preg_match('/<faultcode[^>]*>(.*?)<\/faultcode>/is', $base, $m)) {
            $faultcode = trim(strip_tags($m[1]));
        }
        if ($base !== "" && preg_match('/<faultstring[^>]*>(.*?)<\/faultstring>/is', $base, $m)) {
            $faultstring = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, "UTF-8"));
        }

        if ($faultstring !== "") {
            if (strpos($faultcode, "0140") !== false) {
                return "SUNAT ya tiene este resumen en proceso. Espere 15 minutos y vuelva a consultar/enviar. Detalle SUNAT: ".$faultstring;
            }
            return "SUNAT rechazo la solicitud".($faultcode !== "" ? " (".$faultcode.")" : "").": ".$faultstring;
        }

        if (stripos($texto, "Bad Request") !== false) {
            return "SUNAT devolvio Bad Request. Normalmente significa que el resumen ya fue recibido y esta en proceso. Espere 15 minutos y vuelva a consultar o enviar; no lo regenere todavia.";
        }

        return $texto !== "" ? $texto : "SUNAT no devolvio una respuesta interpretable para ".$operacion.".";
    }

	function phuyu_firmarXML($carpeta_phuyu,$phuyu,$respuesta_detallada = false){
        $xml_file = $carpeta_phuyu.".xml";
        $private_key_file = "./sunat/certificados/private_key.pem";
        $public_key_file = "./sunat/certificados/public_key.pem";

        try {
            if (!is_readable($xml_file)) {
                throw new Exception("No se puede leer el XML a firmar: ".$xml_file);
            }
            if (!is_readable($private_key_file)) {
                throw new Exception("No se puede leer la clave privada: ".$private_key_file);
            }
            if (!is_readable($public_key_file)) {
                throw new Exception("No se puede leer el certificado publico: ".$public_key_file);
            }

        // 1: CARGAMOS EL ARCHIVO XML A FIRMAR //
        $doc = new DOMDocument();
        if (!$doc->load($xml_file)) {
            throw new Exception("El XML no es valido o no se pudo cargar: ".$xml_file);
        }
        
        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $objDSig->addReference($doc,XMLSecurityDSig::SHA1,array("http://www.w3.org/2000/09/xmldsig#enveloped-signature"),array("force_uri" => true));

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array("type" => "private"));
        $objKey->loadKey($private_key_file, true);
        $objDSig->sign($objKey);

        $objDSig->add509Cert(file_get_contents($public_key_file), true, false, array("subjectName" => true));

        $objDSig->appendSignature($doc->getElementsByTagName("ExtensionContent")->item($phuyu));
        
        // 2: GUARDAMOS EL XML FIRMADO //
        if ($doc->save($xml_file) === false) {
            throw new Exception("No se pudo guardar el XML firmado: ".$xml_file);
        }
        chmod($xml_file, 0777);
        
        if (file_exists($xml_file)) {
            return $respuesta_detallada ? $this->phuyu_respuesta_cpe(1, "XML firmado correctamente") : 1;
        }
        throw new Exception("El XML firmado no existe despues de guardar: ".$xml_file);
        } catch (Throwable $e) {
            return $respuesta_detallada ? $this->phuyu_respuesta_cpe(0, $e->getMessage()) : 0;
        }
    }

    function phuyu_sendBill($carpeta_phuyu, $archivo_phuyu, $credenciales){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>'.$credenciales[0].$credenciales[1].'</wsse:Username>
                        <wsse:Password>'.$credenciales[2].'</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:sendBill>
                    <fileName>'.$archivo_phuyu.'.zip</fileName>
                    <contentFile>'.base64_encode(file_get_contents($carpeta_phuyu."/".$archivo_phuyu.".zip")).'</contentFile>
                </ser:sendBill>
            </soapenv:Body>
        </soapenv:Envelope>';
        return $xml;
    }
    
    function phuyu_sendSummary($carpeta_phuyu, $archivo_phuyu, $credenciales){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>'.$credenciales[0].$credenciales[1].'</wsse:Username>
                        <wsse:Password>'.$credenciales[2].'</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:sendSummary>
                    <fileName>'.$archivo_phuyu.'.zip</fileName>
                    <contentFile>'.base64_encode(file_get_contents($carpeta_phuyu."/".$archivo_phuyu.".zip")).'</contentFile>
                </ser:sendSummary>
            </soapenv:Body>
        </soapenv:Envelope>';
        return $xml;
    }
    
    function phuyu_getStatus($ticket, $credenciales){
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>'.$credenciales[0].$credenciales[1].'</wsse:Username>
                        <wsse:Password>'.$credenciales[2].'</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:getStatus>
                    <ticket>'.$ticket.'</ticket>
                </ser:getStatus>
            </soapenv:Body>
        </soapenv:Envelope>';
        return $xml;
    }

    function phuyu_getStatusCDR($informacion, $credenciales){
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>'.$credenciales[0].$credenciales[1].'</wsse:Username>
                        <wsse:Password>'.$credenciales[2].'</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:getStatus>
                    <rucComprobante>'.$credenciales[0].'</rucComprobante>
                    <tipoComprobante>'.$informacion[0].'</tipoComprobante>
                    <serieComprobante>'.$informacion[1].'</serieComprobante>
                    <numeroComprobante>'.$informacion[2].'</numeroComprobante>
                </ser:getStatus>
            </soapenv:Body>
        </soapenv:Envelope>';
        return $xml;
    }

    function phuyu_getCDR($informacion, $credenciales){
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>'.$credenciales[0].$credenciales[1].'</wsse:Username>
                        <wsse:Password>'.$credenciales[2].'</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:getStatusCdr>
                    <rucComprobante>'.$credenciales[0].'</rucComprobante>
                    <tipoComprobante>'.$informacion[0].'</tipoComprobante>
                    <serieComprobante>'.$informacion[1].'</serieComprobante>
                    <numeroComprobante>'.$informacion[2].'</numeroComprobante>
                </ser:getStatusCdr>
            </soapenv:Body>
        </soapenv:Envelope>';
        return $xml;
    }

function phuyu_enviarSUNAT($send, $carpeta_phuyu, $archivo_phuyu, $credenciales, $tipo = "electronico"){
    
    // 1: CREAMOS EL ARCHIVO ZIP CON EL XML DEL COMPROBANTE //
    $this->load->library("zip");
    $this->zip->clear_data();
    $this->zip->read_file($carpeta_phuyu."/".$archivo_phuyu.".xml");
    $this->zip->archive($carpeta_phuyu."/".$archivo_phuyu.".zip");
    $this->zip->clear_data();
    chmod($carpeta_phuyu."/".$archivo_phuyu.".zip", 0777);

    $webservice = $this->db->query("select * from public.webservice")->result_array();
    
    // NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
    $camposervice = "servicesunat";
    if ($webservice[0]["sunatose"] == 1) {
        $camposervice = "serviceose";
    }

    if ($tipo != "electronico") {
        $camposervice = $camposervice.$tipo;
    }

    // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
    if ($webservice[0]["serviceweb"] == 1) {
        $camposervice = $camposervice."_demo";
    }

    $wsdlURL = $webservice[0][$camposervice];
    
    // 2: ESTRUCTURA DEL XML PARA LA CONEXION //
    if ($send == "sendSummary") {
        $XMLString = $this->phuyu_sendSummary($carpeta_phuyu, $archivo_phuyu, $credenciales);
        $result = $this->soapCall($wsdlURL, $send, $XMLString);
        
        if ($result["error"] == "si") {
            $estado = 0;
            $mensaje = $result["mensaje"];
            $respuesta_sunat = isset($result["respuesta"]) ? $result["respuesta"] : "";
        } else {
            // 3: DESCARGAMOS EL ARCHIVO RESPUESTA DE SUNAT //
            $archivoresponse = fopen($carpeta_phuyu."/R-".$archivo_phuyu.".xml", "w+");
            fputs($archivoresponse, $result["mensaje"]);
            fclose($archivoresponse);

            // 4: LEEMOS EL ARCHIVO XML RESPONSE //
            $xml = simplexml_load_file($carpeta_phuyu."/R-".$archivo_phuyu.".xml");

            $ticket = "";
            if ($xml !== false) {
                foreach ($xml->xpath('//ticket') as $item) {
                    $ticket = (string) $item;
                }
            }

            if ($ticket !== "") {
                // 5: CONSULTAMOS EL TICKET //
                $update = array(
                    "fechaenvio" => date("Y-m-d"),
                    "ticket" => $ticket
                );
                $this->db->where("codresumentipo", $credenciales[3]);
                $this->db->where("periodo", $credenciales[4]);
                $this->db->where("nrocorrelativo", $credenciales[5]);
                $this->db->where("codempresa", $credenciales[6]);
                $this->db->update("sunat.resumenes", $update);

                // 5: SI ES RESUMEN DE BOLETAS //
                if ($credenciales[3] == 3) {
                    $detalle = $this->db->query("select codkardex from sunat.kardexsunatdetalle where codresumentipo=".$credenciales[3]." and periodo='".$credenciales[4]."' and nrocorrelativo=".$credenciales[5]." and codempresa=".$credenciales[6])->result_array();

                    foreach ($detalle as $value) {
                        $update = array(
                            "fechaenvio" => date("Y-m-d")
                        );
                        $this->db->where("codkardex", $value["codkardex"]);
                        $this->db->update("sunat.kardexsunat", $update);
                    }

                    $update = array(
                        "fechaenvio" => date("Y-m-d")
                    );
                    $this->db->where("codresumentipo", $credenciales[3]);
                    $this->db->where("periodo", $credenciales[4]);
                    $this->db->where("nrocorrelativo", $credenciales[5]);
                    $this->db->where("codempresa", $credenciales[6]);
                    $this->db->update("sunat.kardexsunatdetalle", $update);
                }

                // 6: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //
                foreach (glob($carpeta_phuyu . "/*") as $archivos_carpeta) {
                    if (is_dir($archivos_carpeta)) {
                        @rmdir($carpeta_phuyu."/dummy");
                    } else {
                        @unlink($archivos_carpeta);
                    }
                }
                @rmdir($carpeta_phuyu);

                // 7: CONSULTAMOS EL TICKET //
                $resultado_ticket = $this->phuyu_consultarTICKET($archivo_phuyu, $ticket, $credenciales);
                $mensaje = $resultado_ticket["mensaje"];
                $estado  = $resultado_ticket["estado"];
            } else {
                $estado = 0;
                $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
            }
        }
    }
    
    if ($send == "sendBill") {
        $XMLString = $this->phuyu_sendBill($carpeta_phuyu, $archivo_phuyu, $credenciales);
        $result = $this->soapCall($wsdlURL, $send, $XMLString);

        if ($result["error"] == "si") {
            $estado = 0;
            $mensaje = $result["mensaje"];

            $comprobante = $this->db->query("select * from kardex.kardex where codkardex=".$credenciales[3])->result_array();

            if (!empty($comprobante)) {
                $fechacomprobante = explode("-", $comprobante[0]["fechacomprobante"]);
                $year = $fechacomprobante[0];
                $month = $fechacomprobante[1];

                $tipocomprobante = $this->db->query("select * from caja.comprobantetipos where codcomprobantetipo=".$comprobante[0]["codcomprobantetipo"])->result_array();

                if (!empty($tipocomprobante)) {
                    $informacion = array(
                        $tipocomprobante[0]["oficial"],
                        $comprobante[0]["seriecomprobante"],
                        $comprobante[0]["nrocomprobante"]
                    );

                    $consultarCDR = $this->phuyu_consultarSUNATCDR($informacion, $carpeta_phuyu, $archivo_phuyu, $credenciales, $tipo);

                    if ($consultarCDR["estado"] != 0) {
                        $archivoresponse = fopen($carpeta_phuyu."/C-".$archivo_phuyu.".xml", "w+");
                        fputs($archivoresponse, $consultarCDR["mensaje"]);
                        fclose($archivoresponse);

                        // 4: LEEMOS EL ARCHIVO XML RESPONSE //
                        $xml = simplexml_load_file($carpeta_phuyu."/C-".$archivo_phuyu.".xml");

                        $response = "";
                        if ($xml !== false) {
                            foreach ($xml->xpath('//content') as $item) {
                                $response = (string) $item;
                            }
                        }

                        if ($response !== "") {
                            // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR AÑO//
                            $carpeta_year = "./sunat/comprobantes/".$year;
                            if (!file_exists($carpeta_year)) {
                                mkdir($carpeta_year, 0777);
                                chmod($carpeta_year, 0777);
                            }

                            // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR MES //
                            $carpeta_month = $carpeta_year."/".$month;
                            if (!file_exists($carpeta_month)) {
                                mkdir($carpeta_month, 0777);
                                chmod($carpeta_month, 0777);
                            }

                            // 5: DESCARGAMOS EL ARCHIVO CDR //
                            $cdr = base64_decode($response);
                            $archivoresponse = fopen($carpeta_month."/R-".$archivo_phuyu.".zip", "w+");
                            fputs($archivoresponse, $cdr);
                            fclose($archivoresponse);

                            // 6: EXTRAEMOS EL ARCHIVO RESPUESTA //
                            $zip = new ZipArchive;
                            if ($zip->open($carpeta_month."/R-".$archivo_phuyu.".zip") === TRUE) {
                                $zip->extractTo($carpeta_phuyu."/");
                                $zip->close();
                            }

                            // 7: LEEMOS EL CDR Y ACTUALIZAMOS EN LA BASE DE DATOS EN KARDEXSUNAT //
                            $xml_respuesta = simplexml_load_file($carpeta_phuyu."/R-".$archivo_phuyu.'.xml');

                            $responsecode_texto = "";
                            $description_texto = "";

                            if ($xml_respuesta !== false) {
                                foreach ($xml_respuesta->xpath('//cbc:ResponseCode') as $responsecode) {
                                    $responsecode_texto = (string) $responsecode;
                                }
                                foreach ($xml_respuesta->xpath('//cbc:Description') as $description) {
                                    $description_texto = (string) $description;
                                }
                            }

                            $responsecode_texto = trim((string) $responsecode_texto);
                            $responsecode_numero = is_numeric($responsecode_texto) ? (int) $responsecode_texto : null;

                            if ($responsecode_texto === "0") {
                                $estado = 1;
                                $mensaje = (string) $description_texto;
                            } elseif ($responsecode_numero !== null && $responsecode_numero >= 100 && $responsecode_numero <= 1999) {
                                $estado = 2;
                                $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                            } elseif ($responsecode_numero !== null && $responsecode_numero >= 2000 && $responsecode_numero <= 3999) {
                                $estado = 3;
                                $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                            } elseif ($responsecode_numero !== null) {
                                $estado = 4;
                                $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                            } else {
                                $estado = 0;
                                $mensaje = "NO SE PUDO LEER EL CODIGO DE RESPUESTA DEL CDR";
                            }

                            $update = array(
                                "fechaenvio" => date("Y-m-d"),
                                "codigorespuesta" => $responsecode_texto,
                                "ruta_cdr" => $carpeta_month."/R-".$archivo_phuyu,
                                "descripcion_cdr" => $mensaje,
                                "estado" => $estado
                            );
                            $this->db->where("codkardex", $credenciales[3]);
                            $this->db->update("sunat.kardexsunat", $update);

                            // 8: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //
                            foreach (glob($carpeta_phuyu . "/*") as $archivos_carpeta) {
                                if (is_dir($archivos_carpeta)) {
                                    @rmdir($carpeta_phuyu."/dummy");
                                } else {
                                    @unlink($archivos_carpeta);
                                }
                            }
                            @rmdir($carpeta_phuyu);
                        } else {
                            $estado = 0;
                            $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
                        }
                    }
                } else {
                    $estado = 0;
                    $mensaje = $consultarCDR["mensaje"];
                }
            }
        } else {
            // 3: DESCARGAMOS EL ARCHIVO RESPUESTA DE SUNAT //
            $archivoresponse = fopen($carpeta_phuyu."/C-".$archivo_phuyu.".xml", "w+");
            fputs($archivoresponse, $result["mensaje"]);
            fclose($archivoresponse);

            // 4: LEEMOS EL ARCHIVO XML RESPONSE //
            $xml = simplexml_load_file($carpeta_phuyu."/C-".$archivo_phuyu.".xml");

            $response = "";
            if ($xml !== false) {
                foreach ($xml->xpath('//applicationResponse') as $item) {
                    $response = (string) $item;
                }
            }

            if ($response !== "") {
                // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR AÑO//
                $carpeta_year = "./sunat/comprobantes/".date("Y");
                if (!file_exists($carpeta_year)) {
                    mkdir($carpeta_year, 0777);
                    chmod($carpeta_year, 0777);
                }

                // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR MES //
                $carpeta_month = $carpeta_year."/".date("m");
                if (!file_exists($carpeta_month)) {
                    mkdir($carpeta_month, 0777);
                    chmod($carpeta_month, 0777);
                }

                // 5: DESCARGAMOS EL ARCHIVO CDR //
                $cdr = base64_decode($response);
                $archivoresponse = fopen($carpeta_month."/R-".$archivo_phuyu.".zip", "w+");
                fputs($archivoresponse, $cdr);
                fclose($archivoresponse);

                // 6: EXTRAEMOS EL ARCHIVO RESPUESTA //
                $zip = new ZipArchive;
                if ($zip->open($carpeta_month."/R-".$archivo_phuyu.".zip") === TRUE) {
                    $zip->extractTo($carpeta_phuyu."/");
                    $zip->close();
                }

                // 7: LEEMOS EL CDR Y ACTUALIZAMOS EN LA BASE DE DATOS EN KARDEXSUNAT //
                $xml_respuesta = simplexml_load_file($carpeta_phuyu."/R-".$archivo_phuyu.'.xml');

                $responsecode_texto = "";
                $description_texto = "";

                if ($xml_respuesta !== false) {
                    foreach ($xml_respuesta->xpath('//cbc:ResponseCode') as $responsecode) {
                        $responsecode_texto = (string) $responsecode;
                    }
                    foreach ($xml_respuesta->xpath('//cbc:Description') as $description) {
                        $description_texto = (string) $description;
                    }
                }

                $responsecode_texto = trim((string) $responsecode_texto);
                $responsecode_numero = is_numeric($responsecode_texto) ? (int) $responsecode_texto : null;

                if ($responsecode_texto === "0") {
                    $estado = 1;
                    $mensaje = (string) $description_texto;
                } elseif ($responsecode_numero !== null && $responsecode_numero >= 100 && $responsecode_numero <= 1999) {
                    $estado = 2;
                    $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                } elseif ($responsecode_numero !== null && $responsecode_numero >= 2000 && $responsecode_numero <= 3999) {
                    $estado = 3;
                    $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                } elseif ($responsecode_numero !== null) {
                    $estado = 4;
                    $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                } else {
                    $estado = 0;
                    $mensaje = "NO SE PUDO LEER EL CODIGO DE RESPUESTA DEL CDR";
                }

                $update = array(
                    "fechaenvio" => date("Y-m-d"),
                    "codigorespuesta" => $responsecode_texto,
                    "ruta_cdr" => $carpeta_month."/R-".$archivo_phuyu,
                    "descripcion_cdr" => $mensaje,
                    "estado" => $estado
                );
                $this->db->where("codkardex", $credenciales[3]);
                $this->db->update("sunat.kardexsunat", $update);

                // 8: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //
                foreach (glob($carpeta_phuyu . "/*") as $archivos_carpeta) {
                    if (is_dir($archivos_carpeta)) {
                        @rmdir($carpeta_phuyu."/dummy");
                    } else {
                        @unlink($archivos_carpeta);
                    }
                }
                @rmdir($carpeta_phuyu);
            } else {
                $estado = 0;
                $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
            }
        }
    }

    $data["estado"] = $estado;
    $data["mensaje"] = $mensaje;
    if (isset($respuesta_sunat) && $respuesta_sunat !== "") {
        $data["respuesta"] = $respuesta_sunat;
    }
    return $data;
}

    function phuyu_enviarSUNATGUIA($send, $carpeta_phuyu, $archivo_phuyu, $credenciales, $tipo = "electronico"){
        
        // 1: CREAMOS EL ARCHIVO ZIP CON EL XML DEL COMPROBANTE //

        $this->load->library("zip");
        $this->zip->clear_data();
        $this->zip->read_file($carpeta_phuyu."/".$archivo_phuyu.".xml");
        $this->zip->archive($carpeta_phuyu."/".$archivo_phuyu.".zip");
        $this->zip->clear_data();
        chmod($carpeta_phuyu."/".$archivo_phuyu.".zip", 0777);

        $webservice = $this->db->query("select * from public.webservice")->result_array();
        
        // NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
        $camposervice = "servicesunatguia";
        if ($webservice[0]["sunatose"]==1) {
            $camposervice = "serviceose";
        }

        if ($tipo!="electronico") {
            $camposervice = $camposervice.$tipo;
        }

        // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
        if ($webservice[0]["serviceweb"]==1) {
            $camposervice = $camposervice."_demo";
        }
        $wsdlURL = $webservice[0][$camposervice];
        
        // 2: ESTRUCTURA DEL XML PARA LA CONEXION //

        if($send=="sendSummary"){
            $XMLString = $this->phuyu_sendSummary($carpeta_phuyu, $archivo_phuyu, $credenciales);
            $result = $this->soapCall($wsdlURL, $callFunction = $send, $XMLString);
            
            if($result["error"] == "si"){
                $estado = 0; $mensaje = $result["mensaje"];
            }else{
                // 3: DESCARGAMOS EL ARCHIVO RESPUESTA DE SUNAT //
                $archivoresponse = fopen($carpeta_phuyu."/R-".$archivo_phuyu.".xml","w+");
                fputs($archivoresponse,$result["mensaje"]); fclose($archivoresponse);

                // 4: LEEMOS EL ARCHIVO XML RESPONSE //
                $xml = simplexml_load_file($carpeta_phuyu."/R-".$archivo_phuyu.".xml"); 
                foreach ($xml->xpath('//ticket') as $response){ 
                    $ticket = $response;
                }

                //print_r('jola '.$ticket);exit;

                if($ticket != ""){
                    // 5: CONSULTAMOS EL TICKET //

                    $update = array(
                        "fechaenvio" => date("Y-m-d"), 
                        "ticket" => $ticket
                    );
                    $this->db->where("codresumentipo",$credenciales[3]);
                    $this->db->where("periodo",$credenciales[4]);
                    $this->db->where("nrocorrelativo",$credenciales[5]);
                    $this->db->where("codempresa",$credenciales[6]);
                    $actualizarkardex = $this->db->update("sunat.resumenes", $update);

                    // 5: SI ES RESUMEN DE BOLETAS //

                    if ($credenciales[3]==3) {
                        $detalle = $this->db->query("select codkardex from sunat.kardexsunatdetalle where codresumentipo=".$credenciales[3]." and periodo='".$credenciales[4]."' and nrocorrelativo=".$credenciales[5]." and codempresa=".$credenciales[6])->result_array();
                        foreach ($detalle as $value) {
                            $update = array(
                                "fechaenvio" => date("Y-m-d")
                            );
                            $this->db->where("codkardex",$value["codkardex"]);
                            $actualizarkardex = $this->db->update("sunat.kardexsunat", $update);
                        }

                        $update = array(
                            "fechaenvio" => date("Y-m-d")
                        );
                        $this->db->where("codresumentipo",$credenciales[3]);
                        $this->db->where("periodo",$credenciales[4]);
                        $this->db->where("nrocorrelativo",$credenciales[5]);
                        $this->db->where("codempresa",$credenciales[6]);
                        $actualizarkardex = $this->db->update("sunat.kardexsunatdetalle", $update);
                    }

                    // 6: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //

                    foreach(glob($carpeta_phuyu . "/*") as $archivos_carpeta){             
                        if (is_dir($archivos_carpeta)){
                            rmdir($carpeta_phuyu."/dummy");
                        } else {
                            unlink($archivos_carpeta);
                        }
                    }
                    rmdir($carpeta_phuyu);

                    // 7: CONSULTAMOS EL TICKET //

                    $estado = $this->phuyu_consultarTICKET($archivo_phuyu, $ticket, $credenciales);
                    $mensaje = $estado["mensaje"]; $estado = $estado["estado"];
                }else{
                    $estado = 0; $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
                }
            }
        }
        
        if($send=="sendBill"){
            $XMLString = $this->phuyu_sendBill($carpeta_phuyu, $archivo_phuyu, $credenciales);
            $result = $this->soapCall($wsdlURL, $callFunction = $send, $XMLString);
            //print_r($result);exit;
            if($result["error"] == "si"){
                $estado = 0; $mensaje = $result["mensaje"];
            }else{
                // 3: DESCARGAMOS EL ARCHIVO RESPUESTA DE SUNAT //
                $archivoresponse = fopen($carpeta_phuyu."/C-".$archivo_phuyu.".xml","w+");
                fputs($archivoresponse,$result["mensaje"]); fclose($archivoresponse);

                // 4: LEEMOS EL ARCHIVO XML RESPONSE //
                $xml = simplexml_load_file($carpeta_phuyu."/C-".$archivo_phuyu.".xml");
                $response = "";
                if ($xml !== false) {
                    foreach ($xml->xpath('//applicationResponse') as $item){
                        $response = (string)$item;
                    }
                }

                if($response != ""){
                    // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR AÑO//
                    $carpeta_year  = "./sunat/comprobantes/".date("Y");
                    if (!file_exists($carpeta_year)) { 
                        mkdir($carpeta_year,0777); chmod($carpeta_year, 0777);
                    }
                    
                    // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR MES //
                    $carpeta_month = $carpeta_year."/".date("m");
                    if (!file_exists($carpeta_month)) { 
                        mkdir($carpeta_month,0777); chmod($carpeta_month, 0777);
                    }

                    // 5: DESCARGAMOS EL ARCHIVO CDR (CONSTANCIA DE RECEPCIÓN) //
                    $cdr = base64_decode($response);
                    $archivoresponse = fopen($carpeta_month."/R-".$archivo_phuyu.".zip","w+");
                    fputs($archivoresponse, $cdr); fclose($archivoresponse);
                    // chmod($carpeta_month."/R-".$archivo_phuyu.".zip", 0777);

                    // 6: EXTRAEMOS EL ARCHIVO RESPUESTA //
                    $zip = new ZipArchive;
                    if ($zip->open($carpeta_month."/R-".$archivo_phuyu.".zip") === TRUE){
                        $zip->extractTo($carpeta_phuyu."/"); $zip->close();
                    }

                    // 7: LEEMOS EL CDR Y ACTUALIZAMOS EN LA BASE DE DATOS EN KARDEXSUNAT //
                    $xml_respuesta = simplexml_load_file($carpeta_phuyu."/R-".$archivo_phuyu.'.xml');
                    $responsecode_texto = "";
                    $description_texto = "";
                    if ($xml_respuesta !== false) {
                        foreach ($xml_respuesta->xpath('//cbc:ResponseCode') as $responsecode){ 
                            $responsecode_texto = (string)$responsecode;
                        }
                        foreach ($xml_respuesta->xpath('//cbc:Description') as $description) {
                            $description_texto = (string)$description;
                        }
                    }

                    $responsecode_texto = trim((string) $responsecode_texto);
                    $responsecode_numero = is_numeric($responsecode_texto) ? (int) $responsecode_texto : null;

                    if($responsecode_texto === "0"){    
                        $estado = 1; $mensaje =  (string)($description_texto);
                    }elseif($responsecode_numero !== null and $responsecode_numero >= 100 and $responsecode_numero<=1999){
                        $estado = 2; $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                    }elseif($responsecode_numero !== null and $responsecode_numero >= 2000 and $responsecode_numero<=3999){
                        $estado = 3; $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                    }elseif($responsecode_numero !== null){
                        $estado = 4; $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                    }else{
                        $estado = 0; $mensaje = "NO SE PUDO LEER EL CODIGO DE RESPUESTA DEL CDR";
                    }

                    $update = array(
                        "fechaenvio" => date("Y-m-d"), 
                        "codigorespuesta" => $responsecode_texto, 
                        "ruta_cdr" => $carpeta_month."/R-".$archivo_phuyu, 
                        "descripcion_cdr" => $mensaje,
                        "estado" => $estado
                    );
                    $this->db->where("codguiar",$credenciales[3]);
                    $actualizarkardex = $this->db->update("sunat.guiasunat", $update);

                    // 8: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //

                    foreach(glob($carpeta_phuyu . "/*") as $archivos_carpeta){             
                        if (is_dir($archivos_carpeta)){
                            rmdir($carpeta_phuyu."/dummy");
                        } else {
                            unlink($archivos_carpeta);
                        }
                    }
                    rmdir($carpeta_phuyu);
                }else{
                    $estado = 0; $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
                }
            }
        }

        $data["estado"] = $estado; $data["mensaje"] = $mensaje;
        return $data;
    }

    function phuyu_consultarTICKET($nombre_xml, $ticket, $credenciales, $tipo = "electronico"){

        $webservice = $this->db->query("select * from public.webservice")->result_array();
        
        // NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
        $camposervice = "servicesunat";
        if ($webservice[0]["sunatose"]==1) {
            $camposervice = "serviceose";
        }

        if ($tipo!="electronico") {
            $camposervice = $camposervice.$tipo;
        }

        // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
        if ($webservice[0]["serviceweb"]==1) {
            $camposervice = $camposervice."_demo";
        }
        $wsdlURL = $webservice[0][$camposervice];

        // 1: ESTRUCTURA PARA LA CONEXION //

        $XMLString = $this->phuyu_getStatus($ticket, $credenciales);
        //print_r($XMLString);exit;
        $result = $this->soapCall($wsdlURL, $callFunction = "getStatus", $XMLString);
//print_r($result);exit;
        if($result["error"] == "si"){
            $estado = 0; $mensaje = $result["mensaje"];
        }else{
            // 3: DESCARGAMOS EL ARCHIVO RESPUESTA DE SUNAT //
            //echo $ticket;exit;
            $carpeta_phuyu  = "./sunat/webphuyu/".$ticket;
            if (!file_exists($carpeta_phuyu)) { 
                mkdir($carpeta_phuyu,0777); chmod($carpeta_phuyu, 0777);
            }

            $archivoresponse = fopen($carpeta_phuyu."/R-".$ticket.".xml","w+");
            fputs($archivoresponse,$result["mensaje"]); fclose($archivoresponse);

            // 4: LEEMOS EL ARCHIVO XML //
            $xml = simplexml_load_file($carpeta_phuyu."/R-".$ticket.".xml"); 
            $response = "";
            $status_code = "";
            if ($xml !== false) {
                foreach ($xml->xpath('//content') as $item){
                    $response = (string)$item;
                }
                foreach ($xml->xpath('//statusCode') as $item){
                    $status_code = trim((string)$item);
                }
            }
//print_r($response);exit;
            if($response != ""){
                // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS TICKETS POR AÑO//
                $carpeta_year  = "./sunat/resumenes/".date("Y");
                if (!file_exists($carpeta_year)) { 
                    mkdir($carpeta_year,0777); chmod($carpeta_year, 0777);
                }

                // 5: CREAMOS UNA CARPETA PARA ALMACENAR LOS CDR POR MES //
                $carpeta_month = $carpeta_year."/".date("m");
                if (!file_exists($carpeta_month)) {
                    mkdir($carpeta_month,0777); chmod($carpeta_month, 0777);
                }

                // 5: DESCARGAMOS EL ARCHIVO CDR (CONSTANCIA DE RECEPCIÓN) //
                $cdr = base64_decode($response);
                //print_r($cdr);exit;
                $archivoresponse = fopen($carpeta_month."/R-".$ticket.".zip","w+");
                fputs($archivoresponse, $cdr); fclose($archivoresponse);
                chmod($carpeta_month."/R-".$ticket.".zip", 0777);

                // 6: EXTRAEMOS EL ARCHIVO RESPUESTA //
                $zip = new ZipArchive;
                if ($zip->open($carpeta_month."/R-".$ticket.".zip") === TRUE){
                    $zip->extractTo($carpeta_phuyu."/"); $zip->close();
                }

                //echo $nombre_xml;exit;

                /*if(file_exists($carpeta_phuyu."/R-".$nombre_xml.'.xml')){
                    echo 'si';
                }else{
                    echo 'no';
                }
                exit;*/

                // 7: LEEMOS EL CDR Y ACTUALIZAMOS EN LA BASE DE DATOS EN RESUMENES //
                $archivo_cdr = $carpeta_phuyu."/R-".$nombre_xml.'.xml';
                if (!is_readable($archivo_cdr)) {
                    return ["estado" => 0, "mensaje" => "SUNAT devolvio CDR, pero no se encontro el XML esperado: R-".$nombre_xml.".xml"];
                }
                $xml_respuesta = simplexml_load_file($archivo_cdr);
                $responsecode_texto = "";
                $description_texto = "";
                if ($xml_respuesta !== false) {
                    foreach ($xml_respuesta->xpath('//cbc:ResponseCode') as $responsecode){ 
                        $responsecode_texto = (string)$responsecode;
                    }
                    foreach ($xml_respuesta->xpath('//cbc:Description') as $description) {
                        $description_texto = (string)$description;
                    }
                }

                $responsecode_texto = trim((string) $responsecode_texto);
                $responsecode_numero = is_numeric($responsecode_texto) ? (int) $responsecode_texto : null;

                if($responsecode_texto === "0"){    
                    $estado = 1; $mensaje =  (string)($description_texto);
                }elseif($responsecode_numero !== null and $responsecode_numero >= 100 and $responsecode_numero<=1999){
                    $estado = 2; $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                }elseif($responsecode_numero !== null and $responsecode_numero >= 2000 and $responsecode_numero<=3999){
                    $estado = 3; $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                }elseif($responsecode_numero !== null){
                    $estado = 4; $mensaje = $this->phuyu_descripcion_cdr($description_texto);
                }else{
                    $estado = 0; $mensaje = "NO SE PUDO LEER EL CODIGO DE RESPUESTA DEL CDR";
                }

                $update = array(
                    "codigorespuesta" => $responsecode_texto, 
                    "ruta_cdr" => $carpeta_month."/R-".$ticket, 
                    "descripcion_cdr" => $mensaje,
                    "estado" => $estado
                );
                $this->db->where("codresumentipo",$credenciales[3]);
                $this->db->where("periodo",$credenciales[4]);
                $this->db->where("nrocorrelativo",$credenciales[5]);
                $this->db->where("codempresa",$credenciales[6]);
                $actualizarkardex = $this->db->update("sunat.resumenes", $update);

                // 7: ACTUALIZAMOS LOS CAMPOS DE LAS TALAS DE SUNAT //
                
                if ($credenciales[3]==1 || $credenciales[3]==4) {
                    $detalle = $this->db->query("select codkardex from sunat.kardexsunatanulados where codresumentipo=".$credenciales[3]." and periodo='".$credenciales[4]."' and nrocorrelativo=".$credenciales[5]." and codempresa=".$credenciales[6])->result_array();
                    foreach ($detalle as $value) {
                        $update = array(
                            "estado" => $estado
                        );
                        $this->db->where("codkardex",$value["codkardex"]);
                        $actualizarkardex = $this->db->update("sunat.kardexsunatanulados", $update);
                    }
                }

                if ($credenciales[3]==3) {
                    $detalle = $this->db->query("select codkardex from sunat.kardexsunatdetalle where codresumentipo=".$credenciales[3]." and periodo='".$credenciales[4]."' and nrocorrelativo=".$credenciales[5]." and codempresa=".$credenciales[6])->result_array();
                    foreach ($detalle as $value) {
                        $update = array(
                            "codigorespuesta" => $responsecode_texto, 
                            "ruta_cdr" => $carpeta_month."/R-".$ticket,
                            "descripcion_cdr" => $mensaje,
                            "estado" => $estado
                        );
                        $this->db->where("codkardex",$value["codkardex"]);
                        $actualizarkardex = $this->db->update("sunat.kardexsunat", $update);
                    }

                    $update = array(
                        "descripcion_cdr" => $mensaje,
                        "estado" => $estado
                    );
                    $this->db->where("codresumentipo",$credenciales[3]);
                    $this->db->where("periodo",$credenciales[4]);
                    $this->db->where("nrocorrelativo",$credenciales[5]);
                    $this->db->where("codempresa",$credenciales[6]);
                    $actualizarkardex = $this->db->update("sunat.kardexsunatdetalle", $update);
                }

                // 8: ELIMINAMOS EL ARCHIVO RESPONSE Y LA CARPETA TEMPORAL //

                foreach(glob($carpeta_phuyu . "/*") as $archivos_carpeta){          
                    if (is_dir($archivos_carpeta)){
                        rmdir($carpeta_phuyu."/dummy");
                    } else {
                        unlink($archivos_carpeta);
                    }
                }
                rmdir($carpeta_phuyu);
            }else{
                $estado = 0;
                if ($status_code === "0098") {
                    $mensaje = "Ticket ".$ticket." en proceso SUNAT (0098). Pendiente consultar CDR; no reenviar XML.";
                } else {
                    $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
                }
            }
        }

        $data["estado"] = $estado; $data["mensaje"] = $mensaje;
        return $data;
    } 
    
    function phuyu_consultarSUNAT($informacion, $credenciales, $tipo = "electronico"){
        $webservice = $this->db->query("select * from public.webservice")->result_array();
        
        // NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
        $camposervice = "servicesunat";
        if ($webservice[0]["sunatose"]==1) {
            $camposervice = "serviceose";
        }

        if ($tipo!="electronico") {
            $camposervice = $camposervice.$tipo;
        }

        // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
        if ($webservice[0]["serviceweb"]==1) {
            $camposervice = $camposervice."_demo";
        }

        if ($tipo=="electronico") {
            if ($webservice[0][$camposervice]=="./sunat/billService.wsdl") {
                $wsdlURL = "https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl";
            }else{
                $wsdlURL = "https://www.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl";
            }
        }else{
            $wsdlURL = $webservice[0][$camposervice];
        }
        
        $XMLString = $this->phuyu_getStatusCDR($informacion, $credenciales);
        $result = $this->soapCall($wsdlURL, $callFunction = "getStatus", $XMLString);
        //print_r($result);
        if($result["error"] == "si"){
            $estado = 0; $mensaje = $result["mensaje"];
        }else{
            $estado = 1; $mensaje = $result["mensaje"];
        }

        $data["estado"] = $estado; $data["mensaje"] = $mensaje;
        return $data;
    }

    function phuyu_consultarSUNATCDR($informacion,$carpeta_phuyu, $archivo_phuyu, $credenciales, $tipo = "electronico"){
        $webservice = $this->db->query("select * from public.webservice")->result_array();
        
        // NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
        $camposervice = "servicesunat";
        if ($webservice[0]["sunatose"]==1) {
            $camposervice = "serviceose";
        }

        if ($tipo!="electronico") {
            $camposervice = $camposervice.$tipo;
        }

        // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
        if ($webservice[0]["serviceweb"]==1) {
            $camposervice = $camposervice."_demo";
        }

        if ($tipo=="electronico") {
            if ($webservice[0][$camposervice]=="./sunat/billService.wsdl") {
                $wsdlURL = "https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl";
            }else{
                $wsdlURL = "https://www.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl";
            }
        }else{
            $wsdlURL = $webservice[0][$camposervice];
        }
        
        $XMLString = $this->phuyu_getCDR($informacion, $credenciales);
        $result = $this->soapCall($wsdlURL, $callFunction = "getStatus", $XMLString);
        //print_r($result);
        if($result["error"] == "si"){
            $estado = 0; $mensaje = $result["mensaje"];
        }else{
            $estado = 1; $mensaje = $result["mensaje"];
        }

        $data["estado"] = $estado; $data["mensaje"] = $mensaje;
        return $data;
    }

    function soapCall($wsdlURL, $callFunction = "", $XMLString) {
        try{
            $client = new funcionSoap($wsdlURL, array("trace" => true, "exceptions" => true));
            $reply  = $client->SoapClientCall($XMLString);
            $client->__call("$callFunction", array(), array());

            return array("error" => "no", "mensaje" => $client->__getLastResponse());
        }catch(Exception $e){
            $respuesta = isset($client) ? trim((string)$client->__getLastResponse()) : "";
            $mensaje = $this->phuyu_normalizar_error_sunat($e->getMessage(), $respuesta, $callFunction);
            return array("error" => "si", "mensaje" => $mensaje, "respuesta" => $respuesta);
        }
    }

    function phuyu_qrcode($textoqr){
        $this->load->library('ciqrcode');
        $params['data'] = $textoqr; $params['level'] = 'H'; $params['size'] = 5;
        $params['savename'] = "./sunat/webphuyu/qrcode.png";
        $this->ciqrcode->generate($params);
        // chmod("./sunat/webphuyu/qrcode.png", 0777);
        
        $archivo_error = APPPATH."/logs/qrcode.png-errors.txt";
        unlink($archivo_error);
        
        return 1;
    }
}

class funcionSoap extends SoapClient{
    public $XMLStr = "";

    public function setXMLStr($value) {
        $this->XMLStr = $value;
    }
    
    public function getXMLStr() {
        return $this->XMLStr;
    }
    
    public function __doRequest($request, $location, $action, $version, $one_way = 0){
        $request = $this->XMLStr;
        $dom = new DOMDocument("1.0");
        try{
            $dom->loadXML($request);
        } catch (DOMException $e) {
            die($e->code);
        }
        $request = $dom->saveXML();
        //Para la solicitud //
        return parent::__doRequest($request, $location, $action, $version, $one_way = 0);
    }
    
    public function SoapClientCall($SOAPXML){
        return $this->setXMLStr($SOAPXML);
    }
}
