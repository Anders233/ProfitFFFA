<?php
namespace ProfitFFFA\PlayerEvent;

use pocketmine\event\Listener;
use ProfitFFFA\ProfitFFFA;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\TextFormat;
use ProfitFFFA\Configs\BlockList;
use pocketmine\event\player\PlayerInteractEvent;
use ProfitFFFA\UI\makeUI;
use pocketmine\event\player\PlayerChatEvent;
use ProfitFFFA\Profit\Profit;

class Event implements Listener
{

    private $plugin;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onMsg(PlayerChatEvent $e)
    {
        $plugin = $this->plugin;
        $player = $e->getPlayer();
        $key = $e->getMessage();
        if (substr($key, 0, 1) == "#") {
            $config = $plugin->getConfigs()->get("领取红包");
            if ($plugin->getListConfig()->get($key, NULL) !== NULL and $config["领取红包"]) {
                $profit = new Profit($plugin);
                $profit->ReceiveProfit($player, $key);
                if ($config["撤回事件检测"]) {
                    $e->setCancelled();
                }
            }
        }
    }

    public function onMake(PlayerInteractEvent $e)
    {
        $plugin = $this->plugin;
        $player = $e->getPlayer();
        if ($plugin->getConfigs()->get("点击打开GUI")["点击打开GUI"]) {
            $config = $plugin->getConfigs()->get("点击打开GUI");
            $block = $e->getBlock();
            $item = $e->getItem();
            if (EventTool::isBlockIDToOK($block->getId() . ":" . $block->getDamage(), $config["被点击物品ID"])) {
                if (EventTool::isBlockIDToOK($item->getId() . ":" . $item->getDamage(), $config["手持物品ID"])) {
                    $make = new makeUI($plugin);
                    $make->makeMain($player);
                    if ($config["撤回事件检测"]) {
                        $e->setCancelled();
                    }
                }
            }
        }
    }

    public function onJoin(PlayerJoinEvent $e)
    {
        $plugin = $this->plugin;
        $player = $e->getPlayer();
        $config = $plugin->getPlayerConfig($player);
        if ($config->get("初次加入") and $plugin->getConfigs()->get("初次进入提示")) {
            $msg = TextFormat::YELLOW . "您好！欢迎您来到" . TextFormat::BLUE . $plugin->getServer()->getMotd() . TextFormat::YELLOW . "！本服支持红包抢夺！赶紧键入命令“/红包”";
            if ($plugin->getConfig()->get("点击打开GUI")["点击打开GUI"]) {
                $msg .= "或尝试用”" . BlockList::getName($plugin->getConfigs()->get("点击打开GUI")["手持物品ID"]) . "”点击“" . BlockList::getName($plugin->getConfigs()->get("点击打开GUI")["被点击物品ID"]);
            }
            $player->sendMessage($msg . "”试试吧~");
            $config->set("初次加入", false);
        }
        if (count($config->get("未读消息")) > 0) {
            $array = $config->get("未读消息");
            foreach ($array as $as) {
                $player->sendMessage($as);
            }
            $config->set("未读消息", array());
        }
        $config->set("Name", $player->getName());
        $config->save();
    }
}