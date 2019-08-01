module Skins.Game
{
    import User = Data.Game.User;

    /**
     * 头部
     */
    export class Header extends eui.Component implements eui.UIComponent
    {
        public header_img: eui.Button;
        public shop: eui.Button;
        public gm: eui.Button;

        private user: User;

        public constructor()
        {
            super();
            this.user = User.instance;
        }

        protected partAdded(partName: string, instance: any): void
        {
            super.partAdded(partName, instance);
        }

        protected childrenCreated(): void
        {
            super.childrenCreated();
        }
    }
}