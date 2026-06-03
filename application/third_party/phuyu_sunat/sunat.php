<?php
	namespace Sunat;
	class Sunat{
		var $cc;
		var $_legal=false;
		var $_trabs=false;
		private $error;
        public $client;
        var $parser;
		public $options;
		public $cookies;
		const URL_CONSULT = 'https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias';
    const URL_RANDOM = 'https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRazonSoc&razSoc=BBVA';
		function __construct( $representantes_legales=false, $cantidad_trabajadores=false )
		{
			$this->_legal = $representantes_legales;
			$this->_trabs = $cantidad_trabajadores;
			
			$this->cc = new \Sunat\cURL();
			$this->cc->setReferer( "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/frameCriterioBusqueda.jsp" );
			$this->cc->useCookie( true );
			$this->cc->setCookiFileLocation( __DIR__ . "/cookie.txt" );
			$this->parser = new \Sunat\HtmlParser();
		}
		
		function getNumRand()
		{
			$url = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRazonSoc&razSoc=";
			$numRand = $this->cc->send($url);
			return $numRand;
		}
		function getDataRUC( $ruc )
		{
            $ch = curl_init("https://e-factura.tuscomprobantes.pe/wsconsulta/ruc/".$ruc);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	        $response = curl_exec($ch);
	        curl_close($ch);

	        return $response;
		}
		function numTrabajadores( $ruc )
		{
			$url = "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
			$data = array(
				"accion" 	=> "getCantTrab",
				"nroRuc" 	=> $ruc,
				"desRuc" 	=> ""
			);
			$rtn = $this->cc->send( $url, $data );
			if( $rtn!="" && $this->cc->getHttpStatus()==200 )
			{
				$patron = "/<td align='center'>(.*)-(.*)<\/td>[\t|\s|\n]+<td align='center'>(.*)<\/td>[\t|\s|\n]+<td align='center'>(.*)<\/td>[\t|\s|\n]+<td align='center'>(.*)<\/td>/";
				$output = preg_match_all($patron, $rtn, $matches, PREG_SET_ORDER);
				if( count($matches) > 0 )
				{
					$cantidad_trabajadores = array();
					//foreach( array_reverse($matches) as $obj )
					foreach( $matches as $obj )
					{
						$cantidad_trabajadores[]=array(
							"periodo" 				=> $obj[1]."-".$obj[2],
							"anio" 					=> $obj[1],
							"mes" 					=> $obj[2],
							"total_trabajadores" 	=> $obj[3],
							"pensionista" 			=> $obj[4],
							"prestador_servicio" 	=> $obj[5]
						);
					}
					return $cantidad_trabajadores;
				}
			}
			return array();
		}
		function RepresentanteLegal( $ruc )
		{
			$url = "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
			$data = array(
				"accion" 	=> "getRepLeg",
				"nroRuc" 	=> $ruc,
				"desRuc" 	=> ""
			);
			$rtn = $this->cc->send( $url, $data );
			if( $rtn!="" && $this->cc->getHttpStatus()==200 )
			{
				$patron = '/<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="center">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>/';
				$output = preg_match_all($patron, $rtn, $matches, PREG_SET_ORDER);
				if( count($matches) > 0 )
				{
					$representantes_legales = array();
					foreach( $matches as $obj )
					{
						$representantes_legales[]=array(
							"tipodoc" 				=> trim($obj[1]),
							"numdoc" 				=> trim($obj[2]),
							"nombre" 				=> utf8_encode(trim($obj[3])),
							"cargo" 				=> utf8_encode(trim($obj[4])),
							"desde" 				=> trim($obj[5]),
						);
					}
					return $representantes_legales;
				}
			}
			return array();
		}
		function dnitoruc($dni)
		{
			if ($dni!="" || strlen($dni) == 8)
			{
				$suma = 0;
				$hash = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);
				$suma = 5; // 10[NRO_DNI]X (1*5)+(0*4)
				for( $i=2; $i<10; $i++ )
				{
					$suma += ( $dni[$i-2] * $hash[$i] ); //3,2,7,6,5,4,3,2
				}
				$entero = (int)($suma/11);

				$digito = 11 - ( $suma - $entero*11);

				if ($digito == 10)
				{
					$digito = 0;
				}
				else if ($digito == 11)
				{
					$digito = 1;
				}
				return "10".$dni.$digito;
			}
			return false;
		}
		function valid($valor) // Script SUNAT
		{
			$valor = trim($valor);
			if ( $valor )
			{
				if ( strlen($valor) == 11 ) // RUC
				{
					$suma = 0;
					$x = 6;
					for ( $i=0; $i<strlen($valor)-1; $i++ )
					{
						if ( $i == 4 )
						{
							$x = 8;
						}
						$digito = $valor[$i];
						$x--;
						if ( $i==0 )
						{
							$suma += ($digito*$x);
						}
						else
						{
							$suma += ($digito*$x);
						}
					}
					$resto = $suma % 11;
					$resto = 11 - $resto;
					if ( $resto >= 10)
					{
						$resto = $resto - 10;
					}
					if ( $resto == $valor[strlen($valor)-1] )
					{
						return true;
					}
				}
			}
			return false;
		}
		function search( $ruc_dni, $inJSON = false )
		{
			if( strlen(trim($ruc_dni))==8 )
			{
				$ruc_dni = $this->dnitoruc($ruc_dni);
			}
			if( strlen($ruc_dni)==11 && $this->valid($ruc_dni) )
			{
				$info = $this->getDataRUC($ruc_dni);
				if( $info!=false )
				{
					$rtn = array(
						"success" 	=> true,
						"result" 	=> $info
					);
				}
				else
				{
					$rtn = array(
						"success" 	=> false,
						"msg" 		=> "No se ha encontrado resultados."
					);
				}
				return ($inJSON==true)?json_encode($rtn, JSON_PRETTY_PRINT):$rtn;
			}

			$rtn = array(
				"success" 	=> false,
				"msg" 		=> "Nro de RUC o DNI no valido."
			);
			return ($inJSON==true)?json_encode($rtn, JSON_PRETTY_PRINT):$rtn;
		}

		// CONSULTA RUC DEL SISTEMA DE phuyu

		private function getValuesFromUrl($url)
	    {
	        $ctx = $this->getContext('GET', null, []);
	        $response = file_get_contents($url);
	        
	        /*$this->saveCookies($http_response_header);
	        if (false === $response) {
	            $this->error = 'Ocurrio un problema conectando a Sunat';

	            return false;
	        }

	        $dic = $this->parser->parse($response);
	        if (false === $dic) {
	            $this->error = 'No se encontro el ruc';

	            return false;
	        }

	        return $dic;*/
	        return mb_convert_encoding($response, 'HTML-ENTITIES', "UTF-8");
	    }

	    private function getRandom(): ?string
	    {
	        $ctx = $this->getContext('GET', null, []);
	        $response = file_get_contents(self::URL_RANDOM, false, $ctx);
	        //print_r($response);exit;
	        $patron='/<input type="hidden" name="numRnd" value="(.*)">/';
			$output = preg_match_all($patron, $response, $matches, PREG_SET_ORDER);
			if( isset($matches[0]) )
			{
				$response = trim($matches[0][1]);
			}
	        $this->saveCookies($http_response_header);

	        return false === $response ? '' : $response;
	    }

	    private function getCompany(array $items): ?Company
	    {
	        $cp = $this->getHeadCompany($items);
	        
	        $this->fixDirection($cp);

	        return $cp;
	    }

	    private function getHeadCompany(array $items): ?Company
	    {
	        $cp = new Company();
	        //dd($items);exit;
	        list($cp->ruc, $cp->razonSocial) = $this->getRucRzSocial($items['RUC:']);
	        $cp->nombreComercial = $items['Nombre Comercial:'];
	        $cp->telefonos = [];
	        $cp->tipo = $items['Tipo Contribuyente:'];
	        $cp->estado = $items['Estado:'];
	        $cp->condicion = $items['Condición:'];
	        $cp->direccion = $items['Domicilio Fiscal:'];
	        $cp->fechaInscripcion = $this->parseDate($items['Fecha de Inscripción:']);

	        return $cp;
	    }

	    private function fixDirection(Company $company)
	    {
	        $items = explode('                                               -', $company->direccion);
	        if (3 !== count($items)) {
	            $company->direccion = preg_replace("[\s+]", ' ', $company->direccion);

	            return;
	        }

	        $pieces = explode(' ', trim($items[0]));
	        list($len, $value) = $this->getDepartment(array_pop($pieces));
	        $company->departamento = $value;
	        $company->provincia = trim($items[1]);
	        $company->distrito = trim($items[2]);
	        array_splice($pieces, -1 * $len);
	        $company->direccion = join(' ', $pieces);
	    }

	        private function getDepartment($department): array
	    {
	        $department = strtoupper($department);
	        $words = 1;
	        switch ($department) {
	            case 'DIOS':
	                $department = 'MADRE DE DIOS';
	                $words = 3;
	            break;
	            case 'MARTIN':
	                $department = 'SAN MARTIN';
	                $words = 2;
	            break;
	            case 'LIBERTAD':
	                $department = 'LA LIBERTAD';
	                $words = 2;
	            break;
	        }

	        return [$words, $department];
	    }

	    private function parseDate($text)
	    {
	        if (empty($text) || '-' == $text) {
	            return null;
	        }

	        $date = \DateTime::createFromFormat('d/m/Y', $text);

	        return false === $date ? null : $date->format('Y-m-d').'T00:00:00.000Z';
	    }

	    private function getCpes($text)
	    {
	        $cpes = [];
	        if ('-' != $text) {
	            $cpes = explode(',', $text);
	        }

	        return $cpes;
	    }

	    private function getContext(string $method, $data, array $headers)
	    {
	        $options = [
	            'http' => [
	                'header' => $this->join(': ', $headers),
	                'method' => $method,
	                'content' => $this->getRawData($data),
	            ],
	        ];

	        if (is_array($this->options)) {
	            $options = array_merge_recursive($options, $this->options);
	        }

	        if (!empty($this->cookies)) {
	            $options['http']['header'] .= 'Cookie: '.$this->join('=', $this->cookies, '; ');
	        }

	        $context = stream_context_create($options);

	        return $context;
	    }

	    private function getRawData($data)
	    {
	        return is_array($data) ? http_build_query($data) : $data;
	    }

	    private function join(string $glue, array $items, string $end = "\r\n"): ?string
	    {
	        $append = '';
	        foreach ($items as $key => $value) {
	            $append .= $key.$glue.$value.$end;
	        }

	        return $append;
	    }

	    private function saveCookies(array $headers)
	    {
	        $cookies = [];
	        foreach ($headers as $hdr) {
	            if (preg_match('/^Set-Cookie:\s*([^;]+)/', $hdr, $matches)) {
	                parse_str($matches[1], $tmp);
	                $cookies = array_merge($cookies, $tmp);
	            }
	        }

	        if (!empty($cookies)) {
	            $this->cookies = $cookies;
	        }
	    }

	    private function getRucRzSocial($text)
	    {
	        $pos = strpos($text, '-');

	        $ruc = trim(substr($text, 0, $pos));
	        $rzSocial = trim(substr($text, $pos + 1));

	        return [$ruc, $rzSocial];
	    }
	}
