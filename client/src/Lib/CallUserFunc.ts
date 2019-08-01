module Lib
{
    export class CallUserFunc
    {
        public static call_user_func(cb, parameters)
        {
            parameters = Array.prototype.slice.call(arguments, 1)
            return CallUserFunc.call_user_func_array(cb, parameters)
        }

        public static call_user_func_array(cb, parameters)
        {
            let $global = (typeof window !== 'undefined' ? window : global)
            let func
            let scope = null

            let validJSFunctionNamePattern = /^[_$a-zA-Z\xA0-\uFFFF][_$a-zA-Z0-9\xA0-\uFFFF]*$/

            if (typeof cb === 'string') {
                if (typeof $global[cb] === 'function') {
                    func = $global[cb]
                } else if (cb.match(validJSFunctionNamePattern)) {
                    func = (new Function(null, 'return ' + cb)()) // eslint-disable-line no-new-func
                }
            } else if (Object.prototype.toString.call(cb) === '[object Array]') {
                if (typeof cb[0] === 'string') {
                    if (cb[0].match(validJSFunctionNamePattern)) {
                        func = eval(cb[0] + "['" + cb[1] + "']") // eslint-disable-line no-eval
                    }
                } else {
                    func = cb[0][cb[1]]
                }

                if (typeof cb[0] === 'string') {
                    if (typeof $global[cb[0]] === 'function') {
                        scope = $global[cb[0]]
                    } else if (cb[0].match(validJSFunctionNamePattern)) {
                        scope = eval(cb[0]) // eslint-disable-line no-eval
                    }
                } else if (typeof cb[0] === 'object') {
                    scope = cb[0]
                }
            } else if (typeof cb === 'function') {
                func = cb
            }

            if (typeof func !== 'function') {
                throw new Error(func + ' is not a valid function')
            }

            return func.apply(scope, parameters)
        }
    }
}