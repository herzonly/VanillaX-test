<?php

namespace Herza\VanillaX;

use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\StringToItemParser;
use pocketmine\block\BlockFactory;
use pocketmine\block\Block;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use pocketmine\player\Player;
use pocketmine\event\player\PlayerInteractEvent;

class VanillaX extends PluginBase implements Listener {
    public function onEnable(): void {
        $this->registerAllVanillaContent();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("VanillaX - Seluruh konten Minecraft Bedrock berhasil didaftarkan!");
    }

    private function registerAllVanillaContent(): void {
        $boatTypes = [
            'oak', 'spruce', 'birch', 'jungle', 'acacia', 'dark_oak', 
            'mangrove', 'cherry', 'bamboo'
        ];
        
        foreach ($boatTypes as $type) {
            $this->registerBoat($type);
        }

        $minecartTypes = [
            'regular', 'chest', 'furnace', 'tnt', 'hopper', 'command_block'
        ];
        
        foreach ($minecartTypes as $type) {
            $this->registerMinecart($type);
        }

        $mobSpawnEggs = [
            'bat', 'bee', 'blaze', 'cat', 'cave_spider', 'chicken', 
            'cod', 'cow', 'creeper', 'dolphin', 'donkey', 'elder_guardian', 
            'enderdragon', 'enderman', 'endermite', 'evoker', 'fox', 
            'ghast', 'goat', 'guardian', 'hoglin', 'horse', 'husk', 
            'iron_golem', 'llama', 'magma_cube', 'mooshroom', 'mule', 
            'ocelot', 'panda', 'parrot', 'phantom', 'pig', 'piglin', 
            'pillager', 'polar_bear', 'pufferfish', 'rabbit', 'ravager', 
            'salmon', 'sheep', 'shulker', 'silverfish', 'skeleton', 
            'slime', 'snow_golem', 'spider', 'squid', 'stray', 
            'strider', 'trader_llama', 'tropical_fish', 'turtle', 
            'vex', 'villager', 'vindicator', 'wandering_trader', 
            'witch', 'wither', 'wither_skeleton', 'wolf', 'zombie', 
            'zombie_horse', 'zombie_piglin', 'zombie_villager', 'zoglin'
        ];

        foreach ($mobSpawnEggs as $mob) {
            $this->registerSpawnEgg($mob);
        }

        $this->registerAdditionalVanillaContent();
    }

    private function registerBoat(string $type): void {
        $itemId = "minecraft:{$type}_boat";
        $entityId = "minecraft:{$type}_boat";
        
        ItemFactory::getInstance()->register(new \pocketmine\item\Boat($itemId, 1), true);
        StringToItemParser::getInstance()->register($itemId, fn() => new \pocketmine\item\Boat($itemId, 1));
        
        EntityFactory::getInstance()->register(
            $entityId, 
            BoatEntity::class, 
            function(World $world, Vector3 $position, float $yaw, float $pitch) use ($type): BoatEntity {
                return new BoatEntity($world, $position, $type);
            }
        );
    }

    private function registerMinecart(string $type): void {
        $itemId = "minecraft:{$type}_minecart";
        $entityId = "minecraft:{$type}_minecart";
        
        ItemFactory::getInstance()->register(new \pocketmine\item\Minecart($itemId, 1), true);
        StringToItemParser::getInstance()->register($itemId, fn() => new \pocketmine\item\Minecart($itemId, 1));
        
        EntityFactory::getInstance()->register(
            $entityId, 
            MinecartEntity::class, 
            function(World $world, Vector3 $position, float $yaw, float $pitch) use ($type): MinecartEntity {
                return new MinecartEntity($world, $position, $type);
            }
        );
    }

    private function registerSpawnEgg(string $mobType): void {
        $itemId = "minecraft:{$mobType}_spawn_egg";
        
        ItemFactory::getInstance()->register(new \pocketmine\item\SpawnEgg($itemId, 1), true);
        StringToItemParser::getInstance()->register($itemId, fn() => new \pocketmine\item\SpawnEgg($itemId, 1));
    }

    private function registerAdditionalVanillaContent(): void {}

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $block = $event->getBlock();
        
        if ($item instanceof \pocketmine\item\Boat) {
            $spawnPos = $block->getPosition()->add(0, 1, 0);
            
            $boat = EntityFactory::getInstance()->create(
                "minecraft:{$item->getWoodType()}_boat", 
                $spawnPos
            );
            
            if ($boat !== null) {
                $boat->spawnToAll();
                
                $item->setCount($item->getCount() - 1);
                $player->getInventory()->setItemInHand($item);
                
                $event->cancel();
            }
        }
        
        if ($item instanceof \pocketmine\item\Minecart) {
            $spawnPos = $block->getPosition()->add(0, 1, 0);
            
            $minecart = EntityFactory::getInstance()->create(
                "minecraft:{$item->getType()}_minecart", 
                $spawnPos
            );
            
            if ($minecart !== null) {
                $minecart->spawnToAll();
                
                $item->setCount($item->getCount() - 1);
                $player->getInventory()->setItemInHand($item);
                
                $event->cancel();
            }
        }
        
        if ($item instanceof \pocketmine\item\SpawnEgg) {
            $spawnPos = $block->getPosition()->add(0, 1, 0);
            
            $mobType = str_replace('_spawn_egg', '', $item->getNamespaceId());
            
            $mob = EntityFactory::getInstance()->create(
                "minecraft:{$mobType}", 
                $spawnPos
            );
            
            if ($mob !== null) {
                $mob->spawnToAll();
                
                $item->setCount($item->getCount() - 1);
                $player->getInventory()->setItemInHand($item);
                
                $event->cancel();
            }
        }
    }
    
    public function onDisable(): void {
        $this->getLogger()->info("VanillaX plugin telah dinonaktifkan!");
    }
}

class BoatEntity extends \pocketmine\entity\Vehicle {
    private string $woodType;
}

class MinecartEntity extends \pocketmine\entity\Vehicle {
    private string $type;
}
