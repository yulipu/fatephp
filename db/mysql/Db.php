<?php
namespace y\db\mysql;

use PDO;

class Db extends \y\db\ImplDb {
    use \y\db\DbOperationTrait;
    
    /**
     * @var int 操作类型
     */
    private $_operate = 0;
    
    /**
     * @var string sql 语句
     */
    private $_sql = '';
    
    /**
     * @var array 数据
     */
    private $_data = [];
    
    /**
     * @var array 操作
     */
    private $_options = [];
    
    /**
     * @var string 表前缀
     */
    private $_tablePrefix = '';

    public function __construct($dsn, $username, $password, $options = []) {
        $options = array_merge($options, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $this->pdo = new PDO($dsn, $username, $password, $options);
    }
    
    public function initConnection(& $config) {
        if(isset($config['charset'])) {
            $this->pdo->exec('SET NAMES \''. $config['charset'] .'\'');
        }
        if(isset($config['prefix'])) {
            $this->_tablePrefix = $config['prefix'];
        }
    }
    
    private function resetOption() {
        $this->_options = [];
        $this->_data = [];
    }
    
    private function closeStatement() {
        $this->pdoStatement->closeCursor();
        $this->pdoStatement = null;
    }
    
    private function closePdo() {
        $this->pdo = null;
    }
    
    private function countDim($array) {
        static $dimcount = 1;
        if(is_array(reset($array))) {
           $dimcount++;
           $ret = $this->countDim(reset($array));
           
        } else {
           $ret = $dimcount;
        }
        
        return $ret;
    }
    
    private function buildCols() {
        $ret = '';
        $length = $this->countDim($this->_data);
        if(1 === $length) {
            $ret = implode('`,`', array_keys($this->_data));
            
        } else if($length > 1) {
            $tmp = $this->_data[0];
            $ret = implode('`,`', array_keys($tmp));
        }
        
        return '' === $ret ? '()' : '(`' . $ret . '`)';
    }
    
    private function buildValues() {
        $ret = '';
        $length = $this->countDim($this->_data);
        if(1 === $length) {
            $ret = implode('\',\'', array_values($this->_data));
            
        } else if($length > 1) {
            $tmp = [];
            // 二维遍历
            foreach($this->_data as $v) {
                $tmp[] = implode('\',\'', array_values($v));
            }
            
            $ret = implode('\'),(\'', $tmp);
        }
        
        return '' === $ret ? '()' : '(\'' . $ret . '\')';
    }
    
    private function buildSet() {
        $ret = '';
        foreach($this->_data as $k => $v) {
            $ret .= "`{$k}`='{$v}',";
        }
        
        return '' === $ret ? '' : 'SET ' . rtrim($ret, ',');
    }
    
    private function initSql() {
        $sql = '';
        switch($this->_operate) {
            case self::$INSERT :
                // insert into t() values()[,(),()...]
                $table = isset($this->_options['table']) ? $this->_options['table'] : '';
                $cols = $this->buildCols();
                $values = $this->buildValues();
                $sql = 'INSERT INTO ' . $table . $cols . ' VALUES' . $values;
                
                break;
            
            case self::$DELETE :
                // delete from t where x
                $table = isset($this->_options['table']) ? $this->_options['table'] : '';
                
                $where = isset($this->_options['where']) ? 
                    ' WHERE ' . $this->_options['where'] : '';
                
                if('' !== $where) {
                    $sql = 'DELETE FROM ' . $table . $where;
                }   
                
                break;
            
            case self::$UPDATE :
                // update t set x=y, a=b where x
                $table = isset($this->_options['table']) ? $this->_options['table'] : '';
                $set = $this->buildSet();
                
                $where = isset($this->_options['where']) ? 
                    ' WHERE ' . $this->_options['where'] : '';
                
                if('' !== $where) {
                    $sql = 'UPDATE '. $table . ' ' . $set . $where;
                }
                
                break;
            
            case self::$SELECT :
                // select * from t
                // where x
                // group by x
                // having x
                // order by x
                // limit x
                $fields = isset($this->_options['fields']) ? $this->_options['fields'] : '*';
                $table = isset($this->_options['table']) ? $this->_options['table'] : '';
                
                $where = isset($this->_options['where']) ? 
                    ' WHERE ' . $this->_options['where'] : '';
                $groupBy = isset($this->_options['groupBy']) ? 
                    ' GROUP BY ' . $this->_options['groupBy'] : '';
                $having = isset($this->_options['having']) ? 
                    ' HAVING ' . $this->_options['having'] : '';
                $orderBy = isset($this->_options['orderBy']) ? 
                    ' ORDER BY ' . $this->_options['orderBy'] : '';
                $limit = isset($this->_options['limit']) ? 
                    ' LIMIT ' . $this->_options['limit'] : '';
                
                $sql = 'SELECT ' . $fields . ' FROM ' . $table . 
                    $where . 
                    $groupBy . 
                    $having . 
                    $orderBy . 
                    $limit;
                    
                break;
            
            default :
                break;
        }
        
        // 重置条件
        $this->resetOption();
        
        return $sql;
    }
    
    /**
     * 返回上一次执行的 sql 语句
     */
    public function getLastSql() {
        return $this->_sql;
    }
    
    /**
     * 指定查询字段
     *
     * @param string $fields 要查询的字段
     */
    public function fields($fields) {
        $this->_options['fields'] = $fields;
        
        return $this;
    }
    
    /**
     * 指定查询表
     *
     * @param string $table 要查询的表
     */
    public function table($table) {
        $this->_options['table'] = $this->_tablePrefix . $table;
        
        return $this;
    }
    
    /**
     * 指定查询条件
     *
     * @param string $condition 查询条件
     */
    public function where($condition) {
        $this->_options['where'] = $condition;
        
        return $this;
    }
    
    /**
     * 指定排序条件
     *
     * @param string $order 排序条件
     */
    public function orderBy($order) {
        $this->_options['orderBy'] = $order;
        
        return $this;
    }
    
    /**
     * 获取记录
     */
    public function getAll() {
        $this->_operate = self::$SELECT;
        $sql = $this->_sql = $this->initSql();
        
        $this->trigger(self::EVENT_BEFORE_QUERY, $this);
        $data = $this->querySql($sql);
        $this->trigger(self::EVENT_AFTER_QUERY, $this);
        
        return $data;
    }
    
    /**
     * 获取一条记录
     *
     * @return array 结果集
     */
    public function getOne() {
        
        return $this;
    }
    
    /**
     * 限制条数
     *
     * @param string | int $limit
     */
    public function limit($limit) {
        $this->_options['limit'] = $limit;
        
        return $this;
    }
    
    /**
     * 插入记录
     *
     * @param array $data 数据
     * @return int insertId
     */
    public function insert(& $data) {
        $this->_operate = self::$INSERT;
        $this->_data = $data;
        $sql = $this->initSql();

        return $this->executeSql($sql);
    }
    
    /**
     * 删除记录
     *
     * @return int 影响行数
     */
    public function delete() {
        $this->_operate = self::$DELETE;
        $sql = $this->initSql();

        return $this->executeSql($sql);
    }
    
    /**
     * 修改记录
     *
     * @param array $data 数据
     * @return int 影响行数
     */
    public function update(& $data) {
        $this->_operate = self::$UPDATE;
        $this->_data = $data;
        $sql = $this->initSql();
        
        return $this->executeSql($sql);
    }
    
    /**
     * 获取记录数
     *
     * @param string $field 列
     * @return int 结果
     */
    public function count($field = '*') {
        $table = isset($this->_options['table']) ? $this->_options['table'] : '';
        $where = isset($this->_options['where']) ? 
            ' WHERE ' . $this->_options['where'] : '';
            
        $sql = "SELECT COUNT({$field}) FROM `{$table}` {$where}";
        $stat = $this->prepareStatement($sql);
        
        return $stat->fetchColumn();
    }
    
    /**
     * 执行 sql 语句
     *
     * @param string $sql sql 语句
     * @param array $params 参数
     * @return PDOStatement
     */
    public function prepareStatement($sql, $params = null) {
        if(null === $params) {
            $this->pdoStatement = $this->pdo->query($sql);
        
        } else if(is_array($params)){
            $this->pdoStatement = $this->pdo->prepare($sql);
            $this->pdoStatement->execute($params);
        }
        
        return $this->pdoStatement;
    }
    
    /**
     * 执行 sql 语句
     *
     * @param string $sql sql 语句
     * @return array 结果数组
     */
    public function querySql($sql, $fetchStyle = PDO::FETCH_ASSOC) {
        $this->prepareStatement($sql);
        $data = $this->pdoStatement->fetchAll($fetchStyle);
        $this->closeStatement();
        
        return $data;
    }
    
    /**
     * 执行 sql 语句
     *
     * @param string $sql sql 语句
     * @return int 影响行数
     */
    public function executeSql($sql) {
        return $this->pdo->exec($sql);
    }
    
}
