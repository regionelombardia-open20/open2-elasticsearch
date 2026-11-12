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
namespace open20\elasticsearch\base;

use yii\data\BaseDataProvider;

class ElasticDataProvider extends BaseDataProvider
{
    
    public $query = null;
    
    /**
     * @var string|callable name of the key column or a callable returning it
     */
    public $key = 'id';
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        $models = [];
        $pagination = $this->getPagination();
 
        if ($pagination === false) {
            $models = $this->query->all();
        } else {
            // in case there's pagination, read only a single page
            $pagination->totalCount = $this->getTotalCount();
            $this->query->from($pagination->getOffset());
            $this->query->limit($pagination->getLimit());
 
            $models = $this->query->all();
        }
 
        return $models;
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];
 
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }
 
            return $keys;
        }

        return array_keys($models);
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        $count = 0;
        $count = $this->query->count();
        return $count;
    }
}
