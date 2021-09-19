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

namespace Refaltor\Natof\CustomItem\Items;

use pocketmine\item\Armor;

class LeggingsItem extends Armor
{
    /** @var string */
    private string $texturePath;

    /** @var string */
    private string $armorGroup = "itemGroup.name.leggings";


    /** @var int */
    private int $creativeCategory = 3;

    private int $durability;

    private int $defense;

    /** @var int */
    private int $slot = 4;


    public function __construct(int $id, int $meta, string $name, int $defense, int $durability, string $texturePath)
    {
        $this->texturePath = $texturePath;
        $this->defense = $defense;
        $this->durability = $durability;
        parent::__construct($id, $meta, $name);
    }

    public function getMaxDurability(): int
    {
        return $this->durability;
    }

    public function getDefensePoints(): int
    {
        return $this->defense;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setTexture(string $path): self
    {
        $this->texturePath = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getTexture(): string
    {
        return $this->texturePath;
    }

    /**
     * @return string
     */
    public function getArmorGroup(): string
    {
        return $this->armorGroup;
    }

    /**
     * @return string
     */
    public function getCreativeCategory(): string
    {
        return $this->creativeCategory;
    }

    public function getArmorSlot(): int
    {
        return $this->slot;
    }
}