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

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\datecontrol\DateControl;
use yii\helpers\Url;
use open20\amos\core\module\BaseAmosModule;
use open20\design\utility\DesignUtility;
use open20\design\assets\BootstrapItaliaDesignAsset;
use open20\elasticsearch\Module;
use open20\elasticsearch\assets\ElasticSearchAsset;

/**
 * @var yii\web\View $this
 * @var open20\elasticsearch\models\ElasticsearchHistory $model
 */

$this->title = Module::t('amoselasticsearch', "Registro risultati ricerca: {text}", [
    'text' => $model->search_text
]);
$this->params['breadcrumbs'][] = ['label' => '', 'url' => ['/elasticsearch']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('amoscore', 'Elasticsearch History'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['titleSection'] = $this->title;

$recordPageSize = 25;

$searchedAt = new \DateTime($model->created_at);

ElasticSearchAsset::register($this);
?>

<div class="elasticsearch-history-view">

    <div class="row">
        <div class="col-xs-12">
            <p>
                <?=
                Module::t(
                    'amoselasticsearch',
                    "In data {date} per la ricerca di {text} sono stati registrati {tot} risultati.",
                    [
                        'date' => Html::tag('strong', $searchedAt->format('d/m/Y H:i')),
                        'text' => Html::tag('strong', $model->search_text),
                        'tot' => Html::tag('strong', $model->tot_found),
                    ]
                )
                ?>
            </p>
            <p>
                <?=
                Module::t(
                    'amoselasticsearch',
                    "Di seguito sono riportati i primi {n} record nello stesso ordine visualizzato al momento della ricerca.",
                    [
                        'n' => Html::tag('strong', $recordPageSize),
                    ]
                )
                ?>
            </p>
        </div>
        <div class="col-xs-12">
            <div class="modulo-backend-search">
                <div class="list-search-container d-flex flex-wrap">
                    <?php
                    $module = \Yii::$app->getModule('elasticsearch');
                    if (!empty($module->history)) {
                        
                        $dataProvider->pagination->pageSize = $recordPageSize;

                        echo \yii\widgets\ListView::widget([
                            'dataProvider' => $dataProvider,
                            'itemView' => '_itemListSearchDesign',
                            'summary' => false,
                            'pager' => DesignUtility::listViewPagerConfig(),
                            'itemOptions' => [
                                'tag' => false
                            ],
                            'options' => [
                                'class' => 'list-view w-100'
                            ]
                        ]);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>


</div>


<div id="form-actions" class="bk-btnFormContainer pull-right">
    <?= Html::a(Module::t('amoselasticsearch', 'Back'), '/elasticsearch/elasticsearch-history/index', ['class' => 'btn btn-secondary']); ?>
</div>