import Pack = Msg.Pack;
import MsgDict = Msg.MsgDict;
import CallFunc = Msg.CallFunc;

class Main extends eui.UILayer
{
    public static instance: Main;
    private _login: Skins.Login.Main;
    private _game: Skins.Game.Main;

    protected createChildren(): void
    {
        super.createChildren();
        Main.instance = this;

        //inject the custom material parser
        //注入自定义的素材解析器
        let assetAdapter = new AssetAdapter();
        egret.registerImplementation("eui.IAssetAdapter", assetAdapter);
        egret.registerImplementation("eui.IThemeAdapter", new ThemeAdapter());

        this.runGame().catch(e => {
            console.log(e);
        });
    }

    private async runGame()
    {
        await this.loadResource();
        CallFunc.init();
        Main.login();
    }

    private async loadResource()
    {
        try {
            const loadingView = new LoadingUI();
            this.addChild(loadingView);
            await RES.loadConfig("resource/default.res.json", "resource/");
            await this.loadTheme();
            await RES.loadGroup("components", 0, loadingView);
            await RES.loadGroup("login", 0, loadingView);
            this.removeChild(loadingView);
        } catch (e) {
            console.error(e);
        }
    }

    private loadTheme()
    {
        return new Promise((resolve, reject) => {
            // load skin theme configuration file, you can manually modify the file. And replace the default skin.
            //加载皮肤主题配置文件,可以手动修改这个文件。替换默认皮肤。
            let theme = new eui.Theme("resource/default.thm.json", this.stage);
            theme.addEventListener(eui.UIEvent.COMPLETE, () => {
                resolve();
            }, this);

        })
    }

    /*********************** 登录相关 ***********************/
    public static login()
    {
        if (!Main.instance._login) {
            Main.instance._login = Skins.Login.Main.instance;
        }
        Main.instance.addChild(Main.instance._login);
    }

    public static loginGame()
    {
        let login = {usign: egret.localStorage.getItem(Data.Common.LocalKey.usign)};
        Pack.send(MsgDict.C_USER_LOGIN, login);
    }

    /*********************** 游戏相关 ***********************/
    public static startGame()
    {
        Main.instance._game = Skins.Game.Main.instance;
        Main.instance.addChild(Main.instance._game);
        Main.instance.removeChild(Main.instance._login);

    }

    public static endGame()
    {
        if (!Main.instance._login.parent) Main.instance.addChild(Main.instance._login);
        if (Main.instance._game && Main.instance._game.parent) Main.instance.removeChild(Main.instance._game);
    }
}
