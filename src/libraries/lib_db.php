<?php
namespace Database;

require_once "lib_jwt.php";

use Token\JWT as Jwt;

class JsonDb {
    private string $file;
    private array $data = [];
    // private $token = new Jwt();

    public function __construct(string $filepath = __DIR__ . "/../../data/db.json") {
        $this->file = $filepath;

        if(file_exists($this->file)) {
            $json = file_get_contents($this->file);
            $this->data = json_decode($json, true) ?? [];
        }
        else {
            $this->data = [];
        }
    }

    function handleGet(array $segments): void {
        $data = $this->data;

        if(!array_key_exists($segments[0], $data)) {
            http_response_code(404);
            echo json_encode(["error" => "Collection {$segments[0]} not found"], JSON_PRETTY_PRINT);
            return;
        }

        if(count($segments) === 1) {
            echo json_encode([$segments[0] => $data[$segments[0]]], JSON_PRETTY_PRINT);
            return;
        }

        if((count($segments) - 1) % 2 !== 0) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid filter format : filters must be in key/value pairs"], JSON_PRETTY_PRINT);
            return;
        }

        $data = $data[$segments[0]];

        for($i = 1; $i < count($segments); $i += 2) {
            $key = $segments[$i];
            $value = $segments[$i + 1];

            $hasKey = array_reduce($data, function ($carry, $item) use ($key) {
                return $carry || array_key_exists($key, $item);
            });

            if(!$hasKey) {
                http_response_code(400);
                echo json_encode(["error", "Key '{$key}' not found in any item of collection '${segments[0]}'"], JSON_PRETTY_PRINT);
                return;
            }
            
            $data = array_filter($data, function ($item) use ($key, $value) {
                return isset($item[$key]) && (string)$item[$key] === (string)$value;
            });
        }

        echo json_encode(array_values($data), JSON_PRETTY_PRINT);
        return;
    }

    function handlePost(array $segments): void {
        if(count($segments) !== 1) {
            http_response_code(400);
            echo json_encode(["error" => "POST must target a single collection"], JSON_PRETTY_PRINT);
            return;
        }

        $collection = $segments[0];
        $data = $this->data;

        if(!isset($data[$collection])) {
            http_response_code(404);
            echo json_encode(["error" => "Collection '{$collection}' not found"], JSON_PRETTY_PRINT);
            return;
        }

        $rawInput = file_get_contents("php://input");
        $input = json_decode($rawInput, true);

        if(!is_array($input)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON body"], JSON_PRETTY_PRINT);
            return;
        }

        $ids = array_column($data[$collection], 'id');
        $newId = empty($ids) ? 1 : max($ids) + 1;
        $input["id"] = $newId;

        $data[$collection][] = $input;
        $this->data = $data;

        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));

        http_response_code(201);
        echo json_encode($input, JSON_PRETTY_PRINT);
        return;
    }

    function handlePut(array $segments): void {
        if(count($segments) !== 1) {
            http_response_code(400);
            echo json_encode(["error" => "PUT must target a single collection"], JSON_PRETTY_PRINT);
            return;
        }

        $collection = $segments[0];
        $data = $this->data;

        if(!isset($data[$collection])) {
            http_response_code(404);
            echo json_encode(["error" => "Collection '{$collection}' not found"], JSON_PRETTY_PRINT);
            return;
        }

        $rawInput = file_get_contents("php://input");
        $input = json_decode($rawInput, true);

        if(!is_array($input)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON body"], JSON_PRETTY_PRINT);
            return;
        }

        error_log(json_encode($input));
        $updated = null;

        foreach ($data[$collection] as $idx => $item) {
            if((int)$item['id'] === (int)$input['id']) {
                $updated = array_replace($item, $input);
                $data[$collection][$idx] = $updated;
                $this->data = $data;
                file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
            }
        }

        http_response_code(201);
        echo json_encode($updated, JSON_PRETTY_PRINT);
        return;
    }

    // // // // // // // // //
    // // // // // // // // //
    // // // // // // // // //
    // // // // // // // // //
    // // // // // // // // //
    //
    // OLD CODE START HERE
    //
    // // // // // // // // //
    // // // // // // // // //
    // // // // // // // // //
    // // // // // // // // //
    // // // // // // // // //
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
