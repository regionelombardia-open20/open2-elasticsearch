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

namespace open20\elasticsearch\behaviors;

use Exception;
use open20\elasticsearch\models\ElasticIndex;
use open20\elasticsearch\Module;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\log\Logger;

class ElasticSearchBehavior extends Behavior {

    /**
     * @inheritdoc
     */
    public function events() {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'createIndex',
            ActiveRecord::EVENT_AFTER_UPDATE => 'updateIndex',
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteIndex',
        ];
    }

    /**
     * 
     */
    public function createIndex() {
        try {
            $index = new ElasticIndex(['model' => $this->owner]);
            $index->save();
        } catch (Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
    }

    /**
     * 
     */
    public function deleteIndex() {
        try {
            $index = new ElasticIndex(['model' => $this->owner]);
            $index->delete();
        } catch (Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
    }

    /**
     * 
     */
    public function updateIndex() {
        try {
            $module = Module::instance();
            $transformer = $module->transformerManager->getTrasnformerManager(get_class($this->owner));
            $mtoe = $transformer->getModelToElasticTransformer();
            $mtoe->setModel($this->owner);
            if ($this->owner->hasAttribute('deleted_at') && !empty($this->owner->deleted_at) || ! $mtoe->canSaveIndex()) {
                $this->deleteIndex();
            } else {
                $index = new ElasticIndex(['model' => $this->owner]);
                $index->save();
            }
        } catch (Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
    }

    public function findIndex($id) {
        
    }

    /**
     * 
     * @return array
     */
    public function toElasticArray() {
        $values = [];
        $module = Module::instance();
        $transformer = $module->transformerManager->getTrasnformerManager(get_class($this->owner));
        if (!is_null($transformer)) {
            $values = $transformer->modelToElastic($this->owner);
        }

        return $values;
    }

    /**
     * 
     * @return boolean
     */
    public function canSaveIndex() {
        $ret = true;
        $module = Module::instance();
        $transformer = $module->transformerManager->getTrasnformerManager(get_class($this->owner));

        if (!is_null($transformer)) {
            $ret = $transformer->canSaveIndex($this->owner);
        }
        return $ret;
    }

}
