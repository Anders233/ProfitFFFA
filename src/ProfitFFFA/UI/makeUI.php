<?php
namespace ProfitFFFA\UI;

use ProfitFFFA\ProfitFFFA;
use pocketmine\Player;
use Instrument\UI\SimpleForm;
use const ProfitFFFA\MainUIID;
use pocketmine\utils\TextFormat;
use Instrument\UI\CustomFormAPI;
use Instrument\SmallTools;
use const ProfitFFFA\sendProUIID;
use const ProfitFFFA\makeMoneyPrUIID;
use const ProfitFFFA\makeItemProUIID;
use const ProfitFFFA\ProListUIID;
use Instrument\UI\ModalForm;
use const ProfitFFFA\SettingProUIID;
use const ProfitFFFA\SettingUIID;
use const ProfitFFFA\XiaokaiUIID;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

class makeUI
{

    private $plugin;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    public function makeXiaokai(Player $player)
    {
        $plugin = $this->plugin;
        $ui = new SimpleForm(XiaokaiUIID);
        $ui->setTitle(TextFormat::YELLOW . "帅帅的凯凯");
        $ui->setContent(TextFormat::WHITE . "您好！\n\n首先，感谢您使用本功能！\n\n本功能由帅帅滴凯凯提供！\n\n以下为凯凯联系方式，如有需要欢迎来访~\n\n有什么好的建议和意见欢迎提出来哦！\n\n" . TextFormat::YELLOW . "QQ：2508543202\n\n" . TextFormat::YELLOW . "Mail:Winfxk@qq.com\n\n" . TextFormat::LIGHT_PURPLE . "对了，下面这个是读“liǎo gǎi”，不是读“liǎo jiě”哦~\n\n" . TextFormat::LIGHT_PURPLE . "(*^▽^*)");
        $ui->addButton(TextFormat::WHITE . "了解");
        $ui->sendToPlayer($player);
        $plugin->ShowUIPlayerList[$player->getName()] = TRUE;
    }

    /**
     * Pro设置，来至红包列表
     *
     * @param Player $player
     * @param int|string|array $data
     */
    public function makeSettingPro(Player $player, $data)
    {
        $plugin = $this->plugin;
        if (isset($plugin->UIProList[$player->getName()]) and is_array($plugin->UIProList[$player->getName()]) and isset($plugin->UIProList[$player->getName()][$data]) and $plugin->getListConfig()->get($plugin->UIProList[$player->getName()][$data], NULL) !== NULL) {
            $key = $plugin->UIProList[$player->getName()][$data];
            $filePath = $plugin->getListConfig()->get($key);
            $ProConfig = $plugin->getProConfig($filePath);
            $plugin->UIProListKey[$player->getName()] = $key;
            $ui = new ModalForm(SettingProUIID);
            $msg = "这个";
            if ($ProConfig->get("Name") == $player->getName()) {
                $msg = "您的";
            }
            $ui->setTitle(TextFormat::YELLOW . $ProConfig->get("Name") . TextFormat::GOLD . "发送的" . TextFormat::GREEN . $ProConfig->get("Type") . TextFormat::GOLD . "红包");
            $ms = TextFormat::WHITE . TextFormat::BOLD . "您想对" . $msg . "红包干什么？";
            $ProArray = $ProConfig->getAll();
            foreach ($ProArray as $k => $v) {
                if (! is_array($v)) {
                    $ms .= "\n" . SmallTools::getFontColor("") . $k . TextFormat::WHITE . " : " . SmallTools::getFontColor("") . $v;
                }
            }
            $ui->setContent($ms);
            if ($ProConfig->get("Name") == $player->getName() or $player->isOp()) {
                $ui->setButton1(TextFormat::RED . "领取");
            } else {
                $ui->setButton1(TextFormat::GREEN . "确定");
            }
            if ($player->isOp() or ($plugin->getConfigs()->get("允许玩家查看红包列表") and $plugin->getConfigs()->get("允许玩家在红包列表领取红包")) or $ProConfig->get("Name") == $player->getName()) {
                $ui->setButton2(TextFormat::DARK_GREEN . "删除");
            } else {
                $ui->setButton2(TextFormat::GREEN . "取消");
            }
            $ui->sendToPlayer($player);
            $plugin->ShowUIPlayerList[$player->getName()] = TRUE;
        } else {
            makeUI::makeTip($player, "数据解析错误 ：" . TextFormat::WHITE . "未知的玩家上级目录！");
        }
        if (isset($plugin->UIProList[$player->getName()])) {
            unset($plugin->UIProList[$player->getName()]);
        }
    }

    /**
     * 物品红包
     *
     * @param Player $player
     */
    public function makeItemPro(Player $player)
    {
        $plugin = $this->plugin;
        $config = $plugin->getConfigs()->getAll();
        $ui = new CustomFormAPI(makeItemProUIID);
        $ui->setTitle(TextFormat::GOLD . $plugin->getName() . TextFormat::WHITE . "-" . TextFormat::YELLOW . "Item");
        $ui->addInput(TextFormat::YELLOW . "请输入物品ID");
        $ui->addInput(TextFormat::YELLOW . "请输入物品数量");
        $ui->addInput(TextFormat::BLUE . "请输入红包个数", $config["默认红包数"]);
        $ui->addInput(TextFormat::BLUE . "红包口令", SmallTools::getKey());
        $ui->addDropdown(TextFormat::WHITE . "红包类型", array(
            TextFormat::GOLD . "拼手气红包",
            TextFormat::GREEN . "人均红包",
            TextFormat::RED . "天梯红包"
        ), 0);
        $ui->sendToPlayer($player);
        $plugin->ShowUIPlayerList[$player->getName()] = TRUE;
    }

    /**
     * 创建发金币红包页面
     *
     * @param Player $player
     *            玩家对象
     */
    public function makeMoneyPr(Player $player)
    {
        $plugin = $this->plugin;
        $economyAPI = $plugin->getServer()
            ->getPluginManager()
            ->getPlugin("EconomyAPI");
        if ($economyAPI !== NULL and ! $economyAPI->isEnabled()) {
            makeUI::makeTip($player, TextFormat::WHITE . $plugin->getServer()->getMotd() . TextFormat::DARK_RED . "暂未安装" . TextFormat::YELLOW . "EconomyAPI" . TextFormat::DARK_RED . "插件！暂不支持发布金币红包！");
            return;
        }
        $config = $plugin->getConfigs()->getAll();
        $MaxKey = "";
        if ((int) $config["红包Key最长数"] > 0) {
            $MaxKey = TextFormat::DARK_RED . "最大支持长度为：" . TextFormat::WHITE . $config["红包Key最长数"];
        }
        if (! $config["允许特殊Key"]) {
            $MaxKey .= TextFormat::RED . "不允许特殊字符";
        }
        $ui = new CustomFormAPI(makeMoneyPrUIID);
        $ui->setTitle(TextFormat::GOLD . $plugin->getName() . TextFormat::WHITE . "-" . TextFormat::YELLOW . "Money");
        $ui->addInput(TextFormat::YELLOW . "红包金额", $config["默认红包金额"]);
        $ui->addInput(TextFormat::YELLOW . "红包个数", $config["默认红包数"]);
        $ui->addInput(TextFormat::WHITE . "红包口令" . $MaxKey, SmallTools::getKey());
        $ui->addDropdown(TextFormat::WHITE . "红包类型", array(
            TextFormat::GOLD . "拼手气红包",
            TextFormat::GREEN . "人均红包",
            TextFormat::RED . "天梯红包"
        ), 0);
        $ui->sendToPlayer($player);
        $plugin->ShowUIPlayerList[$player->getName()] = TRUE;
    }

    /**
     * 创建红包主页UI
     *
     * @param Player $player
     *            玩家对象
     */
    public function makeMain(Player $player)
    {
        $plugin = $this->plugin;
        $ui = new SimpleForm(MainUIID);
        $ui->setTitle(TextFormat::GOLD . $plugin->getName() . TextFormat::WHITE . "-" . TextFormat::YELLOW . $player->getName());
        $ui->addButton(TextFormat::GREEN . "发红包");
        $ui->addButton(TextFormat::YELLOW . "红包列表");
        $ui->addButton(TextFormat::YELLOW . "作者相关");
        $ui->addButton(TextFormat::YELLOW . "我的数据");
        if ($player->isOp()) {
            $ui->addButton(TextFormat::RED . "配置设置");
        }
        $ui->sendToPlayer($player);
        $plugin->ShowUIPlayerList[$player->getName()] = TRUE;
        ProfitFFFA::$isPlayerTo[$player->getName()] = TRUE;
    }

    /**
     * 红包类型选择窗口
     *
     * @param Player $player
     *            玩家对象
     */
    public function sendPro(Player $player)
    {
        $plugin = $this->plugin;
        if ($plugin->getConfigs()->get("玩家红包上限") > 0) {
            if ($plugin->getConfigs()->get("玩家个人红包上限") > 0) {
                $msg = TextFormat::YELLOW . count($plugin->getPlayerConfig($player)->get("未完成红包")) . TextFormat::WHITE . "/" . TextFormat::YELLOW . $plugin->getConfigs()->get("玩家个人红包上限") . TextFormat::WHITE . "|" . TextFormat::YELLOW . count($plugin->getListConfig()->getAll()) . TextFormat::WHITE . "/" . TextFormat::YELLOW . $plugin->getConfigs()->get("玩家红包上限");
            } else {
                $msg = TextFormat::YELLOW . count($plugin->getPlayerConfig($player)->get("未完成红包")) . TextFormat::WHITE . "/" . TextFormat::YELLOW . "Max" . TextFormat::WHITE . "|" . TextFormat::YELLOW . count($plugin->getListConfig()->getAll()) . TextFormat::WHITE . "/" . TextFormat::YELLOW . $plugin->getConfigs()->get("玩家红包上限");
            }
        } else if ($plugin->getConfigs()->get("玩家个人红包上限") > 0) {
            $msg = TextFormat::YELLOW . count($plugin->getPlayerConfig($player)->get("未完成红包")) . TextFormat::WHITE . "/" . TextFormat::YELLOW . $plugin->getConfigs()->get("玩家个人红包上限") . TextFormat::WHITE . "|" . TextFormat::YELLOW . count($plugin->getListConfig()->getAll()) . TextFormat::WHITE . "/" . TextFormat::YELLOW . "Max";
        } else {
            $msg = TextFormat::YELLOW . count($plugin->getPlayerConfig($player)->get("未完成红包")) . TextFormat::WHITE . "/" . TextFormat::YELLOW . "Max" . TextFormat::WHITE . "|" . TextFormat::YELLOW . count($plugin->getListConfig()->getAll()) . TextFormat::WHITE . "/" . TextFormat::YELLOW . "Max";
        }
        $msg = TextFormat::WHITE . "(" . $msg . TextFormat::WHITE . ")";
        $ui = new SimpleForm(sendProUIID);
        $ui->setContent(TextFormat::YELLOW . "请选择想要发红包的类型！\n\n\n\n");
        $ui->setTitle(TextFormat::GOLD . $plugin->getName());
        $ui->addButton(TextFormat::YELLOW . "物品红包" . $msg);
        $ui->addButton(TextFormat::GREEN . "金币红包" . $msg);
        $ui->addButton(TextFormat::AQUA . "经验红包" . $msg);
        $ui->sendToPlayer($player);
        $plugin->ShowUIPlayerList[$player->getName()] = TRUE;
    }

    /**
     * 创建一个提示窗
     *
     * @param Player|CommandSender|ConsoleCommandSender $player
     *            玩家对象
     * @param String $content
     *            弹窗内容
     * @param String $button1
     *            按钮内容
     * @param String $button2
     *            按钮2
     * @param String $title
     *            标题
     */
    public static function makeTip($player, String $content, String $button1 = TextFormat::WHITE."确定", String $button2 = TextFormat::GREEN."取消", $title = NULL)
    {
        if ($title === NULL) {
            $title = TextFormat::GOLD . ProfitFFFA::getInstance()->getName();
        }
        $ui = new ModalForm((int) SmallTools::getRandText(10, "1234567890"));
        $ui->setContent(TextFormat::RED . $content);
        $ui->setTitle($title);
        $ui->setButton1($button1);
        $ui->setButton2($button2);
        $ui->sendToPlayer($player);
    }

    /**
     * 显示红包列表
     *
     * @param Player $player
     */
    public function makeProList(Player $player)
    {
        $plugin = $this->plugin;
        if ($player->isOp() or $plugin->getConfigs()->get("允许玩家查看红包列表")) {
            $list = $plugin->getListConfig()->getAll();
            if (count($list) < 1) {
                makeUI::makeTip($player, "当前暂未可领取红包！");
                return;
            }
            $ui = new SimpleForm(ProListUIID);
            $ui->setTitle(TextFormat::GOLD . $plugin->getName() . TextFormat::WHITE . "-" . TextFormat::YELLOW . "List");
            foreach ($list as $key => $file) {
                $config = $plugin->getProConfig($file)->getAll();
                $plugin->UIProList[$player->getName()][] = $key;
                $ui->addButton(TextFormat::WHITE . $key . TextFormat::AQUA . "|" . TextFormat::DARK_BLUE . $config["Type"] . TextFormat::AQUA . "|" . TextFormat::DARK_GREEN . $config["Time"] . TextFormat::AQUA . "|" . TextFormat::YELLOW . $config["Name"]);
            }
            $ui->sendToPlayer($player);
            $plugin->ShowUIPlayerList[$player->getName()] = TRUE;
            return;
        } else {
            $list = $plugin->getListConfig()->getAll();
            if (count($list) > 1) {
                $foreachTrue = FALSE;
                $ui = new SimpleForm(ProListUIID);
                $ui->setTitle(TextFormat::GOLD . $plugin->getName() . TextFormat::WHITE . "-" . TextFormat::YELLOW . "List");
                foreach ($list as $key => $file) {
                    $config = $plugin->getProConfig($file)->getAll();
                    if ($config["Name"] === $player->getName()) {
                        $foreachTrue = true;
                        $plugin->UIProList[$player->getName()][] = $key;
                        $ui->addButton(TextFormat::WHITE . $key . TextFormat::AQUA . "|" . TextFormat::DARK_BLUE . $config["Type"] . TextFormat::AQUA . "|" . TextFormat::DARK_GREEN . $config["Time"] . TextFormat::AQUA . "|" . TextFormat::YELLOW . $config["Name"]);
                    }
                }
                if ($foreachTrue) {
                    $ui->sendToPlayer($player);
                    $plugin->ShowUIPlayerList[$player->getName()] = TRUE;
                    return;
                } else {
                    unset($ui);
                }
            }
            makeUI::makeTip($player, "当前服务器以禁止非管理员查看红包列表！");
            return;
        }
    }

    /**
     * 设置页面
     *
     * @param Player $player
     */
    public function makeSettingUI(Player $player)
    {
        $plugin = $this->plugin;
        if ($player->isOp()) {
            $textf = TextFormat::WHITE;
            $config = $plugin->getConfigs()->getAll();
            $ui = new CustomFormAPI(SettingUIID);
            $ui->setTitle(TextFormat::GOLD . $plugin->getName() . TextFormat::WHITE . "-" . TextFormat::YELLOW . "Setting");
            $ui->addInput($textf . "红包Key最长数", $config["红包Key最长数"]);
            $ui->addToggle($textf . "定时清理红包", $config["定时清理红包"]);
            $ui->addToggle($textf . "允许特殊Key", $config["允许特殊Key"]);
            $ui->addSlider($textf . "清理间隔", 1, 30, 1, $config["清理间隔"]);
            $ui->addSlider($textf . "玩家红包上限", 100, 10000, 50, $config["玩家红包上限"]);
            $ui->addSlider($textf . "玩家个人红包上限", 5, 500, 5, $config["玩家个人红包上限"]);
            $ui->addSlider($textf . "默认Key长度", 3, 100, 1, $config["默认Key长度"]);
            $ui->addToggle($textf . "允许玩家查看红包列表", $config["允许玩家查看红包列表"]);
            $ui->addToggle($textf . "允许玩家在红包列表领取红包", $config["允许玩家在红包列表领取红包"]);
            $ui->addToggle($textf . "更新检查", $config["更新检查"]);
            $ui->addToggle($textf . "配置校准", $config["配置校准"]);
            $ui->addToggle($textf . "初次进入提示", $config["初次进入提示"]);
            $ui->addToggle($textf . "限制创造模式发送红包", $config["限制创造模式发送红包"]);
            $ui->addSlider($textf . "默认红包数", 5, 100, 5, $config["默认红包数"]);
            $ui->addToggle($textf . "世界黑名单", $config["世界黑名单"]["世界黑名单"]);
            $ui->addInput($textf . "世界黑名单列表(多个请用;分割)", $this->arrayToString($config["世界黑名单"]["worlds"]));
            $ui->addToggle($textf . "允许领取红包", $config["领取红包"]["领取红包"]);
            $ui->addToggle($textf . "撤回领取红包产生的各种事件", $config["撤回事件检测"]["撤回事件检测"]);
            $ui->addToggle($textf . "点击打开GUI", $config["点击打开GUI"]["点击打开GUI"]);
            $ui->addInput($textf . "点击打开GUI时的手持物品ID", $config["点击打开GUI"]["手持物品ID"]);
            $ui->addToggle($textf . "撤回点击打开GUI产生的各种事件", $config["点击打开GUI"]["撤回事件检测"]);
            $ui->addInput($textf . "点击打开GUI时的被点击物品ID", $config["点击打开GUI"]["被点击物品ID"]);
            $ui->addSlider($textf . "默认红包金额", 1000, 100000, 1000, $config["默认红包金额"]);
            $ui->addToggle($textf . "经验红包", $config["经验红包"]);
            $ui->sendToPlayer($player);
            $plugin->ShowUIPlayerList[$player->getName()] = TRUE;
        } else {
            makeUI::makeTip($player, "你没有权限使用此功能！");
        }
    }

    private function arrayToString(Array $a)
    {
        $msg = "";
        foreach ($a as $s) {
            $msg .= $s . ";";
        }
        return $msg;
    }
}
?>