<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\admin\base
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
namespace open20\elasticsearch\transformer;

use open20\elasticsearch\base\interfaces\ModelToElasticTransformerInterface;
use open20\elasticsearch\Module;
use yii\db\ActiveRecord;

abstract class AbstractModelToElasticTransformer extends AbstractTransformer implements ModelToElasticTransformerInterface
{

    public function transform()
    {
        return $this->model->toArray();
    }

    /**
     *
     * @param string $value
     * @return string
     */
    protected function filterString($value)
    {
        if (is_null($value)) {
            $retValue = "";
        } else {
            $retValue = urldecode(html_entity_decode(strip_tags($value)));
        }
        return $retValue;
    }

    /**
     *
     * @param ActiveRecord $model
     * @return boolean
     */
    public function canSaveIndex()
    {
        return true;
    }

    public function cwh($values)
    {
        $ret_values = $values;
        $module = Module::instance();
        if ($module->enableCwh) {
            $ret_values = $this->cwhEvaluation($values);
        }
        return $ret_values;
    }
}
