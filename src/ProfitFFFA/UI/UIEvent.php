<?php
// 2019年3月28日 上午10:58:36
namespace ProfitFFFA\UI;

use ProfitFFFA\ProfitFFFA;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ProfitFFFA\Profit\addProfit;
use Instrument\SmallTools;
use ProfitFFFA\Configs\BlockList;
use pocketmine\item\Item;
use onebone\economyapi\EconomyAPI;
use ProfitFFFA\Profit\Profit;
use ProfitFFFA\Profit\PlayerD;

class UIEvent
{

    private $plugin, $ui, $ui2;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
        $this->ui = new makeUI($plugin);
        $this->ui2 = new makeUI2($plugin);
    }

    /**
     * make Exp Profit
     *
     * @param Player $player
     * @param String|int|array $data
     */
    public function ExpPro(Player $player, $data)
    {
        $plugin = $this->plugin;
        if (! isset($plugin->UIProListKey[$player->getName()])) {
            makeUI::makeTip($player, "数据解析错误！");
            return;
        }
        $Profit = new addProfit($plugin);
        $Profit->addExpProfit($player, $data[2], (int) $data[0], (int) $data[1], $this->getType($data[3]));
    }

    /**
     * UI红包设置点击的内容处理（领取|删除）
     *
     * @param Player $player
     * @param String|int|array $data
     */
    public function StartSettingProConfig(Player $player, $data)
    {
        $plugin = $this->plugin;
        if (! isset($plugin->UIProListKey[$player->getName()])) {
            makeUI::makeTip($player, "数据解析错误！");
            return;
        }
        $key = $plugin->UIProListKey[$player->getName()];
        $File = $plugin->getListConfig()->get($key);
        $ProConfig = $plugin->getProConfig($File);
        if ($data == "0" or $data === 0) {
            if (($ProConfig->get("Name") == $player->getName() or $player->isOp())) {
                $FilePath = $plugin->getDataFolder() . ProfitFFFA::$ProfitPath . $File;
                $plugin->getListConfig()->remove($key);
                $plugin->getListConfig()->save();
                if (is_file($FilePath)) {
                    @unlink($FilePath);
                }
                if (is_file($FilePath)) {
                    makeUI::makeTip($player, TextFormat::GREEN . "Key删除成功！\n" . TextFormat::RED . "文件删除失败！请截图并联系管理员！\n文件名： " . $File);
                } else {
                    makeUI::makeTip($player, TextFormat::GREEN . "您成功删除了这个红包！");
                }
                return;
            }
        } elseif ($data == 1 or $data == "1") {
            if ($player->isOp() or ($plugin->getConfigs()->get("允许玩家查看红包列表") and $plugin->getConfigs()->get("允许玩家在红包列表领取红包")) or $ProConfig->get("Name") == $player->getName()) {
                if (in_array($player->getName(), $ProConfig->get("玩家列表"))) {
                    $player->sendMessage(TextFormat::RED . "你已经领取过这个红包了！");
                    return;
                }
                $Profit = new Profit($plugin);
                $Profit->ReceiveProfit($player, $key);
            }
        }
    }

    /**
     * UI处理金币红包返回事件
     *
     * @param Player $player
     * @param String|int|array $data
     */
    public function makeMoneyPro(Player $player, $data)
    {
        $plugin = $this->plugin;
        $EconomyAPI = $plugin->getServer()
            ->getPluginManager()
            ->getPlugin("EconomyAPI");
        if ($EconomyAPI == NULL or ! $EconomyAPI->isEnabled()) {
            makeUI::makeTip($player, "当前服务器未安装或未启用" . TextFormat::WHITE . "EconomyAPI" . TextFormat::RED . "插件！无法使用金币红包功能");
            return;
        }
        if (! $player->isOp()) {
            if ($data[0] > EconomyAPI::getInstance()->myMoney($player)) {
                makeUI::makeTip($player, "年轻人就应该好好撸管！\n学人家发什么红包？\n你看你钱都没有！\n你还是去好好撸管!\n说不定能撸出" . TextFormat::WHITE . EconomyAPI::getInstance()->myMoney($player) . TextFormat::RED . "金币呢？");
                return;
            } else {
                EconomyAPI::getInstance()->reduceMoney($player, $data[0]);
            }
        }
        $add = new addProfit($plugin);
        $add->addMoneyProfit($player, $data[2], $data[0], (int) $data[1], $this->getType($data[3]));
    }

    /**
     * 处理UI返回的物品红包创建时间
     *
     * @param Player $player
     * @param String|int|array $data
     */
    public function makeItemPro(Player $player, $data)
    {
        $plugin = $this->plugin;
        $count = (int) $data[1];
        $ID = $data[0];
        if (SmallTools::isChinese($ID)) {
            $IDN = $ID;
            $ID = BlockList::getID($IDN);
            if ($IDN == $ID) {
                makeUI::makeTip($player, "当前暂不支持发送当前物品！请更换物品名称或使用物品ID发送物品红包");
                return;
            }
        }
        $IDx = explode(":", $ID);
        $ID = (int) $IDx[0];
        if (! isset($IDx[1])) {
            $IDM = "0";
        } else {
            $IDM = (int) $IDx[1];
        }
        if (! $player->isOp()) {
            if ($plugin->getConfigs()->get("限制创造模式发送红包") and $player->getGamemode() !== 0 and $player->getGamemode() !== 2) {
                makeUI::makeTip($player, "当前服务器已限制非生存(冒险)模式下发送物品红包！");
                return;
            }
            $bb = $player->getInventory();
            $cnt = 0;
            foreach ($bb->getContents() as $item) {
                if ($item->getID() . ":" . $item->getDamage() == $ID . ":" . $IDM) {
                    $cnt += $item->getCount();
                }
            }
            if ($cnt < $count) {
                makeUI::makeTip($player, "咳咳咳...\n年轻人就应该多撸管！\n学人家发什么红包?\n你瞧瞧你物品都不够！\n快去撸管吧\n说不定能撸出" . TextFormat::AQUA . ($count - $cnt) . TextFormat::RED . "个" . TextFormat::AQUA . BlockList::getName($ID . ":" . $IDM) . TextFormat::RED . "呢？");
                return;
            }
            $item = new Item($ID, $IDM);
            for ($i = 0; $i < $count; $i ++) {
                $bb->removeItem($item);
            }
        }
        $addPro = new addProfit($plugin);
        $addPro->addItemProfit($player, $data[3], $ID . ":" . $IDM, $count, (int) $data[2], $this->getType($data[4]));
    }

    /**
     * UI发送红包类型判断
     *
     * @param Player $player
     * @param String|int|array $data
     */
    public function makeProType(Player $player, $data)
    {
        $plugin = $this->plugin;
        $playerConfig = $plugin->getPlayerConfig($player)->getAll();
        $config = $plugin->getConfigs()->getAll();
        if ($config["玩家个人红包上限"] > 0 and $config["玩家个人红包上限"] <= count($playerConfig["未完成红包"])) {
            makeUI::makeTip($player, "当前您还有" . TextFormat::WHITE . count($playerConfig["未完成红包"]) . TextFormat::RED . "个红包未领取！已达本服个人红包上限！无法发送新的红包！请于领取完毕或删除红包后重试！");
            return;
        }
        if ($config["玩家红包上限"] != 0 and count($plugin->getListConfig()->getAll()) >= $config["玩家红包上限"]) {
            makeUI::makeTip($player, "当前服务器已有" . TextFormat::WHITE . count($plugin->getListConfig()->getAll()) . TextFormat::RED . "个红包！已达服务器红包上限！请于红包领取后或管理员清理红包后在发送新的红包！");
            return;
        }
        $make = new makeUI($plugin);
        switch ((int) $data) {
            case 0:
                $make->makeItemPro($player);
                break;
            case 1:
                $make->makeMoneyPr($player);
                break;
            case 2:
                $make = new makeUI2($plugin);
                $make->makeSendExp($player);
                break;
        }
    }

    /**
     * UI主页事件判断
     *
     * @param Player $player
     * @param String|int|array $data
     */
    public function makeMain(Player $player, $data)
    {
        $plugin = $this->plugin;
        if ($data == 0) {
            $this->ui->sendPro($player);
            return;
        }
        if ($data == 1) {
            if ($plugin->getConfigs()->get("允许玩家查看红包列表")) {
                $this->ui->makeProList($player);
            } else {
                makeUI::makeTip($player, "您无权限使用该功能！");
            }
            return;
        }
        if ($data == 2)
            $this->ui->makeXiaokai($player);
        if ($data == 3) {
            $playerD = new PlayerD($plugin);
            $playerD->makeUI($player);
            return;
        }
        if ($data == 4) {
            if ($player->isOp()) {
                $this->ui->makeSettingUI($player);
            } else {
                makeUI::makeTip($player, "您无权限使用该功能！");
            }
        }
    }

    public function getType($type): string
    {
        switch ((int) $type) {
            case 0:
                // 拼手气
                return "Luck";
            case 1:
                // 人均红包
                return "Mean";
            case 2:
                // 阶梯红包
                return "Ladder";
        }
        return "Luck";
    }
}