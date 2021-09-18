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

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class BasicItem extends Item
{

    /** @var string  */
    private string $texturePath;

    /** @var int  */
    private int $maxStackSize;

    /**
     * BasicItem constructor.
     * @param ItemIdentifier $identifier
     * @param string $name
     */
    public function __construct(ItemIdentifier $identifier, string $name)
    {
        $this->texturePath = 'blocks/barrier';
        $this->maxStackSize = 64;
        parent::__construct($identifier, $name);
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
     * @param int $stack
     * @return $this
     */
    public function setStackMaxSize(int $stack): self {
        $this->maxStackSize = $stack;
        return $this;
    }


    /**
     * @return int
     */
    public function getMaxStackSize(): int{
        return $this->maxStackSize;
    }
}