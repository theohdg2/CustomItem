<?php

namespace Refaltor\Natof\CustomItem\Interfaces;

use pocketmine\item\ItemIdentifier;

interface BasicItemInterface
{
    /**
     * BasicItemInterface constructor.
     * Creates the object of a basic item.
     *
     * @param ItemIdentifier $itemIdentifier
     * @param string $name
     */
    public function __construct(ItemIdentifier $itemIdentifier, string $name = 'unknown');


    /**
     * Returns the highest amount of this item which will fit into one inventory slot.
     *
     * @return int
     */
    public function getMaxStackSize(): int;


    /**
     * Sets the number of items it can have in a single inventory slot.
     *
     * @param int $maxStackSize
     * @return self
     */
    public function setMaxStackSize(int $maxStackSize): self;


    /**
     * Returns the name of the item.
     *
     * @return string
     */
    public function getName(): string;


    /**
     * Allows you to change the name of the item.
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self;


    /**
     * Add a script when the player interacts with air.
     * Parameters: Player $player, Vector3 $directionVector
     *
     * @param callable $listener
     */
    public function setInteractOnAirListener(callable $listener): void;


    /**
     * Add a script when the player interacts with block.
     * Parameters: Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector
     * @param callable $listener
     */
    public function setInteractOnBlockListener(callable $listener): void;


    /**
     * Add a script when the player attack a entity.
     * Parameters: Entity $victim
     *
     * @param callable $listener
     */
    public function setAttackOnEntityListener(callable $listener): void;


    /**
     * Add a script when the player destroy block.
     * Parameters: Block $block
     *
     * @param callable $listener
     */
    public function setDestroyBlockListener(callable $listener): void;


    /**
     * Gives you the name of the texture of the item.
     *
     * @return string|null
     */
    public function getTextureName(): ?string;


    /**
     * Lets you add a texture to the item, for example if you put 'apple' the item will take
     * the texture of an apple!
     *
     * @param string $namespace
     * @return $this
     */
    public function setTextureName(string $namespace): self;


    /**
     * Gives you the item identifiers (id and meta)
     *
     * @return ItemIdentifier
     */
    public function getItemIdentifier(): ItemIdentifier;


    /**
     * Allows the class to be in the form of a string.
     *
     * @return string
     */
    public function toString(): string;
}