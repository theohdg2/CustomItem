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

use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use Refaltor\Natof\CustomItem\CustomItem;
use Refaltor\Natof\CustomItem\Items\AxeItem;
use Refaltor\Natof\CustomItem\Items\PickaxeItem;
use Refaltor\Natof\CustomItem\Items\ShovelItem;

class PlayerListener implements Listener
{
    /** @var CustomItem  */
    public CustomItem $main;

    public array $items;

    public function __construct(CustomItem $main)
    {
        $this->main = $main;
        $this->items = [];
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $player->getNetworkSession()->sendDataPacket($this->main->packet);
    }
}