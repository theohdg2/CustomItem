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
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\plugin\PluginBase;
use Refaltor\Natof\CustomItem\Items\ArmorItem;
use Refaltor\Natof\CustomItem\Items\AxeItem;
use Refaltor\Natof\CustomItem\Items\BasicItem;
use Refaltor\Natof\CustomItem\Items\BootsItem;
use Refaltor\Natof\CustomItem\Items\ChestPlateItem;
use Refaltor\Natof\CustomItem\Items\FoodItem;
use Refaltor\Natof\CustomItem\Items\HelmetItem;
use Refaltor\Natof\CustomItem\Items\HoeItem;
use Refaltor\Natof\CustomItem\Items\LeggingsItem;
use Refaltor\Natof\CustomItem\Items\PickaxeItem;
use Refaltor\Natof\CustomItem\Items\ShovelItem;
use Refaltor\Natof\CustomItem\Items\SwordItem;
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


    /**
     * @param ItemIdentifier $itemIdentifier
     * @param string $name
     * @return BasicItem
     */
    public static function createBasicItem(ItemIdentifier $itemIdentifier, string $name): BasicItem {
        return new BasicItem($itemIdentifier, $name);
    }


    /**
     * @param ItemIdentifier $identifier
     * @param ArmorTypeInfo $armorTypeInfo
     * @param string $name
     * @return BootsItem
     */
    public static function createBootsItem(ItemIdentifier $identifier, ArmorTypeInfo $armorTypeInfo, string $name) : BootsItem {
        return new BootsItem($identifier, new ArmorTypeInfo($armorTypeInfo->getDefensePoints(), $armorTypeInfo->getMaxDurability(), 3), $name);
    }


    /**
     * @param ItemIdentifier $identifier
     * @param ArmorTypeInfo $armorTypeInfo
     * @param string $name
     * @return LeggingsItem
     */
    public static function createLeggingsItem(ItemIdentifier $identifier, ArmorTypeInfo $armorTypeInfo, string $name) : LeggingsItem{
        return new LeggingsItem($identifier, new ArmorTypeInfo($armorTypeInfo->getDefensePoints(), $armorTypeInfo->getMaxDurability(), 2), $name);
    }


    /**
     * @param ItemIdentifier $identifier
     * @param ArmorTypeInfo $armorTypeInfo
     * @param string $name
     * @return ChestPlateItem
     */
    public static function createChestPlateItem(ItemIdentifier $identifier, ArmorTypeInfo $armorTypeInfo, string $name) : ChestPlateItem{
        return new ChestPlateItem($identifier, new ArmorTypeInfo($armorTypeInfo->getDefensePoints(), $armorTypeInfo->getMaxDurability(), 1), $name);
    }


    /**
     * @param ItemIdentifier $identifier
     * @param ArmorTypeInfo $armorTypeInfo
     * @param string $name
     * @return HelmetItem
     */
    public static function createHelmetItem(ItemIdentifier $identifier, ArmorTypeInfo $armorTypeInfo, string $name) : HelmetItem{
        return new HelmetItem($identifier, new ArmorTypeInfo($armorTypeInfo->getDefensePoints(), $armorTypeInfo->getMaxDurability(), 0), $name);
    }


    /**
     * @param ItemIdentifier $itemIdentifier
     * @param ArmorTypeInfo $armorTypeInfo
     * @param string $name
     * @return ArmorItem
     */
    public static function createArmorItem(ItemIdentifier $itemIdentifier, ArmorTypeInfo $armorTypeInfo, string $name): ArmorItem {
        return new ArmorItem($itemIdentifier, $armorTypeInfo, $name);
    }


    /**
     * @param ItemIdentifier $itemIdentifier
     * @param string $name
     * @param int $foodRestore
     * @param float $saturationRestore
     * @return FoodItem
     */
    public static function createFoodItem(ItemIdentifier $itemIdentifier, string $name, int $foodRestore, float $saturationRestore): FoodItem {
        return new FoodItem($itemIdentifier, $name, $foodRestore, $saturationRestore, 'aaaa');
    }


    /**
     * @param ItemIdentifier $itemIdentifier
     * @param string $name
     * @param float $damage
     * @param float $durability
     * @return SwordItem
     */
    public static function createSword(ItemIdentifier $itemIdentifier, string $name, float $damage, float $durability): SwordItem{
        return new SwordItem($itemIdentifier, $name, ToolTier::DIAMOND(), $damage, $durability);
    }


    /**
     * @param ItemIdentifier $itemIdentifier
     * @param string $name
     * @param float $damage
     * @param float $durability
     * @param float $efficiency
     * @return AxeItem
     */
    public static function createAxe(ItemIdentifier $itemIdentifier, string $name, float $damage, float $durability, float $efficiency): AxeItem{
        return new AxeItem($itemIdentifier, $name, ToolTier::IRON(), $damage, $durability, $efficiency);
    }


    /**
     * @param ItemIdentifier $itemIdentifier
     * @param string $name
     * @param float $damage
     * @param float $durability
     * @param float $efficiency
     * @return ShovelItem
     */
    public static function createShovel(ItemIdentifier $itemIdentifier, string $name, float $damage, float $durability, float $efficiency): ShovelItem{
        return new ShovelItem($itemIdentifier, $name, ToolTier::DIAMOND(), $damage, $durability, $efficiency);
    }


    /**
     * @param ItemIdentifier $itemIdentifier
     * @param string $name
     * @param float $damage
     * @param float $durability
     * @return HoeItem
     */
    public static function createHoe(ItemIdentifier $itemIdentifier, string $name, float $damage, float $durability): HoeItem{
        return new HoeItem($itemIdentifier, $name, ToolTier::DIAMOND(), $damage, $durability);
    }


    /**
     * @param ItemIdentifier $itemIdentifier
     * @param string $name
     * @param float $damage
     * @param float $durability
     * @param float $efficiency
     * @return PickaxeItem
     */
    public static function createPickaxe(ItemIdentifier $itemIdentifier, string $name, float $damage, float $durability, float $efficiency): PickaxeItem{
        return new PickaxeItem($itemIdentifier, $name, ToolTier::DIAMOND(), $damage, $durability, $efficiency);
    }

    public static function createFood(ItemIdentifier $itemIdentifier, string $name, int $foodRestore, float $saturationRestore, string $group){
        return new FoodItem($itemIdentifier, $name, $foodRestore, $saturationRestore, $group = "todo");
    }


    public static function registerItem(Item $item): void {
        $components = null;
        if ($item instanceof bootsItem || $item instanceof LeggingsItem || $item instanceof ChestPlateItem || $item instanceof HelmetItem) {
            $components = CompoundTag::create()
                ->setTag("components", CompoundTag::create()
                    ->setTag("item_properties", CompoundTag::create()
                        ->setInt("max_stack_size", 1)
                        ->setInt("use_duration", 32)
                        ->setInt("creative_category", $item->getCreativeCategory())
                        ->setString("creative_group", $item->getArmorGroup())
                        ->setString("enchantable_slot", $item->getArmorSlot())
                        ->setInt("enchantable_value", 10)
                    )
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $item->getTexture())
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
                        ->setInt("use_animation", 1)
                        ->setInt("creative_category", 3)
                        ->setString("creative_group", "itemGroup.name.miscFood")
                    )
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $item->getTexture())
                    )
                    ->setTag('minecraft:food', CompoundTag::create()
                        ->setByte('can_always_eat', 1)
                        ->setFloat('nutrition', $item->getFoodRestore())
                        ->setString('saturation_modifier', 'higth')
                    )
                    ->setShort("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000))
                    ->setTag("minecraft:display_name", CompoundTag::create()
                        ->setString("value", $item->getName())
                    )
                );
        } elseif ($item instanceof SwordItem){
            $components = CompoundTag::create()
                ->setTag("components", CompoundTag::create()
                    ->setTag("item_properties", CompoundTag::create()
                        ->setInt("max_stack_size", 1)
                        ->setByte("hand_equipped", true)
                        ->setInt("damage", $item->getAttackPoints())
                        ->setInt("creative_category", $item->getCreativeCategory())
                        ->setString("creative_group", "itemGroup.name.sword")
                        ->setString("enchantable_slot", "sword")
                        ->setInt("enchantable_value", 10)
                    )
                    ->setTag("minecraft:weapon", CompoundTag::create()
                        ->setTag("on_hurt_entity", CompoundTag::create()
                            ->setString("event", "event")
                        )
                    )
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $item->getTexture())
                    )
                    ->setTag("minecraft:durability", CompoundTag::create()
                        ->setInt("max_durability", $item->getMaxDurability())
                    )
                    ->setShort("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000))
                    ->setTag("minecraft:display_name", CompoundTag::create()
                        ->setString("value", $item->getName())
                    )
                );

        } elseif ($item instanceof PickaxeItem){
            $components = CompoundTag::create()
                ->setTag("components", CompoundTag::create()
                    ->setTag("item_properties", CompoundTag::create()
                        ->setInt("max_stack_size", 1)
                        ->setByte("hand_equipped", true)
                        ->setInt("damage", $item->getAttackPoints())
                        ->setInt("creative_category", $item->getCreativeCategory())
                        ->setString("creative_group", "itemGroup.name.pickaxe")
                        ->setString("enchantable_slot", "pickaxe")
                        ->setInt("enchantable_value", 10)
                    )
                    ->setTag("minecraft:weapon", CompoundTag::create()
                        ->setTag("on_hurt_entity", CompoundTag::create()
                            ->setString("event", "event")
                        )
                    )
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $item->getTexture())
                    )
                    ->setTag("minecraft:durability", CompoundTag::create()
                        ->setInt("max_durability", $item->getMaxDurability())
                    )
                    ->setShort("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000))
                    ->setTag("minecraft:display_name", CompoundTag::create()
                        ->setString("value", $item->getName())
                    )
                );
        } elseif ($item instanceof AxeItem){
            $components = CompoundTag::create()
                ->setTag("components", CompoundTag::create()
                    ->setTag("item_properties", CompoundTag::create()
                        ->setInt("max_stack_size", 1)
                        ->setByte("hand_equipped", true)
                        ->setInt("damage", $item->getAttackPoints())
                        ->setInt("creative_category", $item->getCreativeCategory())
                        ->setString("creative_group", "itemGroup.name.axe")
                        ->setString("enchantable_slot", "axe")
                        ->setInt("enchantable_value", 10)
                    )
                    ->setTag("minecraft:weapon", CompoundTag::create()
                        ->setTag("on_hurt_entity", CompoundTag::create()
                            ->setString("event", "event")
                        )
                    )
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $item->getTexture())
                    )
                    ->setTag("minecraft:durability", CompoundTag::create()
                        ->setInt("max_durability", $item->getMaxDurability())
                    )
                    ->setShort("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000))
                    ->setTag("minecraft:display_name", CompoundTag::create()
                        ->setString("value", $item->getName())
                    )
                );
        } elseif ($item instanceof ShovelItem){
            $components = CompoundTag::create()
                ->setTag("components", CompoundTag::create()
                    ->setTag("item_properties", CompoundTag::create()
                        ->setInt("max_stack_size", 1)
                        ->setByte("hand_equipped", true)
                        ->setInt("damage", $item->getAttackPoints())
                        ->setInt("creative_category", $item->getCreativeCategory())
                        ->setString("creative_group", "itemGroup.name.shovel")
                        ->setString("enchantable_slot", "shovel")
                        ->setInt("enchantable_value", 10)
                    )
                    ->setTag("minecraft:weapon", CompoundTag::create()
                        ->setTag("on_hurt_entity", CompoundTag::create()
                            ->setString("event", "event")
                        )
                    )
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $item->getTexture())
                    )
                    ->setTag("minecraft:durability", CompoundTag::create()
                        ->setInt("max_durability", $item->getMaxDurability())
                    )
                    ->setShort("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000))
                    ->setTag("minecraft:display_name", CompoundTag::create()
                        ->setString("value", $item->getName())
                    )
                );
        }   elseif ($item instanceof HoeItem){
            $components = CompoundTag::create()
                ->setTag("components", CompoundTag::create()
                    ->setTag("item_properties", CompoundTag::create()
                        ->setInt("max_stack_size", 1)
                        ->setByte("hand_equipped", true)
                        ->setInt("damage", $item->getAttackPoints())
                        ->setInt("creative_category", $item->getCreativeCategory())
                        ->setString("creative_group", "itemGroup.name.hoe")
                        ->setString("enchantable_slot", "hoe")
                        ->setInt("enchantable_value", 10)
                    )
                    ->setTag("minecraft:weapon", CompoundTag::create()
                        ->setTag("on_hurt_entity", CompoundTag::create()
                            ->setString("event", "event")
                        )
                    )
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $item->getTexture())
                    )
                    ->setTag("minecraft:durability", CompoundTag::create()
                        ->setInt("max_durability", $item->getMaxDurability())
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