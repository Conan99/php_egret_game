module Skins.Login
{
    import LocalKey = Data.Common.LocalKey;
    import Http = Lib.Http;

    /**
     * 登录方式
     */
    export class Way extends eui.Component implements eui.UIComponent
    {
        // private static readonly _addr = 'http://xiuxian.server.com';
        private static readonly _addr = 'http://xiuxian.s.conan99.xyz';

        public warn: eui.Panel;
        public choose: eui.Panel;
        public phone_way: eui.Button;
        public tourist_way: eui.Button;
        public wechat_way: eui.Button;
        public phone_show: eui.Panel;
        public user_phone: eui.TextInput;
        public phone_login: eui.Button;

        public constructor()
        {
            super();
        }

        protected partAdded(partName: string, instance: any): void
        {
            super.partAdded(partName, instance);
        }

        protected childrenCreated(): void
        {
            super.childrenCreated();
            // let timer: egret.Timer = new egret.Timer(2000, 1);
            // timer.addEventListener(egret.TimerEvent.TIMER, () => {
            //     this.warn.visible = false;
            //     this.choose.visible = true;
            // }, this);
            // timer.start();
            this.warn.visible = false;
            this.choose.visible = true;
            this.phone_way.addEventListener(egret.TouchEvent.TOUCH_TAP, () => {
                this.phone_show.visible = true;
                this.choose.visible = false;
            }, this);
            egret.log(this.tourist_way);
            this.tourist_way.addEventListener(egret.TouchEvent.TOUCH_TAP, this._touristLogin, this);
            this.wechat_way.addEventListener(egret.TouchEvent.TOUCH_TAP, this._wechatWay, this);
            this.phone_login.addEventListener(egret.TouchEvent.TOUCH_TAP, this._phoneLogin, this);
        }

        private _wechatWay()
        {
            egret.localStorage.clear();
        }

        private _phoneLogin()
        {
            egret.localStorage.clear();
        }

        private _touristLogin()
        {
            let usign = egret.localStorage.getItem(LocalKey.usign);
            if (!usign) {
                (Http.get(Way._addr + "/tourist") as any).then(res => {
                    if (res['code'] == 0) {
                        usign = res['data'].usign;
                        egret.localStorage.setItem(LocalKey.usign, usign);
                    } else {
                        egret.error(res['msg']);
                    }
                });
            }
            egret.log(usign);
            if (!usign) return;
            Http.get(Way._addr + "/server/" + usign + '/0').then(res => {
                switch (res['code']) {
                    case 0:
                        Main.instance.showServer(res['data']['list'], res['data']['recent_sid']);
                        break;
                    case 1:
                        egret.localStorage.removeItem(LocalKey.usign);
                        egret.error(res['msg']);
                        break;
                    default:
                        egret.error(res['msg']);
                        break;
                }
            })
        }
    }
}