<?php
    namespace Token;

    class JWT{
        public $key = 'PhpJsonLightApi'; // Private Key
        public $header = '';
        public $payload = '';
        public $signature = '';

        function set_header(){
            $h = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
            $this->header = $this->encoding_b64($h);

            return $this->header;
        }

        function set_payload($id = 0, $name = '', $role = 0){
            $p = json_encode(['sub' => $id, 'name' => $name, 'role' => $role, "iat" => time(), "exp" => time() + (36000*3)]);
            $this->payload = $this->encoding_b64($p);

            return $this->payload;
        }

        function set_signature(){
            $s = hash_hmac('sha256', $this->header . '.' . $this->payload, $this->key, true);
            $this->signature = $this->encoding_b64($s);

            return $this->signature;
        }

        function encoding_b64($str){
            return str_replace(['+','/','='],['-','_',''], base64_encode($str));
        }

        function create_JWT($id = 0, $name = '', $role = 0){
            return $this->set_header() . '.' . $this->set_payload($id,$name, $role) . '.' . $this->set_signature();
        }

        function decode_JWT($token){
            $jwt_part = explode('.', $token);

            $this->header = $jwt_part[0];
            $this->payload = $jwt_part[1];
            $this->sign = $jwt_part[2];

            if($this->set_signature() == $this->sign){
                $this->header = base64_decode($jwt_part[0]);
                $this->payload = base64_decode($jwt_part[1]);
                $this->sign = base64_decode($jwt_part[2]);
            }else{
                $this->header = null;
                $this->payload = null;
                $this->sign = null;
            }
        }
    }
