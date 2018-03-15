<?php
tpl('api/Api');

class User extends Api
{
    public $table = 'user';

    public $rule = [
        'username' => 'max_length:24|min_length:2|unique:username',
        'password' => 'max_length:24|min_length:6',
    ];

    //注册
    public function signup($rows, &$msg = null)
    {
        //判断是否输入用户名和密码
        if (!@$rows['username'] || !@$rows['password']) {
            $msg = "invalid_username_or_password";
            return false;
        }

        //如果都输入了 开始
        return $this->add($rows, $msg);
    }

    //登录
    public function login($rows, &$msg)
    {
        //判断是否输入用户名和密码  缺一不可
        if (!($username = @$rows['username']) || !($password = @$rows['password'])) {
            $msg = 'invalid_username&&password';
            return false;
        }
        //在判断数据库中是否有这个用户
        $user = $this->where('username', $username)
            ->where('password', self::password_encryption($password))
            ->first();
        if (!$user) {
            $msg = 'username||password_no_exist';
            return false;
        }
        unset($user['password']);
        $_SESSION['user'] = $user;

        return true;
    }

    //退出
    public function logout()
    {
        unset($_SESSION['user']);
        return true;
    }

    //修改权限或者根系
    //注册时密码加密
    public function before_encryption()
    {
        if (!$password = &$this->filled['password'])
            return false;
        $password = self::password_encryption($password);
    }

    //登录时密码加密   只有self::  能调用这个方法  静态方法
    public static function password_encryption($password)
    {
        return md5(md5($password) . 'wangye');
    }

    //判断是否登录成功
    function is_login()
    {
        $key = (bool)@$_SESSION['user']['id'];
        if ($key) {
            return $_SESSION['user'];
        } else {
            return false;
        }
    }
}