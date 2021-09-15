<?php

namespace Refaltor\Natof\CustomItem\Items;

use pocketmine\item\ItemIdentifier;
use Refaltor\Natof\CustomItem\Interfaces\BasicItemInterface;

class FoodItem implements BasicItemInterface
{
    /** @var ItemIdentifier  */
    public ItemIdentifier $itemIdentifier;

    /** @var string  */
    private string $name;

    /** @var int  */
    private int $maxStackSize = 64;

    private int $foodType = 1;

    /** @var callable|null */
    public $onInteractOnAir = null;

    /** @var callable|null */
    public $onInteractOnBlock = null;

    /** @var callable|null */
    public $attackEntity = null;

    /** @var callable|null */
    public $onDestroyBlock = null;

    /** @var string|null */
    private ?string $textureName = null;

    public function __construct(ItemIdentifier $itemIdentifier, string $name = 'unknown')
    {
        $this->itemIdentifier = $itemIdentifier;
        $this->name = $name;
    }

    public function getMaxStackSize(): int
    {
        return $this->maxStackSize;
    }

    public function setMaxStackSize(int $maxStackSize): self
    {
        $this->maxStackSize = $maxStackSize;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setTypeFood(int $type): self
    {
        $this->foodType = $type;
        return $this;
    }

    public function getTypeFood(): int
    {
        return $this->foodType;
    }

    public function setInteractOnAirListener(callable $listener): void
    {
        $this->onInteractOnAir = $listener;
    }

    public function setInteractOnBlockListener(callable $listener): void
    {
        $this->onInteractOnBlock = $listener;
    }

    public function setAttackOnEntityListener(callable $listener): void
    {
        $this->attackEntity = $listener;
    }

    public function setDestroyBlockListener(callable $listener): void
    {
        $this->onDestroyBlock = $listener;
    }

    public function setTextureName(string $namespace): BasicItemInterface
    {
        $this->textureName = $namespace;
        return $this;
    }

    public function getTextureName(): ?string
    {
        return $this->textureName;
    }

    public function getItemIdentifier(): ItemIdentifier
    {
        return $this->itemIdentifier;
    }

    public function toString(): string
    {
        if (!is_null($this->onInteractOnAir)) {
            $this->onInteractOnAir = base64_encode(serialize($this->onInteractOnAir));
        }

        if (!is_null($this->onInteractOnBlock)) {
            $this->onInteractOnBlock = base64_encode(serialize($this->onInteractOnBlock));
        }

        if (!is_null($this->attackEntity)) {
            $this->onInteractOnAir = base64_encode(serialize($this->attackEntity));
        }

        if (!is_null($this->onDestroyBlock)) {
            $this->onInteractOnAir = base64_encode(serialize($this->onDestroyBlock));
        }
        return base64_encode(serialize($this));
    }

    public static function itemFromString(string $string): self {
        $class = unserialize(base64_decode($string));
        if (!is_null($class->onInteractOnAir)) {
            $class->onInteractOnAir = unserialize(base64_decode($class->onInteractAir));
        }

        if (!is_null($class->onInteractOnBlock)) {
            $class->onInteractOnBlock = unserialize(base64_decode($class->onInteractOnBlock));
        }

        if (!is_null($class->attackEntity)) {
            $class->onInteractOnAir = unserialize(base64_decode($class->attackEntity));
        }

        if (!is_null($class->onDestroyBlock)) {
            $class->onInteractOnAir = unserialize(base64_decode($class->onDestroyBlock));
        }
        return $class;
    }
}