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
use pocketmine\block\BlockToolType;
use pocketmine\block\ItemFrame;
use pocketmine\block\RedstoneOre;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wood;
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
    /** @var CustomItem */
    public CustomItem $main;

    /** @var array */
    public array $handlers = [];

    public function __construct(CustomItem $main)
    {
        $this->main = $main;
    }

    public function onPacketSend(DataPacketSendEvent $event)
    {
        $packets = $event->getPacket();
        $targets = $event->getPlayer();
        if ($packets instanceof StartGamePacket) {
            $packets->itemTable = $this->main->entries;
        }
    }

}