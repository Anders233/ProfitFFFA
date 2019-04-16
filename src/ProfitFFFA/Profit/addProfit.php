<?php
// 2019年3月29日 下午10:27:55
namespace ProfitFFFA\Profit;

use ProfitFFFA\ProfitFFFA;
use pocketmine\Player;
use Instrument\SmallTools;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use ProfitFFFA\PlayerEvent\onPlayer;
use pocketmine\command\ConsoleCommandSender;

class addProfit
{

    private $plugin;

    private $onPlayer;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
        $this->onPlayer = new onPlayer($plugin);
    }

    public function addExpProfit($player, $key, $expLevel, $ProfitCount, $Type)
    {
        if ($key == NULL or $key == "" or strlen($key) < 1) {
            $player->sendMessage(TextFormat::RED . "您还咩有输入红包Key或Key不适用！");
            return;
        }
        if ($expLevel == NULL or $expLevel == "" or strlen($expLevel) < 1) {
            $player->sendMessage(TextFormat::RED . "您还咩有输入经验等级或经验等级不适用！");
            return;
        }
        if (! is_numeric($expLevel)) {
            $player->sendMessage(TextFormat::RED . "经验等级仅支持纯数字！");
            return;
        }
        if ($expLevel < 1) {
            $player->sendMessage(TextFormat::RED . "经验等级必须大于或等于1");
            return;
        }
        if ($ProfitCount == NULL or strlen($ProfitCount) < 1 or $ProfitCount == "") {
            $player->sendMessage(TextFormat::RED . "您还咩有输入红包个数或红包个数不适用！");
            return;
        }
        if (! is_numeric($ProfitCount)) {
            $player->sendMessage(TextFormat::RED . "红包个数仅支持纯数字！");
            return;
        }
        if ($ProfitCount < 1) {
            $player->sendMessage(TextFormat::RED . "红包个数必须大于或等于1");
            return;
        }
        if ($Type == NULL or strlen($Type) < 1 or $Type == "") {
            $player->sendMessage(TextFormat::RED . "无法获取红包领取类型！");
            return;
        }
        $plugin = $this->plugin;
        $ProList = $plugin->getListConfig();
        if ($ProList->get($key, NULL) !== NULL) {
            $player->sendMessage(TextFormat::RED . "红包Key已存在！");
            return;
        }

        if (((int) ($expLevel / $ProfitCount)) < 1) {
            $player->sendMessage(TextFormat::RED . "Exp红包限制每个红包至少能分得1级！请更改您的红包数量或经验等级(" . ($expLevel / $ProfitCount) . "/" . ((int) ($expLevel / $ProfitCount)) . "!");
            return;
        }
        $config = $plugin->getConfigs();
        if (! $player->isOp() and ! $player instanceof ConsoleCommandSender) {
            if (! $config->get("经验红包")) {
                $player->sendMessage(TextFormat::RED . "当前服务器已关闭经验红包功能！如果您真的想使用该功能，请联系管理员开启！");
                return;
            }
            if (mb_strlen($key) > $config->get("红包Key最长数") and $config->get("红包Key最长数") > 0) {
                $player->sendMessage(TextFormat::RED . "您当前设置的红包Key长度超出本服限制长度(" . $config->get("红包Key最长数") . ")，请适当调整后重新发送红包！");
                return;
            }
            if (SmallTools::isChinese($key) and ! $config->get("允许特殊Key")) {
                $player->sendMessage(TextFormat::RED . "当前服务器已禁止特殊红包ID的存在！请使用A-Z(a-z|0-9|./)等字符！");
                return;
            }
            $ProListConfig = $plugin->getListConfig();
            if ($config->get("玩家红包上限") != 0 and count($ProListConfig->getAll()) >= $config->get("玩家红包上限")) {
                $player->sendMessage(TextFormat::RED . "当前本服未领取红包数量已达限制数量上限(" . TextFormat::WHITE . count($ProListConfig->getAll()) . TextFormat::YELLOW . "/" . TextFormat::WHITE . $config->get("玩家红包上限") . TextFormat::RED . ")！请在红包数量减少后重试！");
                return;
            }
            $PlayerConfig = $plugin->getPlayerConfig($player);
            if ($config->get("玩家个人红包上限") != 0 and $config->get("玩家个人红包上限") <= count($PlayerConfig->get("未完成红包"))) {
                $player->sendMessage(TextFormat::RED . "您当前未被领取完毕的红包数量已达本服上限(" . TextFormat::WHITE . count($PlayerConfig->get("未完成红包")) . TextFormat::YELLOW . "/" . TextFormat::WHITE . $config->get("玩家个人红包上限") . TextFormat::RED . ")！请删除或等您的红包领取完毕后重试！");
                return;
            }
            if ($expLevel > $player->getXpLevel()) {
                $player->sendMessage(TextFormat::RED . "您的经验等级不足以完成该红包！(还需" . ($expLevel - $player->getXpLevel()) . "级！)");
                return;
            }
            $player->setXpLevel($player->getXpLevel() - $expLevel);
            $NullPro = $PlayerConfig->get("未完成红包");
            if (! in_array($key, $NullPro)) {
                $NullPro[] = $key;
                $PlayerConfig->set("未完成红包", $NullPro);
                $PlayerConfig->save();
            }
            $PlayerConfig->set("人品", $PlayerConfig->get("人品") + mt_rand(0, $expLevel / $ProfitCount));
            $PlayerConfig->set("发红包总数", $PlayerConfig->get("发红包总数") + 1);
            $PlayerConfig->set("发送金币总数", $PlayerConfig->get("发送经验总数") + $expLevel);
            $PlayerConfig->save();
            $this->onPlayer->PlayerAddProfit($player, $key);
        }
        $this->newProConfig($key, array(
            "Time" => date("Y-m-d H:i:s"),
            "Name" => $player->getName(),
            "Type" => "Exp",
            "Style" => $Type,
            "金额" => $expLevel,
            "Key" => $key,
            "红包数量" => $ProfitCount,
            "物品ID" => NULL,
            "红包剩余金额" => $expLevel,
            "红包剩余个数" => $ProfitCount,
            "玩家列表" => array()
        ));
        $player->sendMessage(TextFormat::GREEN . "红包发布成功！");
    }

    /**
     *
     * @param Player $player
     *            玩家对象
     * @param String $key
     *            红包Key
     * @param int|String $Money
     *            红包金额
     * @param int|String $ProfitCount
     *            红包个数
     * @param String $Type
     *            红包类型
     */
    public function addMoneyProfit($player, $key, $Money, $ProfitCount, $Type)
    {
        if ($key == NULL or $key == "" or strlen($key) < 1) {
            $player->sendMessage(TextFormat::RED . "您还咩有输入红包Key或Key不适用！");
            return;
        }
        if ($Money == NULL or $Money == "" or strlen($Money) < 1) {
            $player->sendMessage(TextFormat::RED . "您还咩有输入红包金额量或红包金额不适用！");
            return;
        }
        if (! is_numeric($Money)) {
            $player->sendMessage(TextFormat::RED . "红包金额仅支持纯数字！");
            return;
        }
        if ($Money < 1) {
            $player->sendMessage(TextFormat::RED . "红包金额必须大于或等于1");
            return;
        }
        if ($ProfitCount == NULL or strlen($ProfitCount) < 1 or $ProfitCount == "") {
            $player->sendMessage(TextFormat::RED . "您还咩有输入红包个数或红包个数不适用！");
            return;
        }
        if (! is_numeric($ProfitCount)) {
            $player->sendMessage(TextFormat::RED . "红包个数仅支持纯数字！");
            return;
        }
        if ($ProfitCount < 1) {
            $player->sendMessage(TextFormat::RED . "红包个数必须大于或等于1");
            return;
        }
        if ($Type == NULL or strlen($Type) < 1 or $Type == "") {
            $player->sendMessage(TextFormat::RED . "无法获取红包领取类型！");
            return;
        }
        $plugin = $this->plugin;
        $ProList = $plugin->getListConfig();
        if ($ProList->get($key, NULL) !== NULL) {
            $player->sendMessage(TextFormat::RED . "红包Key已存在！");
            return;
        }
        $config = $plugin->getConfigs();
        if (! $player->isOp() and ! $player instanceof ConsoleCommandSender) {
            if (mb_strlen($key) > $config->get("红包Key最长数") and $config->get("红包Key最长数") > 0) {
                $player->sendMessage(TextFormat::RED . "您当前设置的红包Key长度超出本服限制长度(" . $config->get("红包Key最长数") . ")，请适当调整后重新发送红包！");
                return;
            }
            if (SmallTools::isChinese($key) and ! $config->get("允许特殊Key")) {
                $player->sendMessage(TextFormat::RED . "当前服务器已禁止特殊红包ID的存在！请使用A-Z(a-z|0-9|./)等字符！");
                return;
            }
            $ProListConfig = $plugin->getListConfig();
            if ($config->get("玩家红包上限") != 0 and count($ProListConfig->getAll()) >= $config->get("玩家红包上限")) {
                $player->sendMessage(TextFormat::RED . "当前本服未领取红包数量已达限制数量上限(" . TextFormat::WHITE . count($ProListConfig->getAll()) . TextFormat::YELLOW . "/" . TextFormat::WHITE . $config->get("玩家红包上限") . TextFormat::RED . ")！请在红包数量减少后重试！");
                return;
            }
            $PlayerConfig = $plugin->getPlayerConfig($player);
            if ($config->get("玩家个人红包上限") != 0 and $config->get("玩家个人红包上限") <= count($PlayerConfig->get("未完成红包"))) {
                $player->sendMessage(TextFormat::RED . "您当前未被领取完毕的红包数量已达本服上限(" . TextFormat::WHITE . count($PlayerConfig->get("未完成红包")) . TextFormat::YELLOW . "/" . TextFormat::WHITE . $config->get("玩家个人红包上限") . TextFormat::RED . ")！请删除或等您的红包领取完毕后重试！");
                return;
            }
            $NullPro = $PlayerConfig->get("未完成红包");
            if (! in_array($key, $NullPro)) {
                $NullPro[] = $key;
                $PlayerConfig->set("未完成红包", $NullPro);
                $PlayerConfig->save();
            }
            $PlayerConfig->set("人品", $PlayerConfig->get("人品") + mt_rand(0, $Money / $ProfitCount));
            $PlayerConfig->set("发红包总数", $PlayerConfig->get("发红包总数") + 1);
            $PlayerConfig->set("发送金币总数", $PlayerConfig->get("发送金币总数") + $Money);
            $PlayerConfig->save();
            $this->onPlayer->PlayerAddProfit($player, $key);
        }
        $this->newProConfig($key, array(
            "Time" => date("Y-m-d H:i:s"),
            "Name" => $player->getName(),
            "Type" => "Money",
            "Style" => $Type,
            "金额" => $Money,
            "Key" => $key,
            "红包数量" => $ProfitCount,
            "物品ID" => NULL,
            "红包剩余金额" => $Money,
            "红包剩余个数" => $ProfitCount,
            "玩家列表" => array()
        ));
        $player->sendMessage(TextFormat::GREEN . "红包发布成功！");
    }

    /**
     * 添加一个物品红包
     *
     * @param Player $player
     *            玩家对象
     * @param String $key
     *            红包Key
     * @param String $id
     *            物品ID
     * @param int|String $count
     *            物品数量
     * @param int|String $ProfitCount
     *            红包数量
     * @param String $Type
     *            红包类型
     */
    public function addItemProfit($player, $key, $id, $count, $ProfitCount, $Type)
    {
        if ($key == NULL or $key == "" or strlen($key) < 1) {
            $player->sendMessage(TextFormat::RED . "您还咩有输入红包Key或Key不适用！");
            return;
        }
        if ($count == NULL or $count == "" or strlen($count) < 1) {
            $player->sendMessage(TextFormat::RED . "您还咩有输入物品数量或物品数量不适用！");
            return;
        }
        if (! is_numeric($count)) {
            $player->sendMessage(TextFormat::RED . "物品数量仅支持纯数字！");
            return;
        }
        if ($count < 1) {
            $player->sendMessage(TextFormat::RED . "物品个数必须大于或等于1");
            return;
        }
        if ($id == NULL or strlen($id) < 1 or $id == "") {
            $player->sendMessage(TextFormat::RED . "您还咩有输入物品ID或您输入的物品ID不适用！");
            return;
        }
        if ($ProfitCount == NULL or strlen($ProfitCount) < 1 or $ProfitCount == "") {
            $player->sendMessage(TextFormat::RED . "您还咩有输入红包个数或红包个数不适用！");
            return;
        }
        if (! is_numeric($ProfitCount)) {
            $player->sendMessage(TextFormat::RED . "红包个数仅支持纯数字！");
            return;
        }
        if ($ProfitCount < 1) {
            $player->sendMessage(TextFormat::RED . "红包个数必须大于或等于1");
            return;
        }
        if ($Type == NULL or strlen($Type) < 1 or $Type == "") {
            $player->sendMessage(TextFormat::RED . "无法获取红包领取类型！");
            return;
        }
        $plugin = $this->plugin;
        $ProList = $plugin->getListConfig();
        if ($ProList->get($key, NULL) !== NULL) {
            $player->sendMessage(TextFormat::RED . "红包Key已存在！");
            return;
        }
        $config = $plugin->getConfigs();
        if (! $player->isOp() and ! $player instanceof ConsoleCommandSender) {
            if (mb_strlen($key) > $config->get("红包Key最长数") and $config->get("红包Key最长数") > 0) {
                $player->sendMessage(TextFormat::RED . "您当前设置的红包Key长度超出本服限制长度(" . $config->get("红包Key最长数") . ")，请适当调整后重新发送红包！");
                return;
            }
            if (SmallTools::isChinese($key) and ! $config->get("允许特殊Key")) {
                $player->sendMessage(TextFormat::RED . "当前服务器已禁止特殊红包ID的存在！请使用A-Z(a-z|0-9|./)等字符！");
                return;
            }
            $ProListConfig = $plugin->getListConfig();
            if ($config->get("玩家红包上限") != 0 and count($ProListConfig->getAll()) >= $config->get("玩家红包上限")) {
                $player->sendMessage(TextFormat::RED . "当前本服未领取红包数量已达限制数量上限(" . TextFormat::WHITE . count($ProListConfig->getAll()) . TextFormat::YELLOW . "/" . TextFormat::WHITE . $config->get("玩家红包上限") . TextFormat::RED . ")！请在红包数量减少后重试！");
                return;
            }
            $PlayerConfig = $plugin->getPlayerConfig($player);
            if ($config->get("玩家个人红包上限") != 0 and $config->get("玩家个人红包上限") <= count($PlayerConfig->get("未完成红包"))) {
                $player->sendMessage(TextFormat::RED . "您当前未被领取完毕的红包数量已达本服上限(" . TextFormat::WHITE . count($PlayerConfig->get("未完成红包")) . TextFormat::YELLOW . "/" . TextFormat::WHITE . $config->get("玩家个人红包上限") . TextFormat::RED . ")！请删除或等您的红包领取完毕后重试！");
                return;
            }
            $NullPro = $PlayerConfig->get("未完成红包");
            if (! in_array($key, $NullPro)) {
                $NullPro[] = $key;
                $PlayerConfig->set("未完成红包", $NullPro);
                $PlayerConfig->save();
            }
            $PlayerConfig->set("人品", $PlayerConfig->get("人品") + mt_rand(0, $count));
            $PlayerConfig->set("发红包总数", $PlayerConfig->get("发红包总数") + 1);
            $PlayerConfig->set("发送物品总数", $PlayerConfig->get("发送物品总数") + $count);
            $PlayerConfig->save();
            $this->onPlayer->PlayerAddProfit($player, $key);
        }
        $this->newProConfig($key, array(
            "Time" => date("Y-m-d H:i:s"),
            "Name" => $player->getName(),
            "Type" => "Item",
            "Style" => $Type,
            "金额" => $count,
            "红包数量" => $ProfitCount,
            "物品ID" => $id,
            "红包剩余金额" => $count,
            "Key" => $key,
            "红包剩余个数" => $ProfitCount,
            "玩家列表" => array()
        ));
        $player->sendMessage(TextFormat::GREEN . "红包发布成功！");
    }

    /**
     * 获取一个随机红包配置文件名
     *
     * @return String
     */
    public static function KeyToFileName(): String
    {
        $FileName = SmallTools::getRandText(mt_rand(5, 30), "abcdefghijklmnopqrstuvwxyz0123456789-") . ".Winf";
        if (is_file(ProfitFFFA::getInstance()->getDataFolder() . ProfitFFFA::$ProfitPath . $FileName)) {
            return addProfit::KeyToFileName();
        }
        return $FileName;
    }

    public function newProConfig(String $key, array $data): Config
    {
        if (substr($key, 0, 1) !== "#") {
            $key = "#" . $key;
        }
        if (substr($data["Key"], 0, 1) !== "#") {
            $data["Key"] = "#" . $data["Key"];
        }
        $FileName = addProfit::KeyToFileName();
        $getListConfig = $this->plugin->getListConfig();
        $getListConfig->set($key, $FileName);
        $getListConfig->save();
        switch (strtolower($data["Type"])) {
            case "exp":
                if ($data["金额"] / $data["红包数量"] > 10) {
                    $this->plugin->getServer()->broadcastMessage(TextFormat::YELLOW . "卧槽！" . TextFormat::GOLD . $data["Name"] . TextFormat::YELLOW . "发了一个屌的一批的红包！快速入" . TextFormat::WHITE . $key . TextFormat::YELLOW . "领取吧~");
                } else if ($data["金额"] / $data["红包数量"] < 3) {
                    $this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . $data["Name"] . TextFormat::WHITE . "挤了挤自己的肾，发送了一个穷的一笔红包，发送" . $key . TextFormat::GREEN . "领取！此种红包不领也罢，平白浪费时间！");
                } else {
                    $this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . $data["Name"] . TextFormat::WHITE . "发送了一个红包，发送" . $key . TextFormat::GREEN . "领取！");
                }
                break;
            case "money":
                if ($data["金额"] / $data["红包数量"] > 10000) {
                    $this->plugin->getServer()->broadcastMessage(TextFormat::YELLOW . "卧槽！" . TextFormat::GOLD . $data["Name"] . TextFormat::YELLOW . "发了一个屌的一批的红包！快速入" . TextFormat::WHITE . $key . TextFormat::YELLOW . "领取吧~");
                } else if ($data["金额"] / $data["红包数量"] < 100) {
                    $this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . $data["Name"] . TextFormat::WHITE . "穷的一笔，掏了掏瘪的一批的口袋，发送了一个穷的一笔红包，发送" . $key . TextFormat::GREEN . "领取！此种红包不领也罢，平白浪费时间！");
                } else {
                    $this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . $data["Name"] . TextFormat::WHITE . "发送了一个红包，发送" . $key . TextFormat::GREEN . "领取！");
                }
                break;
            case "item":
                if ($data["金额"] / $data["红包数量"] > 100) {
                    $this->plugin->getServer()->broadcastMessage(TextFormat::YELLOW . "卧槽！" . TextFormat::GOLD . $data["Name"] . TextFormat::YELLOW . "发了一个屌的一批的红包！快速入" . TextFormat::WHITE . $key . TextFormat::YELLOW . "领取吧~");
                } else if ($data["金额"] / $data["红包数量"] < 10) {
                    $this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . $data["Name"] . TextFormat::WHITE . "穷的一批，发送了一个穷的一批红包，发送" . $key . TextFormat::GREEN . "领取！此种红包不领也罢！");
                } else {
                    $this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . $data["Name"] . TextFormat::WHITE . "发送了一个红包，发送" . $key . TextFormat::GREEN . "领取！");
                }
                break;
        }
        return new Config($this->plugin->getDataFolder() . ProfitFFFA::$ProfitPath . $FileName, Config::YAML, $data);
    }
}