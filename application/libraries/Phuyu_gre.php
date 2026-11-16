<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** GRE remitente 2.0: XML UBL 2.1 y API REST SUNAT. */
class Phuyu_gre {
    public function crearXml($empresa, $g, $detalle) {
        if (!preg_match('/^T[A-Z0-9]{3}$/', $g['seriecomprobante']) || !preg_match('/^[0-9]{1,8}$/', $g['nrocomprobante'])) {
            throw new InvalidArgumentException('Serie o numero de guia remitente invalido.');
        }
        if ((float)$g['peso'] <= 0 || !$detalle) throw new InvalidArgumentException('Indique peso mayor a cero y productos en la guia.');
        foreach (['partida','llegada'] as $p) {
            if (!preg_match('/^[0-9]{6}$/', $g['ubigeo'.$p]) || trim($g['direccion'.$p]) === '') throw new InvalidArgumentException('Complete ubigeo y direccion de '.$p.'.');
        }
        $modo = str_pad((string)(int)$g['codmodalidadtraslado'], 2, '0', STR_PAD_LEFT);
        if (!in_array($modo, ['01','02'], true)) throw new InvalidArgumentException('Modalidad de traslado invalida.');
        if ($modo === '02') {
            foreach (['nroplaca','documentoconductor','licenciaconductor','conductor_nombres','conductor_apellidos'] as $campo) {
                if (trim((string)($g[$campo] ?? '')) === '') throw new InvalidArgumentException('Transporte privado: complete placa, documento, licencia, nombres y apellidos del conductor.');
            }
        } elseif (!preg_match('/^[0-9]{11}$/', trim($g['documentotransportista']))) {
            throw new InvalidArgumentException('Complete el RUC del transportista para transporte publico.');
        }
        if ($g['fechatraslado'] < $g['fechaguia']) throw new InvalidArgumentException('La fecha de traslado no puede ser anterior a la emision.');
        $x = new DOMDocument('1.0','UTF-8'); $x->formatOutput = true;
        $root = $x->appendChild($x->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2','DespatchAdvice'));
        foreach (['cac'=>'CommonAggregateComponents','cbc'=>'CommonBasicComponents','ext'=>'CommonExtensionComponents'] as $prefix=>$name) $root->setAttributeNS('http://www.w3.org/2000/xmlns/','xmlns:'.$prefix,'urn:oasis:names:specification:ubl:schema:xsd:'.$name.'-2');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/','xmlns:ds','http://www.w3.org/2000/09/xmldsig#');
        $add = function($parent,$name,$value=null,$attrs=[]) use ($x) {
            $e=$x->createElement($name); if ($value !== null) $e->appendChild($x->createTextNode((string)$value));
            foreach ($attrs as $k=>$v) $e->setAttribute($k,(string)$v); $parent->appendChild($e); return $e;
        };
        $idAttrs = function($tipo) { return ['schemeID'=>$tipo,'schemeName'=>'Documento de Identidad','schemeAgencyName'=>'PE:SUNAT','schemeURI'=>'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06']; };
        $catalog = function($num,$name) {return ['listAgencyName'=>'PE:SUNAT','listName'=>$name,'listURI'=>'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo'.$num];};
        $add($add($add($root,'ext:UBLExtensions'),'ext:UBLExtension'),'ext:ExtensionContent');
        $add($root,'cbc:UBLVersionID','2.1'); $add($root,'cbc:CustomizationID','2.0');
        $add($root,'cbc:ID',$g['seriecomprobante'].'-'.$g['nrocomprobante']);
        $add($root,'cbc:IssueDate',$g['fechaguia']); $add($root,'cbc:IssueTime','00:00:00');
        $add($root,'cbc:DespatchAdviceTypeCode','09',$catalog('01','Tipo de Documento'));
        if (trim((string)$g['observaciones']) !== '') $add($root,'cbc:Note',$g['observaciones']);
        $sig=$add($root,'cac:Signature'); $add($sig,'cbc:ID','SignSUNAT'); $sp=$add($sig,'cac:SignatoryParty');
        $add($add($sp,'cac:PartyIdentification'),'cbc:ID',$empresa['documento']); $add($add($sp,'cac:PartyName'),'cbc:Name',$empresa['razonsocial']);
        $add($add($add($sig,'cac:DigitalSignatureAttachment'),'cac:ExternalReference'),'cbc:URI','#SignSUNAT');
        foreach ([['cac:DespatchSupplierParty',$empresa['documento'],'6',$empresa['razonsocial']],['cac:DeliveryCustomerParty',$g['documento'],$g['oficial'],$g['destinatario']]] as $party) {
            $p=$add($add($root,$party[0]),'cac:Party'); $add($add($p,'cac:PartyIdentification'),'cbc:ID',$party[1],$idAttrs($party[2]));
            $add($add($p,'cac:PartyLegalEntity'),'cbc:RegistrationName',$party[3]);
        }
        $ship=$add($root,'cac:Shipment'); $add($ship,'cbc:ID','SUNAT_Envio');
        $add($ship,'cbc:HandlingCode',$g['oficialmotivo'],$catalog('20','Motivo de traslado'));
        $add($ship,'cbc:HandlingInstructions',$g['motivo']);
        $add($ship,'cbc:GrossWeightMeasure',number_format($g['peso'],3,'.',''),['unitCode'=>'KGM']);
        if ((int)$g['nropaquetes']>0) $add($ship,'cbc:TotalTransportHandlingUnitQuantity',(int)$g['nropaquetes']);
        $stage=$add($ship,'cac:ShipmentStage'); $add($stage,'cbc:TransportModeCode',$modo,$catalog('18','Modalidad de traslado'));
        $add($add($stage,'cac:TransitPeriod'),'cbc:StartDate',$g['fechatraslado']);
        if ($modo === '01') {
            $carrier=$add($stage,'cac:CarrierParty'); $add($add($carrier,'cac:PartyIdentification'),'cbc:ID',$g['documentotransportista'],['schemeID'=>'6']);
            $add($add($carrier,'cac:PartyLegalEntity'),'cbc:RegistrationName',$g['razonsocialtransportista']);
        } else {
            $driver=$add($stage,'cac:DriverPerson'); $add($driver,'cbc:ID',$g['documentoconductor'],$idAttrs($g['oficialdocumento']));
            $add($driver,'cbc:FirstName',trim($g['conductor_nombres'])); $add($driver,'cbc:FamilyName',trim($g['conductor_apellidos']));
            $add($driver,'cbc:JobTitle','Principal'); $add($add($driver,'cac:IdentityDocumentReference'),'cbc:ID',strtoupper(trim($g['licenciaconductor'])));
        }
        $delivery=$add($ship,'cac:Delivery');
        foreach (['llegada','partida'] as $p) {
            $address=$p==='llegada' ? $add($delivery,'cac:DeliveryAddress') : $add($add($delivery,'cac:Despatch'),'cac:DespatchAddress');
            $add($address,'cbc:ID',$g['ubigeo'.$p],['schemeAgencyName'=>'PE:INEI','schemeName'=>'Ubigeos']);
            $add($add($address,'cac:AddressLine'),'cbc:Line',trim($g['direccion'.$p]));
        }
        if ($modo==='02') {
            $equipment=$add($add($ship,'cac:TransportHandlingUnit'),'cac:TransportEquipment');
            $placa=strtoupper(str_replace(['-',' '],'',trim($g['nroplaca'])));
            if (!preg_match('/^[A-Z0-9]{6,8}$/',$placa)) throw new InvalidArgumentException('Placa del vehiculo invalida.');
            $add($equipment,'cbc:ID',$placa);
        }
        foreach ($detalle as $i=>$d) {
            if ((float)$d['cantidad']<=0 || trim($d['unidad'])==='' || trim($d['producto'])==='') throw new InvalidArgumentException('Producto sin cantidad, unidad SUNAT o descripcion.');
            $line=$add($root,'cac:DespatchLine');$add($line,'cbc:ID',$i+1);
            $add($line,'cbc:DeliveredQuantity',rtrim(rtrim(number_format($d['cantidad'],10,'.',''),'0'),'.'),['unitCode'=>$d['unidad']]);
            $add($add($line,'cac:OrderLineReference'),'cbc:LineID',$i+1);$item=$add($line,'cac:Item');$add($item,'cbc:Description',$d['producto']);
            $add($add($item,'cac:SellersItemIdentification'),'cbc:ID',$d['codproducto']);
        }
        return $x;
    }

    public function request($url, $body=null, $token='') {
        $ch=curl_init($url); $headers=['Accept: application/json'];
        if ($token!=='') $headers[]='Authorization: Bearer '.$token;
        if ($body!==null) {
            curl_setopt($ch,CURLOPT_POST,true); curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
            $headers[]=$token===''?'Content-Type: application/x-www-form-urlencoded':'Content-Type: application/json';
        }
        curl_setopt_array($ch,[CURLOPT_HTTPHEADER=>$headers,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>45]);
        $raw=curl_exec($ch); $error=curl_error($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$data=json_decode((string)$raw,true);
        if ($status<200 || $status>=300 || !is_array($data)) {
            $message=$error!==''?'No se pudo conectar con SUNAT.':($data['error_description']??$data['msg']??('SUNAT HTTP '.$status));
            foreach (($data['errors']??[]) as $e) $message.=' | '.($e['codError']??$e['cod']??'').': '.($e['desError']??$e['msg']??'');
            if ($status===401) {
                $message=$token==='' ? 'SUNAT no pudo autenticar las credenciales API/SOL (HTTP 401). Revise Client ID, Client Secret, Usuario SOL y clave SOL.' : 'SUNAT no reconoce el token en el servicio GRE (HTTP 401). En SOL revise que la aplicacion API tenga Alcance Desktop y el recurso GRE Emision de Comprobantes; verifique tambien la autorizacion del Usuario SOL.';
            } elseif ($status===403) {
                $message='SUNAT denego el acceso al recurso GRE (HTTP 403). Revise los permisos de la aplicacion API y del Usuario SOL.';
            }
            throw new RuntimeException($message,(int)$status);
        }
        return $data;
    }

    public function token($config,$ruc) {
        $id=trim($config['sunat_api_client_id']??''); $secret=trim($config['sunat_api_client_secret']??'');
        if ($id==='' || $secret==='') throw new RuntimeException('Faltan Client ID y Client Secret API SUNAT en Administracion > Empresa. Habilite GRE Emision de Comprobantes en SOL.');
        if ((int)$config['serviceweb']!==0) throw new RuntimeException('La API GRE esta configurada solo para produccion. Cambie a produccion para declarar una guia real.');
        $user=trim($config['usuariosol']); if (strpos($user,$ruc)!==0) $user=$ruc.$user;
        $data=$this->request('https://api-seguridad.sunat.gob.pe/v1/clientessol/'.rawurlencode($id).'/oauth2/token/',http_build_query(['grant_type'=>'password','scope'=>'https://api-cpe.sunat.gob.pe','client_id'=>$id,'client_secret'=>$secret,'username'=>$user,'password'=>$config['clavesol']]));
        if (empty($data['access_token'])) throw new RuntimeException('SUNAT no devolvio un token de acceso GRE.');
        return $data['access_token'];
    }

    public function leerCdr($bytes,$documento) {
        $tmp=tempnam(sys_get_temp_dir(),'phuyu-gre-'); file_put_contents($tmp,$bytes);$zip=new ZipArchive();
        try {
            if ($zip->open($tmp)!==true) throw new RuntimeException('CDR ZIP invalido.');
            $xml=$zip->getFromName('R-'.$documento.'.xml');$zip->close();
            if ($xml===false) throw new RuntimeException('El CDR no corresponde a esta guia.');
            $dom=new DOMDocument(); if (!@$dom->loadXML($xml,LIBXML_NONET)) throw new RuntimeException('CDR XML invalido.');
            $xp=new DOMXPath($dom);$xp->registerNamespace('cac','urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');$xp->registerNamespace('cbc','urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $code=trim($xp->evaluate('string(//cac:DocumentResponse/cac:Response/cbc:ResponseCode)'));
            $description=trim($xp->evaluate('string(//cac:DocumentResponse/cac:Response/cbc:Description)'));
            if ($code==='') throw new RuntimeException('El CDR no contiene codigo de respuesta.');
            return ['codigo'=>$code,'mensaje'=>$description,'estado'=>$code==='0'?1:3];
        } finally {unlink($tmp);}
    }
}
