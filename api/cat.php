<?php
tpl('api/Api');


class Cat extends Api
{
    public $table = 'cat';

    //定义的规则 必须满足这个条件才能往数据库中添加
    public $rule = [
        'title' => 'max_length:24|min_length:2|unique:title',
    ];

}