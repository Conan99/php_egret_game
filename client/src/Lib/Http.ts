module Lib
{
    /**
      * 网络公共类
      * Http.requestInterceptor     请求拦截钩子
      * Http.responseInterceptor    响应拦截钩子
      * Http.get                   
      * Http.post
      * Http.request                
      */
    export class Http
    {
        //钩子函数，推荐在main.ts初始化，可覆盖
        public static requestInterceptor(request)
        {
            return request;
        };

        public static responseInterceptor(response)
        {
            return response;
        };

        /**
         * GET请求
         * @param url 请求URL
         * @param data 请求数据
         * @return Promise 回调函数
         */
        public static get(url: string, data: Object = {})
        {
            return this.request(url, egret.HttpMethod.GET, data)
        }

        /**
         * POST请求
         * @param url 请求URL
         * @param data 请求数据
         * @return Promise 回调函数
         */
        public static post(url: string, data: Object = {})
        {
            return this.request(url, egret.HttpMethod.POST, data)
        }

        /**
         * REQUEST请求
         * @param url 请求URL
         * @param data 请求数据
         * @param type 请求方式
         * @return Promise 回调函数
         */
        public static request(url: string, type: string, data: Object = {})
        {
            return new Promise((resolve, reject) => {
                let request = new egret.HttpRequest();
                request.responseType = egret.HttpResponseType.TEXT;
                request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
                // request.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                // request.setRequestHeader(Config.myHttp.tokenName, egret.localStorage.getItem(Config.myHttp.tokenName) || '');
                if (this.requestInterceptor && typeof this.requestInterceptor === 'function') {
                    request = this.requestInterceptor(request)
                }
                switch (type) {
                    case egret.HttpMethod.POST:
                        request.open(url, type);
                        request.send(this.formatData(data, type));
                        break;
                    case egret.HttpMethod.GET:
                        request.open(url + this.formatData(data, type), type);
                        request.send();
                        break;
                }
                request.addEventListener(egret.Event.COMPLETE, onComplete, this);
                request.addEventListener(egret.IOErrorEvent.IO_ERROR, onIOError, this);
                request.addEventListener(egret.ProgressEvent.PROGRESS, onProgress, this);

                function onComplete(event: egret.Event)
                {
                    let request = <egret.HttpRequest>event.currentTarget;
                    egret.log(request.response);
                    let response = JSON.parse(request.response);
                    if (this.responseInterceptor && typeof this.responseInterceptor === 'function') {
                        response = this.responseInterceptor(response)
                    }
                    resolve(response);
                    // switch (response.code) {
                    //     case 0:
                    //         return resolve(response.data);
                    //         break;
                    //     default:
                    //         return reject(response.code);
                    //         break;
                    // }
                }

                function onIOError(event: egret.IOErrorEvent): void
                {
                    //再次重新请求
                    setTimerS(event)
                }

                function onProgress(event: egret.ProgressEvent): void
                {
                    egret.log("request progress : " + Math.floor(100 * event.bytesLoaded / event.bytesTotal) + "%");
                }

                let TimerCount = 0;

                function setTimerS(event: egret.IOErrorEvent)
                {
                    TimerCount++;
                    if (TimerCount < 3) {
                        request.responseType = egret.HttpResponseType.TEXT;
                        // request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
                        request.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                        request.open(url, type);
                        request.send(data);
                    } else {
                        egret.log("温馨提示", "网络出错！");
                        return reject(event);
                    }

                }
            }).catch(err => {
                egret.error(err);
            })
        }

        private static formatData(data: Object, type: string): string
        {
            let params: string = '';
            for (let key of Object.keys(data)) {
                if (params.length > 0) {
                    params = `${params}&`
                }
                params += `${key}=${data[key]}`
            }
            switch (type) {
                case egret.HttpMethod.POST:
                    params = JSON.stringify(data);//json時启用
                    break;
                case egret.HttpMethod.GET:
                    if (params.length > 0)
                        params = `?${params}`;
                    break;
            }
            return params
        }
    }
}