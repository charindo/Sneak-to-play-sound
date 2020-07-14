<?php

namespace rindou96;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class main extends PluginBase implements Listener{

	public function onEnable(){

		$this->getServer()->getPluginManager()->registerEvents($this,$this);

		$this->tasks = [];

	}

	public function onSneak(PlayerToggleSneakEvent $event){

		$player = $event->getPlayer();
		$name = $player->getName();

		if($event->isSneaking()){

			if(isset($this->tasks[$name])){

				$this->getScheduler()->cancelTask($this->tasks[$name]["task"]->getTaskId());
				unset($this->tasks[$name]);

			}

			$this->tasks[$name] = [
				"task" => $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(int $currentTick) use ($player): void{
					$this->sneakingTask($player);
				}), 20),
				"count" => 0,
			];

		}elseif(isset($this->tasks[$name])){

			$this->getScheduler()->cancelTask($this->tasks[$name]["task"]->getTaskId());
			unset($this->tasks[$name]);

		}

	}

	public function sneakingTask(Player $player): void{

		$name = $player->getName();

		if(isset($this->tasks[$name]) && $player->isSneaking() && $player->isOnline()){

			$this->tasks[$name]["count"] += 1;

			$pk = new PlaySoundPacket();
			$pk->soundName = "note.harp";
			$pk->x = (int) $player->getX();
			$pk->y = (int) $player->getX();
			$pk->z = (int) $player->getX();
			$pk->volume = 1;
			$pk->pitch = 1;
			$player->dataPacket($pk);

			if($this->tasks[$name] >= 3){

				$this->getScheduler()->cancelTask($this->tasks[$name]["task"]->getTaskId());
				unset($this->tasks[$name]);

			}

		}elseif(isset($this->tasks[$name])){

			$this->getScheduler()->cancelTask($this->tasks[$name]["task"]->getTaskId());
			unset($this->tasks[$name]);

		}

	}

}