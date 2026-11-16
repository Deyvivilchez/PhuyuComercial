<?php
// php7.4 tests/gre_rest_test.php [ruta-al-XSD-UBL-DespatchAdvice-2.1.xsd]
define('BASEPATH', true);
require __DIR__.'/../application/libraries/Phuyu_gre.php';
function check($ok,$message) { if (!$ok) throw new RuntimeException($message); }
$api=new Phuyu_gre;
$empresa=['documento'=>'20100070970','razonsocial'=>'Emisor & Asociados'];
$guia=['seriecomprobante'=>'T001','nrocomprobante'=>'00000001','peso'=>3,'nropaquetes'=>1,'ubigeopartida'=>'220901','ubigeollegada'=>'220701','direccionpartida'=>'Origen & anexo','direccionllegada'=>'Destino','codmodalidadtraslado'=>2,'nroplaca'=>'T7R-299','documentoconductor'=>'12345678','licenciaconductor'=>'X12345678','conductor_nombres'=>'JUAN CARLOS','conductor_apellidos'=>'PEREZ CRUZ','fechatraslado'=>'2026-11-05','fechaguia'=>'2026-11-05','observaciones'=>'Caja <cerrada>','documento'=>'20100070970','oficial'=>'6','destinatario'=>'Cliente & Cia','oficialmotivo'=>'01','motivo'=>'VENTA','oficialdocumento'=>'1'];
$detalle=[['cantidad'=>1,'unidad'=>'NIU','producto'=>'Monitor & cable','codproducto'=>1]];
$x=$api->crearXml($empresa,$guia,$detalle);
$parsed=new DOMDocument;check($parsed->loadXML($x->saveXML()),'XML debe admitir caracteres especiales');
$xp=new DOMXPath($parsed);$xp->registerNamespace('cac','urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');$xp->registerNamespace('cbc','urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
check($xp->evaluate('string(//cac:TransportEquipment/cbc:ID)')==='T7R299','La placa debe provenir del vehiculo');
check($xp->evaluate('string(//cac:DriverPerson/cac:IdentityDocumentReference/cbc:ID)')==='X12345678','La licencia debe ser independiente');
check($xp->evaluate('string(//cac:DespatchAddress/cac:AddressLine/cbc:Line)')==='Origen & anexo','Direccion de partida GRE 2.0');
if (isset($argv[1])) { // La firma ocupa ExtensionContent en produccion.
    $ext=$parsed->getElementsByTagName('ExtensionContent')->item(0);
    $ext->appendChild($parsed->createElementNS('urn:phuyu:test','test:SignaturePlaceholder'));
    check($parsed->schemaValidate($argv[1]),'El documento debe cumplir XSD UBL 2.1');
}
foreach (['nroplaca','conductor_nombres','conductor_apellidos'] as $field) {
    $bad=$guia;$bad[$field]='';$failed=false;
    try {$api->crearXml($empresa,$bad,$detalle);}catch(InvalidArgumentException $e){$failed=true;}
    check($failed,'Debe rechazar datos de transporte incompletos: '.$field);
}
$public=$guia;$public['codmodalidadtraslado']=1;$public['documentotransportista']='20100070970';$public['razonsocialtransportista']='Transportista';
$publicXml=new DOMDocument;$publicXml->loadXML($api->crearXml($empresa,$public,$detalle)->saveXML());$xp2=new DOMXPath($publicXml);$xp2->registerNamespace('cac','urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
check($xp2->query('//cac:CarrierParty')->length===1,'Transporte publico requiere transportista');
$documento='20100070970-09-T001-00000001';
foreach (['0'=>1,'2000'=>3,'4000'=>3] as $code=>$expected) {
    $file=tempnam(sys_get_temp_dir(),'gre-test-');$z=new ZipArchive;$z->open($file,ZipArchive::OVERWRITE);
    $z->addFromString('R-'.$documento.'.xml','<ApplicationResponse xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"><cac:DocumentResponse><cac:Response><cbc:ResponseCode>'.$code.'</cbc:ResponseCode><cbc:Description>Resultado</cbc:Description></cac:Response></cac:DocumentResponse></ApplicationResponse>');$z->close();
    $result=$api->leerCdr(file_get_contents($file),$documento);unlink($file);
    check($result['estado']===$expected,'Solo CDR con codigo cero puede aceptar la guia');
}
$failed=false;try{$api->leerCdr('archivo invalido',$documento);}catch(RuntimeException $e){$failed=true;}check($failed,'CDR corrupta nunca debe aceptar');
echo "GRE: XML privado/publico, datos incompletos, caracteres especiales y CDR verificados.\n";
