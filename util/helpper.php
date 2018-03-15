<?php
//专门返回错误信息的
function e($msg, $code = 403)
{
    if ($msg == 'db_internal_error')
        $code = 500;

    http_response_code($code);

    return ['success' => false, 'msg' => $msg];
}

//返回成功的
function s($data, $code = 200)
{
    http_response_code($code);
    return ['success' => true, 'data' => $data];
}

//获取绝对路径 并且加载这个文件
function tpl($path)
{
    require_once(path($path));
}

//获取绝对路径
function path($path, $type = 'php')
{
    return (__DIR__ . '/../' . $path . ($type ? '.' . $type : ''));
}

//解析配置文件 拿数据链接数据库
function config($key)
{
    if (!$config = @$GLOBALS['__config']) {
        //把文件读成字符串
        $json = file_get_contents(path('.config', 'json'));
        $config = json_decode($json, true);
        $GLOBALS['__config'] = $config;
    }

    return @$config[ $key ];
}

//转化成JSON格式
function json($data)
{
    header('Content-Type: application/json');
    return json_encode($data);
}

function json_die($data)
{
    echo json($data);
    die();
}

//差一个图片

