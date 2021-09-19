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
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use Refaltor\Natof\CustomItem\CustomItem;
use Refaltor\Natof\CustomItem\Items\FoodItem;

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

    public function onConsume(PlayerItemConsumeEvent $event) {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if ($item instanceof FoodItem) {
            $item = $item->setCount($item->getCount() - 1);
            $foodRestore = $item->getFoodRestore();
            $saturation = $item->getSaturationRestore();
            $player->getHungerManager()->addFood($foodRestore);
            $player->getHungerManager()->addSaturation($saturation);
            if ($item->getCount() <= 0) {
                $player->getInventory()->setItemInHand(ItemFactory::air());
            }
        }
    }
}