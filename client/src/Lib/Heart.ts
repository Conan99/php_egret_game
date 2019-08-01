module Lib
{
    export class Heart
    {
        private static _instance: Heart;
        /**
         * 获取实例
         */
        public static get instance(): Heart
        {
            if (!this._instance) {
                this._instance = new Heart();
            }
            return this._instance;
        }

        public static set now(now: number)
        {
            this.instance._now = now;
            this.instance._timer.reset();
            this.instance._timer.start();
        }

        public static get now()
        {
            return this.instance._now;
        }

        /**
         * 初始化
         */
        public static init()
        {
            this.instance;
            this._checkNow();
        }

        /**
         * 获取心跳时间
         */
        private static _checkNow()
        {
            Msg.Pack.send(MsgDict.C_HEART_TIME);
        }

        private constructor()
        {
            this._now = Math.floor(Date.now() / 1000);
            this._timer = new egret.Timer(1000, Config.HEART_TIME);//间隔设置为1秒
            this._timer.addEventListener(egret.TimerEvent.TIMER, this._countDown, this);
            this._timer.addEventListener(egret.TimerEvent.TIMER_COMPLETE, Heart._checkNow, this);//倒计时结束触发心跳
        }

        private _now: number;
        private _timer: egret.Timer;

        /**
         * 定时每秒执行
         */
        private _countDown()
        {
            this._now++;
        }
    }
}