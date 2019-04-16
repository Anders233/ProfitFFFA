<?php
namespace Instrument\UI;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;

abstract class RootUIAPI
{

    private $data = [];

    public $id;

    private $callable;

    public $playerName;

    /**
     *
     * @param String|int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function sendToPlayer(Player $player): void
    {
        $pk = new ModalFormRequestPacket();
        $pk->formId = $this->id;
        $pk->formData = json_encode($this->data);
        $player->dataPacket($pk);
        $this->playerName = $player->getName();
    }
}
