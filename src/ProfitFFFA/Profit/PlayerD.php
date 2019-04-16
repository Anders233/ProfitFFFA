<?php
namespace ProfitFFFA\Profit;

use Instrument\SmallTools;
use ProfitFFFA\ProfitFFFA;
use pocketmine\utils\TextFormat;
use Instrument\UI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\command\ConsoleCommandSender;

// 2019年4月2日 下午10:18:13
class PlayerD
{

    private $plugin;

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     *
     * @param CommandSender|Player|ConsoleCommandSender $player
     */
    public function makeUI($player)
    {
        $msg = "";
        $ui = new SimpleForm((int) SmallTools::getRandText(mt_rand(3, 5), "0123456789"));
        $configs = $this->plugin->getPlayerConfig($player)->getAll();
        $ui->setTitle(TextFormat::GOLD . $player->getName());
        foreach ($configs as $key => $data) {
            if ($data === FALSE)
                $data = "否";
            if ($data === TRUE)
                $data = "是";
            if (strtolower($key) == "name")
                $key = "你的名字";
            if (! is_array($data))
                $msg .= SmallTools::getFontColor("") . $key . TextFormat::LIGHT_PURPLE . ":" . SmallTools::getFontColor("") . $data . "\n\n";
        }
        $ui->setContent($msg);
        $ui->addButton("确定");
        $ui->sendToPlayer($player);
    }
}