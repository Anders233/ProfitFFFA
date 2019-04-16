<?php
namespace ProfitFFFA\UI;

use ProfitFFFA\ProfitFFFA;
use pocketmine\Player;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use Instrument\UI\CustomFormAPI;
use const ProfitFFFA\makeSendExpUIID;
use pocketmine\command\CommandSender;
use Instrument\SmallTools;

// 2019年4月3日 下午2:10:07
class makeUI2
{

    private $plugin;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * make经验红包界面
     *
     * @param Player|ConsoleCommandSender|CommandSender $player
     */
    public function makeSendExp($player)
    {
        $plugin = $this->plugin;
        if ($player instanceof ConsoleCommandSender) {
            $player->sendMessage(TextFormat::RED . "如果您真的想使用该功能，请加入游戏！");
            return;
        }
        if ($player instanceof CommandSender) {
            $player = $plugin->getServer()->getPlayer($player->getName());
            if ($player === NULL) {
                $plugin->getLogger()->warning(TextFormat::YELLOW . $player->getName() . TextFormat::RED . "正在发送Exp红包！但他当前不在线！勤检查数据。");
                return;
            }
        }
        if ($player->getXpLevel() < 1) {
            makeUI::makeTip($player, "在发送经验红包之前，您可能还需要先去刷一点等级。");
            return;
        }
        $config = $plugin->getConfigs();
        $ui = new CustomFormAPI(makeSendExpUIID);
        $ui->setTitle(TextFormat::YELLOW . $plugin->getName() . TextFormat::WHITE . "-" . TextFormat::GOLD . "Exp");
        $ui->addInput(TextFormat::WHITE . "您需要发送多少经验（经验等级）作为红包呢？", $player->getXpLevel());
        $ui->addInput(TextFormat::WHITE . "红包个数", $config->get("默认红包数"));
        $ui->addInput(TextFormat::YELLOW . "红包口令", SmallTools::getKey());
        $ui->addDropdown(TextFormat::WHITE . "红包类型", array(
            TextFormat::GOLD . "拼手气红包",
            TextFormat::GREEN . "人均红包",
            TextFormat::RED . "天梯红包"
        ), 0);
        $ui->sendToPlayer($player);
        $plugin->ShowUIPlayerList[$player->getName()] = TRUE;
    }
}