<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\admin\base
 * @category   CategoryName
 */
namespace open20\elasticsearch\commands;

use open20\amos\core\record\Record;
use Exception;
use Goutte\Client;
use open20\elasticsearch\models\ElasticIndex;
use open20\elasticsearch\models\Nav;
use open20\elasticsearch\models\NavItem;
use open20\elasticsearch\Module;
use ReflectionClass;
use Symfony\Component\DomCrawler\Crawler;
use Yii;
use yii\console\Controller;
use yii\db\Query;
use yii\log\Logger;

class RebuildIndexController extends  Controller 
{
    private $baseUrl = "";
    public $client = null;
    private $_crawler = null;
    
    public function init() {
        parent::init();
        $this->module = Module::instance();
        $this->baseUrl = \Yii::$app->params['platform']['frontendUrl'];
    }
    
    /**
     * 
     */
    public function actionClearAllIndexes()
    {
        $params = [
            "index"=> "_all",
            'body' => \yii\helpers\Json::encode(["query" => [
                        "match_all" => (object) null
                      ]]),
        ];
        $results = $this->module->client->deleteByQuery($params);
        var_dump($results);
    }
    
    /**
     * 
     */
    public function actionRebuild()
    {
        $this->Rebuild();
    }
    
    
    protected function Rebuild()
    {
        try{
            foreach($this->module->modelsEnabled as $entity => $transformer)
            {
                $r = new ReflectionClass( $entity );
                $b = new ReflectionClass( Record::className() );
                if($r->isSubclassOf($b))
                {
                    $query = (new Query)->from($entity::tableName())->andWhere(['deleted_at' => null]);
                    foreach($query->batch() as $i => $models)
                    {
                        foreach($models as $model)
                        {
                            var_dump($entity);
                            var_dump("id:" . $model['id']);
                            $obj = Yii::createObject($entity, [$model]);
                            $this->module->attachElasticSearchBehavior($obj);
                            $obj->setIsNewRecord(false);
                            $index = new ElasticIndex(['model' => $obj]);
                            $index->save($index);
                        }
                    }   
                }
            }
        } catch (Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
    }


    /**
     * 
     */
    public function actionReIndexCms()
    {
        $this->RebuildIndexCms();
    }
    
    /**
     * 
     */
    protected function RebuildIndexCms()
    {
        try{
            $query = (new Query)->from(NavItem::tableName());
            foreach($query->batch() as $i => $models)
            {
                foreach($models as $model)
                {
                    $obj = new NavItem($model);
                    if($obj->createUrl())
                    {
                        $path_string = $obj->elasticUrl;
                        $this->module->attachElasticSearchBehavior($obj);    
                        $obj->setElasticSourceText($this->getCrawlerHtml($this->baseUrl."/" . $path_string)); 
                        if($this->client->getInternalResponse()->getStatus() === 200)
                        {
                            var_dump($path_string);
                            var_dump("id:" . $obj->id);
                            $obj->setElasticUrl($path_string);
                            $index = new ElasticIndex(['model' => $obj]);
                            $index->save($index);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
    }
    
    protected function getCrawler($pageUrl)
    {
        
            try {
                $this->client   = new Client();
                $this->_crawler = $this->client->request('GET', $pageUrl);

                if ($this->client->getInternalResponse()->getStatus() !== 200) {
                    $this->_crawler = false;
                }
            } catch (\Exception $e) {
                $this->_crawler = false;
            }

        return $this->_crawler;
    }
    
    
    protected function getCrawlerHtml($pageUrl)
    {
        try {
            $crawler = $this->getCrawler($pageUrl);

            if (!$crawler) {
                return '';
            }
            
           
            $crawler->filter('nav')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });
            $crawler->filter('footer')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });
            
            $crawler->filter('script')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });

            $crawler->filter('style')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });


            return preg_replace('/\s\s+/', ' ', strip_tags($crawler->filter('body')->html()));
        } catch (\Exception $e) {
            return '';
        }
    }
}
