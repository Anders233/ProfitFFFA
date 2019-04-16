<?php
namespace ProfitFFFA\PlayerEvent;

use const ProfitFFFA\MainUIID;
use const ProfitFFFA\ProListUIID;
use ProfitFFFA\ProfitFFFA;
use const ProfitFFFA\SettingProUIID;
use const ProfitFFFA\SettingUIID;
use const ProfitFFFA\XiaokaiUIID;
use const ProfitFFFA\makeItemProUIID;
use const ProfitFFFA\makeMoneyPrUIID;
use const ProfitFFFA\sendProUIID;
use ProfitFFFA\UI\UIEvent;
use ProfitFFFA\UI\makeUI;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\utils\TextFormat;
use const ProfitFFFA\makeSendExpUIID;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class UICallable implements Listener
{

    private $plugin;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onReceive(DataPacketReceiveEvent $e)
    {
        $plugin = $this->plugin;
        $pk = $e->getPacket();
        $player = $e->getPlayer();
        if (! $pk instanceof ModalFormResponsePacket) {
            return;
        }
        $id = $pk->formId;
        $data = json_decode($pk->formData);
        if (isset(ProfitFFFA::$isPlayerTo[$player->getName()])) {
            $event = new UIEvent($plugin);
            $event->makeMain($player, $data);
            unset(ProfitFFFA::$isPlayerTo[$player->getName()]);
        } else {
            if (isset($plugin->ShowUIPlayerList[$player->getName()]) and $plugin->ShowUIPlayerList[$player->getName()] and $id !== NULL and $data !== NULL) {
                $event = new UIEvent($plugin);
                $make = new makeUI($plugin);
                unset($plugin->ShowUIPlayerList[$player->getName()]);
                switch ($id) {
                    case makeSendExpUIID:
                        $event->ExpPro($player, $data);
                        break;
                    case SettingUIID:
                        $new = new Setting($plugin);
                        $new->start($player, $data);
                        break;
                    case XiaokaiUIID:
                        makeUI::makeTip($player, TextFormat::GOLD . "感谢您的支持！");
                        break;
                    case SettingProUIID:
                        $event->StartSettingProConfig($player, $data);
                        break;
                    case ProListUIID:
                        $make->makeSettingPro($player, $data);
                        break;
                    case makeMoneyPrUIID:
                        $event->makeMoneyPro($player, $data);
                        break;
                    case makeItemProUIID:
                        $event->makeItemPro($player, $data);
                        break;
                    case sendProUIID:
                        $event->makeProType($player, $data);
                        break;
                    case MainUIID:
                        $event->makeMain($player, $data);
                        break;
                    default:
                        break;
                }
            }
        }
    }
}
?>