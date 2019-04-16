<?php
namespace ProfitFFFA\PlayerEvent;

use ProfitFFFA\ProfitFFFA;
use pocketmine\Player;
use Instrument\SmallTools;
use ProfitFFFA\Configs\BlockList;
use ProfitFFFA\UI\makeUI;
use pocketmine\utils\TextFormat;

// 2019年4月2日 下午9:21:32
class Setting
{

    private $plugin;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    public function start(Player $player, $data)
    {
        if (! $player->isOp()) {
            makeUI::makeTip($player, "你没有权限使用这个功能");
            return;
        }
        $plugin = $this->plugin;
        $configs = $plugin->getConfigs();
        $config = $configs->getAll();
        $config["红包Key最长数"] = (int) $data[0];
        $config["定时清理红包"] = (bool) $data[1];
        $config["允许特殊Key"] = (bool) $data[2];
        $config["清理间隔"] = (int) $data[3];
        $config["玩家红包上限"] = (int) $data[4];
        $config["玩家个人红包上限"] = (int) $data[5];
        $config["默认Key长度"] = (int) $data[6];
        $config["允许玩家查看红包列表"] = (bool) $data[7];
        $config["允许玩家在红包列表领取红包"] = (bool) $data[8];
        $config["更新检查"] = (bool) $data[9];
        $config["配置校准"] = (bool) $data[10];
        $config["初次进入提示"] = (bool) $data[11];
        $config["限制创造模式发送红包"] = (bool) $data[12];
        $config["默认红包数"] = (int) $data[13];
        $config["世界黑名单"]["世界黑名单"] = (bool) $data[14];
        $config["世界黑名单"]["worlds"] = $this->StringToArray($data[15]);
        $config["领取红包"]["领取红包"] = (bool) $data[16];
        $config["领取红包"]["撤回事件检测"] = (bool) $data[17];
        $config["点击打开GUI"]["点击打开GUI"] = (bool) $data[18];
        $config["点击打开GUI"]["手持物品ID"] = $this->getID($data[19]);
        $config["点击打开GUI"]["撤回事件检测"] = (bool) $data[20];
        $config["点击打开GUI"]["被点击物品ID"] = $this->getID($data[21]);
        $config["默认红包金额"] = (int) $data[22];
        $config["经验红包"] = (bool) $data[23];
        $configs->setAll($config);
        $configs->save();
        makeUI::makeTip($player, TextFormat::GREEN . "保存完成！");
    }

    /**
     * 世界黑名单
     *
     * @param String $string
     * @return array
     */
    public function StringToArray(String $string): array
    {
        if (SmallTools::isText($string, ";")) {
            $array = explode(";", $string);
            for ($i = 0; $i <= count($array); $i ++) {
                if ($array[$i] == NULL or $array[$i] == "")
                    unset($array[$i]);
            }
            return $array;
        } else {
            return array(
                $string
            );
        }
    }

    /**
     * 获取返回的ID
     *
     * @param String $string
     * @return string
     */
    public function getID(String $string): string
    {
        if (SmallTools::isChinese($string)) {
            return BlockList::getID($string);
        } else {
            return $string;
        }
    }
}