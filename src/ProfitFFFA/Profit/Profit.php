<?php
// 2019年3月30日 下午7:09:30
namespace ProfitFFFA\Profit;

use ProfitFFFA\ProfitFFFA;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use onebone\economyapi\EconomyAPI;
use ProfitFFFA\Configs\BlockList;
use pocketmine\item\Item;

class Profit
{

    public $plugin;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    public function ReceiveExpProfit(Player $player, Config $ProConfig)
    {
        $plugin = $this->plugin;
        $Pro = $ProConfig->getAll();
        if (in_array($player->getName(), $Pro["玩家列表"])) {
            $player->sendMessage(TextFormat::RED . "你已经领取过这个红包了！");
            return;
        }
        switch (strtolower($Pro["Style"])) {
            case "ladder":
                $Money = $Pro["红包剩余金额"] / 2;
                if ($Money > $Pro["红包剩余金额"]) {
                    $Money = $Pro["红包剩余金额"];
                }
                break;
            case "mean":
                if ($Pro["红包数量"] == 1 or $Pro["红包剩余个数"] == 1) {
                    $Money = $Pro["红包剩余金额"];
                    break;
                }
                $Money = $Pro["金额"] / $Pro["红包数量"];
                if ($Money > $Pro["红包剩余金额"]) {
                    $Money = $Pro["红包剩余金额"];
                }
                break;
            case "luck":
                if ($Pro["红包数量"] == 1 or $Pro["红包剩余个数"] == 1) {
                    $Money = $Pro["红包剩余金额"];
                    break;
                }
                $Money = mt_rand(mt_rand(0, $Pro["红包剩余金额"] / $Pro["红包剩余个数"] - 1), mt_rand($Pro["红包剩余金额"] / $Pro["红包剩余个数"], ($Pro["红包剩余金额"] / $Pro["红包剩余个数"]) * 2));
                if ($Money > $Pro["红包剩余金额"] or $Pro["红包剩余个数"] == 1) {
                    $Money = $Pro["红包剩余金额"];
                }
                break;
            default:
                $player->sendMessage(TextFormat::RED . "您无法领取一个未知类型的红包！请联系管理员！error: Profit Style to" . $ProConfig->get("Type"));
                $plugin->getLogger()->warning(TextFormat::WHITE . $player->getName() . TextFormat::RED . "正在领取一个未知类型的红包！但该类型不存在！请检查系统数据！");
                return;
        }
        if ($Money == 0) {
            $plugin->getServer()->broadcastMessage(TextFormat::GOLD . "[" . TextFormat::YELLOW . $plugin->getName() . TextFormat::GOLD . "]" . TextFormat::WHITE . $player->getName() . TextFormat::GREEN . "丑的一笔！领取一个剩余经验等级为" . TextFormat::BLUE . $Pro["红包剩余金额"] . TextFormat::GREEN . "的红包时居然毛都木领到！大家请欢迎这位黑B！");
        } else {
            $player->sendMessage(TextFormat::YELLOW . "您成功领取了" . TextFormat::GREEN . $Pro["Name"] . TextFormat::YELLOW . "的红包！本次获得" . TextFormat::BLUE . $Money . TextFormat::YELLOW . "经验");
        }
        if ($player instanceof Player) {
            $playerConfig = $plugin->getPlayerConfig($player);
            $playerConfig->set("领取物品红包数", $playerConfig->get("领取红包总数") + 1);
            $playerConfig->set("领取红包总数", $playerConfig->get("接受经验红包数") + 1);
            $playerConfig->set("领取物品总数", $playerConfig->get("接受经验总数") + $Money);
            $playerConfig->set("人品", $playerConfig->get("人品") + mt_rand(0, $Money));
            $playerConfig->save();
        }
        $Pro["红包剩余金额"] = $Pro["红包剩余金额"] - $Money;
        $Pro["红包剩余个数"] --;
        $Pro["玩家列表"][] = $player->getName();
        $ProConfig->setAll($Pro);
        $ProConfig->save();
        $player->addXpLevels($Money);
        $voer = new OverProfit($this);
        if ($this->isProfitEnd($ProConfig->get("Key"))) {
            $voer->ProfitOver($ProConfig);
        }
    }

    /**
     * 接收物品红包
     *
     * @param Player $player
     * @param Config $ProConfig
     *            红包配置文件
     */
    private function ReceiveItemProfit(Player $player, Config $ProConfig)
    {
        $plugin = $this->plugin;
        $Pro = $ProConfig->getAll();
        if (in_array($player->getName(), $Pro["玩家列表"])) {
            $player->sendMessage(TextFormat::RED . "你已经领取过这个红包了！");
            return;
        }
        switch (strtolower($Pro["Style"])) {
            case "ladder":
                $Money = $Pro["红包剩余金额"] / 2;
                if ($Money > $Pro["红包剩余金额"]) {
                    $Money = $Pro["红包剩余金额"];
                }
                break;
            case "mean":
                if ($Pro["红包数量"] == 1 or $Pro["红包剩余个数"] == 1) {
                    $Money = $Pro["红包剩余金额"];
                    break;
                }
                $Money = $Pro["金额"] / $Pro["红包数量"];
                if ($Money > $Pro["红包剩余金额"]) {
                    $Money = $Pro["红包剩余金额"];
                }
                break;
            case "luck":
                if ($Pro["红包数量"] == 1 or $Pro["红包剩余个数"] == 1) {
                    $Money = $Pro["红包剩余金额"];
                    break;
                }
                $Money = mt_rand(mt_rand(0, $Pro["红包剩余金额"] / $Pro["红包剩余个数"] - 1), mt_rand($Pro["红包剩余金额"] / $Pro["红包剩余个数"], ($Pro["红包剩余金额"] / $Pro["红包剩余个数"]) * 2));
                if ($Money > $Pro["红包剩余金额"] or $Pro["红包剩余个数"] == 1) {
                    $Money = $Pro["红包剩余金额"];
                }
                break;
            default:
                $player->sendMessage(TextFormat::RED . "您无法领取一个未知类型的红包！请联系管理员！error: Profit Style to" . $ProConfig->get("Type"));
                $plugin->getLogger()->warning(TextFormat::WHITE . $player->getName() . TextFormat::RED . "正在领取一个未知类型的红包！但该类型不存在！请检查系统数据！");
                return;
        }
        if ($Money == 0) {
            $plugin->getServer()->broadcastMessage(TextFormat::GOLD . "[" . TextFormat::YELLOW . $plugin->getName() . TextFormat::GOLD . "]" . TextFormat::WHITE . $player->getName() . TextFormat::GREEN . "丑的一笔！领取一个剩余物品数量为" . TextFormat::BLUE . $Pro["红包剩余金额"] . TextFormat::GREEN . "的红包时居然毛都木领到！大家请欢迎这位黑B！");
        } else {
            $player->sendMessage(TextFormat::YELLOW . "您成功领取了" . TextFormat::GREEN . $Pro["Name"] . TextFormat::YELLOW . "的红包！本次获得" . TextFormat::BLUE . $Money . TextFormat::YELLOW . "个" . TextFormat::GREEN . BlockList::getName($Pro["物品ID"]));
        }
        if ($player instanceof Player) {
            $playerConfig = $plugin->getPlayerConfig($player);
            $playerConfig->set("领取物品红包数", $playerConfig->get("领取红包总数") + 1);
            $playerConfig->set("领取红包总数", $playerConfig->get("领取红包总数") + 1);
            $playerConfig->set("领取物品总数", $playerConfig->get("领取金币红包数") + $Money);
            $playerConfig->set("人品", $playerConfig->get("人品") + mt_rand(0, $Money));
            $playerConfig->save();
        }
        $Pro["红包剩余金额"] = $Pro["红包剩余金额"] - $Money;
        $Pro["红包剩余个数"] --;
        $Pro["玩家列表"][] = $player->getName();
        $ProConfig->setAll($Pro);
        $ProConfig->save();
        $IDx = explode(":", $Pro["物品ID"]);
        $item = new Item($IDx[0], $IDx[1]);
        for ($i = 0; $i < $Money; $i ++) {
            $player->getInventory()->addItem($item);
        }
        $voer = new OverProfit($this);
        if ($this->isProfitEnd($ProConfig->get("Key"))) {
            $voer->ProfitOver($ProConfig);
        }
    }

    /**
     * 接收金币红包
     *
     * @param Player $player
     * @param Config $ProConfig
     *            红包配置文件
     */
    private function ReceiveMoneyProfit(Player $player, Config $ProConfig)
    {
        $plugin = $this->plugin;
        $EconomyAPI = $plugin->getServer()
            ->getPluginManager()
            ->getPlugin("EconomyAPI");
        if ($EconomyAPI == NULL or ! $EconomyAPI->isEnabled()) {
            $player->sendMessage(TextFormat::RED . "当前服务器尚未安装或启动EconomyAPI插件！暂时无法使用金币红包功能！");
            return;
        }
        unset($EconomyAPI);
        $Pro = $ProConfig->getAll();
        if (in_array($player->getName(), $Pro["玩家列表"])) {
            $player->sendMessage(TextFormat::RED . "你已经领取过这个红包了！");
            return;
        }
        switch (strtolower($ProConfig->get("Style"))) {
            case "ladder":
                $Money = $Pro["红包剩余金额"] / 2;
                if ($Money > $Pro["红包剩余金额"]) {
                    $Money = $Pro["红包剩余金额"];
                }
                break;
            case "mean":
                if ($Pro["红包数量"] == 1 or $Pro["红包剩余个数"] == 1) {
                    $Money = $Pro["红包剩余金额"];
                    break;
                }
                $Money = $Pro["金额"] / $Pro["红包数量"];
                if ($Money > $Pro["红包剩余金额"]) {
                    $Money = $Pro["红包剩余金额"];
                }
                break;
            case "luck":
                if ($Pro["红包数量"] == 1 or $Pro["红包剩余个数"] == 1) {
                    $Money = $Pro["红包剩余金额"];
                    break;
                }
                $Money = mt_rand(mt_rand(0, $Pro["红包剩余金额"] / $Pro["红包剩余个数"] - 1), mt_rand($Pro["红包剩余金额"] / $Pro["红包剩余个数"], ($Pro["红包剩余金额"] / $Pro["红包剩余个数"]) * 2));
                if ($Money > $Pro["红包剩余金额"] or $Pro["红包剩余个数"] == 1) {
                    $Money = $Pro["红包剩余金额"];
                }
                break;
            default:
                $player->sendMessage(TextFormat::RED . "您无法领取一个未知类型的红包！请联系管理员！error: Profit Style to" . $ProConfig->get("Type"));
                $plugin->getLogger()->warning(TextFormat::YELLOW . "[" . $plugin->getName() . "]" . TextFormat::WHITE . $player->getName() . TextFormat::RED . "正在领取一个未知类型的红包！但该类型不存在！请检查系统数据！");
                return;
        }
        if ($player instanceof Player) {
            $playerConfig = $plugin->getPlayerConfig($player);
            $playerConfig->set("领取红包总数", $playerConfig->get("领取红包总数") + 1);
            $playerConfig->set("领取金币红包数", $playerConfig->get("领取金币红包数") + 1);
            $playerConfig->set("领取金币总数", $playerConfig->get("领取金币总数") + $Money);
            $playerConfig->set("人品", $playerConfig->get("人品") + mt_rand(0, $Money));
            $playerConfig->save();
        }
        if ($Money == 0) {
            $plugin->getServer()->broadcastMessage(TextFormat::GOLD . "[" . TextFormat::YELLOW . $plugin->getName() . TextFormat::GOLD . "]" . TextFormat::WHITE . $player->getName() . TextFormat::GREEN . "丑的一笔！领取一个余额为" . TextFormat::BLUE . $Pro["红包剩余金额"] . TextFormat::GREEN . "的红包时居然毛都木领到！大家请欢迎这位黑B！");
        } else {
            $player->sendMessage(TextFormat::YELLOW . "您成功领取了" . TextFormat::GREEN . $Pro["Name"] . TextFormat::YELLOW . "的红包！本次获得金币：" . TextFormat::BLUE . $Money);
        }
        $Pro["红包剩余金额"] = $Pro["红包剩余金额"] - $Money;
        $Pro["红包剩余个数"] --;
        $Pro["玩家列表"][] = $player->getName();
        $ProConfig->setAll($Pro);
        $ProConfig->save();
        EconomyAPI::getInstance()->addMoney($player, $Money);
        $voer = new OverProfit($this);
        if ($this->isProfitEnd($ProConfig->get("Key"))) {
            $voer->ProfitOver($ProConfig);
        }
    }

    /**
     * 接受红包！
     *
     * @param Player|CommandSender|ConsoleCommandSender $player
     *            玩家对象
     * @param String $key
     *            要领取的红包Key
     */
    public function ReceiveProfit($player, $key)
    {
        $plugin = $this->plugin;
        $configs = $plugin->getConfigs();
        if (! $configs->get("领取红包")["领取红包"]) {
            $player->sendMessage(TextFormat::RED . "当前服务器已禁止领取红包！");
            return;
        }
        if ($player instanceof CommandSender) {
            $player = $player->getName();
        }
        if ($player instanceof ConsoleCommandSender) {
            $player->sendMessage(TextFormat::RED . "我们强烈不建议您那么做！如果您真的想参与抢红包，请加入服务器！");
            return;
        }
        if (! $player instanceof Player) {
            $player = $plugin->getServer()->getOfflinePlayer($player);
            if (! $player->isOnline()) {
                $plugin->getLogger()->warning(TextFormat::YELLOW . "[" . $plugin->getName() . "]" . TextFormat::WHITE . $player->getName() . TextFormat::RED . "正在领取红包！但他(她)却不在线！请检查系统数据！");
                return;
            }
        }
        if ($configs->get("世界黑名单")["世界黑名单"] and in_array($player->getLevel()->getFolderName(), $configs->get("世界黑名单")["worlds"])) {
            $player->sendMessage(TextFormat::RED . "当前世界已禁止领取红包！");
            return;
        }
        if ($key == NULL or $key == "") {
            $player->sendMessage(TextFormat::RED . "红包Key不能为空！");
            return;
        }
        if ($plugin->getListConfig()->get($key, NULL) === NULL) {
            $player->sendMessage(TextFormat::RED . "红包不再存在！请检查您的Key");
            return;
        } else if (! is_file($plugin->getDataFolder() . ProfitFFFA::$ProfitPath . $plugin->getListConfig()->get($key))) {
            $player->sendMessage(TextFormat::RED . "红包Key：" . $key . "配置数据错误！请检查您的Key并联系服务器管理员！");
            $plugin->getLogger()->warning(TextFormat::YELLOW . "[" . $plugin->getName() . "]" . TextFormat::RED . "红包Key：" . $key . "数据已错误或不存在！建议删除该条数据！红包数据名: " . $plugin->getListConfig()
                ->get($key));
            return;
        }
        $ProConfig = $plugin->getProConfig($plugin->getListConfig()
            ->get($key));
        switch (strtolower($ProConfig->get("Type"))) {
            case "exp":
                $this->ReceiveExpProfit($player, $ProConfig);
            case "item":
                $this->ReceiveItemProfit($player, $ProConfig);
                break;
            case "money":
                $this->ReceiveMoneyProfit($player, $ProConfig);
                break;
            default:
                $player->sendMessage(TextFormat::RED . "您无法领取一个未知类型的红包！请联系管理员！error: Profit Type to" . $ProConfig->get("Type"));
                $plugin->getLogger()->warning(TextFormat::WHITE . $player->getName() . TextFormat::RED . "领取了一个未知红包类型的红包！请检查数据！");
                break;
        }
    }

    /**
     * 检查红包是否领取完毕
     *
     * @param String $key
     *            红包Key
     */
    public function isProfitEnd($key): bool
    {
        $plugin = $this->plugin;
        if ($plugin->getListConfig()->get($key, NULL) === NULL) {
            return FALSE;
        }
        $ProConfig = $plugin->getProConfig($plugin->getListConfig()
            ->get($key))
            ->getAll();
        if ($ProConfig["红包剩余金额"] < 1 or $ProConfig["红包剩余个数"] < 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getType($Type): string
    {
        if ($Type == NULL)
            return "Luck";
        switch (strtolower($Type)) {
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
        return $Type;
    }
}