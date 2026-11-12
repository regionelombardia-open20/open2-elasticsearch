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

namespace open20\elasticsearch\models;

class ItemCondition extends BaseItemCondition {

    private $field;
    private $condition;
    private $operation = 'must';
    private $lenient = false;
    private $tie_breaker;

    public function getTie_breaker() {
        return $this->tie_breaker;
    }

    public function setTie_breaker($tie_breaker) {
        $this->tie_breaker = $tie_breaker;
    }

    public function getField() {
        return $this->field;
    }

    public function getCondition() {
        return $this->condition;
    }

    public function setField($field) {
        $this->field = $field;
        return $this;
    }

    public function setCondition($condition) {
        $this->condition = $this->escapeElasticSearchReservedChars($condition);
        return $this;
    }

    public function getOperation() {
        return $this->operation;
    }

    public function setOperation($operation) {
        $this->operation = $operation;
        return $this;
    }

    public function getLenient() {
        return $this->lenient;
    }

    public function setLenient($lenient) {
        $this->lenient = $lenient;
        return $this;
    }

    /**
     * 
     * @return type
     */
    public function toArray() {
        $condition = [$this->field => $this->condition];
        if (!empty($this->analyzer)) {
            $condition["analyzer"] = $this->analyzer;
        }
        if (count($this->fields)) {
            $condition["fields"] = $this->fields;
        }
        if ($this->lenient) {
            $condition['lenient'] = $this->lenient;
        }
        if (!empty($this->tie_breaker)) {
            $condition['tie_breaker'] = $this->tie_breaker;
        }
        return [$this->operation => $condition];
    }
}
