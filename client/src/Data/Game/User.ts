module Data.Game
{
    import Pack = Msg.Pack;

    export class User
    {
        private static _instance: User;
        /**
         * 获取实例
         */
        public static get instance(): User
        {
            if (!User._instance) {
                User._instance = new User();
            }
            return User._instance;
        }

        public sid;
        public sname;

        public usign: string;
        public uid: number;
        public name: string;
        public sex: number;

        /********** 对象类 ***********/

        private constructor()
        {
        }

        public static init()
        {
            User.instance;
        }

        public setData(data: any[])
        {
            for (let i in data) {
                if (this[i] !== undefined) {
                    this[i] = data[i];
                }
            }
        }
    }
}