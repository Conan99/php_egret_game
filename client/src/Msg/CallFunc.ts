module Msg
{
    import Heart = Lib.Heart;
    import User = Data.Game.User;

    export class CallFunc
    {
        private static _func = {};

        public static init()
        {
            //其他
            this._func[MsgDict.S_HEART_TIME] = (data) => {//心跳时间
                console.log('心跳时间', data);
                Heart.now = data.now;
            };
            this._func[MsgDict.S_NOTICE_MSG] = (data) => {//提示语
                Skins.Game.Main.showErrorMsg(data.code + '：' + data.msg);
            };
            //角色模块
            // this._func[MsgDict.S_USER_LOGIN] = (data) => {//登录界面
            //     egret.log('登录界面');
            // };
            // this._func[MsgDict.S_USER_CREATE] = (data) => {//创建角色
            //     egret.log('创建角色');
            //     Pack.send(MsgDict.C_USER_CREATE, {name: 'test', sex: 1});
            // };
            // this._func[MsgDict.S_USER_LOGIN_SUCCESS] = (data) => {//登录成功
            //     egret.log('登录成功');
            //     Main.startGame();
            // };
            // this._func[MsgDict.S_USER_SEND_DATA] = (data) => {//设置角色基础信息
            //     egret.log('设置角色基础信息', data);
            //     User.instance.setData(data);
            // };
        }

        /**
         * 调用方法
         */
        public static call(id, data)
        {
            if (typeof this._func[id] != 'function') return;
            this._func[id](data);
        }
    }
}