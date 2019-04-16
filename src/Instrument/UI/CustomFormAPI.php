<?php
namespace Instrument\UI;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;

class CustomFormAPI extends RootUIAPI
{

    public $id;

    private $data = [];

    public $playerName;

    /**
     *
     * @param String|int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
        parent::__construct($id);
        $this->data["type"] = "custom_form";
        $this->data["title"] = "";
        $this->data["content"] = [];
    }

    /**
     * 添加输入框
     *
     * @param string $text
     *            提示的 文字
     * @param string $default
     *            默认显示的文字
     * @param string $placeholder
     */
    public function addInput(string $text, string $default = null, string $placeholder = ""): void
    {
        $this->addContent([
            "type" => "input",
            "text" => $text,
            "placeholder" => $placeholder,
            "default" => $default
        ]);
    }

    /**
     * 发送UI给玩家
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
     * 设置标题
     *
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->data["title"] = $title;
    }

    /**
     * 添加标签
     *
     * @param string $text
     */
    public function addLabel(string $text): void
    {
        $this->addContent([
            "type" => "label",
            "text" => $text
        ]);
    }

    /**
     * 添加开关
     *
     * @param string $text
     *            开关文字内容
     * @param bool $default
     *            开关默认状态
     */
    public function addToggle(string $text, bool $default = null): void
    {
        $content = [
            "type" => "toggle",
            "text" => $text
        ];
        if ($default !== null) {
            $content["default"] = $default;
        }
        $this->addContent($content);
    }

    /**
     * 添加滑块
     *
     * @param string $text
     *            滑块文字内容
     * @param int $min
     *            滑块最小值
     * @param int $max
     *            滑块最大值
     * @param int $step
     *            每滑动一下移动的最小数值（步）
     * @param int $default
     *            默认显示的数值
     */
    public function addSlider(string $text, int $min, int $max, int $step = -1, int $default = -1): void
    {
        $content = [
            "type" => "slider",
            "text" => $text,
            "min" => $min,
            "max" => $max
        ];
        if ($step !== - 1) {
            $content["step"] = $step;
        }
        if ($default !== - 1) {
            $content["default"] = $default;
        }
        $this->addContent($content);
    }

    /**
     * 添加滑步滑块
     *
     * @param string $text
     *            提示的内容
     * @param array $steps
     *            步长
     * @param int $defaultIndex
     *            默认显示的数值
     */
    public function addStepSlider(string $text, array $steps, int $defaultIndex = -1): void
    {
        $content = [
            "type" => "step_slider",
            "text" => $text,
            "steps" => $steps
        ];
        if ($defaultIndex !== - 1) {
            $content["default"] = $defaultIndex;
        }
        $this->addContent($content);
    }

    /**
     * 添加下拉菜单
     *
     * @param string $text
     *            添加菜单的默认内容
     * @param array $options
     *            菜单内容
     * @param int $default
     *            默认显示的内容(菜单数组的键值)
     */
    public function addDropdown(string $text, array $options, int $default = null): void
    {
        $this->addContent([
            "type" => "dropdown",
            "text" => $text,
            "options" => $options,
            "default" => $default
        ]);
    }

    private function addContent(array $content): void
    {
        $this->data["content"][] = $content;
    }
}
