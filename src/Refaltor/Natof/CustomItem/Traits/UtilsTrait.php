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

namespace Refaltor\Natof\CustomItem\Traits;

use Exception;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use Refaltor\Natof\CustomItem\Events\Listeners\PacketListener;
use Refaltor\Natof\CustomItem\Events\Listeners\PlayerListener;
use Refaltor\Natof\CustomItem\CustomItem;
use ReflectionException;
use ReflectionObject;
use const pocketmine\RESOURCE_PATH;

trait UtilsTrait
{
    /**
     * @param CustomItem $customItem
     */
    private function registerEvents(CustomItem $customItem): void {
        $events = [new PacketListener($customItem), new PlayerListener($customItem)];
        foreach ($events as $event) $customItem->getServer()->getPluginManager()->registerEvents($event, $customItem);
    }

    /**
     * @param CustomItem $customItem
     * @throws ReflectionException
     */
    private function loadDataFiles(CustomItem $customItem): void {
        $array = ["r16_to_current_item_map" => ["simple" => []], "item_id_map" => [], "required_item_list" => []];
        foreach (self::$queue as $item) {
            $array['r16_to_current_item_map']['simple']['minecraft:' . $item->getName()] = 'minecraft:' . $item->getName();
            $array['item_id_map']['minecraft:' . $item->getName()] = $item->getId();
            $array['required_item_list']['minecraft:' . $item->getName()] = ["runtime_id" => $item->getId() + ($item->getId() > 0 ? 5000 : -5000), "component_based" => true];
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
        foreach ($json["simple"] as $oldId => $newId) {
            $simpleMappings[$newId] = $legacyStringToIntMap[$oldId];
        }
        foreach ($legacyStringToIntMap as $stringId => $intId) {
            $simpleMappings[$stringId] = $intId;
        }
        $complexMappings = [];
        foreach ($json["complex"] as $oldId => $map) {
            foreach ($map as $meta => $newId) {
                $complexMappings[$newId] = [$legacyStringToIntMap[$oldId], (int)$meta];
            }
        }


        $old = json_decode(file_get_contents(RESOURCE_PATH . '/vanilla/required_item_list.json'), true);
        $add = $array["required_item_list"];
        $table = array_merge($old, $add);
        $params = [];
        foreach ($table as $name => $entry) {
            $params[] = new ItemTypeEntry($name, $entry["runtime_id"], $entry["component_based"]);
        }
        $customItem->entries = $entries = (new ItemTypeDictionary($params))->getEntries();
        foreach ($entries as $entry) {
            $stringId = $entry->getStringId();
            $netId = $entry->getNumericId();
            if (isset($complexMappings[$stringId])) {
            } elseif (isset($simpleMappings[$stringId])) {
                $customItem->simpleCoreToNetMapping[$simpleMappings[$stringId]] = $netId;
                $customItem->simpleNetToCoreMapping[$netId] = $simpleMappings[$stringId];
            }
        }
        foreach (self::$queue as $item) {
            if (!ItemFactory::getInstance()->isRegistered($item->getId())) {
                ItemFactory::getInstance()->register($item);
                CreativeInventory::getInstance()->add($item);
            } else {
                ItemFactory::getInstance()->register($item, true);
                CreativeInventory::getInstance()->add($item);
            }
        }
        $instance = ItemTranslator::getInstance();
        $ref = new ReflectionObject($instance);
        $r1 = $ref->getProperty("simpleCoreToNetMapping");
        $r2 = $ref->getProperty("simpleNetToCoreMapping");
        $r1->setAccessible(true);
        $r2->setAccessible(true);
        $r1->setValue($instance, $customItem->simpleCoreToNetMapping);
        $r2->setValue($instance, $customItem->simpleNetToCoreMapping);
    }

    public function loadConfigFile(){
        $config = $this->getConfig();
        foreach ($config->get("item") as $item => $key) {
            switch ($item){
                case "sword":
                    $sword = CustomItem::createSword(new ItemIdentifier($key["id"], 0), $key["name"], $key["damage"], $key["durability"]);
                    $sword->setTexture($key["texture"]);

                    CustomItem::registerItem($sword);
                    break;
                case "boots":
                    $boots = CustomItem::createBootsItem(new ItemIdentifier($key["id"], 0), new ArmorTypeInfo($key["defense"], $key["durability"], 0), $key["name"]);
                    $boots->setTexture($key["texture"]);

                    CustomItem::registerItem($boots);
                    break;
                case "leggings":
                    $leggings = CustomItem::createLeggingsItem(new ItemIdentifier($key["id"], 0), new ArmorTypeInfo($key["defense"], $key["durability"], 1), $key["name"]);
                    $leggings->setTexture($key["texture"]);

                    CustomItem::registerItem($leggings);
                    break;
                case "chestplate":
                    $chestplate = CustomItem::createChestPlateItem(new ItemIdentifier($key["id"], 0), new ArmorTypeInfo($key["defense"], $key["durability"], 2), $key["name"]);
                    $chestplate->setTexture($key["texture"]);

                    CustomItem::registerItem($chestplate);
                    break;
                case "helmet":
                    $helmet = CustomItem::createHelmetItem(new ItemIdentifier($key["id"], 0), new ArmorTypeInfo($key["defense"], $key["durability"], 3), $key["name"]);
                    $helmet->setTexture($key["texture"]);

                    CustomItem::registerItem($helmet);
                    break;
                case "pickaxe":
                    $pickaxe = CustomItem::createPickaxe(new ItemIdentifier($key["id"], 0), $key["name"], $key["damage"], $key["durability"], $key["efficiency"]);
                    $pickaxe->setTexture($key["texture"]);

                    CustomItem::registerItem($pickaxe);
                    break;
                case "hoe":
                    $hoe = CustomItem::createHoe(new ItemIdentifier($key["id"], 0), $key["name"], $key["damage"], $key["durability"]);
                    $hoe->setTexture($key["texture"]);

                    CustomItem::registerItem($hoe);
                    break;
                case "shovel":
                    $shovel = CustomItem::createShovel(new ItemIdentifier($key["id"], 0), $key["name"], $key["damage"], $key["durability"], $key["efficiency"]);
                    $shovel->setTexture($key["texture"]);

                    CustomItem::registerItem($shovel);
                    break;
                case "axe":
                    $axe = CustomItem::createAxe(new ItemIdentifier($key["id"], 0), $key["name"], $key["damage"], $key["durability"], $key["efficiency"]);
                    $axe->setTexture($key["texture"]);

                    CustomItem::registerItem($axe);
                    break;
                case "food":
                    $food = CustomItem::createFood(new ItemIdentifier($key["id"], 0), $key["name"], $key["restore"], $key["saturation"], "aaaa");
                    $food->setTexture($key["texture"]);

                    CustomItem::registerItem($food);
                    break;
            }
        }
    }

    /**
     * @param CustomItem $customItem
     * @throws Exception
     */
    public function start(CustomItem $customItem): void{
        $this->saveConfig();
        $this->loadConfigFile();
        $this->registerEvents($customItem);
        $this->loadDataFiles($customItem);
        $this->packet = ItemComponentPacket::create(self::$components);
    }

}