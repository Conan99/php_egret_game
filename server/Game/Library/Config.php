<?php
/**
 * Created by PhpStorm.
 * User: conan
 * Date: 2019-01-06
 * Time: 00:09
 */

namespace Library;

class Config
{
    private static $_config = [];

    /**
     * 将配置信息加载到config中
     */
    public static function init()
    {
        if (self::$_config) return;
        $files = Dir::tree(APP_PATH . '/config/' . ENV, "/.php$/", true);
        if (!empty($files)) {
            foreach ($files as $file) {
                self::$_config += include_once "{$file}";
            }
        }
        self::_afterInit();
    }

    /**
     * 初始化一些在load配置文件就需要马上执行的文件配置参数
     */
    private static function _afterInit()
    {
        // 设置php进程使用的最大内存
        $memory = Config::get('memory_limit') ?: '1024M';
        ini_set('memory_limit', $memory);
    }

    /**
     * 获取配置组
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public static function get($key, $default = null)
    {
        return self::$_config[$key] ?? $default;
    }

    /**
     * 获取配置组中字段
     * @param $key
     * @param $field
     * @param null $default
     * @return mixed|null
     */
    public static function getField($key, $field, $default = null)
    {
        if (empty(self::$_config[$key])) return null;
        return self::$_config[$key][$field] ?? $default;
    }

    /**
     * 设置配置组
     * @param $key
     * @param null $value
     */
    public static function set($key, $value = null)
    {
        self::$_config[$key] = $value;
    }

    /**
     * 设置配置组中字段
     * @param $key
     * @param $field
     * @param $value
     */
    public static function setField($key, $field, $value)
    {
        self::$_config[$key][$field] = $value;
    }
}
