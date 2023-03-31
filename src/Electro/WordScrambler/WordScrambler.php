<?php

namespace Electro\WordScrambler;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use cooldogedev\BedrockEconomy;
class WordScrambler extends PluginBase implements Listener{

    public ?string $word = null;
    public float $reward;
    public bool $rewardEnabled = false;
    public array $words = [];
    public function onEnable() : void
    {
            if ($this->getConfig()->get("Activate Rewards"))
        {
            $this->rewardEnabled = true;
        }
        if (!$this->getServer()->getPluginManager()->getPlugin("BedrockEconomy") && $this->rewardEnabled == true)
        {
            $this->getLogger()->warning("Rewards has been disabled because you don't have BedrockEconomy installed on your server.");
            $this->rewardEnabled = false;
        }
        $this->loadWords();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleDelayedTask(new ScrambleTask($this), (20 * 60 * $this->getConfig()->get("Time")));
        $this->getLogger)()->info("This Plugin will be deprecated soon. read the update logs: https://skyss0fly.github.io/ChatScrambler");
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
        foreach($this->getConfig()->get("Say") as $word)
        {
            $this->words[] = $word;
        }
    }
    public function playerWon($player)
    {
        $this->getServer()->broadcastMessage("§6" . $player->getName() . "Has answered the word correctly.\n§6 The word is §e" . $this->word);
        if ($this->rewardEnabled)
        {
             BedrockEconomyAPI::getInstance()->addBalance($player, $this->reward);
        }
    }

    public function scrambleWord()
    {
        $this->word = $this->words[array_rand($this->words)];
        if ($this->rewardEnabled)
        {
            $this->reward = mt_rand($this->getConfig()->get("-Smallest Gift"), $this->getConfig()->get("-Biggest Prize"));
        }
        foreach($this->getServer()->getOnlinePlayers() as $player)
        {
            if ($this->rewardEnabled)
            {
                $player->sendMessage("§bThe first player to compose the following word §e". str_shuffle($this->word) ." §bWill get $". $this->reward ."!");
            }
            else
            {
                $player->sendMessage("§bTry to be the first player to compose the word". str_shuffle($this->word) . "!");
            }
        }
        $this->getScheduler()->scheduleDelayedTask(new ScrambleTask($this), (20 * 60 * $this->getConfig()->get("Time")));
    }
}
