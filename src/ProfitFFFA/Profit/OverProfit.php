<?php
namespace ProfitFFFA\Profit;

use pocketmine\utils\Config;
use ProfitFFFA\ProfitFFFA;
use Instrument\SmallTools;
use pocketmine\utils\TextFormat;
use ProfitFFFA\Configs\BlockList;

// 2019年3月31日 下午1:08:05
class OverProfit
{

    private $Profit;

    public function __construct(Profit $Profit)
    {
        $this->Profit = $Profit;
    }

    /**
     * Pro金币红包领取完毕事件处理
     *
     * @param Config $ProfitConfig
     *            Pro配置文件
     */
    public function ProfitOver(Config $ProfitConfig)
    {
        $plugin = $this->Profit->plugin;
        $Pro = $ProfitConfig->getAll();
        $enddate = strtotime(date("Y-m-d H:i:s"));
        $startdate = strtotime($Pro["Time"]);
        $Names = arsort($Pro["玩家列表"]);
        $ms = "!";
        foreach ($Names as $tName => $Money) {
            switch (strtolower($Pro["Type"])) {
                case "money":
                    $ms = "(" . $Money . ")!";
                    break;
                case "item":
                    $ms = "(" . $Money . "个" . BlockList::getName($Pro["物品ID"]) . ")!";
                    break;
                default:
                    $ms = "(" . $Money . ")!";
                    break;
            }
            break;
        }
        $plugin->getServer()->broadcastMessage(TextFormat::GREEN . "在经历" . TextFormat::RED . SmallTools::ComputationTimeToAutoString($enddate - $startdate) . TextFormat::DARK_GREEN . "后，" . TextFormat::WHITE . $Pro["Name"] . TextFormat::GREEN . "的红包被领取完毕！运气王是: " . $tName . $ms);
        $Config = $plugin->getListConfig();
        $File = $Config->get($Pro["Key"]);
        @unlink($plugin->getDataFolder() . ProfitFFFA::$ProfitPath . $File);
        $Config->remove($Pro["Key"]);
        $Config->save();
        unset($Config, $plugin, $File, $Pro, $ms, $Money, $Names, $tName);
    }
}