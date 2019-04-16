<?php
declare(strict_types = 1);
namespace Instrument\UI;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class ModalForm extends RootUIAPI
{

    public $id;

    private $data = [];

    private $content = "";

    public $playerName;

    /**
     * I创建选择性UI
     *
     * @param int $id
     */
    public function __construct(int $id)
    {
        parent::__construct($id);
        $this->data["type"] = "modal";
        $this->data["title"] = "";
        $this->data["content"] = $this->content;
        $this->data["button1"] = "";
        $this->data["button2"] = "";
    }

    /**
     * UI表单发送至玩家
     *
     * {@inheritdoc}
     * @see \Instrument\UI\RootUIAPI::sendToPlayer()
     */
    public function sendToPlayer(Player $player): void
    {
        $pk = new ModalFormRequestPacket();
        $pk->formId = $this->id;
        $pk->formData = json_encode($this->data);
        $player->dataPacket($pk);
        $this->playerName = $player->getName();
    }

    /**
     * UI 标题
     *
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->data["title"] = $title;
    }

    /**
     * UI标题
     *
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->data["content"] = $content;
    }

    /**
     * UI第一个按钮
     *
     * @param string $text
     */
    public function setButton1(string $text): void
    {
        $this->data["button1"] = $text;
    }

    /**
     * UI 第二个按钮
     *
     * @param string $text
     */
    public function setButton2(string $text): void
    {
        $this->data["button2"] = $text;
    }
}
