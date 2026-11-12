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

namespace open20\elasticsearch\models\folding;

use open20\elasticsearch\base\ElasticConst;
use open20\elasticsearch\Module;

class ItalianFolding extends Folding {

    private $accentFilter = "ìòàùéèioea";
    private $articles = [
        "c", "l", "all", "dall", "dell",
        "nell", "gl", "un", "m", "t", "s", "v", "d"
    ];

    public function folding($phrase) {
        $module = Module::instance();
        $phrase = str_replace('"', '', $phrase);
        $parts = preg_split('/ +/', $phrase);
        $parts = $this->parseArticles($parts);
        $folded = "";
        foreach ($parts as $key => $part) {
            $char = mb_substr($part, -1);
            $with_end = "";
            if ($char == ElasticConst::WILDCARD_MORE) {
                $with_end = $char;
                $char = mb_substr($part, -2, 1);
            }
            if (strpos($this->accentFilter, $char)) {
                $s = mb_substr($part, 0, empty($with_end) ? -1 : -2);
                $parts[$key] = $s . "?" . $with_end;
            } else {
                if ($module->useFinalSpecial && empty($with_end)) {
                    $parts[$key] = $part . ElasticConst::WILDCARD_MORE;
                }
            }
        }
        $folded = implode(" ", $parts);

        return $folded;
    }

    /**
     * 
     * @param array $parts
     */
    protected function parseArticles($parts) {
        $purifyArray = [];

        foreach ($parts as $key => $part) {
            $remove = false;
            foreach ($this->articles as $article) {
                $val = str_replace($article, '', $part);
                if (strlen($val) <= 1) {
                    $remove = true;
                    break;
                }
            }
            if (!$remove) {
                $purifyArray[$key] = $part;
            }
        }
        return $purifyArray;
    }

}
