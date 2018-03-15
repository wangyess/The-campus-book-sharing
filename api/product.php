<?php
tpl('api/Api');

class Product extends Api
{
    public $table = 'product';

    public $rule=[
        'title'       => 'max_length:24|min_length:2',
        'price'       => 'numeric|positive',
        'isbn'        => 'numeric',
        'number_page' => 'numeric|integer',
    ];
}