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

namespace Refaltor\Natof\CustomItem;

use Exception;
use pocketmine\block\Block;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use Refaltor\Natof\CustomItem\Interfaces\CustomInterface;
use Refaltor\Natof\CustomItem\Items\ArmorItem;
use Refaltor\Natof\CustomItem\Items\BasicItem;
use Refaltor\Natof\CustomItem\Items\FoodItem;
use Refaltor\Natof\CustomItem\Traits\UtilsTrait;

class CustomItem extends PluginBase
{
    use UtilsTrait;

    const ITEM_BASIC = 'CustomItem:identifier:BasicItem';
    const ITEM_FOOD = 'CustomItem:identifier:FoodItem';
    const ITEM_PICKAXE = 'CustomItem:identifier:ToolItem.pickaxe';
    const ITEM_SHOVEL = 'CustomItem:identifier:ToolItem.shovel';
    const ITEM_HOE = 'CustomItem:identifier:ToolItem.hoe';
    const ITEM_SWORD = 'CustomItem:identifier:ToolItem.sword';
    const ITEM_AXE = 'CustomItem:identifier:ToolItem.axe';

    /** @var array */
    private static array $queue = [];

    /** @var ItemComponentPacketEntry[] */
    private static array $components = [];

    /** @var ItemComponentPacket|null  */
    public ?ItemComponentPacket $packet = null;

    /** @var array  */
    private array $coreToNetValues = [];

    /** @var array */
    public array $entries = [];

    /** @var array */
    private array $simpleCoreToNetMapping = [];

    /** @var array */
    private array $simpleNetToCoreMapping = [];

    protected function onLoad(): void
    {
        $item = self::createBasicItem(new ItemIdentifier(1000, 0), 'apple');
        $item->setTexture('apple');
        $item->setInteractOnBlockListener(function (Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector)
        {
            $player->sendMessage('<block>: arete de me taper');
        });
        self::registerItem($item);
    }

    /**
     * @throws Exception
     */
    protected function onEnable(): void
    {
        $this->start($this);
    }

    /*
     * ---------------------------------
     *  _______  _______ _________
     * (  ___  )(  ____ )\__   __/
     * | (   ) || (    )|   ) (
     * | (___) || (____)|   | |
     * |  ___  ||  _____)   | |
     * | (   ) || (         | |
     * | )   ( || )      ___) (___
     * |/     \||/       \_______/
     * ---------------------------------
     */


    public static function createBasicItem(ItemIdentifier $itemIdentifier, string $name): BasicItem {
        return new BasicItem($itemIdentifier, $name);
    }

    public static function createArmorItem(ItemIdentifier $itemIdentifier, ArmorTypeInfo $armorTypeInfo, string $name): ArmorItem {
        return new ArmorItem($itemIdentifier, $armorTypeInfo, $name);
    }

    public static function createFoodItem(ItemIdentifier $itemIdentifier, string $name, int $foodRestore, float $saturationRestore): FoodItem {
        return new FoodItem($itemIdentifier, $name, $foodRestore, $saturationRestore);
    }

    public static function registerItem(Item $item): void {
        $components = null;
        if ($item instanceof ArmorItem) {
            $components = CompoundTag::create()
                ->setTag("components", CompoundTag::create()
                    ->setTag("item_properties", CompoundTag::create()
                        ->setInt("max_stack_size", 1)
                        ->setInt("use_duration", 32)
                        ->setInt("creative_category", 3)
                        ->setString("creative_group", "itemGroup.name.helmet")
                        ->setString("enchantable_slot", $item->getArmorSlot())
                        ->setInt("enchantable_value", 10)
                    )
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", 'apple')
                    )
                    ->setTag("minecraft:durability", CompoundTag::create()
                        ->setInt("max_durability", $item->getMaxDurability())
                    )
                    ->setTag("minecraft:armor", CompoundTag::create()
                        ->setInt("protection", $item->getDefensePoints())
                    )
                    ->setTag("minecraft:wearable", CompoundTag::create()
                        ->setInt("slot", $item->getArmorSlot())
                    )
                    ->setShort("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000))
                    ->setTag("minecraft:display_name", CompoundTag::create()
                        ->setString("value", $item->getName())
                    )
                );
        } elseif ($item instanceof BasicItem) {
            $components = CompoundTag::create()
                ->setTag("components", CompoundTag::create()
                    ->setTag("item_properties", CompoundTag::create()
                        ->setInt("max_stack_size", $item->getMaxStackSize())
                    )
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $item->getTexture())
                    )
                    ->setShort("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000))
                    ->setTag("minecraft:display_name", CompoundTag::create()
                        ->setString("value", $item->getName())
                    )
                );
        } elseif ($item instanceof FoodItem) {
            $components = CompoundTag::create()
                ->setTag("components", CompoundTag::create()
                    ->setTag("item_properties", CompoundTag::create()
                        ->setInt("max_stack_size", $item->getMaxStackSize())
                        ->setInt("use_duration", 32)
                        ->setInt("use_animation", 0)
                    )
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $item->getTexture())
                    )
                    ->setTag('minecraft:food', CompoundTag::create()
                        ->setByte('can_always_eat', 1)
                        ->setFloat('nutrition', $item->getFoodRestore())
                        ->setString('saturation_modifier', 'low')
                    )
                    ->setShort("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000))
                    ->setTag("minecraft:display_name", CompoundTag::create()
                        ->setString("value", $item->getName())
                    )
                );
        }
        array_push(self::$components, new ItemComponentPacketEntry('minecraft:' . $item->getName(), $components));
        array_push(self::$queue, $item);
    }
}