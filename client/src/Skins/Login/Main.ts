module Skins.Login
{
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

        private constructor()
        {
            super();
            Main._instance = this;
            this.width = Config.STAGE_WIDTH;
            this.height = Config.STAGE_HEIGHT;
            this.showWay();
        }

        public showWay()
        {
            let way: Way = new Way();
            this.addChild(way);
            way.width = this.width;
            way.height = this.height;
        }

        public showServer(list, recent)
        {
            let server: Server = new Server(list, recent);
            this.addChild(server);
            server.width = this.width;
            server.height = this.height;
        }
    }
}