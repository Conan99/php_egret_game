<?php
/**
 * Created by PhpStorm.
 * User: yangzhi
 * Date: 2018/7/19 0019
 * Time: 20:09
 */

namespace Library;

class JsonData
{
    const JSON_DATA_FILE_PATH = APP_PATH . "/JsonData";//json数据保存文件夹

    private static function _getFilePath($dir, $name)
    {
        Dir::make($dir);
        return $dir . "/" . self::_getFileName($name);
    }

    private static function _getFileName($name)
    {
        return $name . ".json";
    }

    /**
     * 保存App内的json数据(全量保存)
     * @param string $dir
     * @param string $name
     * @param $data
     */
    public static function saveAppJsonData($dir, $name, $data)
    {
        if (!$dir || !is_string($dir) || !$name || !is_string($name)) return;
        $dir = self::_getAppFileDir($dir);
        self::saveJsonData($dir, $name, $data);
    }

    /**
     * 获取App内的json数据
     * @param string $dir
     * @param string $name
     * @return null|int|string|array
     */
    public static function getAppJsonData($dir, $name)
    {
        if (!$dir || !is_string($dir) || !$name || !is_string($name)) return null;
        $dir  = self::_getAppFileDir($dir);
        $data = self::getJsonData($dir, $name);
        return $data;
    }

    /**
     * 获取App内的json文件夹路径
     * @param $dir
     * @return string
     */
    private static function _getAppFileDir($dir)
    {
        $file_dir = self::JSON_DATA_FILE_PATH . '/' . $dir;
        !file_exists($file_dir) && @mkdir($file_dir, 0777, true);
        return $file_dir;
    }

    /**
     * 保存json数据
     * @param $dir
     * @param $name
     * @param $data
     */
    public static function saveJsonData($dir, $name, $data)
    {
        $file_path = self::_getFilePath($dir, $name);
        $data      = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($file_path, $data);
    }

    /**
     * 获取json数据
     * @param $dir
     * @param $name
     * @return false|mixed|string
     */
    public static function getJsonData($dir, $name)
    {
        $file_path = self::_getFilePath($dir, $name);
        if (!is_file($file_path)) return null;
        $data = file_get_contents($file_path);
        $data = json_decode($data, true);
        return $data;
    }
}