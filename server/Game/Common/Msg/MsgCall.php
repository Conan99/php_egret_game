<?php
/**
 * Created by PhpStorm.
 * User: conan
 * Date: 2019-01-20
 * Time: 13:47
 */

namespace Common\Msg;

class MsgCall
{
    //不用登录的控制器方法
    private static $_no_check_login_call_func = [
        MsgDict::C_HEART_TIME
    ];

    //控制器方法
    private static $_call_func = [
        //用户
        MsgDict::C_HEART_TIME              => 'UserControl::heartTime',//心跳时间
    ];

    /**
     * 获取控制器方法
     * @param $call_id
     * @return mixed|null
     */
    public static function getCallFunc($call_id)
    {
        return !empty(self::$_call_func[$call_id]) ? 'Control\\' . self::$_call_func[$call_id] : null;
    }

    /**
     * 判断是否为不需要登录便可调用的控制器方法
     * @param $call_id
     * @return bool
     */
    public static function isNocheckLoginCall($call_id)
    {
        return in_array($call_id, self::$_no_check_login_call_func);
    }
}