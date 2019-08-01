module Skins.Game
{
    /**
     * 底部
     */
    export class Footer extends eui.Component implements eui.UIComponent
    {
        public btn_group: eui.Group;

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
            this.y = Config.STAGE_HEIGHT - Config.MAIN_FOOTER_HEIGHT;

            //让Group可以点击
            this.btn_group.touchEnabled = true;

            //事件委托，点击按钮的时候触发toggleBtn
            this.btn_group.addEventListener(egret.TouchEvent.TOUCH_TAP, (e) => {
                if (!(e.target instanceof eui.ToggleButton)) return;
                let btn: eui.ToggleButton = e.target;
                //在点击触发这个事件的时候，点击的那个btn已经变成了选中状态
                //判断btn是否存在btn.selected属性且为true
                if (btn.selected) {
                    // 获取当前点击的按钮的下标, 用来实现不同按钮对应的功能
                    let index = this.btn_group.getChildIndex(btn);
                    Main.changeBody(index);
                } else {
                    //当selected为false的时候，说明按钮在点击之前就是选中状态
                    //点击后变成了false，所以这里改回选中状态
                    btn.selected = true;
                }
            }, this);
        }

        /**
         * 切换按钮选中状态
         */
        public toggleBtnSelect(index: number)
        {
            for (let i = 0; i < this.btn_group.numChildren; i++) {
                let btn = <eui.ToggleButton>this.btn_group.getChildAt(i);
                btn.selected = i == index;
            }
        }
    }
}