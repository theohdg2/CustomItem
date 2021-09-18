<div align="center">
  <img src="./img/logo.png" width="200px">
  <h1>CustomItem (API / Configuration)</h1>
</div>

<p align="center">
 A plugin to add items hyper easily !
</p>

> **Note:** Creates issues if you find bugs :)

## Features
* Food Fix Bugs

## Quick start

To get started on our API, here are the basics :

```PHP
// creates an item
$item = CustomItem::createBasicItem(new ItemIdentifier(1000, 0), 'name');

// to put a texture to the item
$item->setTexture('apple');

// to save the item on the server
CustomItem::registerItem($item);
```

## Items events

> The plugin contains support with events for all items, events are used with a callable
```PHP
$item = CustomItem::createBasicItem(new ItemIdentifier(<id>, <meta>), 'name');

// all available events with the parameters

$item->setInteractOnBlockListener(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector);
$item->setReleaseUsingListener(Player $player);
$item->setClickAirListener(Player $player, Vector3 $directionVector);
$item->setDestroyBlockListener(Block $block);
$item->setAttackEntityListener(Entity $victim);
```

## Support Armor
> The plugin allows to add real armor with real durability
```PHP
// to create a helmet
$item = CustomItem::createHelmetItem(new ItemIdentifier(<id>, <meta>), new ArmorTypeInfo(<defense points>, <durability>, <armor slot but it’s not important>), 'Helmet Test');
$item->setTexture('iron_helmet');
CustomItem::registerItem($item);

// all the functions of armor
$item = CustomItem::createHelmetItem(new ItemIdentifier(<id>, <meta>), new ArmorTypeInfo(<defense points>, <durability>, <armor slot but it’s not important>), 'Helmet Test');
$item = CustomItem::createChestplateItem(new ItemIdentifier(<id>, <meta>), new ArmorTypeInfo(<defense points>, <durability>, <armor slot but it’s not important>), 'Chestplate Test');
$item = CustomItem::createLeggingsItem(new ItemIdentifier(<id>, <meta>), new ArmorTypeInfo(<defense points>, <durability>, <armor slot but it’s not important>), 'Leggings Test');
$item = CustomItem::createBootsItem(new ItemIdentifier(<id>, <meta>), new ArmorTypeInfo(<defense points>, <durability>, <armor slot but it’s not important>), 'Boots Test');
```


## Demo

ici natof il y aura les plugins exemple

## TODO

* [ ] Food Bugs Fix
* [ ] Armor Equip Fix
* [x] Pickaxe Mining fix 1/2
