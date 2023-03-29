<?php

namespace Electro\WordScrambler;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use onebone\economyapi\EconomyAPI;

class WordScrambler extends PluginBase implements Listener{

    public ?string $word = null;
    public float $reward;
    public bool $rewardEnabled = false;
    public array $words = [];
    public function onEnable() : void
    {
        if ($this->getConfig()->get("Aktifkan Hadiah"))
        {
            $this->rewardEnabled = true;
        }
        if (!$this->getServer()->getPluginManager()->getPlugin("EconomyAPI") && $this->rewardEnabled == true)
        {
            $this->getLogger()->warning("Hadiah telah dinonaktifkan karena Anda tidak menginstal EconomyAPI di server Anda.");
            $this->rewardEnabled = false;
        }
        $this->loadWords();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleDelayedTask(new ScrambleTask($this), (20 * 60 * $this->getConfig()->get("Waktu")));
    }

    public function onChat(playerChatEvent $event)
    {
        $player = $event->getPlayer();
        $msg = $event->getMessage();

        if (strtolower($msg) == strtolower($this->word))
        {
            $event->cancel();
            $this->playerWon($player);
            $this->word = null;
        }
    }


    public function loadWords()
    {
        foreach($this->getConfig()->get("Kata") as $word)
        {
            $this->words[] = $word;
        }
    }
    public function playerWon($player)
    {
        $this->getServer()->broadcastMessage("§6" . $player->getName() . " Telah menjawab kata dengan benar.\n§6Kata itu adalah §e" . $this->word);
        if ($this->rewardEnabled)
        {
            EconomyAPI::getInstance()->addMoney($player, $this->reward);
        }
    }

    public function scrambleWord()
    {
        $this->word = $this->words[array_rand($this->words)];
        if ($this->rewardEnabled)
        {
            $this->reward = mt_rand($this->getConfig()->get("Hadiah-Terkecil"), $this->getConfig()->get("Hadiah-Terbesar"));
        }
        foreach($this->getServer()->getOnlinePlayers() as $player)
        {
            if ($this->rewardEnabled)
            {
                $player->sendMessage("§bPlayer pertama yang menyusun kata berikut §e". str_shuffle($this->word) ." §bAkan mendapatkan $". $this->reward ."!");
            }
            else
            {
                $player->sendMessage("§bCobalah untuk menjadi player pertama yang menyusun kata §e". str_shuffle($this->word) . "!");
            }
        }
        $this->getScheduler()->scheduleDelayedTask(new ScrambleTask($this), (20 * 60 * $this->getConfig()->get("Waktu")));
    }
}
