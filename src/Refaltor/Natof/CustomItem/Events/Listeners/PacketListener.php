<?php


namespace Refaltor\Natof\CustomItem\Events\Listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use Refaltor\Natof\CustomItem\Loader;

class PacketListener implements Listener
{
    /** @var Loader  */
    public Loader $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    public function onPacketSendEvent(DataPacketSendEvent $event): void {
        $packets = $event->getPackets();
        foreach ($packets as $packet) {
            if ($packet instanceof StartGamePacket) {
                $packet->itemTable = $this->loader->entries;
            }
        }
    }


    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $player->getNetworkSession()->sendDataPacket($this->loader->packet);
    }
}