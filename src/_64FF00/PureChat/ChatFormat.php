<?php

namespace _64FF00\PureChat;
use pocketmine\player\chat\ChatFormatter;
use pocketmine\lang\Translatable;
use _64FF00\PureChat\PureChat;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
class ChatFormat implements ChatFormatter
{

    public PureChat $PureChat;
    public $player;
    public $WorldName;
    public function __construct($player, $message, $WorldName, $PureChat)
    {
        $this->PureChat = $PureChat;
        $this->player = $player;
        $this->WorldName = $WorldName;
        $this->format("", $message);
    }

    public function format($username, $message): Translatable|string{
        $msg = $this->PureChat->getChatFormat($this->player, $message, $this->WorldName);
        return $msg;
    }
}