<?php
    class Reconoserid 
    {

        private $convenio_olimpia = 'CONVENIO_OLIMPIA';
        //Credenciales para generar token
        private $credencialesToken = ['ClientId'=>'CLIENT_ID', 'ClientSecret'=>'CLIENT_PASSWORD'];
        //Credenciales para login
        private $credencialesLogin = ['Login'=>'USER', 'Clave'=>'PASSWORD'];

        //Proceso
        private $procesoConvenioGuid = '';

        //URL IFRAME del proceso
        private $url_proceso = '';

        private $token = '';

        private $endpoints = ['TraerToken'=>'https://demorcs.olimpiait.com:6317/TraerToken',
         'SolicitudValidacion'=>'https://demorcs.olimpiait.com:6314/Validacion/SolicitudValidacion',
         'ConsultarProcesoConvenio'=>'https://demorcs.olimpiait.com:6317/ConsultarProcesoConvenio',
         'GuardarBiometria'=>'https://demorcs.olimpiait.com:6317/GuardarBiometria',
         'ValidarBiometria'=>'https://demorcs.olimpiait.com:6317/ValidarBiometria',
         'GuardarDocumentoAmbasCaras'=>'https://demorcs.olimpiait.com:6317/GuardarDocumentoAmbasCaras',
         'ConsultarValidacion'=>'https://demorcs.olimpiait.com:6314/Validacion/ConsultarValidacion'
        ];

        private $responses = ['sucesss'=>true, 'msg'=>''];
        

        public function __construct()
        {
            $this->traerToken();
        }

        /**
         * PASO 1
         */
        public function traerToken()
        {

            $objResponse = $this->curlRequest($this->endpoints['TraerToken'], $this->credencialesToken);

            if(is_object($objResponse) && isset($objResponse->accessToken))
            {
                $this->token = $objResponse->accessToken;
            }
        }

        /**
         * PASO 2 Iniciar un proceso
         */
        public function solicitudValidacion($doc='', $typedoc='CC', $email='', $phone='')
        {

            $data = ['guidConv'=>$this->convenio_olimpia,
                     'tipoValidacion' => '1',
                     'asesor' => 'ASESOR',
                     'sede' => 'SEDE',
                     'codigoCliente' => $this->generateClientCode(),
                     'tipoDoc' => $typedoc,
                     'numDoc' => $doc,
                     'email' => $email,
                     'celular' => $phone,
                     'usuario' => $this->credencialesLogin['Login'],
                     'clave' => $this->credencialesLogin['Clave'],
                     'InfCandidato'=> "{\"company\":\"COMPANY_NAME\"}"
                    ];

            $objResponse = $this->curlRequest($this->endpoints['SolicitudValidacion'], $data);

            if(is_object($objResponse))
            {
                if($objResponse->code == 200)
                {
                    $this->procesoConvenioGuid = $objResponse->data->procesoConvenioGuid;
                    $this->url_proceso = $objResponse->data->url;
                }
            }
        }

        /**
         * PASO 6 Consultar un proceso (Se puede saltar, si se utiliza la versión iframe de OLIMPIA 
         * para que sean ellos los que ofrezcan el servicio de subir foto y documentos. Luego de eso, hayq ue consultar ese proceso en
         * este paso)
         */

        public function ConsultarValidacion()
        {
            $data = $this->credencialesLogin;
            $data[] = ["guidConv" => $this->convenio_olimpia];
            $data[] = ['procesoConvenioGuid'=>$this->procesoConvenioGuid];
            $result = $this->curlRequest($this->endpoints['ConsultarValidacion'], $data);
        }
        
        public function getToken()
        {
            return $this->token;
        }

        public function getUrlProceso()
        {
            return $this->url_proceso;
        }

        public function generateClientCode($long=10)
        {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            return substr(str_shuffle($chars), 0, $long);
        }

        public function curlRequest($url, $data)
        {

            $headers = ['Content-Type: application/json'];

            if($this->token != ""){ $headers[] = "Authorization: Bearer ".$this->token; }

            try
            {

                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => $url,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'POST',
                  CURLOPT_POSTFIELDS => json_encode($data),
                  CURLOPT_HTTPHEADER => $headers,
                ));

                $response = curl_exec($curl);

                curl_close($curl);

                return $response != "" ? json_decode($response) : null;
                
            }catch(Exception $e){
                //var_dump(json_encode($e->getMessage()));
            }
        }
    }
?>