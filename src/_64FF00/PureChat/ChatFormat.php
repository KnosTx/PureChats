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
        $msg = $this->getChatFormat($this->player, $message, $this->WorldName);
        return $msg;
    }

    public function getNametag(Player $player, ?string $WorldName = null): string
    {
        $originalNametag = $this->getOriginalNametag($player, $WorldName);
        $nameTag = $this->applyColors($originalNametag);
        $nameTag = $this->applyPCTags($nameTag, $player, null, $WorldName);
        return $nameTag;
    }



    public function getOriginalNametag(Player $player, ?string $WorldName = null): string
    {
        /** @var \_64FF00\PurePerms\PPGroup $group */
        $group = $this->PureChat->getPurePerms()->getUserDataMgr()->getGroup($player, $WorldName);
        if($WorldName === null)
        {
            $originalNametag = $this->PureChat->getthisConfig()->getNested("groups." . $group->getName() . ".nametag");
            if(!is_string($originalNametag))
            {
                $this->getLogger()->critical("Invalid nametag found in config.yml (Group: " . $group->getName() . ") / Setting it to default value.");
                $this->PureChat->getthisConfig()->setNested("groups." . $group->getName() . ".nametag", $originalNametag = "&8&l[" . $group->getName() . "]&f&r {display_name}");
                $this->PureChat->getthisConfig()->save();
                $this->PureChat->getthisConfig()->reload();
            }
            return $originalNametag;
        }
        else
        {
            $originalNametag = $this->PureChat->getthisConfig()->getNested("groups." . $group->getName() . "worlds.$WorldName.nametag");
            if(!is_string(($originalNametag)))
            {
                $this->getLogger()->critical("Invalid nametag found in config.yml (Group: " . $group->getName() . ", WorldName = $WorldName) / Setting it to default value.");
                $this->PureChat->getthisConfig()->setNested("groups." . $group->getName() . "worlds.$WorldName.nametag", $originalNametag = "&8&l[" . $group->getName() . "]&f&r {display_name}");
                $this->PureChat->getthisConfig()->save();
                $this->PureChat->getthisConfig()->reload();
            }
            return $originalNametag;
        }
    }

    public function getPrefix(Player $player, ?string $WorldName = null): string
    {
        if($WorldName === null)
        {
            $prefix = $this->PureChat->getPurePerms()->getUserDataMgr()->getNode($player, "prefix");
            return is_string($prefix) ? $prefix : '';
        }
        else
        {
            $worldData = $this->PureChat->getPurePerms()->getUserDataMgr()->getWorldData($player, $WorldName);
            if(!isset($worldData["prefix"]) || !is_string($worldData["prefix"]))
                return "";
            return $worldData["prefix"];
        }
    }

    public function getSuffix(Player $player, ?string $WorldName = null): string
    {
        if($WorldName === null)
        {
            $suffix = $this->PureChat->getPurePerms()->getUserDataMgr()->getNode($player, "suffix");
            return is_string($suffix) ? $suffix : '';
        }
        else
        {
            $worldData = $this->PureChat->getPurePerms()->getUserDataMgr()->getWorldData($player, $WorldName);

            if(!isset($worldData["suffix"]) || !is_string($worldData["suffix"]))
                return "";
            return $worldData["suffix"];
        }
    }

    public function setOriginalChatFormat(PPGroup $group, string $chatFormat, ?string $WorldName = null): bool
    {
        if($WorldName === null)
        {
            $this->PureChat->getthisConfig()->setNested("groups." . $group->getName() . ".chat", $chatFormat);
        }
        else
        {
            $this->PureChat->getthisConfig()->setNested("groups." . $group->getName() . "worlds.$WorldName.chat", $chatFormat);
        }
        $this->PureChat->getthisConfig()->save();
        $this->PureChat->getthisConfig()->reload();
        return true;
    }

    public function setOriginalNametag(PPGroup $group, string $nameTag, ?string $WorldName = null): bool
    {
        if($WorldName === null)
        {
            $this->PureChat->getthisConfig()->setNested("groups." . $group->getName() . ".nametag", $nameTag);
        }
        else
        {
            $this->PureChat->getthisConfig()->setNested("groups." . $group->getName() . "worlds.$WorldName.nametag", $nameTag);
        }
        $this->PureChat->getthisConfig()->save();
        $this->PureChat->getthisConfig()->reload();
        return true;
    }

    public function setPrefix(string $prefix, Player $player, ?string $WorldName = null): bool
    {
        if($WorldName === null)
        {
            $this->PureChat->getPurePerms()->getUserDataMgr()->setNode($player, "prefix", $prefix);
        }
        else
        {
            $worldData = $this->PureChat->getPurePerms()->getUserDataMgr()->getWorldData($player, $WorldName);
            $worldData["prefix"] = $prefix;
            $this->PureChat->getPurePerms()->getUserDataMgr()->setWorldData($player, $WorldName, $worldData);
        }

        return true;
    }

    public function setSuffix(string $suffix, Player $player, ?string $WorldName = null): bool
    {
        if($WorldName === null)
        {
            $this->PureChat->getPurePerms()->getUserDataMgr()->setNode($player, "suffix", $suffix);
        }
        else
        {
            $worldData = $this->PureChat->getPurePerms()->getUserDataMgr()->getWorldData($player, $WorldName);
            $worldData["suffix"] = $suffix;
            $this->PureChat->getPurePerms()->getUserDataMgr()->setWorldData($player, $WorldName, $worldData);
        }

        return true;
    }

    public function stripColors(string $string): string
    {
        return TextFormat::clean($string);
    }

    public function applyColors(string $string): string
    {
        return TextFormat::colorize($string);
    }

    public function applyPCTags(string $string, Player $player, ?string $message, ?string $WorldName): string
    {
        // TODO
        $string = str_replace("{display_name}", $player->getDisplayName(), $string);
        if($message === null)
            $message = "";
        if($player->hasPermission("pchat.coloredMessages"))
        {
            $string = str_replace("{msg}", $this->applyColors($message), $string);
        }
        else
        {
            $string = str_replace("{msg}", $this->stripColors($message), $string);
        }
        {
            $string = str_replace("{fac_name}", '', $string);
            $string = str_replace("{fac_rank}", '', $string);
        }
        $string = str_replace("{world}", ($WorldName === null ? "" : $WorldName), $string);
        $string = str_replace("{prefix}", $this->getPrefix($player, $WorldName), $string);
        $string = str_replace("{suffix}", $this->getSuffix($player, $WorldName), $string);
        return $string;
    }
    public function getChatFormat(Player $player, ?string $message, ?string $WorldName = null): string
    {
        $originalChatFormat = $this->getOriginalChatFormat($player, $WorldName);
        $chatFormat = $this->applyColors($originalChatFormat);
        $chatFormat = $this->applyPCTags($chatFormat, $player, $message, $WorldName);
        return $chatFormat;
    }
    public function getOriginalChatFormat(Player $player, ?string $WorldName = null): string
    {
        /** @var \_64FF00\PurePerms\PPGroup $group */
        $group = $this->PureChat->getPurePerms()->getUserDataMgr()->getGroup($player, $WorldName);
        if($WorldName === null)
        {
            $originalChatFormat = $this->PureChat->getthisConfig()->getNested("groups." . $group->getName() . ".chat");
            if(!is_string($originalChatFormat))
            {
                $this->getLogger()->critical("Invalid chat format found in config.yml (Group: " . $group->getName() . ") / Setting it to default value.");
                $this->PureChat->getthisConfig()->setNested("groups." . $group->getName() . ".chat", $originalChatFormat = "&8&l[" . $group->getName() . "]&f&r {display_name} &7> {msg}");
                $this->PureChat->getthisConfig()->save();
                $this->PureChat->getthisConfig()->reload();
            }

            return $originalChatFormat;
        }
        else
        {
            $originalChatFormat = $this->PureChat->getthisConfig()->getNested("groups." . $group->getName() . "worlds.$WorldName.chat");
            if(!is_string($originalChatFormat))
            {
                $this->getLogger()->critical("Invalid chat format found in config.yml (Group: " . $group->getName() . ", WorldName = $WorldName) / Setting it to default value.");
                $this->PureChat->getthisConfig()->setNested("groups." . $group->getName() . "worlds.$WorldName.chat", $originalChatFormat = "&8&l[" . $group->getName() . "]&f&r {display_name} &7> {msg}");
                $this->PureChat->getthisConfig()->save();
                $this->PureChat->getthisConfig()->reload();
            }

            return $originalChatFormat;
        }
    }

}