<?php
declare(strict_types = 1);
namespace Instrument\UI;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;

class SimpleForm extends RootUIAPI
{

    public $id;

    private $data = [];

    private $content = "";

    public $playerName;

    /**
     *
     * @param String|int $id
     */
    public function __construct($id)
    {
        parent::__construct($id);
        $this->data["type"] = "form";
        $this->data["title"] = "";
        $this->data["content"] = $this->content;
    }

    public function sendToPlayer(Player $player): void
    {
        $pk = new ModalFormRequestPacket();
        $pk->formId = $this->id;
        $pk->formData = json_encode($this->data);
        $player->dataPacket($pk);
        $this->playerName = $player->getName();
    }

    public function setTitle(string $title): void
    {
        $this->data["title"] = $title;
    }

    public function setContent(string $content): void
    {
        $this->data["content"] = $content;
    }

    /**
     * 添加按钮
     *
     * @param string $text
     *            按钮内容
     * @param int $imageType
     *            按钮图片类型[0:材质包|-1:网络]
     * @param string $imagePath
     *            图片地址
     */
    public function addButton(string $text, int $imageType = -1, string $imagePath = ""): void
    {
        $content = [
            "text" => $text
        ];
        if ($imageType !== - 1) {
            $content["image"]["type"] = $imageType === 0 ? "path" : "url";
            $content["image"]["data"] = $imagePath;
        }
        $this->data["buttons"][] = $content;
    }
}
