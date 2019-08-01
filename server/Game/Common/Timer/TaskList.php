<?php
/**
 * Created by PhpStorm.
 * User: conan
 * Date: 2019-01-25
 * Time: 03:54
 */

namespace Common\Timer;

/**
 * 定时任务列表
 * Class TaskList
 * @package Timer
 */
class TaskList
{
    /**
     * 初始化定时任务列表
     * 注：定时器会根据任务加入的先后顺序执行，所以优先级高的任务赢优先添加
     */
    public static function init()
    {
    }
}