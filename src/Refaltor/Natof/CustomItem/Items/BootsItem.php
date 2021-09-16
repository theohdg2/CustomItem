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
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier;

class BootsItem extends Armor
{
    /** @var string  */
    private string $texturePath;

    /** @var string  */
    private string $armorGroup = "itemGroup.name.boots";

    /** @var int */
    private int $creativeCategory = 3;

    public function __construct(ItemIdentifier $identifier, ArmorTypeInfo $info, string $name)
    {
        $this->texturePath = 'blocks/barrier';
        parent::__construct($identifier, $name, $info);
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setTexture(string $path): self {
        $this->texturePath = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getTexture(): string {
        return $this->texturePath;
    }

    /**
     * @return string
     */
    public function getArmorGroup() : string {
        return $this->armorGroup;
    }

    /**
     * @return string
     */
    public function getCreativeCategory(): string {
        return $this->creativeCategory;
    }
}