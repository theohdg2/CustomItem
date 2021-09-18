<?php

/*
 *     /$$$$$$$           /$$               /$$
 *   | $$__  $$         | $$              | $$
 *   | $$  \ $$/$$$$$$ /$$$$$$   /$$$$$$ /$$$$$$   /$$$$$$  /$$$$$$  /$$$$$$$
 *   | $$$$$$$/$$__  $|_  $$_/  |____  $|_  $$_/  /$$__  $$/$$__  $$/$$_____/
 *   | $$____| $$  \ $$ | $$     /$$$$$$$ | $$   | $$  \ $| $$$$$$$|  $$$$$$
 *   | $$    | $$  | $$ | $$ /$$/$$__  $$ | $$ /$| $$  | $| $$_____/\____  $$
 *   | $$    |  $$$$$$/ |  $$$$|  $$$$$$$ |  $$$$|  $$$$$$|  $$$$$$$/$$$$$$$/
 *   |__/     \______/   \___/  \_______/  \___/  \______/ \_______|_______/
 *
 *  GNU General Public License v2.0
 *  Copyright (C) 1989, 1991 Free Software Foundation, Inc.
 *  51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 *  Everyone is permitted to copy and distribute verbatim copies
 *  of this license document, but changing it is not allow
 */

namespace Refaltor\Natof\CustomItem\Events\Listeners;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\tile\ItemFrame;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;
use pocketmine\world\sound\BlockBreakSound;
use Refaltor\Natof\CustomItem\CustomItem;
use Refaltor\Natof\CustomItem\Items\BasicItem;

class PacketListener implements Listener
{
    /** @var CustomItem  */
    public CustomItem $main;

    /** @var array  */
    public array $handlers = [];

    public function __construct(CustomItem $main)
    {
        $this->main = $main;
    }

    public function onPacketSend(DataPacketSendEvent $event) {
        $packets = $event->getPackets();
        foreach ($packets as $packet) {
            if ($packet instanceof StartGamePacket) {
                $packet->itemTable = $this->main->entries;
            }
        }
    }

   /* public function onTool(DataPacketSendEvent $event): void {
        $packets = $event->getPackets();
        $targets = $event->getTargets();
        foreach ($packets as $packet) {
            foreach ($targets as $target) {
                $player = $target->getPlayer();
                if ($packet instanceof PlayerActionPacket) {
                    $handled = false;
                    try {
                        $pos = new Vector3($packet->x, $packet->y, $packet->z);

                        if ($packet->action === PlayerActionPacket::ACTION_START_BREAK) {
                            $item = $player->getInventory()->getItemInHand();
                            if (!($item instanceof BasicItem)) return; // test

                            if ($pos->distanceSquared($player->getPosition()) > 10000) return;

                            $target = $player->getWorld()->getBlock($pos);

                            $ev = new PlayerInteractEvent($player, $player->getInventory()->getItemInHand(), $target, null, $packet->face, PlayerInteractEvent::LEFT_CLICK_BLOCK);
                            if ($player->isSpectator()) {
                                $ev->cancel();
                            }
                            $ev->call();
                            if ($ev->isCancelled()) {
                                $player->getInventory()->setHeldItemIndex($player->getInventory()->getHeldItemIndex());
                                return;
                            }

                            $tile = $player->getWorld()->getTile($pos);
                            if ($tile instanceof ItemFrame && $tile->hasItem()) {
                                if (lcg_value() <= $tile->getItemDropChance()) {
                                    $player->getWorld()->dropItem($tile->getBlock()->getPos(), $tile->getItem());
                                }
                                $tile->setItem($item);
                                $tile->setItemRotation(0);
                                return;
                            }
                            $block = $target->getSide($packet->face);
                            if ($block->getId() === BlockLegacyIds::FIRE) {
                                $player->getWorld()->setBlock($block->getPos(), VanillaBlocks::AIR());
                                return;
                            }

                            if (!$player->isCreative()) {
                                $handled = true;
                                //TODO: improve this to take stuff like swimming, ladders, enchanted tools into account, fix wrong tool break time calculations for bad tools (pmmp/PocketMine-MP#211)
                                $breakTime = ceil($target->getBreakInfo()->getBreakTime($player->getInventory()->getItemInHand()) * 20);
                                if ($breakTime > 0) {
                                    if ($breakTime > 10) {
                                        $breakTime -= 10;
                                    }
                                    $this->scheduleTask(Position::fromObject($pos, $player->getWorld()), $player->getInventory()->getItemInHand(), $player, $breakTime);
                                    $player->getWorld()->addSound($pos, new BlockBreakSound($block));
                                }
                            }
                        } elseif ($packet->action === PlayerActionPacket::ACTION_ABORT_BREAK) {
                            //$player->getWorld()->addSound($pos, new BlockBreakSound($block));
                            $handled = true;
                            $this->stopTask($player, Position::fromObject($pos, $player->getWorld()));
                        }
                    } finally {
                        if ($handled) {
                            $event->cancel();
                        }
                    }
                }
            }
        }
    }

    private function scheduleTask(Position $pos, Item $item, Player $player, float $breakTime): void {
        $handler = $this->main->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($pos, $item, $player): void {
        $pos->getWorld()->useBreakOn($pos, $item, $player);
        unset($this->handlers[$player->getName()][$this->blockHash($pos)]);
        }), (int)floor($breakTime));
        if (!isset($this->handlers[$player->getName()])) {
         $this->handlers[$player->getName()] = [];
        }
        $this->handlers[$player->getName()][$this->blockHash($pos)] = $handler;
    }

    private function stopTask(Player $player, Position $pos): void {
        if (!isset($this->handlers[$player->getName()][$this->blockHash($pos)])) {
            return;
        }
        $handler = $this->handlers[$player->getName()][$this->blockHash($pos)];
        $handler->cancel();
        unset($this->handlers[$player->getName()][$this->blockHash($pos)]);
    }

    private function blockHash(Position $pos): string {
        return implode(":", [$pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld()->getFolderName()]);
    }*/
}