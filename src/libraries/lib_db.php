<?php
    namespace Database;

    require('lib_jwt.php');
    use Token\JWT as Jwt;

    class JsonDb {
        private $fn = 'db.json'; // $fn is for filename
        // private $token = new Jwt();

        function login($c, $s=array()){
            $data=file_get_contents($this->fn);

            $password = $s['password'];
            unset($s['password']);
            unset($s['login']);

            $ret = $this->readBy($c,$s,true);
            if(PASSWORD_VERIFY($password,$ret->password)){
                $jwt = new Jwt();
                $ret = ["token"=>$jwt->create_JWT($ret->id,"{$ret->firstname} {$ret->lastname}", $ret->usertype)];
            }
            else{
                $ret = ["error"=>"Bad credentials..."];
            }

            return $ret;
        }

        function insert($c, $s = array()) {
            $data = file_get_contents($this->fn);

            $id = 1;
            $newData = array();

            foreach (json_decode($data) as $k => $v) {
                if($k==$c){
                    if(@$v['0']->id || @$s['id']){
                        $id += count($v);
                        $s += ["id"=>$id];
                    }
                    if(@$s['password']){
                        $s['password']=PASSWORD_HASH($s['password'], PASSWORD_DEFAULT);
                    }
                    $v += [count($v)=>$s];
                }
                $newData +=[$k => $v];
            }

            file_put_contents($this->fn, json_encode($newData), LOCK_EX);
            return ['error'=>'Insert successfull','data'=>$s];
        }

        function readAll(){
            $data = file_get_contents($this->fn);
            return json_decode($data);
        }

        function readBy($c, $s = array(),$login=false){
            $data = file_get_contents($this->fn);
            $keys = array_keys($s);
            $ret = [];
            foreach (json_decode($data) as $k => $v) {
                if($k==$c){
                    if(@$keys[0]){
                        for ($i=0; $i <count($v) ; $i++) {
                            foreach ($v[$i] as $key => $value) {
                                if($keys[0]==$key && $s[$keys[0]]==$value && $login){
                                    $ret = $v[$i];
                                    break;
                                }else if($keys[0]==$key && $s[$keys[0]]==$value){
                                    unset($v[$i]->password);
                                    array_push($ret, $v[$i]);
                                    break;
                                }
                            }
                        }
                    }else{
                        $ret = $v;
                    }
                }
            }
            return $ret;
        }
    }
