class Config
{
    /** 舞台宽 */
    public static readonly STAGE_WIDTH = 640;
    /** 舞台高 */
    public static readonly STAGE_HEIGHT = 1126;

    /** 顶部高度 */
    public static readonly MAIN_HEADER_HEIGHT: number = 120;
    /** 底部高度 */
    public static readonly MAIN_FOOTER_HEIGHT: number = 131;
    /** 中部高度 */
    public static readonly MAIN_BODY_HEIGHT: number = Config.STAGE_HEIGHT - Config.MAIN_FOOTER_HEIGHT;

    /** 头像长度 */
    public static readonly MAIN_HEADER_IMG_WIDTH: number = 70;

    public static readonly TEXT_CORLOR: string = '733f19';

    public static readonly TEXT_SIZE: number = 15;

    public static readonly HEART_TIME: number = 60;//心跳时间（秒）

    public static readonly TIMER_DELAY: number = 50;//计时器间隔（毫秒）

    public static readonly MAP_ITEM_W = 56;//地图格子大小
}