<?php
/**
 * Created by PhpStorm.
 * User: conan
 * Date: 2019-01-16
 * Time: 18:22
 */

namespace Library;

class Log
{
    /**
     * 过滤前后‘/’以及特殊字符
     * @param $path_name
     * @return string|string[]|null
     */
    private static function pathNameReplace($path_name)
    {
        // pathName只能同英文数字和下划线、'/'组成，并且不能最前或最后带 /
        $path_name = preg_replace('#[^\w/]#', '', $path_name);
        $path_name = preg_replace('#^/#', '', $path_name);
        $path_name = preg_replace('#/$#', '', $path_name);
        return $path_name;
    }

    /**
     * 获取框架的基础日志目录地址，如果此目录没有创建并创建目录
     * @return string
     */
    public static function getBasePath()
    {
        $basePath = APP_PATH . '/Log';
        if (!empty(Config::getField('project', 'log_path', ''))) {
            $basePath .= Dir::DS . Config::getField('project', 'log_path', '');
        }
        Dir::make($basePath);
        return $basePath;
    }

    /**
     * 创建目录并返回当前文件路径
     * @param $path_name
     * @param bool $is_make
     * @return string
     */
    public static function getLogPath($path_name, $is_make = true)
    {
        // 1、过滤特殊字符和前后的'/'
        $path_name = self::pathNameReplace($path_name);
        // 2、如果path_name含有'/'，拿到真正的文件名
        if (preg_match('#/#', $path_name)) {
            // 真实文件名
            $log_name = preg_replace("#(.*)/#", '', $path_name);
            // 真实的目录
            $path = preg_replace("#/{$log_name}$#", '', $path_name);
        } else {
            $log_name = $path_name;
            $path     = "";
        }

        // 3、拼接真实的日志地址
        $log_path = empty($path) ? self::getBasePath() : self::getBasePath() . Dir::DS . $path;

        if ($is_make === true) {
            // 如果目录没有，创建目录
            Dir::make($log_path);
        }

        // 4、返回创建好日志目录的真实的日志地址
        $log_file = $log_path . Dir::DS . $log_name . ".log";
        return $log_file;
    }

    /**
     * 记录原始日志 保留5天日志
     * @param $msg
     * @param string $file_name
     */
    public static function save($msg, $file_name = 'debug')
    {
        $file_name     = self::pathNameReplace($file_name);
        $old_file_name = $file_name . '_' . date('Y-m-d', time() - 60 * 60 * 24 * (intval(Config::get('log_days', 7))));
        self::unlink($old_file_name);
        $file_name = $file_name . '_' . date('Y-m-d', time());
        $log_file  = self::getLogPath($file_name);
        \file_put_contents($log_file, $msg . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * 保留原始日志, 不自动删除日志文件
     * @param $msg
     * @param string $file_name
     */
    public static function show($msg, $file_name = 'debug')
    {
        $log_file = self::getLogPath($file_name);
        \file_put_contents($log_file, $msg . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * 删除某个日志文件
     * @param string $file_name
     */
    public static function unlink($file_name = 'debug')
    {
        $log_file = self::getLogPath($file_name, false);
        if (\is_file($log_file)) {
            @\unlink($log_file);
        }
    }

    /**
     * 只保留固定天数的文件, 日志内容自动包含进程号等信息
     * @param $msg
     * @param string $file_name
     * @param string $log_type
     */
    public static function syslog($msg, $file_name = 'debug', $log_type = 'info')
    {
        /*{{{*/
        $file_name     = self::pathNameReplace($file_name);
        $old_file_name = $file_name . '_' . date('Y-m-d', time() - 60 * 60 * 24 * (intval(Config::get('log_days', 5))));
        self::unlink($old_file_name);
        $file_name = $file_name . '_' . date('Y-m-d', time());

        $debug_data = \debug_backtrace();
        $file       = $function = $class = $type = $line = "";
        if ($debug_data && isset($debug_data[1])) {
            $file     = isset($debug_data[0]['file']) ? \substr($debug_data[0]['file'], \strrpos($debug_data[0]['file'], '/') + 1) : "";
            $line     = isset($debug_data[0]['line']) ? $debug_data[0]['line'] : "";
            $class    = isset($debug_data[1]['class']) ? $debug_data[1]['class'] : "";
            $type     = isset($debug_data[1]['type']) ? $debug_data[1]['type'] : "";
            $function = isset($debug_data[1]['function']) ? $debug_data[1]['function'] . "()" : "";
        }
        $message = \date("Y-m-d H:i:s", time()) . " PID[" . \posix_getpid() . "] ";
        if ($file) {
            $message .= $file . " ";
        }
        if ($line) {
            $message .= "[" . $line . "]行 ";
        }
        if ($class) {
            $message .= $class;
        }
        if ($type) {
            $message .= $type;
        } else {
            $message .= '->';
        }
        if ($function) {
            $message .= $function . " ";
        }

        $message .= ": " . $msg;
        switch ($log_type) {
            case 'debug':
                $message = "\033[36m" . $message . "\033[0m";
                break;
            case 'warn':
                $message = "\033[33m" . $message . "\033[0m";
                break;
            case 'error':
                $message = "\033[31m" . $message . "\033[0m";
                break;
        }
        self::show($message, $file_name);
        /*}}}*/
    }

    /**
     * 记录日志信息，自动带上日志打印地址和进程号，不自动删除日志文件
     * @param $msg
     * @param string $file_name
     * @param string $log_type
     */
    public static function add($msg, $file_name = 'debug', $log_type = 'info')
    {
        /*{{{*/
        $debug_data = \debug_backtrace();
        $file       = $function = $class = $type = $line = "";
        if ($debug_data && isset($debug_data[1])) {
            $file     = isset($debug_data[0]['file']) ? \substr($debug_data[0]['file'], \strrpos($debug_data[0]['file'], '/') + 1) : "";
            $line     = isset($debug_data[0]['line']) ? $debug_data[0]['line'] : "";
            $class    = isset($debug_data[1]['class']) ? $debug_data[1]['class'] : "";
            $type     = isset($debug_data[1]['type']) ? $debug_data[1]['type'] : "";
            $function = isset($debug_data[1]['function']) ? $debug_data[1]['function'] . "()" : "";
        }

        $message = \date("Y-m-d H:i:s", time()) . " PID[" . \posix_getpid() . "] ";
        if ($file) {
            $message .= $file . " ";
        }
        if ($line) {
            $message .= "[" . $line . "]行 ";
        }
        if ($class) {
            $message .= $class;
        }
        if ($type) {
            $message .= $type;
        } else {
            $message .= '->';
        }
        if ($function) {
            $message .= $function . " ";
        }

        $message .= ": " . $msg;
        switch ($log_type) {
            case 'debug':
                $message = self::shellColor($message, 'ultramarine');
                break;
            case 'warn':
                $message = self::shellColor($message, 'yellow');
                break;
            case 'error':
                $message = self::shellColor($message, 'red');
                break;
        }
        self::show($message, $file_name);
        /*}}}*/
    }

    public static function notice($msg, $file_name)
    {
        static::syslog($msg, "notice/{$file_name}", 'info');
    }

    public static function debug($msg, $file_name)
    {
        static::syslog($msg, "debug/{$file_name}", 'debug');
    }

    public static function warn($msg, $file_name)
    {
        static::syslog($msg, "warn/{$file_name}", 'warn');
    }

    public static function error($msg, $file_name)
    {
        static::syslog($msg, "error/{$file_name}", 'error');
    }

    /**
     * 返回带颜色的shell字符串
     * @param $string
     * @param string $color
     * @return string
     *
     * 前景     背景      颜色
     * ------------------------
     *  30      40      黑色
     *  31      41      红色
     *  32      42      绿色
     *  33      43      黄色
     *  34      44      蓝色
     *  35      45      紫红色
     *  36      46      青蓝色
     *  37      47      白色
     */
    public static function shellColor($string, $color = 'green')
    {
        switch ($color) {
            case 'red':
                $string = "\033[31;40m{$string}\033[0m";
                break;
            case 'green':
                $string = "\033[32;40m{$string}\033[0m";
                break;
            case 'yellow':
                $string = "\033[33;40m{$string}\033[0m";
                break;
            case 'blue':
                $string = "\033[34;40m{$string}\033[0m";
                break;
            case 'amaranth':
                $string = "\033[35;40m{$string}\033[0m";
                break;
            case 'ultramarine':
                $string = "\033[36;40m{$string}\033[0m";
                break;
            case 'white':
                $string = "\033[37;40m{$string}\033[0m";
                break;
        }
        return $string;
    }
}