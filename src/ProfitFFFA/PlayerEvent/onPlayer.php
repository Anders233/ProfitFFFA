<?php
namespace ProfitFFFA\PlayerEvent;

use ProfitFFFA\ProfitFFFA;
use pocketmine\Player;

// 2019年4月2日 上午12:04:37
class onPlayer
{

    private $plugin;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    public function addMsg(Player $player, String $msg)
    {
        if ($msg == NULL or $player == NULL) {
            return FALSE;
        }
        $playerConfig = $this->plugin->getPlayerConfig($player);
        $playerArray = $playerConfig->get("未读消息");
        $playerArray[] = $msg;
        $playerConfig->set("未读消息", $playerArray);
        $playerConfig->save();
        return TRUE;
    }

    public function PlayerAddProfit(Player $player, $key)
    {
        $playerConfig = $this->plugin->getPlayerConfig($player);
        $playerArray = $playerConfig->get("未完成红包");
        $playerArray[$key] = $this->plugin->getListConfig()->get($key);
        $playerConfig->set("未完成红包", $playerArray);
        $playerConfig->save();
    }
}