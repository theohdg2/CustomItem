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

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\Pickaxe;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use Refaltor\Natof\CustomItem\CustomItem;

class PacketListener implements Listener
{
    /** @var CustomItem  */
    public CustomItem $main;

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

    public function onPacketReceive(DataPacketReceiveEvent $event) {
        $packet = $event->getPacket();
        if ($packet instanceof PlayerActionPacket) {
            $player = $event->getOrigin()->getPlayer();
            $action = $packet->action;
            if ($action === PlayerActionPacket::ACTION_START_BREAK) {
                $item = $player->getInventory()->getItemInHand();
                if ($item instanceof Pickaxe) {
                }
            }
        }
    }
}