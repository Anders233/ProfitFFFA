<?php
namespace ProfitFFFA\Configs;

use ProfitFFFA\ProfitFFFA;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use Instrument\SmallTools;
use ProfitFFFA\PlayerEvent\onPlayer;

// 2019年4月1日 下午7:23:55
class Calibration
{

    private $plugin;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    public function start()
    {
        $plugin = $this->plugin;
        $onP = new onPlayer($plugin);
        $ListConfig = $plugin->getListConfig();
        $ListKey = $ListConfig->getAll();
        $plugin->getLogger()->info(TextFormat::GREEN . "正在执行文件校准！这可能需要一点时间.....");
        $count = 0;
        $NoOK = 0;
        foreach ($ListKey as $key => $File) {
            $count ++;
            $FilePath = $plugin->getDataFolder() . ProfitFFFA::$ProfitPath . $File;
            if (is_file($FilePath)) {
                $ProConfig = $plugin->getProConfig($File);
                if ($ProConfig->get("Key") != $key) {
                    $ProConfig->set("Key", $key);
                    $ProConfig->save();
                    $NoOK ++;
                    $plugin->getLogger()->info(TextFormat::RED . "数据值不匹配！已强制纠正！Key=" . $key . " File=" . $File);
                }
                if (! is_array($ProConfig->get("玩家列表"))) {
                    $array = array();
                    for ($i = 0; $i < ($ProConfig->get("红包数量") - $ProConfig->get("红包剩余个数")); $i ++) {
                        $array[] = NULL;
                    }
                    $ProConfig->set("玩家列表", $array);
                    $ProConfig->save();
                    $NoOK ++;
                }
            } else {
                $ListConfig->remove($key);
                $ListConfig->save();
                $plugin->getLogger()->info(TextFormat::RED . "Key无数据！已删除！(" . $key . ")");
                $NoOK ++;
            }
        }
        $DirPath = $plugin->getDataFolder() . ProfitFFFA::$ProfitPath;
        $Files = scandir($DirPath);
        foreach ($Files as $File) {
            $count ++;
            $FilePath = $DirPath . $File;
            if (is_file($FilePath) and $File != "." and $File != "..") {
                $ProConfig = $plugin->getProConfig($File);
                if (! is_array($ProConfig->get("玩家列表"))) {
                    $array = array();
                    for ($i = 0; $i < ($ProConfig->get("红包数量") - $ProConfig->get("红包剩余个数")); $i ++) {
                        $array[] = NULL;
                    }
                    $ProConfig->set("玩家列表", $array);
                    $ProConfig->save();
                    $NoOK ++;
                }
                $key = $ProConfig->get("Key");
                if ($plugin->getListConfig()->get($key, NULL) == NULL) {
                    @unlink($FilePath);
                    $plugin->getLogger()->info(TextFormat::RED . "数据不存在！已删除数据文件！(Key=" . $key . "  File=" . $File . ")");
                    $NoOK ++;
                }
                if ($File != $plugin->getListConfig()->get($key)) {
                    @unlink($FilePath);
                    $plugin->getLogger()->info(TextFormat::RED . "数据不匹配你！已删除数据文件！(Key=" . $key . "  File=" . $File . ")");
                    $NoOK ++;
                }
                if (SmallTools::ComputationTime(strtotime(date("Y-m-d H:i:s")) - strtotime($ProConfig->get("Time")))["days"] > $plugin->getConfigs()->get("清理间隔")) {
                    $player = $plugin->getServer()->getPlayer($ProConfig->get("Name"));
                    if ($player == NULL or ! $player instanceof Player or ! $player->isOp()) {
                        $Pro = $ProConfig->getAll();
                        $onP->addMsg($player, "您Key为：" . $Pro["Key"] . "的红包已过期删除！");
                        $PlayerConfig = $plugin->getPlayerConfig($ProConfig->get("Name"));
                        $PlayerArray = $PlayerConfig->get("未完成红包");
                        unset($PlayerArray[$key]);
                        $PlayerConfig->set("未完成红包", $PlayerArray);
                        $PlayerConfig->save();
                        $plugin->getServer()
                            ->getLogger()
                            ->info(TextFormat::RED . $ProConfig->get("Name") . "的红包“" . $key . "”已过期删除！");
                    }
                }
            }
        }
        $plugin->getLogger()->info(TextFormat::DARK_GREEN . "数据检查完毕！共检查位置" . TextFormat::WHITE . $count . TextFormat::DARK_GREEN . "个，修复问题" . TextFormat::RED . $NoOK . TextFormat::DARK_GREEN . "个！");
    }
}