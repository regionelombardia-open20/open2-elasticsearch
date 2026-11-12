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
namespace open20\elasticsearch\models;

use Yii;
use yii\db\ActiveRecord;


class ElasticModel extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%elastic_model}}';
    }

	/**
	 * @inheritdoc
	 */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'deleted_by', 'content_type'], 'integer'],
            [['description', 'classname', 'tags'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['title', 'status'], 'string', 'max' => 255],
        ];
    }

	/**
	 * @inheritdoc
	 */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('amoselasticsearch', 'ID'),
            'title' => Yii::t('amoselasticsearch', 'Title'),
            'description' => Yii::t('amoselasticsearch', 'Description'),
            'status' => Yii::t('amoselasticsearch', 'Workflow Status'),
            'created_at' => Yii::t('amoselasticsearch', 'Created at'),
            'updated_at' => Yii::t('amoselasticsearch', 'Updated at'),
            'deleted_at' => Yii::t('amoselasticsearch', 'Deleted at'),
            'created_by' => Yii::t('amoselasticsearch', 'Created by'),
            'updated_by' => Yii::t('amoselasticsearch', 'Updated by'),
            'deleted_by' => Yii::t('amoselasticsearch', 'Deleted by'),
        ];
    }
}
