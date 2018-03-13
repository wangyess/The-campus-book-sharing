<?php

class Db
{
    public $table;
    public $pdo;
    public $pdo_sta = 0;
    public $sql;
    public $sql_column;
    public $sql_value;
    public $sql_where;
    public $sql_update;
    public $sql_select;
    public $sql_order_by;
    public $sql_limit;
    public $sql_join;
    public $where_relation = ' and ';
    public $where_count = 0;

    public function __construct($table)
    {
        $this->table = $table;
        $this->connect();
    }

    //链接数据库
    public function connect()
    {
        $options = [
            /* 常用设置 */
            PDO::ATTR_CASE              => PDO::CASE_NATURAL, /*PDO::CASE_NATURAL | PDO::CASE_LOWER 小写，PDO::CASE_UPPER 大写， */
            PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION, /*是否报错，PDO::ERRMODE_SILENT 只设置错误码，PDO::ERRMODE_WARNING 警告级，如果出错提示警告并继续执行| PDO::ERRMODE_EXCEPTION 异常级，如果出错提示异常并停止执行*/
            PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL, /* 空值的转换策略 */
            PDO::ATTR_STRINGIFY_FETCHES => false, /* 将数字转换为字符串 */
            PDO::ATTR_EMULATE_PREPARES  => false, /* 模拟语句准备 */
        ];
        $host = config('db_host');
        $this->pdo = new PDO("mysql:dbname=" . config('db_database') . ";" . "host=$host;charset=utf8", config('db_username'), config('db_password'), $options);
    }

    //增加
    public function insert($rows)
    {
        foreach ($rows as $col => $val) {
            $this->sql_column .= " $col ,";
            $this->sql_value .= " '$val' ,";
        }
        $this->sql_column = trim($this->sql_column, ',');
        $this->sql_value = trim($this->sql_value, ',');
        $this->sql = "insert into $this->table ($this->sql_column) VALUES ($this->sql_value)";
        $r = $this->execute();
        $this->sql_init();
        return $r;
    }

    //删除
    public function delete()
    {
        $this->sql = "delete from $this->table $this->sql_where ";
        $r = $this->execute();
        $this->sql_init();
        return $r;
    }

    //修改
    public function update($rows)
    {
        //因为是更新 所以要把ID 去掉 ID不是随意更新的
        unset($rows['id']);
        foreach ($rows as $col => $val) {
            $this->sql_update .= " $col = '$val' ,";
        }
        $this->sql_update = trim($this->sql_update, ',');
        $this->sql = "update  $this->table set $this->sql_update  $this->sql_where";
        $r = $this->execute();
        $this->sql_init();
        return $r;
    }

    //查看
    public function see($type = null)
    {
        //判断调用是否调用了 select 如果没有调用一次
        if (!$this->sql_select)
            $this->select();
        $this->sql = "select $this->sql_select from $this->table  $this->sql_join $this->sql_where $this->sql_order_by  $this->sql_limit";
        $this->execute();
        $this->sql_init();
        return $this->get_data($type);
    }
    //选择
    public function select($row = null)
    {
        if (!$row) {
            $this->sql_select = " * ";
            return;
        } else {
            foreach ($row as $key) {
                $this->sql_select .= $key . ',';
            }
            $this->sql_select = trim($this->sql_select, ',');
            return $this;
        }
    }
    //排序
    public function order_by($col = 'id', $des = 'desc')
    {
        $this->sql_order_by = " order by $col $des ";
        return $this;
    }
    //限制
    public function limit($limit = 10, $offset = 0)
    {
        $this->sql_limit = " limit $offset, $limit ";
        return $this;
    }

    //准备
    public function prepare()
    {
        $this->pdo_sta = $this->pdo->prepare($this->sql);
    }

    //执行
    public function execute()
    {
        //先调准备之后在执行
        $this->prepare();
        return $this->pdo_sta->execute();
    }

    //获取数据
    public function get_data($type = null)
    {
        return $this->pdo_sta->fetchAll($type ? PDO::FETCH_NUM : PDO::FETCH_ASSOC);
    }

    public function where()
    {
        //调用make_where 函数  配合func_get_args(); 获得参数
        $this->where_relation = ' and ';
        return call_user_func_array([$this, 'make_where'], func_get_args());
    }

    public function or_where()
    {
        $this->where_relation = ' or ';
        return call_user_func_array([$this, 'make_where'], func_get_args());
    }

    //这个函数主要作用就是判断有多少参数 有哪些不同的类型 比如  id=1 或者id>10  或者price=10 and count=10
    public function make_where()
    {
        if (!$this->sql_where) {
            $this->sql_where = " where ";
        }
        //$args 就是参数组成的数组
        $args = func_get_args();

        if (count($args) == 2) {
            $this->make_where_sql($args[0], '=', $args[1]);
        } elseif (count($args) == 3) {
            $this->make_where_sql($args[0], $args[1], $args[2]);
        } else {
            if (is_array($args[0])) {
                foreach ($args[0] as $col => $val) {
                    $this->make_where_sql($col, '=', $val);
                }
            }
        }
        return $this;
    }

    //主要生成语句的函数
    public function make_where_sql($col, $operation, $val)
    {
        //如果已经存在 那么给他追加一个关系符号  and 或者or
        if ($this->where_count)
            $this->sql_where .= $this->where_relation;
        $this->sql_where .= " $col $operation '$val' ";
        $this->where_count++;
    }

    //初始化
    public function sql_init()
    {
        $this->sql =
        $this->sql_select =
        $this->sql_where =
        $this->sql_order_by =
        $this->sql_limit =
        $this->sql_column =
        $this->sql_value = '';
        $this->sql_join =
        $this->sql_update = '';
        $this->where_count = 0;
        $this->where_relation = 'AND';
    }

    //链接两个表  返回把两个表相同的字段的数据都合成一个返回
    public function join($biao, $arr)
    {
        $this->sql_join = "inner join $biao on $this->table.$arr[0]=$biao.$arr[1]";
        return $this;
    }

    //获取当前表所有字段名称
    public function all_column_name(){
        //字段名称数组
        $name_list = [];
        $data = $this->all_column();
        foreach ($data as $col) {
            $name_list[] = $col['Field'];
        }
        return $name_list;
    }

    public function all_column(){
        $this->sql = "desc $this->table";
        $this->execute();
        $r = $this->get_data();
        $this->sql_init();
        return $r;
    }

    //返回最后一次操作ID的值
    public function last_id()
    {
        return $this->pdo->lastInsertId();
    }

    //判断数据是否存在
    public function exist(){
        $this->limit(1);
        return (bool)$this->see();
    }

    //获取一条数据
    public function first()
    {
        $this->limit(1);
        return @$this->see()[0];
    }

    //拿到一条数据
    public function find($id)
    {
        return @$this
                    ->where('id', $id)
                    ->see()[0];
    }
}

?>