<?php
/**
 * Created by PhpStorm.
 * User: conan
 * Date: 2019-01-07
 * Time: 13:08
 */

namespace Library;

class Dir
{
    const DS = '/';

    /**
     * 递归创建一个目录
     * @param $dir
     * @param int $mode
     * @return bool
     */
    public static function make($dir, $mode = 0755)
    {
        if (\is_dir($dir) || @\mkdir($dir, $mode, true)) {
            return true;
        }
        return false;
    }

    /**
     * 递归获取目录下的文件
     * @param $dir
     * @param string $filter 过滤某些文件的正则, 匹配到的才放在result里面; ‘#*.php#i’
     * @param bool $whole 是否需要返回目录下的文件的完整路径
     * @return array
     */
    public static function tree($dir, $filter = '', $whole = false)
    {
        $result = [];
        if (!file_exists($dir)) return $result;
        // 获取$dir目录下的所有文件和目录
        $files = new \DirectoryIterator($dir);
        foreach ($files as $file) {
            // 过滤目录下的 '.' 和 '..'
            if ($file->isDot()) continue;

            // 获取目录下的文件名
            $filename = $file->getFilename();

            // 判断是否目录
            if ($file->isDir()) {
                $result[$filename] = self::tree($dir . self::DS . $filename, $filter, $whole);
            } else {
                // 过滤文件
                if ($filter && !preg_match($filter, $filename)) continue;
                if ($whole) {
                    $result[] = $dir . self::DS . $filename;
                } else {
                    $result[] = $filename;
                }
            }
        }

        return $result;
    }

    /**
     * 递归删除某个目录
     * @param $dir
     * @param string $filter 正则某些文件, 匹配到的才删除
     * @return bool
     */
    public static function del($dir, $filter = '')
    {
        if (!file_exists($dir)) return true;
        $files = new \DirectoryIterator($dir);
        foreach ($files as $file) {
            if ($file->isDot()) {
                continue;
            }

            $filename = $file->getFilename();

            if (!empty($filter) && !\preg_match($filter, $filename)) {
                continue;
            }

            if ($file->isDir()) {
                self::del($dir . self::DS . $filename);
            } else {
                \unlink($dir . self::DS . $filename);
            }
        }

        return \rmdir($dir);
    }
}