<?php
namespace ProfitFFFA;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Instrument\SmallTools;
use ProfitFFFA\Configs\BlockList;
use ProfitFFFA\PlayerEvent\Event;
use ProfitFFFA\Configs\Configs;
use Instrument\CheckForUpdates;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use ProfitFFFA\PlayerEvent\UICallable;
use ProfitFFFA\Configs\Calibration;
use ProfitFFFA\Command\MyCommand;

/**
 * Main ID
 *
 * @var integer
 */
const MainUIID = 01111;

/**
 * Money红包ID
 *
 * @var integer
 */
const makeMoneyPrUIID = 11111;

/**
 * Pro类型ID
 *
 * @var integer
 */
const sendProUIID = 21111;

/**
 * Item红包ID
 *
 * @var integer
 */
const makeItemProUIID = 31111;

/**
 * Pro列表ID
 *
 * @var integer
 */
const ProListUIID = 41111;

/**
 * Pro设置界面ID
 *
 * @var integer
 */
const SettingProUIID = 51111;

/**
 * 主功能设置界面ID
 *
 * @var integer
 */
const SettingUIID = 61111;

const XiaokaiUIID = 71111;

/**
 * exp红包UIID
 *
 * @var integer
 */
const makeSendExpUIID = 81111;

class ProfitFFFA extends PluginBase implements Listener
{

    public static $isPlayerTo = array();

    private static $getInstance = null;

    /**
     * 红包配置文件存放路径
     *
     * @var string
     */
    public static $ProfitPath = "Profit/";

    /**
     * 玩家配置文件储存路径
     *
     * @var string
     */
    public static $PlayerPath = "Players/";

    /**
     * ID物品对照表Config对象
     *
     * @var Config
     */
    public $BlickID;

    /**
     * C插件配置文件对象
     *
     * @var Config
     */
    public $Config;

    /**
     * Pro红包列表配置文件对象
     *
     * @var Config
     */
    public $ListConfig;

    /**
     * UI已经发送了的玩家对照表
     *
     * @var array
     */
    public $ShowUIPlayerList = array();

    /**
     * UI发送并且临时存储发送的Key列表
     *
     * @var array
     */
    public $UIProList = array();

    /**
     * UI发送并且临时存储点击操作的Key
     *
     * @var String
     */
    public $UIProListKey;

    /**
     *
     * @var MyCommand
     */
    public $onCmd;

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $ok = $this->onCmd->onCmd($sender, $command, $label, $args);
        if ($ok === NULL or $ok === FALSE or ($ok !== FALSE and $ok !== TRUE)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function onDisable()
    {
        $this->getLogger()->info(SmallTools::getFontColor($this->getName() . "插件关闭！QAQ你居然把我关闭了，是不是不爱我了."));
    }

    public function onLoad()
    {
        self::$getInstance = $this;
        $this->getLogger()->info(SmallTools::getFontColor($this->getName() . "插件加载中....."));
    }

    public function onEnable()
    {
        // error_reporting(0);
        @date_default_timezone_set('Etc/GMT-8');
        $start = $this->getServer()->getPluginManager();
        $start->registerEvents($this, $this);
        $start->registerEvents(new Event($this), $this);
        $start->registerEvents(new UICallable($this), $this);
        BlockList::makeConfig($this);
        new Configs($this);
        if ($this->Config->get("更新检查")) {
            new CheckForUpdates($this);
        }
        $moneyAPI = $this->getServer()
            ->getPluginManager()
            ->getPlugin("EconomyAPI");
        if ($moneyAPI !== NULL and ! $moneyAPI->isEnabled()) {
            $this->getLogger()->info(TextFormat::RED . "检测未启用" . TextFormat::YELLOW . "EconomyAPI" . TextFormat::RED . "插件！部分功能可能不可用！");
        }
        if ($this->getConfigs()->get("配置校准")) {
            $Calibration = new Calibration($this);
            $Calibration->start();
        }
        $this->onCmd = new MyCommand($this);
        $this->getLogger()->info(SmallTools::getFontColor($this->getName() . "插件启动！作者：小凯     QQ：2508543202  感谢使用！"));
    }

    public static function getInstance()
    {
        return self::$getInstance;
    }

    public function getBlickID(): Config
    {
        return $this->BlickID;
    }

    public function getConfigs(): Config
    {
        return $this->Config;
    }

    public function getListConfig(): Config
    {
        return $this->ListConfig;
    }

    public function getProConfig(String $FilePath): Config
    {
        return new Config($this->getDataFolder() . ProfitFFFA::$ProfitPath . $FilePath, Config::YAML, Configs::getProDC());
    }

    /**
     * 获取玩家配置文件
     *
     * @param Player|CommandSender|ConsoleCommandSender|String $player
     * @return Config
     */
    public function getPlayerConfig($player): Config
    {
        if ($player instanceof CommandSender) {
            $player = $player->getName();
        }
        if ($player instanceof ConsoleCommandSender) {
            return NULL;
        }
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        return new Config($this->getDataFolder() . ProfitFFFA::$PlayerPath . $player . ".yml", Config::YAML, Configs::getDPlayer());
    }
}
