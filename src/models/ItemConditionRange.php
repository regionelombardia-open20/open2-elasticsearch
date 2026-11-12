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

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace open20\elasticsearch\models;

/**
 * Description of ItemConditionRange
 *
 */
class ItemConditionRange extends BaseItemCondition
{
    const DATE_FORMAT = 'c'; //ISO 8601 Date Format

    private $field;
    private $start_date     = null;
    private $end_date       = null;
    private $from_condition = 'gte';
    private $to_condition   = 'lte';
    private $useDate        = true;

    public function getUseDate()
    {
        return $this->useDate;
    }

    public function setUseDate($useDate)
    {
        $this->useDate = $useDate;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setField($field)
    {
        $this->field = $field;
    }

    public function from($from)
    {
        $this->start_date = $from;
    }

    public function to($to)
    {
        $this->end_date = $to;
    }

    public function formGt($from)
    {
        $this->from_condition = 'gt';
        $this->from($from);
    }

    public function toLe($to)
    {
        $this->to_condition = 'lt';
        $this->to($to);
    }

    public function fromGte($from)
    {
        $this->from_condition = 'gte';
        $this->from($from);
    }

    public function toLte($to)
    {
        $this->to_condition = 'lte';
        $this->to($to);
    }

    public function toArray()
    {
        $condition [$this->field] = [];
        if (!is_null($this->start_date)) {
            if ($this->getUseDate()) {
                $condition [$this->field] [$this->from_condition] = date(self::DATE_FORMAT, strtotime($this->start_date));
            } else {
                $condition [$this->field] [$this->from_condition] = $this->start_date;
            }
        }
        if (!is_null($this->end_date)) {
            if ($this->getUseDate()) {
                $condition [$this->field] [$this->to_condition] = date(self::DATE_FORMAT, strtotime($this->end_date));
            } else {
                $condition [$this->field] [$this->to_condition] = $this->end_date;
            }
        }
        if (!empty($this->analyzer)) {
            $condition["analyzer"] = $this->analyzer;
        }
        return ['range' => $condition];
    }
}