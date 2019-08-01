module Skins.Login
{
    /**
     * 选服
     */
    export class Server extends eui.Component implements eui.UIComponent
    {
        public recent: eui.Panel;
        public list: eui.Group;
        public back: eui.Button;

        public server_list = [];
        public recent_sid = [];

        public constructor(server_list, recent_sid)
        {
            super();
            this.server_list = server_list;
            this.recent_sid = recent_sid;
        }

        protected partAdded(partName: string, instance: any): void
        {
            super.partAdded(partName, instance);
        }

        protected childrenCreated(): void
        {
            super.childrenCreated();
            for (let i in this.server_list) {
                let server_item = new ServerItem(this.server_list[i]);
                this.list.addChild(server_item);
                if (this.recent_sid == this.server_list[i].id) {//最近登录
                    this.recent.addChild(server_item);
                }
            }
            this.back.addEventListener(egret.TouchEvent.TOUCH_TAP, this._back, this);
        }

        private _back()
        {
            if (this.parent) {
                this.parent.removeChild(this);
            }
        }
    }
}