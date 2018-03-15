<?php

class Validator extends Db
{
    public function __construct($table) { parent::__construct($table); }

    //拆分规则  传到这里的是一条规则 比如 "'title' => 'max_length:24|min_length:2|unique:title'"
    public function parsing_rules($rule)
    {
        $rule_arr = explode('|', $rule);
        $arr = [];
        foreach ($rule_arr as $rule) {
            $item_rule = explode(':', $rule);
            if (count($item_rule) == 1) {
                $arr[ $item_rule[0] ] = true;
            } else {
                $arr[ $item_rule[0] ] = $item_rule[1];
            }
        }
        return $arr;
    }

    public function validator_rule($key, $rule, &$error = null)
    {
        if (is_string($rule))
            $rule_arr = $this->parsing_rules($rule);

        if (!$rule_arr)
            return true;

        foreach ($rule_arr as $col => $val) {
            $method = "validator_" . $col;
            $r = $this->$method($key, $val);

            if (!$r) {
                $error = "validator_" . $col;
                return false;
            }
        }
        return true;
    }

    //最大长度
    public function validator_max_length($key, $val)
    {
        $key = (string)$key;
        return strlen($key) <= $val;
    }

    //最小长度
    public function validator_min_length($key, $val)
    {
        $key = (string)$key;
        return strlen($key) >= $val;
    }

    //判断唯一性
    public function validator_unique($key, $val)
    {
        return !$this->is_exist($key, $val);
    }

    //判断非负数
    public function validator_positive($val)
    {
        $val = (float)$val;
        return $val >= 0;
    }

    //判断为整数
    function validator_integer($val)
    {
        if (!is_numeric($val))
            return false;
        $val = (string)$val;
        $r = strpos($val, '.') === false;
        return $r;
    }

    //判断为数字
    public function validator_numeric($val)
    {
        return is_numeric($val);
    }

    //检查是否已经存在
    public function is_exist($key, $val)
    {
        return $this->where($val, $key)
            ->exist();
    }

}