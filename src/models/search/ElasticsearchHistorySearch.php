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

namespace open20\elasticsearch\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use open20\elasticsearch\models\ElasticsearchHistory;

/**
 * ElasticsearchHistorySearch represents the model behind the search form about `open20\elasticsearch\models\ElasticsearchHistory`.
 */
class ElasticsearchHistorySearch extends ElasticsearchHistory
{

    public $dateFrom;

//private $container; 

    public function __construct(array $config = [])
    {
        $this->isSearch = true;
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['id', 'user_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['dateFrom', 'search_text', 'results', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            ['User', 'safe'],
        ];
    }

    public function scenarios()
    {
// bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = ElasticsearchHistory::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->joinWith('user');

        $dataProvider->setSort([
            'defaultOrder' => [ 'created_at' => SORT_DESC],
            'attributes' => [
                'search_text' => [
                    'asc' => ['elasticsearch_history.search_text' => SORT_ASC],
                    'desc' => ['elasticsearch_history.search_text' => SORT_DESC],
                ],
                'created_at' => [
                    'asc' => ['elasticsearch_history.created_at' => SORT_ASC],
                    'desc' => ['elasticsearch_history.created_at' => SORT_DESC],
                ],
            ]]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }


        $query->andFilterWhere([
            'id' => $this->id,
            'elasticsearch_history.user_id' => $this->user_id,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'elasticsearch_history.search_text', $this->search_text])
            ->andFilterWhere(['like', 'elasticsearch_history.results', $this->results])
            ->andFilterWhere(['>=', 'elasticsearch_history.created_at', $this->dateFrom]);

        return $dataProvider;
    }
}
