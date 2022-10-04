<?php
    ini_set('display_errors','0');
    require("lib_db.php");
    use Database\JsonDb as Db;

    $db = new Db();

    $requestMethod=($_SERVER["REQUEST_METHOD"])? $_SERVER["REQUEST_METHOD"] : 'GET';
    $urlPart = @explode('/',@$_SERVER['REDIRECT_URL']);
    $path = (strlen(end($urlPart))!=0)?end($urlPart):$urlPart[count($urlPart)-2];

    $ret = array();

    switch ($requestMethod) {
        case 'GET':
            $ret = (@$path)?$db->readBy($path, @$_GET):$db->readAll();
            break;

        case 'POST':
            $p = JSON_DECODE(file_get_contents("php://input"), true);
            if($p==null)
                $p=$_POST;
            $ret = (@$path && @$p && @$p['login'])?$db->login($path,@$p):((@$path && @$p)?$db->insert($path, @$p):['error'=>'Missing data...','data'=>$p]);
            break;

        default:
            $ret = ['error'=>'Unknow Error...'];
            break;
    }

    echo json_encode($ret);
