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

namespace open20\elasticsearch\models\search;

use open20\amos\core\interfaces\CmsModelInterface;
use open20\amos\core\record\CmsField;
use open20\amos\cwh\utility\CwhUtil;
use open20\elasticsearch\base\ElasticDataProvider;
use open20\elasticsearch\models\ElasticModel;
use open20\elasticsearch\models\ElasticQuery;
use open20\elasticsearch\Module;
use open20\elasticsearch\transformer\AbstractTransformer;
use Yii;

class ElasticModelSearch extends ElasticModel implements CmsModelInterface {

    private $module;
    
    public function getModule() {
        return $this->module;
    }

        
    public function init() {
        parent::init();
        $this->module = Module::getInstance();
    }

    public function cmsIsVisible($id) {
        $retValue = true;
        return $retValue;
    }

    public function cmsSearch($params, $limit) {
        $filter = '';

        $params = array_merge($params, Yii::$app->request->get());
        $query = new ElasticQuery();
        $query->boolQuery();
        $query = $this->addCwhConditions($query);
        if (!empty($params['searchtext'])) {
            $query->addMust("query", "*" . $this->getModule()->folding->folding($params['searchtext']), "query_string", "open20_italian", ["title^5", "*^1"]);
        }

        $dataProvider = new ElasticDataProvider([
            'query' => $query,
        ]);
        if ($params["withPagination"]) {
            $dataProvider->setPagination(['pageSize' => $limit]);
            $query->limit(null);
        } else {
            $query->limit($limit);
        }
        return $dataProvider;
    }

    public function cmsSearchFields() {
        $searchFields = [];

        array_push($searchFields, new CmsField("title", "TEXT"));
        array_push($searchFields, new CmsField("description", "TEXT"));

        return $searchFields;
    }

    public function cmsViewFields() {
        return [
            new CmsField('title', 'TEXT', 'amoselasticsearch', $this->attributeLabels()['title']),
            new CmsField('description', 'TEXT', 'amoselasticsearch', $this->attributeLabels()['description']),
        ];
    }

    /**
     * 
     * @param type $query
     * @return type
     */
    public function addCwhConditions($query) {
        if ($this->getModule()->enableCwh) {
            $loggedUser = Yii::$app->user->identity;
            $loggedUserId = $loggedUser->id;
            $loggedUserProfileId = $loggedUser->userProfile->id;
            
            if ($this->getModule()->filterCwhByUserTags) {
                $usersTags = $this->getUsersTag($loggedUserProfileId);
                $usersTags[] = sprintf("'%s'",AbstractTransformer::NOTAGS_TAG);
                $query->addShould(AbstractTransformer::TAGS_INDEX, $usersTags);
            }
            $cwhModule = \Yii::$app->getModule('cwh');
            if ($cwhModule) {
                $listNetworks = $cwhModule->getUserNetworks($loggedUserId);
                $networksIds = [];
                $networksIds[] = AbstractTransformer::PLATFORM_TAG;
                if (count($listNetworks)) {
                    foreach($listNetworks as $record){
                        $networksIds[] = str_replace('-', '', $record['id']);
                    }
                }
                $query->addAsBoolFilter(AbstractTransformer::NETWORKS_INDEX, $networksIds, 'match');
            }
        }
        return $query;
    }

    /**
     * 
     * @param type $userProfileId
     * @return type
     */
    private function getUsersTag($userProfileId) {
        $usersTags = [];
        if (!is_null(Yii::$app->getModule('cwh'))) {
            $usersTags = CwhUtil::findInterestTagIdsByUser($userProfileId);
            $calcTags = [];
            foreach($usersTags as $tag){
               $calcTags[] =  "'" . $tag . "'";
            }
            $usersTags = $calcTags;
        }
        return $usersTags;
    }

}
