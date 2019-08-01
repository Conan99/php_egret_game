module Skins.Login
{
    import Ws = Lib.Ws;
    import User = Data.Game.User;

    /**
     * 服
     */
    export class ServerItem extends eui.Component implements eui.UIComponent
    {
        public sid;
        public sname;
        public label;
        public ip;
        public port;

        public enter: eui.Button;

        public constructor(server)
        {
            super();
            for (let k in server) {
                this[k] = server[k];
            }
        }

        protected partAdded(partName: string, instance: any): void
        {
            super.partAdded(partName, instance);
        }

        protected childrenCreated(): void
        {
            super.childrenCreated();
            this.enter.addEventListener(egret.TouchEvent.TOUCH_TAP, this._enter, this);
            // Ws.instance.connect('ws://' + this.ip + ':' + this.port);
        }

        private _enter()
        {
            User.instance.sid = this.sid;
            User.instance.sname = this.sname;
            Ws.instance.connect('ws://' + this.ip + ':' + this.port);
        }
    }
}