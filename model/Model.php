<?php
tpl('DB/Db');

class Model extends Db
{
    //这个是储存安全的传参的数组
    public $filled = [];

    public function __construct()
    {
        parent::__construct($this->table);
    }

    //过滤传参的函数
    public function filtration($params)
    {
        //定义一个过滤后的储存数组
        $safe_arr = [];
        //首先拿到该表都有的字段\
        $name_list = $this->all_column_name();
        //遍历传过来的参数 判断是否在$name_list这个数组里  不存在就跳过 存在就留下
        foreach ($params as $col => $val) {
            if (in_array($col, $name_list) == false)
                continue;
            $safe_arr[ $col ] = $val;
        }
        $this->filled = $safe_arr;
        return $this;
    }

    //执行
    public function start_execute(&$msg = [])
    {
        $filled = &$this->filled;
        //验证
        //判断是不是更新
        $is_update = (bool)@$id = @$filled['id'];
        //写到用户的时候要有加密

        //如果是更新
        if ($is_update) {
            $this->where('id', $id);
            //判断是否存在
            if (!$this->see()) {
                $msg['id'] = 'no_exist';
                return false;
            }
            //如果存在  更新一下时间
            if (!$filled['update_at']) {
                $filled['update_at'] = $this->set_time();
            }
            $this->where('id', $filled['id']);
            $r = $this->update($filled);
        }else{
            //删除ID  这是添加 不管有没有ID  都要有这个删除以防万一
            unset($filled['id']);
            if (!@$filled['created_at']) {
                $filled['created_at'] = $this->set_time();
            }
            if (!@$filled['update_at']) {
                $filled['update_at'] = $this->set_time();
            }
            if($this->insert($filled)){
                //如果添加成功 返回最后一条ID
                $r = $this->last_id();
            }else{
                return false;
            }
        }
        return $r;
    }

    //生成时间
    public function set_time()
    {
        date_default_timezone_set("PRC");
        return date('Y-m-d H:i:s', time());
    }

    //限制显示
    public function page($page,$limit=10){
        $this->limit($limit,($page-1)*$limit);
        return $this;
    }

}