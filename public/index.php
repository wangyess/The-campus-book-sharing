<?php
require_once('../util/helpper.php');
tpl('DB/Db');
tpl('model/Model');
tpl('api/Api');
tpl('api/cat');
tpl('api/product');
tpl('api/user');
tpl('api/sharebook');

init();
function init()
{
    session_start();
    router_path();
}

function router_path()
{
    //var_dump($_SERVER['REQUEST_URI']);
    //获取地址栏传参
    $uri = $_SERVER['REQUEST_URI'];
    $uri = trim(explode('?', $uri)[0], '/');
    $arr = explode('/', $uri);
    //获取传入的值
    $params = array_merge($_GET, $_POST);

    switch ($arr[0]) {
        case 'api':
            $method = ucfirst($arr[1]);
            $action = $arr[2];
            //用于放错误信息的
            $msg = [];
            //这里需要权限判断

            //实例化这个类
            $klass = new $method();
            //并且调用他的方法
            $r = $klass->$action($params, $msg);

            if ($r == false)
                json_die(e($msg));

            json_die(s($r));
            break;

    }


}
