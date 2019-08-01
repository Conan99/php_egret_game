module Msg
{
    export class Pack
    {
        /**
         * 解析信息并调用方法
         */
        public static call(msg)
        {
            let data = this.decodeMsg(msg);
            let call_id = data[0];
            egret.log(call_id, data);
            CallFunc.call(call_id, data);
        }

        /**
         * 打包数据并推送
         */
        public static send(call_id, data: Object = {})
        {
            data[0] = call_id;
            let msg = this.encodeMsg(data);
            Lib.Ws.instance.writeUTF(msg);
        }
        /**
         * 打包数据
         */
        public static encodeMsg(data): string
        {
            console.log('发送数据', data);
            let str = JSON.stringify(data);
            return str;
        }

        /**
         * 解析数据
         */
        public static decodeMsg(data: string)
        {
            let str = JSON.parse(data);
            console.log('解析数据', str);
            return str;
        }
    }
}