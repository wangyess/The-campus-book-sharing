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
            if (!has_permission_to($arr[1], $arr[2])) {
                json_die(e('permission_denied'));
            }

            //实例化这个类
            $klass = new $method();
            //并且调用他的方法
            $r = $klass->$action($params, $msg);

            if ($r == false)
                json_die(e($msg));

            json_die(s($r));
            break;
        case 'admin':
            tpl('view/admin/' . $arr[1]);
            die();
            break;
        case '':
            tpl('view/public/home');
            break;
        case 'login':
            tpl('view/public/login');
            break;
        case 'signup':
            tpl('view/public/signup');
            break;
        case 'logout':
//            User::logout();
//            redirect('/login');
            break;
        default:
            break;
    }
}

//权限控制
function has_permission_to($model, $action)
{
    $public = [
        'user'    => ['signup', 'login', 'logout'],
        'product' => ['read', 'read_item', 'read_number'],
        'cat'     => ['read', 'read_number'],
    ];
    $private = [
        'product' => [
            'add'    => ['admin'],
            'change' => ['admin'],
            'remove' => ['admin'],
        ],
        'cat'     => [
            'add'    => ['admin'],
            'change' => ['admin'],
            'remove' => ['admin'],
        ],
        //            'user'    => [
        //                'remove' => ['admin'],
        //                'update' => ['admin'],
        //            ],
    ];

    //首先判断 是否存在这个类和这个方法
    if (!key_exists($model, $public) && !key_exists($model, $private)) {
        return false;
    }

    //如果存在这个类 在判断这个类中是否有这个方法
    $public_model = @$public[ $model ];
    if ($public_model && in_array($action, $public_model)) {
        return true;
    }

    $action_arr = @$private[ $model ];
    //判断方法是否在这个数组中
    if (!$action_arr || !key_exists($action, $action_arr)) {
        return false;
    }
    //权限数组
    $permission_arr = @$action_arr[ $action ];

    //获取当前用户权限
    $user_permission = @$_SESSION['user']['permission'];
    if (!in_array($user_permission, $permission_arr)) {
        return false;
    }

    return true;
}


