<?php
namespace ProfitFFFA\Configs;

use ProfitFFFA\ProfitFFFA;
use pocketmine\utils\Config;

class Configs
{

    private $plugin;

    public static function getDPlayer(): array
    {
        return array(
            "Name" => NULL,
            "初次加入" => true,
            "领取红包总数" => 0,
            "发红包总数" => 0,
            "领取金币红包数" => 0,
            "领取物品红包数" => 0,
            "发送物品总数" => 0,
            "领取物品总数" => 0,
            "发送金币总数" => 0,
            "领取金币总数" => 0,
            "接受经验红包数" => 0,
            "发送经验总数" => 0,
            "接受经验总数" => 0,
            "人品" => 0,
            "未完成红包" => array(),
            "未读消息" => array()
        );
    }

    public static function getDC(): array
    {
        return array(
            "红包Key最长数" => 15,
            "定时清理红包" => TRUE,
            "允许特殊Key" => true,
            "清理间隔" => 7,
            "玩家红包上限" => 100,
            "玩家个人红包上限" => 5,
            "默认Key长度" => 3,
            "允许玩家查看红包列表" => true,
            "允许玩家在红包列表领取红包" => TRUE,
            "更新检查" => true,
            "配置校准" => TRUE,
            "初次进入提示" => true,
            "限制创造模式发送红包" => TRUE,
            "默认红包数" => 10,
            "世界黑名单" => array(
                "世界黑名单" => true,
                "worlds" => array(
                    "dy",
                    "地狱"
                )
            ),
            "领取红包" => array(
                "领取红包" => TRUE,
                "撤回事件检测" => true
            ),
            "点击打开GUI" => array(
                "点击打开GUI" => true,
                "手持物品ID" => "280:0",
                "撤回事件检测" => true,
                "被点击物品ID" => "42:0"
            ),
            "默认红包金额" => 10000,
            "经验红包" => true
        );
    }

    /**
     * 默认红包配置文件
     *
     * @return array
     */
    public static function getProDC(): array
    {
        return array(
            "Time" => date("Y-m-d H:i:s"),
            "Name" => NULL,
            "Type" => "Money",
            "Key" => NULL,
            "Style" => "Luck",
            "金额" => ProfitFFFA::getInstance()->getConfigs()->get("默认红包金额"),
            "红包数量" => ProfitFFFA::getInstance()->getConfigs()->get("默认红包数"),
            "物品ID" => NULL,
            "红包剩余金额" => ProfitFFFA::getInstance()->getConfigs()->get("默认红包金额"),
            "红包剩余个数" => ProfitFFFA::getInstance()->getConfigs()->get("默认红包数"),
            "玩家列表" => array()
        );
    }

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
        @mkdir($plugin->getDataFolder());
        @mkdir($plugin->getDataFolder() . ProfitFFFA::$ProfitPath);
        @mkdir($plugin->getDataFolder() . "Players");
        $plugin->Config = new Config($plugin->getDataFolder() . "Config.yml", Config::YAML, Configs::getDC());
        $plugin->ListConfig = new Config($plugin->getDataFolder() . "List.yml", Config::YAML, array());
    }
}