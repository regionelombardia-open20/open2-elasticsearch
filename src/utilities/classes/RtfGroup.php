<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos-elasticsearch
 * @author    Aria S.p.A. <protocollo@pec.ariaspa.it>
 * @license   GNU General Public License v3.0 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @link      https://www.ariaspa.it/
 *
 * This file is part of OPEN 2.0.
 *
 * OPEN 2.0 is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OPEN 2.0 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OPEN 2.0. If not, see <https://www.gnu.org/licenses/>.
 */

namespace open20\elasticsearch\utilities\classes;

class RtfGroup extends RtfElement {

    public $parent;
    public $children;

    public function __construct() {
        $this->parent = null;
        $this->children = array();
    }

    public function GetType() {
        // No children?
        if (sizeof($this->children) == 0)
            return null;
        // First child not a control word?
        $child = $this->children[0];
        if (get_class($child) != RtfControlWord::class)
            return null;
        return $child->word;
    }

    public function IsDestination() {
        // No children?
        if (sizeof($this->children) == 0)
            return null;
        // First child not a control symbol?
        $child = $this->children[0];
        if (get_class($child) != RtfControlSymbol::class)
            return null;
        return $child->symbol == '*';
    }

    public function dump($level = 0) {
        echo "<div>";
        $this->Indent($level);
        echo "{";
        echo "</div>";

        foreach ($this->children as $child) {
            if (get_class($child) == RtfGroup::class) {
                if ($child->GetType() == "fonttbl")
                    continue;
                if ($child->GetType() == "colortbl")
                    continue;
                if ($child->GetType() == "stylesheet")
                    continue;
                if ($child->GetType() == "info")
                    continue;
                // Skip any pictures:
                if (substr($child->GetType(), 0, 4) == "pict")
                    continue;
                if ($child->IsDestination())
                    continue;
            }
            $child->dump($level + 2);
        }

        echo "<div>";
        $this->Indent($level);
        echo "}";
        echo "</div>";
    }
}
