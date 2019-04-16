<?php
namespace ProfitFFFA\Command;

use ProfitFFFA\ProfitFFFA;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Instrument\SmallTools;
use pocketmine\utils\TextFormat;
use ProfitFFFA\Profit\addProfit;
use pocketmine\Player;
use ProfitFFFA\UI\makeUI;
use ProfitFFFA\Profit\Profit;
use pocketmine\command\ConsoleCommandSender;
use ProfitFFFA\Profit\PlayerD;

// 2019年4月1日 下午8:02:22
class MyCommand
{

    private $plugin;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Cmd监听
     *
     * @param CommandSender $player
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCmd($player, $command, string $label, array $k): bool
    {
        $plugin = $this->plugin;
        $Config = $plugin->getConfigs();
        switch (strtolower($command->getName())) {
            case "pf":
            case "profit":
            case "profitfffa":
                if (! isset($k[0]))
                    return FALSE;
                switch (strtolower($k[0])) {
                    case "my":
                    case "我的":
                        if (! $player instanceof Player) {
                            if (! isset($k[1])) {
                                $player->sendMessage(TextFormat::RED . "请输入需要查看数据的玩家名！");
                                return TRUE;
                            }
                            if (! is_file($plugin->getDataFolder() . ProfitFFFA::$PlayerPath . $k[1] . ".yml")) {
                                $player->sendMessage(TextFormat::RED . "该玩家可能还从未加入过游戏呢！！");
                                return TRUE;
                            }
                            $configs = $plugin->getPlayerConfig($k[1])->getAll();
                            $player->sendMessage(TextFormat::GOLD . "您查询的数据如下~");
                            foreach ($configs as $key => $data) {
                                if ($data === FALSE)
                                    $data = "否";
                                if ($data === TRUE)
                                    $data = "是";
                                if (! is_array($data))
                                    $player->sendMessage(SmallTools::getFontColor("") . $key . TextFormat::LIGHT_PURPLE . ":" . SmallTools::getFontColor("") . $data);
                            }
                            return TRUE;
                        }
                        if (isset($k[1])) {
                            switch (strtolower($k[0])) {
                                case "ui":
                                case "show":
                                case "u":
                                case "s":
                                case "界面":
                                case "显示":
                                    $ui = new PlayerD($plugin);
                                    $ui->makeUI($player);
                                    return TRUE;
                                default:
                                    break;
                            }
                        }
                        $configs = $plugin->getPlayerConfig($player)->getAll();
                        $player->sendMessage(TextFormat::GOLD . "您的数据如下~");
                        foreach ($configs as $key => $data) {
                            if ($data === FALSE)
                                $data = "否";
                            if ($data === TRUE)
                                $data = "是";
                            if (! is_array($data))
                                $player->sendMessage(SmallTools::getFontColor("") . $key . TextFormat::LIGHT_PURPLE . ":" . SmallTools::getFontColor("") . $data);
                        }
                        break;
                    case "add":
                    case "红包":
                    case "pf":
                    case "profit":
                    case "hb":
                    case "a":
                        if (! isset($k[1])) {
                            $player->sendMessage(TextFormat::RED . "请输入红包类型(item|money)!");
                            return TRUE;
                        }
                        switch ($k[1]) {
                            case "m":
                            case "money":
                            case "金币":
                            case "金钱":
                                if (! isset($k[2]) and ! $player instanceof Player) {
                                    $player->sendMessage(TextFormat::RED . "请输入要发送红包的金币数量！");
                                    return TRUE;
                                } else if (isset($k[2]) and strtolower($k[2])!="ui") {
                                    $Money = $k[2];
                                } else {
                                    $make = new makeUI($plugin);
                                    $make->makeMoneyPr($player);
                                    return TRUE;
                                }
                                $ProCount = $Config->get("默认红包数");
                                if (isset($k[3]))
                                    $ProCount = $k[3];
                                $key = SmallTools::getKey();
                                if (isset($k[4]))
                                    $key = $k[4];
                                $profit = new Profit($plugin);
                                $Type = "Luck";
                                if (isset($k[4]))
                                    $Type = $profit->getType($k[4]);
                                $addpro = new addProfit($plugin);
                                $addpro->addMoneyProfit($player, $key, $Money, $ProCount, $Type);
                                break;
                            case "item":
                            case "i":
                            case "物品":
                            case "方块":
                                if (! $player instanceof Player and ! isset($k[2])) {
                                    $player->sendMessage(TextFormat::RED . "请输入物品ID！");
                                    return TRUE;
                                } elseif (isset($k[2]) and strtolower($k[2])!="ui") {
                                    $ID = $k[2];
                                } else {
                                    $make = new makeUI($plugin);
                                    $make->makeItemPro($player);
                                    return TRUE;
                                }
                                if (! isset($k[3])) {
                                    $player->sendMessage(TextFormat::RED . "请输入物品数量！");
                                    return TRUE;
                                }
                                $ItemCount = (int) $k[3];
                                if (! isset($k[4])) {
                                    $player->sendMessage(TextFormat::RED . "请输入红包数量！");
                                    return TRUE;
                                }
                                $ProCount = (int) $k[4];
                                $key = SmallTools::getKey();
                                if (! isset($k[5]))
                                    $key = $k[5];
                                $Type = "Luck";
                                if (isset($k[6]))
                                    $Type = $k[6];
                                $profiy = new Profit($plugin);
                                $Type = $profiy->getType($Type);
                                $add = new addProfit($plugin);
                                $add->addItemProfit($player, $key, $ID, $ItemCount, $ProCount, $Type);
                                break;
                            default:
                                $player->sendMessage(TextFormat::RED . "请输入红包类型！(item|money)");
                                break;
                        }
                        break;
                    case "设置":
                    case "set":
                    case "setting":
                    case "s":
                        if ($player instanceof Player) {
                            $make = new makeUI($plugin);
                            $make->makeSettingUI($player);
                        } else {
                            $player->sendMessage(TextFormat::RED . "该功能需要使用UI，请加入游戏！");
                        }
                        break;
                    case "ui":
                    case "show":
                    case "界面":
                    case "显示":
                        if ($player instanceof Player) {
                            $make = new makeUI($plugin);
                            $make->makeMain($player);
                        } else {
                            $player->sendMessage(TextFormat::RED . "如果您真的想使用UI，请加入游戏！");
                        }
                        break;
                    case "help":
                    case "h":
                    case "帮助":
                    case "?":
                    case "？":
                        $this->makeHelp($player);
                        break;
                    default:
                        $this->makeHelp($player);
                        break;
                }
                return TRUE;
            case "hb":
            case "红包":
                if (isset($k[0])) {
                    $Money = $k[0];
                } else {
                    $Money = $Config->get("默认红包金额");
                }
                if (isset($k[1])) {
                    $key = $k[1];
                } else {
                    $key = SmallTools::getKey();
                }
                if (isset($k[2])) {
                    $count = $k[2];
                } else {
                    $count = $Config->get("默认红包数");
                }
                if (isset($k[3])) {
                    switch (strtolower($k[3])) {
                        case 0:
                        case "0":
                        case "luck":
                        case "l":
                        case "拼手气":
                            $Type = "Luck";
                            break;
                        case 1:
                        case "1":
                        case "平均":
                        case "mean":
                        case "m":
                            $Type = "Mean";
                            break;
                        case 2:
                        case "2":
                        case "天梯":
                        case "阶梯":
                        case "ladder":
                            $Type = "Ladder";
                        default:
                            $Type = "Luck";
                            break;
                    }
                } else {
                    $Type = "Luck";
                }
                $add = new addProfit($plugin);
                $add->addMoneyProfit($player, $key, $Money, $count, $Type);
                return TRUE;
            default:
                $this->makeHelp($player);
                return TRUE;
        }
        return FALSE;
    }

    /**
     *
     * @param ConsoleCommandSender|CommandSender|Player $player
     */
    public function makeHelp($player)
    {
        if ($player instanceof ConsoleCommandSender) {
            $player->sendMessage($this->makeHelpToString());
            return;
        }
        if ($player instanceof CommandSender) {
            $name = $player->getName();
            $player = $this->plugin->getServer()->getPlayer($name);
            if ($player == NULL or ! $player instanceof Player) {
                $this->plugin->getServer()
                    ->getLogger()
                    ->info("无法获取玩家对象！请检查系统数据！位置：makeHelp()  玩家： " . $name);
                return;
            }
        }
        makeUI::makeTip($player, $this->makeHelpToString());
    }

    public function getHelp(): array
    {
        return array(
            "/红包 <红包金额> <红包Key> <红包个数> <Luck|Mean|Ladder> " => " 快捷发布一个红包，可直接省略后部内容，仅输入“/红包”可快捷发送",
            "/pf add [item] [物品ID] [物品数量] [红包数量] <红包Key> <红包领取类型<Luck|Mean|Ladder>>" => "发布一个物品红包",
            "/pf add [money] [红包金额] [红包数量] <红包Key> <红包领取类型<Luck|Mean|Ladder>>" => "发布一个金币红包",
            "/pf show " => "显示红包功能主界面(游戏内有效)",
            "/pf set" => "显示红包功能设置页面(游戏内且管理员有效)",
            "/pf help" => "获取帮助界面",
            "/pf my <ui>" => "查看个人信息(当第二个参数为ui时，将以UI的形式展示您的信息！)"
        );
    }

    public function makeHelpToString(): string
    {
        $msg = "";
        $helps = $this->getHelp();
        foreach ($helps as $cmd => $help) {
            $cmd = TextFormat::GREEN . $cmd;
            $cmd = str_replace("[", TextFormat::WHITE . "[" . TextFormat::AQUA, $cmd);
            $cmd = str_replace("]", TextFormat::WHITE . "]" . TextFormat::GREEN, $cmd);
            $cmd = str_replace("<", TextFormat::BLUE . "<" . TextFormat::YELLOW, $cmd);
            $cmd = str_replace(">", TextFormat::BLUE . ">" . TextFormat::GREEN, $cmd);
            $cmd = str_replace("|", TextFormat::LIGHT_PURPLE . "|" . TextFormat::YELLOW, $cmd);
            $help = TextFormat::GOLD . $help;
            $help = str_replace("(", TextFormat::WHITE . "(" . TextFormat::AQUA, $help);
            $help = str_replace(")", TextFormat::WHITE . ")" . TextFormat::GOLD, $help);
            $msg .= $cmd . TextFormat::LIGHT_PURPLE . " :" . TextFormat::GOLD . $help . "\n";
        }
        $msg = $msg . TextFormat::WHITE . "[]内的内容为必须输入的内容！<>内的内容则可选不输入。";
        return $msg;
    }
}