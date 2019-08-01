<?php
/**
 * Created by PhpStorm.
 * User: conan
 * Date: 2019-01-07
 * Time: 15:52
 */

namespace Common\Msg;

use GatewayWorker\Lib\Gateway;
use Library\Debug;
use Library\Log;
use Model\UserModel;

class Msg
{
    const HEAD_LEN = 16;

    /**
     * 解析信息并调用方法
     * @param $client_id
     * @param $msg
     * @throws \Exception
     */
    public static function call($client_id, $msg)
    {
        $decode_msg = self::_decodeMsg($msg);
        if (!$decode_msg) return;
        Debug::output(
            Log::shellColor('================解析数据开始================', 'green'),
            $decode_msg,
            ''
        );
        $call_id   = array_shift($decode_msg);
        $call_func = MsgCall::getCallFunc($call_id);
        if (!$call_func) return;
        if (MsgCall::isNocheckLoginCall($call_id)) {
            call_user_func($call_func, $client_id, $decode_msg);
        } else {
            $uid  = UserModel::getClientUid($client_id);
            $user = UserModel::getOnlineUser($uid);
            if (!$user) {
                self::sendCode($client_id, 1, '请先登录');
                return;
            }
            call_user_func($call_func, $user, $decode_msg);
        }
    }

    /**
     * 推送错误提示码
     * @param $client_id
     * @param $code
     * @param $msg
     * @throws \Exception
     */
    public static function sendCode($client_id, $code, $msg = '')
    {
        $data = ['code' => $code, 'msg' => $msg];
        self::send($client_id, MsgDict::S_NOTICE_MSG, $data);
    }

    /**
     * 打包数据并推送
     * @param $client_id
     * @param $call_id
     * @param array $data
     * @throws \Exception
     */
    public static function send($client_id, $call_id, $data = [])
    {
        array_unshift($data, $call_id);
        Debug::output(
            Log::shellColor('================打包数据开始================', 'blue'),
            $data,
            ''
        );
        $msg = self::_encodeMsg($data);
        if (!$msg) return;
        if ($client_id) {
            Gateway::sendToClient($client_id, $msg);
        } else {
            Gateway::sendToAll($msg);
        }
    }

    /**
     * 推送所有人
     * @param $call_id
     * @param array $data
     * @throws \Exception
     */
    public static function sendToAll($call_id, $data = [])
    {
        self::send(0, $call_id, $data);
    }

    /**
     * 打包数据
     * @param $data
     * @return string
     */
    private static function _encodeMsg($data)
    {
        $msg = json_encode($data);
        return $msg;
    }

    /**
     * 解析数据
     * @param $msg
     * @return array
     */
    private static function _decodeMsg($msg)
    {
        $data = json_decode($msg, true);
        return $data;
    }
}