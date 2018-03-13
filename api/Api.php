<?php
tpl('model/Model');

class Api extends Model
{

    //增加
    public function add($params = [], &$msg)
    {
        //首先过滤传参
        return $this->filtration($params)
            ->start_execute($msg);
    }

    //删除
    public function remove($params = [], &$msg)
    {
        //判断是否传入ID
        if (!$id = @$params['id']) {
            $msg['id'] = 'invalid_id';
            return false;
        }
        return $this->where('id', $id)
            ->delete();
    }

    //修改
    public function change($params = [], &$msg)
    {
        return $this->filtration($params)
            ->start_execute($msg);
    }

    //查看
    public function read($params = [], &$msg)
    {
        $page = @$params['page'] ?: 1;
        $limit = @$params['limit'] ?: 10;
        return $this->order_by('id')
            ->page($page, $limit)
            ->see();
    }

    //获取数据的总数
    public function read_number($params = [], &$msg)
    {
        return $this->select(['count(*)'])
                   ->see(true)[0][0];
    }

    //拿到一条数据
    public function read_item($params = [], &$msg)
    {
        if (!$id = @$params['id']) {
            $msg = 'invalid_id';
            return false;
        }
        return $this->where('id', $id)
            ->first();
    }

}