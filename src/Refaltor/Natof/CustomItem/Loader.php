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

use pocketmine\block\Block;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\plugin\PluginBase;
use Refaltor\Natof\CustomItem\Events\Listeners\PacketListener;
use Refaltor\Natof\CustomItem\Items\BasicItem;
use ReflectionObject;
use const pocketmine\RESOURCE_PATH;

class Loader extends PluginBase
{
    /** @var array */
    public array $items = [];

    /** @var ItemComponentPacket|null  */
    public ?ItemComponentPacket $packet = null;

    /** @var array */
    public array $coreToNetValues = [];

    /** @var array  */
    public array $netToCoreValues = [];

    /** @var array  */
    public array $entries = [];

    /** @var array  */
    public array $simpleCoreToNetMapping = [];

    /** @var array  */
    public array $simpleNetToCoreMapping = [];


    protected function onLoad(): void
    {
        $item = new BasicItem(new ItemIdentifier(1000, 0), 'apple');
        $item->setTextureName('apple');
        $this->registerItem($item);
    }

    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new PacketListener($this), $this);
        $packets = [];
        $array = ["r16_to_current_item_map" => ["simple" => []], "item_id_map" => [], "required_item_list" => []];
        foreach ($this->items as $itemInString => $components) {
            $item = BasicItem::itemFromString($itemInString);
            $id = $item->getItemIdentifier()->getId();
            $packets[] = new ItemComponentPacketEntry('minecraft:' . $item->getName(), $components);
            $array['r16_to_current_item_map']['simple']['minecraft:' . $item->getName()] = 'minecraft:custom_' . $item->getName();
            $array['item_id_map']['minecraft:' . $item->getName()] = $id;
            $array['required_item_list']['minecraft:' . $item->getName()] = ["runtime_id" => $id + ($id > 0 ? 5000 : -5000), "component_based" => true];
            $item = new Item($item->getItemIdentifier(), $item->getName());
            ItemFactory::getInstance()->register($item);
            CreativeInventory::getInstance()->add($item);
        }
        $data = file_get_contents(RESOURCE_PATH . '/vanilla/r16_to_current_item_map.json');
        $json = json_decode($data, true);
        $add = $array['r16_to_current_item_map'];
        $json["simple"] = array_merge($json["simple"], $add["simple"]);
        $legacyStringToIntMapRaw = file_get_contents(RESOURCE_PATH . '/vanilla/item_id_map.json');
        $add = $array["item_id_map"];
        $legacyStringToIntMap = json_decode($legacyStringToIntMapRaw, true);
        $legacyStringToIntMap = array_merge($add, $legacyStringToIntMap);
        $simpleMappings = [];
        foreach($json["simple"] as $oldId => $newId){
            $simpleMappings[$newId] = $legacyStringToIntMap[$oldId];
        }
        foreach($legacyStringToIntMap as $stringId => $intId){
            $simpleMappings[$stringId] = $intId;
        }
        $complexMappings = [];
        foreach($json["complex"] as $oldId => $map){
            foreach($map as $meta => $newId){
                $complexMappings[$newId] = [$legacyStringToIntMap[$oldId], (int) $meta];
            }
        }


        $old = json_decode(file_get_contents(RESOURCE_PATH  . '/vanilla/required_item_list.json'), true);
        $add = $array["required_item_list"];
        $table = array_merge($old, $add);
        $params = [];
        foreach($table as $name => $entry){
            $params[] = new ItemTypeEntry($name, $entry["runtime_id"], $entry["component_based"]);
        }
        $this->entries = $entries = (new ItemTypeDictionary($params))->getEntries();
        foreach($entries as $entry){
            $stringId = $entry->getStringId();
            $netId = $entry->getNumericId();
            if (isset($complexMappings[$stringId])){
            }elseif(isset($simpleMappings[$stringId])){
                $this->simpleCoreToNetMapping[$simpleMappings[$stringId]] = $netId;
                $this->simpleNetToCoreMapping[$netId] = $simpleMappings[$stringId];
            }
        }
        $this->packet = ItemComponentPacket::create($packets);

        $instance = ItemTranslator::getInstance();
        $ref = new ReflectionObject($instance);
        $r1 = $ref->getProperty("simpleCoreToNetMapping");
        $r2 = $ref->getProperty("simpleNetToCoreMapping");
        $r1->setAccessible(true);
        $r2->setAccessible(true);
        $r1->setValue($instance, $this->simpleCoreToNetMapping);
        $r2->setValue($instance, $this->simpleNetToCoreMapping);
    }


    protected function onDisable(): void
    {

    }


    /*
     *  ________  ________  ___
     * |\   __  \|\   __  \|\  \
     * \ \  \|\  \ \  \|\  \ \  \
     *  \ \   __  \ \   ____\ \  \
     *   \ \  \ \  \ \  \___|\ \  \
     *    \ \__\ \__\ \__\    \ \__\
     *     \|__|\|__|\|__|     \|__|
     */


    public function registerItem($item): void
    {

        if ($item instanceof BasicItem) {
            $components = CompoundTag::create()
                ->setTag("components", CompoundTag::create()
                    ->setTag("item_properties", CompoundTag::create()
                        ->setInt("max_stack_size", 1)
                        ->setInt("damage", 10)
                        ->setInt("creative_category", 3)
                        ->setString("enchantable_slot", "armor_head")
                        ->setInt("enchantable_value", 10)
                        ->setString("creative_group", "itemGroup.name.helmet")
                    )
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $item->getTextureName())
                    )
                    /*->setTag("minecraft:repairable", CompoundTag::create()
                        ->setTag("repair_items", CompoundTag::create()
                            ->setString("items","minecraft:stick")
                            ->setInt("repair_amount", 50))
                    )*/
                    /*->setTag("minecraft:weapon", CompoundTag::create()
                        ->setTag("on_hurt_entity", CompoundTag::create()
                            ->setString("event", "foo:drill")))*/
                    ->setTag("minecraft:durability", CompoundTag::create()
                        ->setInt("max_durability", 600)
                    )
                )
                ->setTag("minecraft:armor", CompoundTag::create()
                    ->setInt("protection", 5)
                )
                ->setTag("minecraft:wearable", CompoundTag::create()
                    ->setInt("slot", 0))

                ->setShort("minecraft:identifier", $item->getItemIdentifier()->getId() + ($item->getItemIdentifier()->getId() > 0 ? 5000 : -5000))
                ->setTag("minecraft:display_name", CompoundTag::create()
                    ->setString("value", $item->getName())

                    /*->setTag('minecraft:on_use_on', CompoundTag::create()
                        ->setByte('on_use_on', 1)
                    )*/
                    ->setTag('minecraft:use_duration', CompoundTag::create()
                        ->setByte('value', 1)
                    )
                );
            $this->items[$item->toString()] = $components;
        }
    }
}