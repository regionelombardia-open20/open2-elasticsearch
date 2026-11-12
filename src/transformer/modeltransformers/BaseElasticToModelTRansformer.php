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

namespace open20\elasticsearch\transformer\modeltransformers;

use open20\elasticsearch\Module;
use open20\elasticsearch\transformer\AbstractElasticToModelTransformer;
use open20\elasticsearch\transformer\AbstractTransformer;

class BaseElasticToModelTRansformer extends AbstractElasticToModelTransformer {

    protected $elasticObject;

    public function setElasticObject($elasticObject) {
        $this->elasticObject = $elasticObject;
        return $this;
    }
    
    public function getElasticObject() {
        return $this->elasticObject;
    }

    
    public function dropValues() {
        $values = $this->elasticObject['_source'];

        $module = Module::instance();
        if ($module->enableCwh) {
            unset($values[AbstractTransformer::NETWORKS_INDEX]);
            unset($values[AbstractTransformer::TAGS_INDEX]);
            unset($values[AbstractTransformer::STATUS_INDEX]);
        }
        $this->elasticObject['_source'] = $values;
        return $this;
    }

}
