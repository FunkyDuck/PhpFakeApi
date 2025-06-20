<?php
namespace Database;

require_once "lib_jwt.php";
require_once "lib_validator.php";

use Token\JWT as Jwt;
use JsonValidator\JsonValidator as Validator;

class JsonDb {
    private string $file;
    private array $data = [];
    // private $token = new Jwt();

    public function __construct(string $collection) {
        $filepath = __DIR__ . "/../../data/{$collection}.json";
        $this->file = $filepath;

        if(file_exists($this->file)) {
            $json = file_get_contents($this->file);
            $this->data = json_decode($json, true) ?? [];
        }
        else {
            throw new \RuntimeException("Collection [{$collection}] not found");
        }
    }

    function handleGet(string $collection, ?string $resourceId = null): void {
        if(empty($collection)) {
            http_response_code(400);
            echo json_encode(["error" => "No collection specified"], JSON_PRETTY_PRINT);
            return;
        }

        if(!$resourceId) {
            echo json_encode(array_values($this->data), JSON_PRETTY_PRINT);
            return;
        }

        $data = null;

        foreach ($this->data as $key => $value) {
            if($value['id'] == $resourceId) {
                $data = $value;
            }
        }

        if(!$data) {
            http_response_code(404);
            echo json_encode(["error" => "Item id [{$resourceId}] not found"], JSON_PRETTY_PRINT);
            return;
        }

        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    function handlePost(string $collection, ?string $resourceId): void {
        if(!$collection || $resourceId) {
            http_response_code(400);
            echo json_encode(["error" => "POST must target a single collection"], JSON_PRETTY_PRINT);
            return;
        }

        $data = $this->data;
        
        $rawInput = file_get_contents("php://input");
        $input = json_decode($rawInput, true);

        $schemaPath = __DIR__ . "/../../data/{$collection}-schema.json";
        if(!file_exists($schemaPath)) {
            echo json_encode(["error" => "Schema file not found for '{$collection}'"], JSON_PRETTY_PRINT);
            http_response_code(500);
            return;
        }

        $schema = json_decode(file_get_contents($schemaPath), true);
        if(!$schema) {
            echo json_encode(["error" => "Invalid schema file"], JSON_PRETTY_PRINT);
            http_response_code(500);
            return;
        }

        if(!$input || !is_array($input)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid or missing JSON body", "input" => $input], JSON_PRETTY_PRINT);
            return;
        }

        $errors = Validator::validate($input, $schema);

        if(!empty($errors)) {
            http_response_code(400);
            echo json_encode(["error" => "validation failed", "details" => $errors], JSON_PRETTY_PRINT);
            return;
        }

        $ids = array_column($this->data, "id");
        $input["id"] = empty($ids) ? 1 : max($ids) + 1;
        $this->data[] = $input;
        file_put_contents($this->file, json_encode($this->data, JSON_PRETTY_PRINT));

        http_response_code(201);
        echo json_encode($input, JSON_PRETTY_PRINT);
        return;
    }

    function handlePut(string $collection, ?string $resourceId): void {
        if(!$collection) {
            http_response_code(400);
            echo json_encode(["error" => "PUT requests must specify a collection"], JSON_PRETTY_PRINT);
            return;
        }

        if(!$resourceId) {
            http_response_code(400);
            echo json_encode(["error" => "PUT requests must specify a resource ID"], JSON_PRETTY_PRINT);
            return;
        }

        $data = $this->data;
        
        $rawInput = file_get_contents("php://input");
        $input = json_decode($rawInput, true);

        $schemaPath = __DIR__ . "/../../data/{$collection}-schema.json";
        if(!file_exists($schemaPath)) {
            echo json_encode(["error" => "Schema file not found for '{$collection}'"], JSON_PRETTY_PRINT);
            http_response_code(500);
            return;
        }

        $schema = json_decode(file_get_contents($schemaPath), true);
        if(!$schema) {
            echo json_encode(["error" => "Invalid schema file"], JSON_PRETTY_PRINT);
            http_response_code(500);
            return;
        }

        if(!$input || !is_array($input)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid or missing JSON body", "input" => $input], JSON_PRETTY_PRINT);
            return;
        }

        $errors = Validator::validate($input, $schema);

        if(!empty($errors)) {
            http_response_code(400);
            echo json_encode(["error" => "validation failed", "details" => $errors], JSON_PRETTY_PRINT);
            return;
        }

        if($input['id']) {
            unset($input['id']);
        }

        foreach ($data as $resource => $item) {
            if($item["id"] == $resourceId) {
                $updated = array_replace($item, $input);
                $data[$resource] = $updated;
                $this->data = $data;
                file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
            }
        }

        http_response_code(204);
        return;
    }

    function handleDelete(string $collection, ?string $resourceId): void {
        if(!$collection) {
            http_response_code(400);
            echo json_encode(["error" => "DELETE requests must specify a collection"], JSON_PRETTY_PRINT);
            return;
        }

        if(!$resourceId) {
            http_response_code(400);
            echo json_encode(["error" => "DELETE requests must specify a resource ID"], JSON_PRETTY_PRINT);
            return;
        }

        $data = $this->data;

        foreach ($data as $resource => $item) {
            if($item['id'] == $resourceId) {
                unset($data[$resource]);
                $this->data = $data;
                file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));

                http_response_code(204);
                return;
            }
        }
        
        http_response_code(404);
        echo json_encode(["error" => "Item with id '{$resourceId}' not found"], JSON_PRETTY_PRINT);
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
}
