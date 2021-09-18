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
use pocketmine\block\ItemFrame;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;
use Refaltor\Natof\CustomItem\CustomItem;
use Refaltor\Natof\CustomItem\Items\AxeItem;
use Refaltor\Natof\CustomItem\Items\PickaxeItem;
use Refaltor\Natof\CustomItem\Items\ShovelItem;

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
        $targets = $event->getTargets();
        foreach ($packets as $packet) {
            if ($packet instanceof StartGamePacket) {
                $packet->itemTable = $this->main->entries;
            }
        }
    }

    public function onPacketReceive(DataPacketReceiveEvent $event): void {
        $packet = $event->getPacket();
        if ($packet instanceof PlayerActionPacket) {
            $cancel = false;
            try {
                $pos = new Vector3($packet->x, $packet->y, $packet->z);
                $player = $event->getOrigin()->getPlayer();
                if ($packet->action === PlayerActionPacket::ACTION_START_BREAK) {
                    $item = $player->getInventory()->getItemInHand();
                    $class = get_class($item);
                    if (!in_array($class, [AxeItem::class, ShovelItem::class, PickaxeItem::class])) return;
                    if ($pos->distanceSquared($player->getPosition()) > 10000) return;
                    $target = $player->getWorld()->getBlock($pos);
                    $ev = new PlayerInteractEvent($player, $player->getInventory()->getItemInHand(), $target, null, $packet->face, PlayerInteractEvent::LEFT_CLICK_BLOCK);
                    if ($player->isSpectator()) $ev->cancel();
                    $ev->call();
                    if ($ev->isCancelled()) return;
                    $frame = $player->getWorld()->getBlock($pos);
                    if ($frame instanceof ItemFrame && !is_null($frame->getFramedItem())) {
                        if (lcg_value() <= $frame->getItemDropChance()) {
                            $player->getWorld()->dropItem($frame->getPos(), $frame->getFramedItem());
                        }
                        $frame->setFramedItem($item);
                        $frame->setItemRotation(0);
                        return;
                    }
                    $block = $target->getSide($packet->face);
                    if ($block->getId() === BlockLegacyIds::FIRE) {
                        $player->getWorld()->setBlock($block->getPos(), VanillaBlocks::AIR());
                        return;
                    }

                    if (!$player->isCreative()) {
                        $cancel = true;
                        $breakTime = ceil($target->getBreakInfo()->getBreakTime($player->getInventory()->getItemInHand()) * 20);
                        if ($breakTime > 0) {
                            if ($breakTime > 10) $breakTime -= 10;
                            $this->scheduleTask(Position::fromObject($pos, $player->getWorld()), $player->getInventory()->getItemInHand(), $player, $breakTime);
                            $pk = new LevelEventPacket();
                            $pk->data = LevelEventPacket::EVENT_BLOCK_START_BREAK;
                            $pk->position = $pos;
                            $pk->evid = (int)(65535 / $breakTime);
                            $player->getNetworkSession()->sendDataPacket($pk);
                        }
                    }
                } elseif ($packet->action === PlayerActionPacket::ACTION_ABORT_BREAK) {
                    $cancel = true;
                    $this->stopTask($player, Position::fromObject($pos, $player->getWorld()));
                }
            } finally {if ($cancel) $event->cancel();}
        }
    }

    private function scheduleTask(Position $pos, Item $item, Player $player, float $breakTime): void {
        $handler = $this->main->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($pos, $item, $player): void {
            $player->breakBlock($pos);
            unset($this->handlers[$player->getName()][implode(":", [$pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld()->getFolderName()])]);
        }), (int)floor($breakTime));
        if (!isset($this->handlers[$player->getName()])) $this->handlers[$player->getName()] = [];
        $this->handlers[$player->getName()][implode(":", [$pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld()->getFolderName()])] = $handler;
    }

    private function stopTask(Player $player, Position $pos): void {
        if (!isset($this->handlers[$player->getName()][implode(":", [$pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld()->getFolderName()])])) return;
        $handler = $this->handlers[$player->getName()][implode(":", [$pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld()->getFolderName()])];
        $handler->cancel();
        unset($this->handlers[$player->getName()][implode(":", [$pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld()->getFolderName()])]);
    }

}