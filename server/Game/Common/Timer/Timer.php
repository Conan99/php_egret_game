<?php
/**
 * Created by PhpStorm.
 * User: conan
 * Date: 2019-01-24
 * Time: 19:51
 */

namespace Common\Timer;

use Workerman\Lib\Timer as WorkTimer;

/**
 * 定时任务管理
 * Class Timer
 * @package Timer
 */
class Timer
{
    /** @var float 游戏渲染单帧时间 单位秒 */
    const RENDER_INTERVAL = 0.1;

    /** @var array 间隔时间定时任务列表 */
    private static $_task_list = [];
    /** @var int 自增任务ID */
    private static $_incr_task_id = 0;

    /**
     * 初始化定时任务
     */
    public static function init()
    {
        self::_resetTaskList();
        //游戏主渲染初始化
        WorkTimer::add(self::RENDER_INTERVAL, function () {
            self::check(time());
        });
    }

    /**
     * 重置任务列表
     */
    private static function _resetTaskList()
    {
        self::$_task_list = [];
        TaskList::init();
    }

    /**
     * 获取任务ID
     * @return int
     */
    private static function _getTaskId()
    {
        return ++self::$_incr_task_id;
    }

    /**
     * 添加任务
     * @param $data
     * @return bool|int
     */
    public static function addTask($data)
    {
        $task_id    = self::_getTaskId();
        $data['id'] = $task_id;
        $task       = Task::createTask($data);
        if (!$task) return false;
        self::$_task_list[$task_id] = $task;
        return $task_id;
    }

    /**
     * 删除任务
     * @param int $task_id
     */
    public static function delTask(int $task_id)
    {
        if (isset(self::$_task_list[$task_id])) unset(self::$_task_list[$task_id]);
    }

    /**
     * 添加一次性任务
     * @param int $next 下次执行时间（秒）
     * @param $func
     * @param array $args
     * @return bool|int
     */
    public static function addOneTimeTask(int $next, $func, $args = [])
    {
        $data    = ['interval' => $next, 'func' => $func, 'args' => $args, 'persistent' => false];
        $task_id = self::addTask($data);
        return $task_id;
    }

    /**
     * 添加间隔任务
     * @param int $interval 间隔时间（秒）
     * @param callable $func
     * @param array $args
     * @param int $next 下次执行时间（秒）
     * @return bool|int
     */
    public static function addIntervalTask(int $interval, $func, $args = [], $next = 0)
    {
        $data    = ['interval' => $interval, 'func' => $func, 'args' => $args, 'persistent' => true, 'next_time' => $next];
        $task_id = self::addTask($data);
        return $task_id;
    }

    /**
     * 添加定点任务（某一天的几点几分执行的任务）
     * @param int $week 0每天  1-7 指定的星期几任务
     * @param int $hour 0-23 时
     * @param int $min 0-59 分
     * @param callable $func
     * @param array $args
     * @return bool|int
     */
    public static function addClockTask($week, $hour, $min, $func, $args = [])
    {
        $week = intval($week);
        $hour = intval($hour);
        $min  = intval($min);
        $now  = time();
        list($w, $h, $m) = explode(',', date('w,H,i', $now));
        $next = 0;
        $next += ($min - $m) * 60 + ($min >= $m ? 0 : 3600);
        $h    = $m > $min ? $h + 1 : $h;//分超过就加一小时
        $next += ($hour - $h) * 3600 + ($hour >= $h ? 0 : 86400);
        if ($week) {
            $w        = $h > $hour ? $w + 1 : $w;//时超过就加一天
            $next     += ($week - $w) * 86400 + ($week >= $w ? 0 : 7 * 86400);
            $interval = 7 * 86400;//周间隔为7天
        } else {
            $interval = 86400;//否则间隔为1天
        }
        $task_id = self::addIntervalTask($interval, $func, $args, $next);
        return $task_id;
    }

    /**
     * 添加整点任务
     * @param callable $func
     * @param array $args
     * @return bool|int
     */
    public static function addZeroMinTask($func, $args = [])
    {
        $now      = time();
        $m        = date('i', $now);
        $next     = $m ? (60 - $m) * 60 : 0;
        $interval = 3600;//间隔为1小时
        $task_id  = self::addIntervalTask($interval, $func, $args, $next);
        return $task_id;
    }

    /** @var int 上次check时间 */
    private static $_last_check_time = 0;

    /**
     * 触发所有任务
     * @param int $now
     */
    private static function triggerTasks(int $now)
    {
        /** @var Task $task */
        foreach (self::$_task_list as $task_id => $task) {
            $persistent = $task->trigger($now);
            if (!$persistent) self::delTask($task_id);
        }
    }


    /**
     * 定时器渲染，精确到1秒
     * @param $now
     */
    public static function check($now)
    {
        if (self::$_last_check_time > 0 && $now - self::$_last_check_time > 10) {//只要当前时间和上次check时间相差超过10秒，就触发快速时钟
            self::fastRunTime(self::$_last_check_time, $now);
        } elseif (self::$_last_check_time > 0 && self::$_last_check_time - $now > 0) {//时间倒退则重新设置定时任务列表
            self::_resetTaskList();
        } else {
            self::triggerTasks($now);
        }
        self::$_last_check_time = $now;
    }

    /**
     * 时钟快走
     * @param $start int 开始时间
     * @param $end int 结束时间
     */
    private static function fastRunTime($start, $end)
    {
        //超出一天的跨天，只触发最后一天，避免阻塞过久
        if ($end - $start > 24 * 3600) $start = $end - 24 * 3600;
        for ($i = $start; $i <= $end; $i++) {
            self::triggerTasks($i);
        }
    }

}