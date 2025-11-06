<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Empresa extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model"); $this->load->model("Caja_model"); $this->load->model("Kardex_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$info = $this->db->query("select *from public.personas where codpersona=".$_SESSION["phuyu_codempresa"])->result_array();
			$empresa = $this->db->query("select *from public.empresas where codempresa=".$_SESSION["phuyu_codempresa"])->result_array();
			$service = $this->db->query("select *from public.webservice where codempresa=".$_SESSION["phuyu_codempresa"])->result_array();

			if (file_exists("./sunat/certificados/public_key.pem")) {
				$pen = "private_key.pem - public_key.pem GENERADOS";
			}else{
				$pen = "NO GENERADOS";
			}
			$this->load->view("administracion/empresa/index",compact("info","empresa","service","pen"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function editar(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->request = json_decode(file_get_contents('php://input'));

				$empresa = $this->db->query("select *from public.empresas where codpersona=".$this->request->codregistro)->result_array();
				$service = $this->db->query("select *from public.webservice where codempresa=".$this->request->codregistro)->result_array();
				$rubros = $this->db->query("select *from public.rubros where estado = 1")->result_array();
				$this->load->view("administracion/empresa/editar",compact("empresa","service","rubros"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar_origen(){
		if ($this->input->is_ajax_request()) {
			$campos = ["rubro","facturacion","ubigeo","departamento","provincia","distrito"]; 
			$valores = [$_POST["rubro"],1,$_POST["ubigeo"],$_POST["departamento"],$_POST["provincia"],$_POST["distrito"]];
			$estado = $this->phuyu_model->phuyu_editar("public.empresas", $campos, $valores,"codempresa",$_POST["codempresa"]);

			// NOTA: campo->sunatose = 0: SERVICIO SUNAT, campo->sunatose = 1: SERVICIO OSE //
	        $service = "servicesunat"; $service_guia = "servicesunatguia"; $service_retencion = "servicesunatretencion";
	        if ($_POST["sunatose"]==1) {
	            $service = "serviceose"; $service_guia = "serviceoseguia"; $service_retencion = "serviceoseretencion";
	        }
	        // NOTA: campo->serviceweb = 0: PRODUCCION SUNAT, campo->serviceweb = 1: DEMO //
	        if ($_POST["serviceweb"]==1) {
	            $service = $service."_demo"; $service_guia = $service_guia."_demo"; $service_retencion = $service_retencion."_demo";
	        }

			$campos = ["usuariosol","clavesol","envioemail","claveemail","certificado_clave","sunatose","serviceweb",$service,$service_guia,$service_retencion];
			$valores = [$_POST["usuariosol"],$_POST["clavesol"],$_POST["envioemail"],$_POST["claveemail"],$_POST["certificado_clave"],$_POST["sunatose"],$_POST["serviceweb"],$_POST[$service],$_POST[$service_guia],$_POST[$service_retencion]];
			$estado = $this->phuyu_model->phuyu_editar("public.webservice", $campos, $valores,"codempresa",$_POST["codempresa"]);

			if ($_FILES["certificado_pfx"]["name"]!="") {
				$file = $_FILES["certificado_pfx"]["name"];
				move_uploaded_file($_FILES["certificado_pfx"]["tmp_name"],"./sunat/certificados/".$file);

				$data = array("certificado_pfx" => $file);
				$this->db->where("codempresa",$_POST["codempresa"]);
				$estado = $this->db->update("public.webservice",$data);

                // Creamos los archivos PEM //
                $carpeta_certificados = "./sunat/certificados"; 
                $carpeta_archivo = $carpeta_certificados."/".$file; $clave = $_POST["certificado_clave"];
                //chmod($carpeta_archivo, 0777); 
                $pkcs12 = file_get_contents($carpeta_archivo);

                $certificados = array(); $respuesta = openssl_pkcs12_read($pkcs12, $certificados, $clave);
                if ($respuesta) {
                    $publicKeyPem  = $certificados["cert"]; // Archivo público
                    $privateKeyPem = $certificados["pkey"]; // Archivo privado

                    // guardo la clave publica y privada en mi directorio en formato .pem //
                    file_put_contents($carpeta_certificados."/private_key.pem", $privateKeyPem);
                    file_put_contents($carpeta_certificados."/public_key.pem", $publicKeyPem);
                    chmod($carpeta_certificados."/private_key.pem", 0777);
                    chmod($carpeta_certificados."/public_key.pem", 0777);
                }else {
                    $estado = 0;
                }
			}

			unset($_SESSION["phuyu_rubro"]);

			$_SESSION["phuyu_rubro"] = (int)$_POST["rubro"];

			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
	function guardar(){
    if ($this->input->is_ajax_request()) {
        $estado = 0;
        
        try {
            // Primera actualización
            $campos = ["rubro","facturacion","ubigeo","departamento","provincia","distrito"]; 
            $valores = [$_POST["rubro"],1,$_POST["ubigeo"],$_POST["departamento"],$_POST["provincia"],$_POST["distrito"]];
            $estado = $this->phuyu_model->phuyu_editar("public.empresas", $campos, $valores,"codempresa",$_POST["codempresa"]);

            // Configuración de servicios
            $service = "servicesunat"; 
            $service_guia = "servicesunatguia"; 
            $service_retencion = "servicesunatretencion";
            
            if ($_POST["sunatose"]==1) {
                $service = "serviceose"; 
                $service_guia = "serviceoseguia"; 
                $service_retencion = "serviceoseretencion";
            }
            
            if ($_POST["serviceweb"]==1) {
                $service = $service."_demo"; 
                $service_guia = $service_guia."_demo"; 
                $service_retencion = $service_retencion."_demo";
            }

            // Segunda actualización
            $campos = ["usuariosol","clavesol","envioemail","claveemail","certificado_clave","sunatose","serviceweb",$service,$service_guia,$service_retencion];
            $valores = [$_POST["usuariosol"],$_POST["clavesol"],$_POST["envioemail"],$_POST["claveemail"],$_POST["certificado_clave"],$_POST["sunatose"],$_POST["serviceweb"],$_POST[$service],$_POST[$service_guia],$_POST[$service_retencion]];
            $estado = $this->phuyu_model->phuyu_editar("public.webservice", $campos, $valores,"codempresa",$_POST["codempresa"]);

            // Procesar certificado si se subió uno
            if (isset($_FILES["certificado_pfx"]) && $_FILES["certificado_pfx"]["name"] != "") {
                $carpeta_certificados = "./sunat/certificados/";
                $file = $_FILES["certificado_pfx"]["name"];
                $destination = $carpeta_certificados . $file;

                // VERIFICAR Y CREAR DIRECTORIO CON PERMISOS
                if (!is_dir($carpeta_certificados)) {
                    if (!mkdir($carpeta_certificados, 0755, true)) {
                        throw new Exception("No se pudo crear el directorio de certificados");
                    }
                }

                // VERIFICAR PERMISOS DE ESCRITURA
                if (!is_writable($carpeta_certificados)) {
                    if (!chmod($carpeta_certificados, 0755)) {
                        throw new Exception("El directorio no tiene permisos de escritura");
                    }
                }

                // MOVER ARCHIVO
                if (!move_uploaded_file($_FILES["certificado_pfx"]["tmp_name"], $destination)) {
                    $error = error_get_last();
                    throw new Exception("Error al mover archivo: " . ($error['message'] ?? 'Error desconocido'));
                }

                // VERIFICAR QUE EL ARCHIVO SE MOVIÓ CORRECTAMENTE
                if (!file_exists($destination)) {
                    throw new Exception("El archivo certificado no se encuentra en el destino");
                }

                // ACTUALIZAR BASE DE DATOS
                $data = array("certificado_pfx" => $file);
                $this->db->where("codempresa", $_POST["codempresa"]);
                $estado = $this->db->update("public.webservice", $data);

                // SOLUCIÓN: CREAR ARCHIVOS PEM USANDO OPENSSL COMMAND LINE
                $clave = $_POST["certificado_clave"];
                $private_key_file = $carpeta_certificados . "private_key.pem";
                $cert_file = $carpeta_certificados . "public_key.pem";

                // PRIMER INTENTO: Con legacy
                $command_private = "openssl pkcs12 -in '" . $destination . "' -nocerts -out '" . $private_key_file . "' -nodes -passin pass:'" . $clave . "' -legacy 2>&1";
                $command_cert = "openssl pkcs12 -in '" . $destination . "' -nokeys -out '" . $cert_file . "' -nodes -passin pass:'" . $clave . "' -legacy 2>&1";

                exec($command_private, $output_private, $return_var_private);
                exec($command_cert, $output_cert, $return_var_cert);

                // SEGUNDO INTENTO: Si falla, probar con algoritmos específicos
                if ($return_var_private !== 0 || $return_var_cert !== 0) {
                    $command_private = "openssl pkcs12 -in '" . $destination . "' -nocerts -out '" . $private_key_file . "' -nodes -passin pass:'" . $clave . "' -keypbe PBE-SHA1-3DES -certpbe PBE-SHA1-3DES 2>&1";
                    $command_cert = "openssl pkcs12 -in '" . $destination . "' -nokeys -out '" . $cert_file . "' -nodes -passin pass:'" . $clave . "' -keypbe PBE-SHA1-3DES -certpbe PBE-SHA1-3DES 2>&1";
                    
                    exec($command_private, $output_private, $return_var_private);
                    exec($command_cert, $output_cert, $return_var_cert);
                }

                // TERCER INTENTO: Si aún falla, probar sin opciones
                if ($return_var_private !== 0 || $return_var_cert !== 0) {
                    $command_private = "openssl pkcs12 -in '" . $destination . "' -nocerts -out '" . $private_key_file . "' -nodes -passin pass:'" . $clave . "' 2>&1";
                    $command_cert = "openssl pkcs12 -in '" . $destination . "' -nokeys -out '" . $cert_file . "' -nodes -passin pass:'" . $clave . "' 2>&1";
                    
                    exec($command_private, $output_private, $return_var_private);
                    exec($command_cert, $output_cert, $return_var_cert);
                }

                // VERIFICAR RESULTADO
                if ($return_var_private === 0 && $return_var_cert === 0) {
                    // Verificar que los archivos se crearon correctamente
                    if (file_exists($private_key_file) && filesize($private_key_file) > 0 && 
                        file_exists($cert_file) && filesize($cert_file) > 0) {
                        
                        chmod($private_key_file, 0644);
                        chmod($cert_file, 0644);
                        error_log("✅ Certificado procesado exitosamente");
                    } else {
                        throw new Exception("Archivos PEM creados pero vacíos");
                    }
                } else {
                    $error_msg = "Error al procesar certificado:\n";
                    if ($return_var_private !== 0) {
                        $error_msg .= "Clave privada: " . implode(" ", $output_private) . "\n";
                    }
                    if ($return_var_cert !== 0) {
                        $error_msg .= "Certificado: " . implode(" ", $output_cert);
                    }
                    
                    // Limpiar archivos en caso de error
                    if (file_exists($private_key_file)) unlink($private_key_file);
                    if (file_exists($cert_file)) unlink($cert_file);
                    
                    throw new Exception($error_msg);
                }
            }

            // Actualizar sesión
            unset($_SESSION["phuyu_rubro"]);
            $_SESSION["phuyu_rubro"] = (int)$_POST["rubro"];

            $estado = 1;

        } catch (Exception $e) {
            error_log("Error en guardar certificado: " . $e->getMessage());
            $estado = 0;
            echo "ERROR: " . $e->getMessage();
            return;
        }

        echo $estado;
        
    } else {
        $this->load->view("phuyu/404");
    }
}

	function copia_seguridad(){
		/* echo exec('whoami');
		$comando = '/usr/bin/pg_dump --host localhost --port 5432 --username "postgres" --no-password  --format custom --blobs --verbose --file "/var/www/html/phuyuperu_demo/phuyuperu_1.backup" "phuyuperu_demo" 2>&1';
		echo exec($comando); */
   		
   		$this->load->database();
   		$this->load->dbutil();

		// Realice una copia de seguridad de toda su base de datos y asigne una variable 
		$backup = & $this->dbutil->backup ();

		// Cargue el archivo de ayuda y escriba el archivo en su servidor 
		$this->load->helper('file'); 
		write_file ( '/public/mybackup.zip' ,  $backup );

		// Cargue el asistente de descarga y envíe el archivo a su escritorio 
		$this->load->helper( 'download' ); 
		force_download('mybackup.zip' ,  $backup);
	}

	function actualizar(){
		// $lista = $this->db->query("select *from kardex.kardex where codcomprobantetipo=12 order by codkardex asc")->result_array();
		
		/* $lista = $this->db->query("select *from kardex.kardex where codcomprobantetipo=12 order by codkardex asc")->result_array();
		$correlativo = 4317;
		foreach ($lista as $key => $value) {
			$correlativo = $correlativo + 1;
			$data = array(
				'nrocomprobante' => str_pad($correlativo, 8, "0", STR_PAD_LEFT)
			);
			$this->db->where("codkardex",$value["codkardex"]);
			$estado = $this->db->update("kardex.kardex",$data);

			$data = array(
				'nrocomprobante_ref' => str_pad($correlativo, 8, "0", STR_PAD_LEFT)
			);
			$this->db->where("codkardex",$value["codkardex"]);
			$estado = $this->db->update("caja.movimientos",$data);
		}

		$data = array(
			"nrocorrelativo" => $correlativo
		);
		$this->db->where("codcomprobantetipo", 12);
		$this->db->where("seriecomprobante", "BE01");
		$estado = $this->db->update("caja.comprobantes", $data); */

		/* $lista = $this->db->query("select *from kardex.kardex where codkardex=132 or codkardex=133 or codkardex=135")->result_array();
		foreach ($lista as $key => $value) {
			$total = $this->db->query("select sum(subtotal) as total from kardex.kardexdetalle where codkardex=".$value["codkardex"])->result_array();
			$data = array(
				'valorventa' => (double)$total[0]["total"],
				'importe' => (double)$total[0]["total"] 
			);
			$this->db->where("codkardex",$value["codkardex"]);
			$estado = $this->db->update("kardex.kardex",$data);

			$movimiento = $this->db->query("select codmovimiento from caja.movimientos where codkardex=".$value["codkardex"])->result_array();
			$data = array(
				'importe' => (double)$total[0]["total"] 
			);
			$this->db->where("codmovimiento",$movimiento[0]["codmovimiento"]);
			$estado = $this->db->update("caja.movimientos",$data);

			$data = array(
				'importe' => (double)$total[0]["total"],
				'importeentregado' => (double)$total[0]["total"] 
			);
			$this->db->where("codmovimiento",$movimiento[0]["codmovimiento"]);
			$estado = $this->db->update("caja.movimientosdetalle",$data);			
		} */

		echo $estado;
	}
}
