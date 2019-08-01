<?php
/**
 * Created by PhpStorm.
 * User: conan
 * Date: 2019-01-07
 * Time: 15:39
 */

namespace Library;

use Medoo\Medoo;

final class Db extends Medoo
{
    /**
     * 实例数组
     * @var array
     */
    private static $_instance_arr = [];

    /**
     * @param $db_key
     * @return Db|null
     */
    public static function getInstance($db_key)
    {
        if (!isset(self::$_instance_arr[$db_key])) {
            $db_config = Config::get('db');
            if (!$db_config[$db_key]) {
                self::$_instance_arr[$db_key] = null;
            } else {
                self::$_instance_arr[$db_key] = new self([
                    // 必须配置项
                    'database_type' => $db_config[$db_key]['type'],
                    'database_name' => $db_config[$db_key]['dbname'],
                    'server'        => $db_config[$db_key]['host'],
                    'username'      => $db_config[$db_key]['user'],
                    'password'      => $db_config[$db_key]['pass'],
                    'charset'       => $db_config[$db_key]['charset'],
                    // 可选参数
                    'port'          => $db_config[$db_key]['port'],
                    // 可选，定义表的前缀
                    'prefix'        => $db_config[$db_key]['prefix'],
                    // 连接参数扩展, 更多参考 http://www.php.net/manual/en/pdo.setattribute.php
                    'option'        => [
                        \PDO::ATTR_CASE => \PDO::CASE_NATURAL
                    ]
                ]);
            }
        }
        return self::$_instance_arr[$db_key];
    }

    /**
     * @param $config
     */
    private function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * 防止用户克隆实例
     */
    public function __clone()
    {
        die('Clone is not allowed.' . E_USER_ERROR);
    }

    /**
     * 判断表是否存在
     * @param $table
     * @return bool
     */
    public function tableExist($table)
    {
        return $this->query("SHOW TABLES LIKE   '$table';") ? true : false;
    }

    /**
     * 获取表所有字段
     * @param $table
     * @return bool|\PDOStatement
     */
    public function getTableColumns($table)
    {
        return $this->query("SHOW COLUMNS FROM `$table`");
    }
}