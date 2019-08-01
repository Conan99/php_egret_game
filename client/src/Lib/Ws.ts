module Lib
{
    export class Ws extends egret.WebSocket
    {
        private static _instance: Ws | null;

        private static readonly _CONNECT_STATUS = {
            'NO': 0,
            'ING': 1,
            'ED': 2,
        };

        /**
         * 获取实例
         */
        public static get instance(): Ws
        {
            if (!this._instance) {
                this._instance = new Ws();
            }
            return Ws._instance;
        }

        private _status: number = 0;//连接状态0未连接1连接中2已连接

        private constructor()
        {
            super();
        }

        public ws_addr: string;

        /**
         * 开始连接
         * @param ws_addr
         */
        public connect(ws_addr)
        {
            if (!ws_addr) {
                egret.log('连接地址错误');
                return;
            }
            if (this._status == Ws._CONNECT_STATUS.ING) {//连接中无法再次连接
                return;
            }
            if (this._status == Ws._CONNECT_STATUS.ED) {//已连接需断开连接
                this.close();
                this._status = Ws._CONNECT_STATUS.NO;
            }
            //设置数据格式为二进制，默认为字符串
            // this.type = egret.WebSocket.TYPE_BINARY;
            //添加链接打开侦听，连接成功会调用此方法
            this.addEventListener(egret.Event.CONNECT, this.onSocketOpen, this);
            //添加收到数据侦听，收到数据会调用此方法
            this.addEventListener(egret.ProgressEvent.SOCKET_DATA, this.onReceiveMessage, this);
            //添加链接关闭侦听，手动关闭或者服务器关闭连接会调用此方法
            this.addEventListener(egret.Event.CLOSE, this.onSocketClose, this);
            //添加异常侦听，出现异常会调用此方法
            this.addEventListener(egret.IOErrorEvent.IO_ERROR, this.onSocketError, this);
            //连接服务器
            this.connectByUrl(ws_addr);
            this._status = Ws._CONNECT_STATUS.ING;
            console.log('connecting');
        }

        /**
         * 重新连接
         */
        public reConnect()
        {
            this.connect(this.ws_addr);
        }

        private onSocketOpen(): void
        {
            egret.log("WebSocketOpen");
            Ws._instance = this;
            this._status = Ws._CONNECT_STATUS.ED;
            Main.loginGame();
        }

        private onSocketClose(): void
        {
            egret.log("WebSocketClose");
            Ws._instance = null;
            this._status = Ws._CONNECT_STATUS.NO;
            Main.endGame();
        }

        private onSocketError(): void
        {
            this._status = Ws._CONNECT_STATUS.NO;
            egret.log("WebSocketError");
        }

        private onReceiveMessage(e: egret.Event): void
        {
            egret.log("WebSocketMessage");
            let msg: string = Ws.instance.readUTF();
            Msg.Pack.call(msg);
        }
    }
}