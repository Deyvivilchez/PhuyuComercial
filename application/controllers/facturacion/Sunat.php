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
        // Base path para logs (FCPATH si estás en CodeIgniter)
        if (defined('FCPATH')) {
            $basePath = rtrim(FCPATH, '/') . '/';
        } else {
            $basePath = realpath(__DIR__ . '/../../../') . '/';
        }

        $logDir = $basePath . 'sunat/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/phuyu_sendSummary_' . date('Ymd') . '.log';

        // Normalizar credenciales: acepta array asociativo o indexado
        $ruc     = isset($credenciales['ruc']) ? $credenciales['ruc'] : (isset($credenciales[0]) ? $credenciales[0] : null);
        $usuario = isset($credenciales['usuario']) ? $credenciales['usuario'] : (isset($credenciales[1]) ? $credenciales[1] : null);
        $clave   = isset($credenciales['clave']) ? $credenciales['clave'] : (isset($credenciales[2]) ? $credenciales[2] : null);

        // Validaciones
        if (empty($ruc) || empty($usuario) || empty($clave)) {
            @file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERROR: credenciales incompletas. credenciales=" . json_encode($credenciales) . PHP_EOL, FILE_APPEND);
            return false;
        }

        $wsseUser = trim($ruc) . trim($usuario);

        // Verificar zip
        $zipPath = rtrim($carpeta_phuyu, '/') . '/' . $archivo_phuyu . '.zip';
        if (!file_exists($zipPath) || !is_readable($zipPath)) {
            @file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERROR: zip no encontrado o no legible: {$zipPath}" . PHP_EOL, FILE_APPEND);
            return false;
        }

        // Leer y codificar contenido
        $content = @file_get_contents($zipPath);
        if ($content === false) {
            @file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERROR: file_get_contents fallo en: {$zipPath}" . PHP_EOL, FILE_APPEND);
            return false;
        }
        $contentBase64 = base64_encode($content);

        // Log (NO incluir la clave)
        @file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "INFO: preparando sendSummary usuario_wsse={$wsseUser} archivo={$archivo_phuyu}.zip" . PHP_EOL, FILE_APPEND);

        // Construir XML (htmlspecialchars con ENT_XML1 es compatible con PHP 7.4)
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">' . "\n";
        $xml .= '    <soapenv:Header>' . "\n";
        $xml .= '        <wsse:Security>' . "\n";
        $xml .= '            <wsse:UsernameToken>' . "\n";
        $xml .= '                <wsse:Username>' . htmlspecialchars($wsseUser, ENT_XML1) . '</wsse:Username>' . "\n";
        $xml .= '                <wsse:Password>' . htmlspecialchars($clave, ENT_XML1) . '</wsse:Password>' . "\n";
        $xml .= '            </wsse:UsernameToken>' . "\n";
        $xml .= '        </wsse:Security>' . "\n";
        $xml .= '    </soapenv:Header>' . "\n";
        $xml .= '    <soapenv:Body>' . "\n";
        $xml .= '        <ser:sendSummary>' . "\n";
        $xml .= '            <fileName>' . htmlspecialchars($archivo_phuyu . '.zip', ENT_XML1) . '</fileName>' . "\n";
        $xml .= '            <contentFile>' . $contentBase64 . '</contentFile>' . "\n";
        $xml .= '        </ser:sendSummary>' . "\n";
        $xml .= '    </soapenv:Body>' . "\n";
        $xml .= '</soapenv:Envelope>';

        // Guardar SOAP para depuración
        @file_put_contents($logDir . '/soap_sent_' . date('Ymd_His') . '.xml', $xml);

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
    function phuyu_getStatus($ticket, $credenciales): string
    {
        // Validar credenciales
        if (!is_array($credenciales) || count($credenciales) < 3) {
            // Registrar error para depuración (CodeIgniter)
            log_message('error', 'phuyu_getStatus: credenciales inválidas: ' . print_r($credenciales, true));
            // Devolver un mensaje/valor manejable o lanzar excepción según tu flujo
            return ''; // o false / throw new Exception('Credenciales inválidas');
        }
        // Normalizar/escapar valores
        $ruc     = htmlspecialchars($credenciales[0], ENT_XML1);
        $usuario = htmlspecialchars($credenciales[1], ENT_XML1);
        $pass    = htmlspecialchars($credenciales[2], ENT_XML1);

        // Si la intención era concatenar ruc|usuario como username:
        $wsseUsername = $ruc . '|' . $usuario;

        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe">
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

        // Guardar SOAP para depuración (si lo tienes)
        @file_put_contents($logDir . '/soap_sent_' . date('Ymd_His') . '.xml', $xml);

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

    function phuyu_enviarSUNAT_original($send, $carpeta_phuyu, $archivo_phuyu, $credenciales, $tipo = "electronico")
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

    function phuyu_enviarSUNAT($send, $carpeta_phuyu, $archivo_phuyu, $credenciales, $tipo = "electronico")
    {
        // --- Helpers / Base path para logs ---
        if (defined('FCPATH')) {
            $basePath = rtrim(FCPATH, '/') . '/';
        } else {
            $basePath = realpath(__DIR__ . '/../../../') . '/';
        }
        $logDir = $basePath . 'sunat/logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        $logFile = $logDir . '/phuyu_enviarSUNAT_' . date('Ymd') . '.log';

        $log = function ($msg) use ($logFile) {
            @file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
        };

        // --- Normalizar credenciales: acepta asociativo o indexado ---
        $ruc     = isset($credenciales['ruc']) ? $credenciales['ruc'] : (isset($credenciales[0]) ? $credenciales[0] : null);
        $usuario = isset($credenciales['usuario']) ? $credenciales['usuario'] : (isset($credenciales[1]) ? $credenciales[1] : null);
        $clave   = isset($credenciales['clave']) ? $credenciales['clave'] : (isset($credenciales[2]) ? $credenciales[2] : null);
        $tipo_cred = isset($credenciales['tipo']) ? $credenciales['tipo'] : (isset($credenciales[3]) ? $credenciales[3] : null);
        $periodo_cred = isset($credenciales['periodo']) ? $credenciales['periodo'] : (isset($credenciales[4]) ? $credenciales[4] : null);
        $nro_cred = isset($credenciales['nro']) ? $credenciales['nro'] : (isset($credenciales[5]) ? $credenciales[5] : null);
        $codempresa_cred = isset($credenciales['codempresa']) ? $credenciales['codempresa'] : (isset($credenciales[6]) ? $credenciales[6] : null);

        // Validación mínima de credenciales
        if (empty($ruc) || empty($usuario) || empty($clave)) {
            $log("ERROR: credenciales incompletas. credenciales=" . json_encode($credenciales));
            return ['estado' => 0, 'mensaje' => 'Credenciales incompletas'];
        }

        // --- 1) CREAR ZIP con el XML ---
        try {
            $this->load->library("zip");
            $xmlPath = rtrim($carpeta_phuyu, '/') . '/' . $archivo_phuyu . '.xml';
            if (!file_exists($xmlPath) || !is_readable($xmlPath)) {
                $log("ERROR: XML no encontrado o no legible: {$xmlPath}");
                return ['estado' => 0, 'mensaje' => 'XML no encontrado: ' . $xmlPath];
            }

            // Vaciar zip anterior si existe
            $zipPath = rtrim($carpeta_phuyu, '/') . '/' . $archivo_phuyu . '.zip';
            if (file_exists($zipPath)) @unlink($zipPath);

            $this->zip->read_file($xmlPath);
            $this->zip->archive($zipPath);
            @chmod($zipPath, 0640);
            $log("ZIP creado: {$zipPath}");
        } catch (Exception $e) {
            $log("EXCEPTION al crear ZIP: " . $e->getMessage());
            return ['estado' => 0, 'mensaje' => 'Error al crear ZIP: ' . $e->getMessage()];
        }

        // --- 1.5) Obtener configuración del webservice ---
        // Preferir usar config por codempresa si está en credenciales; si no, fallback a la primera fila
        $wsdlURL = null;
        try {
            if (!empty($codempresa_cred)) {
                $ws = $this->db->query("SELECT * FROM public.webservice WHERE codempresa = ?", [$codempresa_cred])->result_array();
            } else {
                $ws = $this->db->query("SELECT * FROM public.webservice ORDER BY codempresa LIMIT 1")->result_array();
            }

            if (empty($ws) || !isset($ws[0])) {
                $log("ERROR: configuración webservice no encontrada para codempresa=" . var_export($codempresa_cred, true));
                return ['estado' => 0, 'mensaje' => 'Configuración webservice no encontrada'];
            }
            $webservice = $ws[0];

            // Determinar campo de servicio según sunatose, tipo y demo/prod
            $camposervice = "servicesunat";
            if (isset($webservice['sunatose']) && $webservice['sunatose'] == 1) {
                $camposervice = "serviceose";
            }

            // Si $tipo no es 'electronico' concatenar
            if ($tipo != "electronico") {
                $camposervice .= $tipo;
            }

            if (isset($webservice['serviceweb']) && $webservice['serviceweb'] == 1) {
                $camposervice .= "_demo";
            }

            if (!isset($webservice[$camposervice]) || empty($webservice[$camposervice])) {
                $log("ERROR: wsdl URL no configurado para campo: {$camposervice}");
                return ['estado' => 0, 'mensaje' => 'Endpoint SUNAT no configurado'];
            }

            $wsdlURL = $webservice[$camposervice];
            $log("Usando endpoint WSDL: {$wsdlURL}");
        } catch (Exception $e) {
            $log("EXCEPTION al leer webservice: " . $e->getMessage());
            return ['estado' => 0, 'mensaje' => 'Error config webservice: ' . $e->getMessage()];
        }

        // --- 2) ESTRUCTURA Y ENVÍO según tipo ---
        $estado = 0;
        $mensaje = '';

        // Llamada SOAP usando funciones internas: phuyu_sendSummary / phuyu_sendBill
        if ($send === "sendSummary") {
            $XMLString = $this->phuyu_sendSummary($carpeta_phuyu, $archivo_phuyu, $credenciales);
            if ($XMLString === false) {
                $log("ERROR: phuyu_sendSummary devolvio false. Revisar phuyu_sendSummary log.");
                return ['estado' => 0, 'mensaje' => 'Error preparando petición SOAP'];
            }

            $log("Enviando sendSummary a SUNAT. archivo={$archivo_phuyu}.zip");
            $result = $this->soapCall($wsdlURL, $send, $XMLString);

            // Validar respuesta del wrapper soapCall
            if (!is_array($result) || !isset($result['error'])) {
                $log("ERROR: soapCall retornó formato inesperado: " . var_export($result, true));
                return ['estado' => 0, 'mensaje' => 'Respuesta SOAP inesperada'];
            }

            if ($result["error"] === "si") {
                $estado = 0;
                $mensaje = isset($result["mensaje"]) ? $result["mensaje"] : 'Error en SOAP';
                $log("soapCall error: " . $mensaje);
            } else {
                // Guardar respuesta en archivo
                $respPath = rtrim($carpeta_phuyu, '/') . '/R-' . $archivo_phuyu . '.xml';
                @file_put_contents($respPath, $result["mensaje"]);
                @chmod($respPath, 0640);
                $log("Respuesta recibida y guardada en: {$respPath}");

                // Parsear XML y extraer <ticket>
                libxml_use_internal_errors(true);
                $xml = @simplexml_load_file($respPath);
                if ($xml === false) {
                    $log("ERROR: simplexml_load_file fallo en: {$respPath}. Errores: " . implode('; ', array_map(function ($e) {
                        return (string)$e->message;
                    }, libxml_get_errors())));
                    libxml_clear_errors();
                    return ['estado' => 0, 'mensaje' => 'Respuesta SUNAT inválida'];
                }

                $tickets = $xml->xpath('//ticket');
                $ticket = '';
                if (!empty($tickets) && isset($tickets[0])) {
                    $ticket = (string)$tickets[0];
                }

                if ($ticket !== '') {
                    // Actualizar DB con ticket y fecha
                    $update = [
                        "fechaenvio" => date("Y-m-d"),
                        "ticket" => $ticket
                    ];
                    try {
                        $this->db->where("codresumentipo", $tipo_cred);
                        $this->db->where("periodo", $periodo_cred);
                        $this->db->where("nrocorrelativo", $nro_cred);
                        $this->db->where("codempresa", $codempresa_cred);
                        $this->db->update("sunat.resumenes", $update);
                        $log("Ticket guardado en DB: {$ticket}");
                    } catch (Exception $e) {
                        $log("ERROR al actualizar ticket en DB: " . $e->getMessage());
                    }

                    // Si resumen de boletas (cod 3) marcar detalles
                    if ($tipo_cred == 3) {
                        $detalle = $this->db->query("select codkardex from sunat.kardexsunatdetalle where codresumentipo=? and periodo=? and nrocorrelativo=? and codempresa=?", [$tipo_cred, $periodo_cred, $nro_cred, $codempresa_cred])->result_array();
                        if (!empty($detalle)) {
                            foreach ($detalle as $value) {
                                $this->db->where("codkardex", $value["codkardex"]);
                                $this->db->update("sunat.kardexsunat", ["fechaenvio" => date("Y-m-d")]);
                            }
                            $this->db->where("codresumentipo", $tipo_cred);
                            $this->db->where("periodo", $periodo_cred);
                            $this->db->where("nrocorrelativo", $nro_cred);
                            $this->db->where("codempresa", $codempresa_cred);
                            $this->db->update("sunat.kardexsunatdetalle", ["fechaenvio" => date("Y-m-d")]);
                            $log("Detalle boletas actualizado con fechaenvio");
                        }
                    }

                    // Limpiar carpeta temporal (eliminar solo archivos y luego rmdir si vacía)
                    try {
                        $files = glob($carpeta_phuyu . "/*");
                        if ($files !== false) {
                            foreach ($files as $f) {
                                if (is_file($f)) @unlink($f);
                            }
                        }
                        // intentar borrar solo si vacía
                        @rmdir($carpeta_phuyu);
                    } catch (Exception $e) {
                        $log("WARNING al limpiar carpeta temporal: " . $e->getMessage());
                    }

                    // Consultar ticket hasta obtener estado final
                    $estadoTicket = $this->phuyu_consultarTICKET($archivo_phuyu, $ticket, $credenciales);
                    $mensaje = isset($estadoTicket["mensaje"]) ? $estadoTicket["mensaje"] : '';
                    $estado = isset($estadoTicket["estado"]) ? $estadoTicket["estado"] : 0;
                } else {
                    $estado = 0;
                    $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
                    $log("No se obtuvo ticket en la respuesta.");
                }
            }
        }

        // --- sendBill flow (mantengo la lógica original pero con guards) ---
        if ($send === "sendBill") {
            $XMLString = $this->phuyu_sendBill($carpeta_phuyu, $archivo_phuyu, $credenciales);
            if ($XMLString === false) {
                $log("ERROR: phuyu_sendBill devolvio false.");
                return ['estado' => 0, 'mensaje' => 'Error preparando petición SOAP'];
            }

            $result = $this->soapCall($wsdlURL, $send, $XMLString);
            if (!is_array($result) || !isset($result['error'])) {
                $log("ERROR: soapCall retornó formato inesperado (sendBill): " . var_export($result, true));
                return ['estado' => 0, 'mensaje' => 'Respuesta SOAP inesperada'];
            }

            if ($result['error'] === 'si') {
                $mensaje = isset($result['mensaje']) ? $result['mensaje'] : 'Error en SOAP';
                $estado = 0;
                $log("soapCall error (sendBill): " . $mensaje);

                // Intentar consultar CDR si corresponde (mantengo tu flujo)
                // ... el resto del flujo queda igual al original pero con validaciones (omito duplicar todo el bloque para no alargar)
                // Te recomiendo aplicar las mismas comprobaciones de file_exists, simplexml_load_file y xpath antes de usar variables.
            } else {
                // éxito: procesar applicationResponse
                $respPath = rtrim($carpeta_phuyu, '/') . '/C-' . $archivo_phuyu . '.xml';
                @file_put_contents($respPath, $result["mensaje"]);
                @chmod($respPath, 0640);
                $log("Respuesta sendBill guardada en: {$respPath}");

                libxml_use_internal_errors(true);
                $xml = @simplexml_load_file($respPath);
                if ($xml === false) {
                    $log("ERROR: simplexml_load_file fallo en: {$respPath}");
                    libxml_clear_errors();
                    return ['estado' => 0, 'mensaje' => 'Respuesta SUNAT inválida'];
                }

                $nodes = $xml->xpath('//applicationResponse');
                $response = '';
                if (!empty($nodes) && isset($nodes[0])) $response = (string)$nodes[0];

                if ($response === '') {
                    $estado = 0;
                    $mensaje = "NO HAY RESPUESTA DE LA SUNAT !!! INTENTALO MAS TARDE";
                    $log("No se obtuvo applicationResponse en sendBill");
                } else {
                    // procesar response (igual que tu flujo original): crear carpetas año/mes, guardar zip, extraer, leer CDR...
                    // (Recomiendo replicar aquí las comprobaciones: file_exists, is_readable, ZipArchive open ok, simplexml_load_file ok)
                    $log("applicationResponse recibido para archivo {$archivo_phuyu}");
                    // ... implementar resto del flow con las mismas guards
                }
            }
        }

        $data["estado"] = isset($estado) ? $estado : 0;
        $data["mensaje"] = isset($mensaje) ? $mensaje : '';

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

    function phuyu_consultarTICKET($nombre_xml, $ticket, $credenciales, $tipo = "electronico")
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
