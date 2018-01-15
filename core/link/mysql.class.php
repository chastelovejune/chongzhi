<?php 
class mysql_server
{
    private $server;// 数据库
    private $table;//表名
    
    /**
     * 构造函数，建立一个全新的mysql连接
     * @param unknown $config            
     */
    public function __construct($_db)
    {
        $this->server = new PDO("mysql:host=".$_db['host'].";dbname=".$_db['dbname'].";charset=".$_db['charset'], $_db['user'], $_db['pass']);
        $charset = $_db['charset'];
        $this->server->query("set names $charset");
        $this->table = $_db['prefix'] . $_db['table'];
        if (empty($_db['table'])) exit('表初始化失败!');
    }

    /**
     * 执行原生SQL语句
     * @param unknown $query
     */
    public function sql($query) {
        $Model = $this->server->prepare ( "$query" ); // 参加预执行sql语句，pdo对象方法
        $Model->execute (); // 该方法为开始执行预处理sql语句
        $m_data = $Model->fetchAll ( 2 ); // 二维数组
        return $m_data; //
    }

    /**
     *
     * @author 心剑
     * @param unknown $table 该参数为数据库的表名
     * @param unknown $array 例子: array("username"=>'心剑',"password"=>'123456');
     * @return string
     */
    public function insert($array)
    {
        $into = ""; // 键名分散
        $val = array(); // 值加入到预执行
        $insert_num = 0; // 记录加入的个数
        $create_method = ''; // 预执行参数
                             // 循环将传进来的$array分开,键名为数据库的字段名,键值为添加的数据,与键名一一对应
        foreach ($array as $key => $value) {
            $into .= "{$key},";
            $val[] = $value;
            $insert_num ++;
        }
        // 有多少个键值,就循环多少个填充问号出来填充预执行参数
        for ($i = 0; $i < $insert_num; $i ++) {
            $create_method .= '?,';
        }
        // 去掉末尾的逗号,
        $create_method = rtrim($create_method, ",");
        $into = rtrim($into, ",");
        // 参加一条预执行sql语句,该方法为pdo对象
        $Model = $this->server->prepare("insert into {$this->table} ({$into}) values ({$create_method})");
        $exe = $Model->execute($val); // 该方法为开始执行预处理sql语句
        $method_id = $this->server->lastInsertId(); // 该方法为添加后该记录的id
        return $method_id; // 返回该记录id
    }

    /**
     *
     * @author 心剑
     * @param unknown $table 该参数为数据库的表名
     * @param unknown $array 例子: array("username"=>'心剑',"password"=>'123456');
     * @param string $where 该参数为 判断条件 例子： id=12
     * @return boolean
     */
    public function update($array, $where = '')
    {
        $data = ""; // 设置数据
        $update_data = array(); // 设置预执行的数据
        $ifwhere = ''; // where判断语句
        foreach ($array as $key => $value) {
            $data .= "{$key}=?,"; // 通过键名自动设置数据
            $update_data[] = $value; // 通过键值自动设置修改的数据
        }
        $data = rtrim($data, ","); // 去掉右边逗号
                                   // 加入where判断语句
        if (! empty($where)) {
            $ifwhere = "where {$where}";
        }
        $Model = $this->server->prepare("update {$this->table} set {$data} {$ifwhere}"); // 参加预执行sql语句，pdo对象方法
        $exe = $Model->execute($update_data); // 该方法为开始执行预处理sql语句
        return $exe; // 返回该记录id
    }

    /**
     *
     * @author 心剑
     * @param unknown $table 该参数为数据库的表名
     * @param unknown $where 该参数为 判断条件 例子： id=12 ,那么id是12的这行记录将会被删除, 如果不传$where条件，那么整个表的数据全部都会删除
     * @return boolean
     */
    public function delete($where = '')
    {
        // 加入where判断语句
        if (! empty($where)) {
            $ifwhere = "where {$where}";
        }
        $del = $this->server->exec("DELETE FROM {$this->table} {$ifwhere}"); // 空数组，表示这里我不需要预处理的数据
        return $del; // 返回删除是否成功
    }
    /**
     * @author 心剑
     * @param unknown $table 该参数为数据库的表名
     * @param string $join  联表查询，例子：left join table_b on table_a.fuck_id=table_b.id
     * @param string $where 该参数为 判断条件 例子： id=12 或者  like %哈哈% 
     * @param string $desc  该参数为倒序查询，输入一个唯一的字段名，如输入 id 
     * @param string $limit 该参数查询限制，从什么开始..每次查询多少记录，例子： 5,10 （从5开始查，向后查10条记录）、例子2： 10（只查10条记录出来）
     * @return Ambigous <>|multitype:
     */
    public function select($where = '', $join = '', $desc = '', $limit = '', $ap='*')
    {   
        $ifwhere = '';
        $order = '';
        $desc_limit = '';
        
        // 加入where判断语句
        if (! empty($where)) {
            $ifwhere = "where {$where}";
        }
        // 倒序查询
        if (! empty($desc)) {
            $order = "order by {$desc} desc";
        }
        // 从什么开始..每次查询多少记录
        if (! empty($limit)) {
            $desc_limit = "limit {$limit}";
        }
        $Model = $this->server->prepare("SELECT {$ap} FROM {$this->table} {$join} {$ifwhere} {$order} {$desc_limit}"); // 参加预执行sql语句，pdo对象方法
        $Model->execute(); // 空数组，表示这里我不需要预处理的数据
        $m_data = $Model->fetchAll(2); // 二维数组
        return $m_data; // 返回一个二维数组
        
    }
    
    /**
     * @author proty
     * 开启事务
     */
    public function openTransaction(){
        $this->server->beginTransaction();
    }
    
    /**
     * @author 心剑
     * 提交事务
     */
    public function commit(){
        $this->server->commit();
    }
    
    /**
     * @author 心剑
     * 回滚事务
     */
    public function rollBack(){
        $this->server->rollBack();
    }

    /**
     *
     * @param 析构函数,关闭数据库连接,释放资源
     */
    public function __destruct()
    {
        // ...
        $this->server = null; // 关闭连接
    } 
}

?>