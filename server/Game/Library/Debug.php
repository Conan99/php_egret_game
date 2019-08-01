<?php
/**
 * Created by PhpStorm.
 * User: conan
 * Date: 2019-01-16
 * Time: 18:02
 */

namespace Library;

/**
 * DEBUG工具
 * Class Debug
 * @package Library
 */
class Debug
{
    public static function init()
    {
        set_exception_handler(__CLASS__ . '::debugException');
        register_shutdown_function(__CLASS__ . '::shutDown');
        set_error_handler(__CLASS__ . '::debugError', E_ALL);
    }

    public static function output()
    {
        $args = func_get_args();
        $str  = '';
        foreach ($args as $v) {
            $v   = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
            $str .= "$v\n";
        }
        echo $str;
    }

    public static function log($data)
    {
        $args = func_get_args();
        $str  = '';
        foreach ($args as $v) {
            $v   = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
            $str .= "$v\n";
        }
        Log::show($str);
    }

    public static $_type = [
        E_WARNING         => "\033[33;1m警告\033[0m",
        E_NOTICE          => "\033[33;1m普通警告\033[0m",
        E_USER_ERROR      => "\033[31;1m用户错误\033[0m",
        E_USER_WARNING    => "\033[33;1m用户警告\033[0m",
        E_USER_NOTICE     => "\033[33;1m用户提示\033[0m",
        E_STRICT          => "\033[31;1m运行时错误\033[0m",
        E_ERROR           => "\033[31;1m致命错误\033[0m",
        E_PARSE           => "\033[31;1m解析错误\033[0m",
        E_CORE_ERROR      => "\033[31;1m核心致命错误\033[0m",
        E_CORE_WARNING    => "\033[33;1m核心警告\033[0m",
        E_COMPILE_ERROR   => "\033[31;1m编译致命错误\033[0m",
        E_COMPILE_WARNING => "\033[33;1m编译警告\033[0m"
    ];

    // 存储所有的异常信息，然后一起输出到前端web
    public static $errMsg = [];

    public static function debugException($e)
    {
        $errno     = $e->getCode();
        $errmsg    = $e->getMessage();
        $linenum   = $e->getLine();
        $filename  = $e->getFile();
        $backtrace = $e->getTrace();
        self::format($errno, $errmsg, $filename, $linenum, $backtrace);
    }

    public static function debugError($errno, $errmsg, $filename, $linenum, $vars)
    {
        self::format($errno, $errmsg, $filename, $linenum, []);
    }

    /**
     * 格式化数据
     * @param $errno
     * @param $errmsg
     * @param $filename
     * @param $linenum
     * @param $vars
     */
    public static function format($errno, $errmsg, $filename, $linenum, $vars)
    {
        if (!is_file($filename)) return;

        $fp         = fopen($filename, 'r');
        $n          = 0;
        $error_line = '';
        while (!feof($fp)) {
            $line = fgets($fp, 1024);
            $n++;
            if ($n == $linenum) {
                $error_line = trim($line);
                break;
            }
        }
        fclose($fp);

        // 如果读取到的出错所在的行中用 @ 进行屏蔽，则不显示错误
        if ($error_line[0] == '@' || preg_match("/[\(\t ]@/", $error_line)) return;

        // 将数据整理成服务端生成文件
        $msg = self::packServerDebugMsg($errno, $error_line, $errmsg, $filename, $linenum, $vars);

        Log::show($msg, "fatal_error_" . date("Ymd", time()));
        // 删除前7天的数据日志
        $old_log_file = "fatal_error_" . date("Ymd", time() - 60 * 60 * 24 * (intval(Config::get('log_days', 7))));
        Log::unlink($old_log_file);
        echo $msg;
    }

    /**
     * 获取服务的记录信息
     * @param $errno
     * @param $error_line
     * @param $errmsg
     * @param $filename
     * @param $linenum
     * @param array $vars
     * @return string
     */
    public static function packServerDebugMsg($errno, $error_line, $errmsg, $filename, $linenum, $vars = [])
    {
        if (empty(self::$_type[$errno])) {
            self::$_type[$errno] = "手动抛出";
        }
        $err = '';
        $err .= "-----------------------\033[47;30m Error Tracking \033[0m-----------------------\n";
        $err .= "发生环境：\033[34;1m" . date("Y-m-d H:i:s", time()) . "\033[0m\n";
        $err .= "错误类型：" . self::$_type[$errno] . "\n";
        $err .= "出错原因：\033[34;1m" . $errmsg . "\033[0m\n";
        $err .= "提示位置：" . $filename . " 第 {$linenum} 行\n";
        $err .= "断点源码：{$error_line}\n";
        $err .= "详细跟踪：\n";
        if ($vars && is_array($vars)) {
            $narr = array('class', 'type', 'function', 'file', 'line');
            foreach ($vars as $i => $l) {
                foreach ($narr as $k) {
                    if (!isset($l[$k])) $l[$k] = '';
                }
                $err .= "[$i] in function {$l['class']}{$l['type']}{$l['function']} ";
                if ($l['file']) $err .= " in {$l['file']} ";
                if ($l['line']) $err .= " on line {$l['line']} ";
                $err .= "\n";
            }
        } else {
            $backtrace = debug_backtrace();
            array_shift($backtrace);
            $narr = array('class', 'type', 'function', 'file', 'line');
            foreach ($backtrace as $i => $l) {
                foreach ($narr as $k) {
                    if (!isset($l[$k])) $l[$k] = '';
                }
                $err .= "[$i] in function {$l['class']}{$l['type']}{$l['function']} ";
                if ($l['file']) $err .= " in {$l['file']} ";
                if ($l['line']) $err .= " on line {$l['line']} ";
                $err .= "\n";
            }
        }
        $err .= "--------------------------------------------------------------\n\n\n";
        return $err;
    }

    /**
     * PHP结束之后最后调用的函数
     **/
    public static function shutDown()
    {
        /**
         * 不管是不是socket常驻内存的进程，都需要处理fatal错误，
         * 一旦常驻内存的进程发生fatal错误，那么进程会自动销毁，并重建
         * 还是需要记录一下fatal错误到日志
         **/
        // Getting Last Error
        $last_error = error_get_last();
        // Check if Last error is of type FATAL
        // 对PHP而言error接管函数是没有办法接管E_ERROR的函数的,因此在这里做
        // if(isset($last_error['type']) && $last_error['type']==E_ERROR) {
        if (isset($last_error['type'])) {
            $errno    = isset($last_error['type']) ? $last_error['type'] : '';
            $errmsg   = isset($last_error['message']) ? $last_error['message'] : '';
            $filename = isset($last_error['file']) ? $last_error['file'] : '';
            $linenum  = isset($last_error['line']) ? $last_error['line'] : '';
            self::format($errno, $errmsg, $filename, $linenum, []);
        }

        // 清空错误日志
        self::$errMsg = [];
    }
}