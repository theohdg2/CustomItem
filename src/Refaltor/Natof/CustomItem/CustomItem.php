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
use pocketmine\item\Armor;
use pocketmine\item\Food;
use pocketmine\item\Item;
use pocketmine\item\TieredTool;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\plugin\PluginBase;
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
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\convert\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;

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

    /** @var ItemComponentPacket[] */
    private static array $components = [];

    /** @var ItemComponentPacket|null */
    public ?ItemComponentPacket $packet = null;

    /** @var array */
    private array $coreToNetValues = [];

    /** @var array */
    public array $entries = [];

    /** @var array */
    private array $simpleCoreToNetMapping = [];

    /** @var array */
    private array $simpleNetToCoreMapping = [];

    public function onLoad(): void
    {
        
    }

    /**
     * @throws Exception
     */
    public function onEnable(): void
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
     * @param Item
     * @param string $name
     * @return BasicItem
     */
    public static function createBasicitem(int $id, int $meta, string $name): BasicItem
    {
        return new BasicItem($id, $meta, $name);
    }

    /**
     * @param string $name
     * @return BootsItem
     */
    public static function createBootsItem(int $id, int $meta, string $name, int $defense, int $durability, string $texturePath): BootsItem
    {
        return new BootsItem($id, $meta, $name, $defense, $durability, $texturePath);
    }

    /**
     * @param string $name
     * @return LeggingsItem
     */
    public static function createLegginsItem(int $id, int $meta, string $name, int $defense, int $durability, string $texturePath): LeggingsItem
    {
        return new LeggingsItem($id, $meta, $name, $defense, $durability, $texturePath);
    }

    /**
     * @param string $name
     * @return ChestPlateItem
     */
    public static function createChestPlateItem(int $id, int $meta, string $name, int $defense, int $durability, string $texturePath): ChestPlateItem
    {
        return new ChestPlateItem($id, $meta, $name, $defense, $durability, $texturePath);
    }

    /**
     * @param string $name
     * @return HelmetItem
     */
    public static function createHelmetItem(int $id, int $meta, string $name, int $defense, int $durability, string $texturePath): HelmetItem
    {
        return new HelmetItem($id, $meta, $name, $defense, $durability, $texturePath);
    }


    /**
     * @param string $name
     * @param int $foodRestore
     * @param float $saturationRestore
     * @return FoodItem
     */
    public static function createFoodItem(int $id, int $meta, string $name, int $foodRestore, float $saturationRestore, string $group = 'todo'): FoodItem
    {
        return new FoodItem($id, $meta, $name, $foodRestore, $saturationRestore, $group);
    }

    /**
     * @param string $name
     * @param float $damage
     * @param float $durability
     * @return SwordItem
     */
    public static function createSword(int $id, int $meta, string $name, int $tier, int $damageSword, int $durability): SwordItem
    {
        return new SwordItem($id, $meta, $name, $tier, $damageSword, $durability);
    }


    /**
     * @param string $name
     * @param float $damage
     * @param float $durability
     * @param float $efficiency
     * @return AxeItem
     */
    public static function createAxe(int $id, int $meta, string $name, int $tier, float $damageTools, float $durability, float $efficiency): AxeItem
    {
        return new AxeItem($id, $meta, $name, $tier, $damageTools, $durability, $efficiency);
    }

    /**
     * @param string $name
     * @param float $damage
     * @param float $durability
     * @param float $efficiency
     * @return ShovelItem
     */
    public static function createShovel(int $id, int $meta, string $name, int $tier, float $damageTools, float $durability, float $efficiency): ShovelItem
    {
        return new ShovelItem($id, $meta, $name, $tier, $damageTools, $durability, $efficiency);
    }


    /**
     * @param string $name
     * @param float $damage
     * @param float $durability
     * @return HoeItem
     */
    public static function createHoe(int $id, int $meta, string $name, int $tier, float $damageTools, float $durability): HoeItem
    {
        return new HoeItem($id, $meta, $name, $tier, $damageTools, $durability);
    }

    /**
     * @param string $name
     * @param float $damage
     * @param float $durability
     * @param float $efficiency
     * @return PickaxeItem
     */
    public static function createPickaxe(int $id, int $meta, string $name, int $tier, float $damageTools, float $durability, float $efficiency): PickaxeItem
    {
        return new PickaxeItem($id, $meta, $name, $tier, $damageTools, $durability, $efficiency);
    }

    public static function registerItem(Item $item): void
    {
        $components = null;
        if ($item instanceof bootsItem || $item instanceof LeggingsItem || $item instanceof ChestPlateItem || $item instanceof HelmetItem) {
            $components =
                new CompoundTag("", [
                    new CompoundTag("components", [
                        new CompoundTag("item_properties", [
                            new IntTag("max_stack_size", 1),
                            new IntTag("use_duration", 32),
                            new IntTag("creative_category", $item->getCreativeCategory()),
                            new StringTag("creative_group", $item->getArmorGroup()),
                            new StringTag("enchantable_slot", $item->getArmorSlot())
                        ]),
                        new CompoundTag("minecraft:icon", [
                            new StringTag("texture", $item->getTexture())
                        ]),
                        new CompoundTag("minecraft:durability", [
                            new IntTag("max_durability", $item->getMaxStackSize())
                        ]),
                        new CompoundTag("minecraft:armor", [
                            new IntTag("protection", $item->getDefensePoints())
                        ]),
                        new CompoundTag("minecraft:wearable", [
                            new IntTag("slot", $item->getArmorSlot())
                        ]),
                        new ShortTag("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000)),
                        new CompoundTag("minecraft:display_name", [
                            new StringTag("value", $item->getName())
                        ])
                    ])
                ]);
        } elseif ($item instanceof BasicItem) {
            $components =
                new CompoundTag("", [
                    new CompoundTag("components", [
                        new CompoundTag("item_properties", [
                            new IntTag("max_stack_size", $item->getMaxStackSize())]),
                        new CompoundTag("minecraft:icon", [
                            new StringTag("texture", $item->getTexture())]),
                        new ShortTag("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000)),
                        new CompoundTag("minecraft:display_name", [
                            new StringTag("value", $item->getName())])
                    ])
                ]);
        } elseif ($item instanceof FoodItem) {
            $components =
                new CompoundTag("", [
                    new CompoundTag("components", [
                        new CompoundTag("item_properties", [
                            new IntTag("max_stack_size", $item->getMaxStackSize()),
                            new IntTag("use_duration", 32),
                            new IntTag("use_animation", 1),
                            new IntTag("creative_category", 3),
                            new StringTag("creative_group", "itemGroup.name.miscFood")]),
                        new CompoundTag("minecraft:icon", [
                            new StringTag("texture", $item->getTexture())]),
                        new CompoundTag("minecraft:food", [
                            new ByteTag("can_always_eat", 1),
                            new FloatTag("saturation_modifier", $item->getFoodRestore())
                        ]),
                        new ShortTag("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000)),
                        new CompoundTag("minecraft:display_name", [
                            new StringTag("value", $item->getName())])
                    ])
                ]);
        } elseif ($item instanceof SwordItem) {
            $components =
                new CompoundTag("", [
                    new CompoundTag("components", [
                        new CompoundTag("item_properties", [
                            new IntTag("max_stack_size", 1),
                            new ByteTag("hand_equipped", true),
                            new IntTag("damage", $item->getAttackPoints()),
                            new IntTag("creative_category", $item->getCreativeCategory()),
                            new StringTag("creative_group", "itemGroup.name.sword"),
                            new StringTag("enchantable_slot", "sword"),
                            new IntTag("enchantable_value", 10)
                        ]),
                        new CompoundTag("minecraft:weapon", [
                            new CompoundTag("on_hurt_entity", [
                                new StringTag("event", "event")
                            ])
                        ]),
                        new CompoundTag("minecraft:icon", [
                            new StringTag("texture", $item->getTexture())]),
                        new CompoundTag("minecraft:durability", [
                            new IntTag("max_durability", $item->getMaxStackSize())
                        ]),
                        new ShortTag("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000)),
                        new CompoundTag("minecraft:display_name", [
                            new StringTag("value", $item->getName())
                        ])
                    ])
                ]);
        } elseif ($item instanceof PickaxeItem) {
            $components =
                new CompoundTag("", [
                    new CompoundTag("components", [
                        new CompoundTag("item_properties", [
                            new IntTag("max_stack_size", 1),
                            new ByteTag("hand_equipped", true),
                            new IntTag("damage", $item->getAttackPoints()),
                            new IntTag("creative_category", $item->getCreativeCategory()),
                            new StringTag("creative_group", "itemGroup.name.pickaxe"),
                            new StringTag("enchantable_slot", "pickaxe"),
                            new IntTag("enchantable_value", 10)
                        ]),
                        new CompoundTag("minecraft:weapon", [
                            new CompoundTag("on_hurt_entity", [
                                new StringTag("event", "event")
                            ])
                        ]),
                        new CompoundTag("minecraft:icon", [
                            new StringTag("texture", $item->getTexture())]),
                        new CompoundTag("minecraft:durability", [
                            new IntTag("max_durability", $item->getMaxStackSize())
                        ]),
                        new ShortTag("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000)),
                        new CompoundTag("minecraft:display_name", [
                            new StringTag("value", $item->getName())
                        ])
                    ])
                ]);
        } elseif ($item instanceof AxeItem) {
            $components =
                new CompoundTag("", [
                    new CompoundTag("components", [
                        new CompoundTag("item_properties", [
                            new IntTag("max_stack_size", 1),
                            new ByteTag("hand_equipped", true),
                            new IntTag("damage", $item->getAttackPoints()),
                            new IntTag("creative_category", $item->getCreativeCategory()),
                            new StringTag("creative_group", "itemGroup.name.axe"),
                            new StringTag("enchantable_slot", "axe"),
                            new IntTag("enchantable_value", 10)
                        ]),
                        new CompoundTag("minecraft:weapon", [
                            new CompoundTag("on_hurt_entity", [
                                new StringTag("event", "event")
                            ])
                        ]),
                        new CompoundTag("minecraft:icon", [
                            new StringTag("texture", $item->getTexture())]),
                        new CompoundTag("minecraft:durability", [
                            new IntTag("max_durability", $item->getMaxStackSize())
                        ]),
                        new ShortTag("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000)),
                        new CompoundTag("minecraft:display_name", [
                            new StringTag("value", $item->getName())
                        ])
                    ])
                ]);
        } elseif ($item instanceof ShovelItem) {
            $components =
                new CompoundTag("", [
                    new CompoundTag("components", [
                        new CompoundTag("item_properties", [
                            new IntTag("max_stack_size", 1),
                            new ByteTag("hand_equipped", true),
                            new IntTag("damage", $item->getAttackPoints()),
                            new IntTag("creative_category", $item->getCreativeCategory()),
                            new StringTag("creative_group", "itemGroup.name.shovel"),
                            new StringTag("enchantable_slot", "shovel"),
                            new IntTag("enchantable_value", 10)
                        ]),
                        new CompoundTag("minecraft:weapon", [
                            new CompoundTag("on_hurt_entity", [
                                new StringTag("event", "event")
                            ])
                        ]),
                        new CompoundTag("minecraft:icon", [
                            new StringTag("texture", $item->getTexture())]),
                        new CompoundTag("minecraft:durability", [
                            new IntTag("max_durability", $item->getMaxStackSize())
                        ]),
                        new ShortTag("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000)),
                        new CompoundTag("minecraft:display_name", [
                            new StringTag("value", $item->getName())
                        ])
                    ])
                ]);
        } elseif ($item instanceof HoeItem) {
            $components =
                new CompoundTag("", [
                    new CompoundTag("components", [
                        new CompoundTag("item_properties", [
                            new IntTag("max_stack_size", 1),
                            new ByteTag("hand_equipped", true),
                            new IntTag("damage", $item->getAttackPoints()),
                            new IntTag("creative_category", $item->getCreativeCategory()),
                            new StringTag("creative_group", "itemGroup.name.hoe"),
                            new StringTag("enchantable_slot", "hoe"),
                            new IntTag("enchantable_value", 10)
                        ]),
                        new CompoundTag("minecraft:weapon", [
                            new CompoundTag("on_hurt_entity", [
                                new StringTag("event", "event")
                            ])
                        ]),
                        new CompoundTag("minecraft:icon", [
                            new StringTag("texture", $item->getTexture())]),
                        new CompoundTag("minecraft:durability", [
                            new IntTag("max_durability", $item->getMaxStackSize())
                        ]),
                        new ShortTag("minecraft:identifier", $item->getId() + ($item->getId() > 0 ? 5000 : -5000)),
                        new CompoundTag("minecraft:display_name", [
                            new StringTag("value", $item->getName())
                        ])
                    ])
                ]);
        }
        array_push(self::$components, new ItemComponentPacketEntry('minecraft:' . $item->getName(), $components));
        array_push(self::$queue, $item);
    }
}