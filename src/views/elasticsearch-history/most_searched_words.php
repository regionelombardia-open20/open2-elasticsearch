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
use open20\elasticsearch\Module;
use open20\amos\core\icons\AmosIcons;

?>

<div class="row">

    <?php

    foreach ($results as $result) {
        $classActive = '';
        $titleLink = Module::t('amoselasticsearch', "Filtra cronologia per testo");
        $urlLink = ['/elasticsearch/elasticsearch-history/index' , 'ElasticsearchHistorySearch[search_text]' => $result['search_text']];
        if($searchText == $result['search_text']) {
            $classActive =  'btn-active-filter';
            $urlLink = ['/elasticsearch/elasticsearch-history/index'];
            $titleLink = Module::t('amoselasticsearch', "Annulla filtro cronologia per testo");

        }?>
        <div class="col-xs-12 single-top-researched p-t-5">
            <p class="p-r-15">
                <?=
                Module::t('amoselasticsearch', "Ricercata") . ' ' .
                    Html::tag('span', $result['n'], ['class' => 'label label-secondary']) . ' ' .
                    Module::t('amoselasticsearch', "volte il testo") . ' ' .
                    Html::tag('strong', $result['search_text'], ['class' => ''])
                ?>
            </p>
            <p>
                <?= Html::a(
                    //AmosIcons::show('filter-list'),
                    Html::tag('span', '', ['class' => 'mdi mdi-filter-outline']),
                    $urlLink,
                    [
                        'title' => $titleLink . ' "' . $result['search_text'] . '"',
                        'class' => 'btn btn-outline-secondary'.' '.$classActive
                    ]
                ) ?>
            </p>
        </div>
    <?php } ?>

</div>