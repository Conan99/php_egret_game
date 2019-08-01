<?php
/**
 * Created by PhpStorm.
 * User: conan
 * Date: 2019-01-24
 * Time: 19:59
 */

namespace Common\Timer;

/**
 * 定时任务
 * Class Task
 * @package Timer
 */
class Task
{
    /**
     * 创建定时任务
     * @param $data
     * @return bool|Task
     */
    public static function createTask($data)
    {
        if (empty($data['id'])) return false;
        if (empty($data['interval'])) return false;
        if (empty($data['func']) || !is_callable($data['func'])) return false;
        $task = new Task($data);
        return $task;
    }

    private $id = 0;

    /** @var int 定时器出生时间 */
    private $born_time = 0;

    /** @var int 间隔时间 */
    private $interval = 0;

    /** @var int 下次执行时间 */
    private $next_time = 0;

    /** @var callable */
    private $func = null;

    /** @var array */
    private $args = [];

    /** @var bool 是否持续，否则将执行一次后失效 */
    private $persistent = true;

    /** @var int 已执行次数 */
    private $times = 0;

    private function __construct($data)
    {
        $this->id         = $data['id'];
        $this->born_time  = time();
        $this->interval   = $data['interval'];
        $this->next_time  = !empty($data['next_time']) ? $data['next_time'] : $this->born_time + $data['interval'];
        $this->func       = $data['func'];
        $this->args       = $data['args'] ?: [];
        $this->persistent = $data['persistent'] ?: true;
    }

    /**
     * 触发任务
     * @param $now
     * @return bool
     */
    public function trigger($now)
    {
        if ($this->next_time <= $now) {
            call_user_func_array($this->func, $this->args);
            $this->next_time = $now + $this->interval;
            $this->times++;
            return $this->persistent;
        }
        return true;
    }
}