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

use open20\amos\core\helpers\Html;
use open20\amos\core\views\DataProviderView;
use yii\widgets\Pjax;
use open20\elasticsearch\Module;
use open20\elasticsearch\assets\ElasticSearchAsset;
use open20\amos\layout\assets\SpinnerWaitAsset;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var open20\elasticsearch\models\search\ElasticsearchHistorySearch $model
 */

ElasticSearchAsset::register($this);
SpinnerWaitAsset::register($this);

$this->title = Yii::t('amoselasticsearch', 'Search history');
$this->params['breadcrumbs'][] = ['label' => '', 'url' => ['/elasticsearch']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['titleSection'] = $this->title;

$searchTex = $model->search_text;
$js = <<<JS

      $.ajax({
           url: '/elasticsearch/elasticsearch-history/most-searched-words',
           type: 'get',
           data: {search_text: '$searchTex'},
           success: function (data) {
              $('#most-searched-words').html(data);
           }
     });

JS;

$this->registerJs($js, \yii\web\View::POS_LOAD);

?>
<div class="elasticsearch-history-index">

    <div class="row">

        <div class="col-xs-12">
            <?= $this->render('_search', ['model' => $model, 'originAction' => Yii::$app->controller->action->id]); ?>
        </div>

        <div class="col-xs-12 col-md-6">
            <h4 class="text-uppercase mb-0"><?= Module::t('amoselasticsearch', "Le ricerche") ?></h4>
            <p class="lead">
                <?= Module::t(
                    'amoselasticsearch',
                    "Elenco di tutte le ricerche effettuate {time}",
                    [
                        'limit' => '15',
                        'time' => Module::t('amoselasticsearch', 'da inizio tracciamento')
                    ]
                )
                ?>
            </p>
            <hr>
            <?php $dataProvider->pagination->pageSize = 15; ?>
            <?= DataProviderView::widget([
                'dataProvider' => $dataProvider,
                //'filterModel' => $model,
                'currentView' => $currentView,
                'gridView' => [
                    'columns' => [
                        [
                            'attribute' => 'created_at',
                            'value' => function ($model) {
                                if ($model->created_at) {
                                    $date = new \DateTime($model->created_at);
                                    return $date->format('d/m/Y H:i:s');
                                }
                            }

                        ],
                        'search_text',
                        [
                            'class' => 'open20\amos\core\views\grid\ActionColumn',
                            'template' => '{view}'
                        ],
                    ],
                ],
            ]);
            ?>
        </div>

        <div class="col-xs-12 col-md-5 col-md-push-1 m-t-30">
            <div id="most-searched-words-container" class="callout callout-info">
                <div class="callout-title">
                    <span class="mdi mdi-trending-up"></span><?= ' ' . Module::t('amoselasticsearch', "I trend più ricercati") ?>
                </div>
                <p class="lead">
                    <?= Module::t(
                        'amoselasticsearch',
                        "Elenco dei {limit} trend più ricercati {time}",
                        [
                            'limit' => '15',
                            'time' => Module::t('amoselasticsearch', 'da inizio tracciamento')
                        ]
                    )
                    ?>
                </p>
                <hr>
                <div id="most-searched-words">
                    <div class="col-md-12">
                        <div class="loader-3dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>