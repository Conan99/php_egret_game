module Skins.Game
{
    import User = Data.Game.User;
    import Heart = Lib.Heart;

    export class Main extends eui.Panel
    {
        private static _instance: Main;
        /**
         * 获取实例
         */
        public static get instance(): Main
        {
            if (!Main._instance) {
                Main._instance = new Main();
            }
            return Main._instance;
        }

        public header: Header;
        private _body: Body;
        private _footer: Footer;
        private _error_msg: egret.TextField;
        private _goods_msg: egret.TextField;

        private constructor()
        {
            super();
            Main._instance = this;
            this.addEventListener(egret.Event.ADDED_TO_STAGE, this._add_to_stage, this);
        }

        private _add_to_stage()
        {
            // egret.lifecycle.addLifecycleListener((context) => {
            //     // custom lifecycle plugin
            // });
            // egret.lifecycle.onPause = () => {
            //     egret.ticker.pause();
            // };
            // egret.lifecycle.onResume = () => {
            //     egret.ticker.resume();
            // };
            this.runGame().catch(e => {
                console.log(e);
            });
        }

        private async runGame()
        {
            await this.loadResource().then(() => {
                this._show();
                Main.changeBody(0);
                Heart.init();
            });
        }

        private async loadResource()
        {
            try {
                const loadingView = new LoadingUI();
                this.addChild(loadingView);
                await RES.loadGroup("game", 0, loadingView);
                await RES.loadGroup("table", 0, loadingView).then(User.init);
                this.removeChild(loadingView);
            } catch (e) {
                console.error(e);
            }
        }

        private _show()
        {
            this.width = Config.STAGE_WIDTH;
            this.height = Config.STAGE_HEIGHT;
            this._body = new Body();
            this.addChild(this._body);
            this.header = new Header();
            this.addChild(this.header);
            this._footer = new Footer();
            this.addChild(this._footer);
        }

        /**
         * 切换中部
         */
        public static changeBody(index: number)
        {
            Main.instance._footer.toggleBtnSelect(index);
        }

        /**
         * 展示提示语
         * @param msg
         */
        public static showErrorMsg(msg: string)
        {
            if (!Main.instance._error_msg) {
                Main.instance._error_msg = new egret.TextField();
                Main.instance._error_msg.width = Config.STAGE_WIDTH;
                Main.instance._error_msg.height = 30;
                Main.instance._error_msg.x = 0;
                Main.instance._error_msg.y = (Config.STAGE_HEIGHT - Main.instance._error_msg.height) / 2;
                Main.instance._error_msg.size = 16;
                Main.instance._error_msg.textAlign = egret.HorizontalAlign.CENTER;
                Main.instance._error_msg.verticalAlign = egret.VerticalAlign.MIDDLE;
                Main.instance._error_msg.textColor = 0xff0000;
            }
            Main.instance._error_msg.text = msg;
            Main.instance.addChild(Main.instance._error_msg);
            let tw = egret.Tween.get(Main.instance._error_msg);
            tw.to({y: Main.instance.y - 10}, 1000).call(function () {
                Main.instance.removeChild(Main.instance._error_msg);
            });
        }
    }
}