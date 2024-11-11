<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\admin\base
 * @category   CategoryName
 */
namespace open20\elasticsearch\models;

use open20\elasticsearch\Module;

class ElasticQuery extends \yii\base\BaseObject 
{    
    const WILDCARD_MORE = "*";
    const WILDCARD_PLACE = "?";


    private $module;
    private $query = null;
    private $index = '_all';
    private $type = null;
    private $size = null;
    private $scroll_id = null;
    private $from = null;
    private $should = [];
    private $must = [];
    private $filter = [];
    private $minimum_should_match = 1;
    

    public function init() {
        $this->module = Module::instance();
    }
    
    
    public function getMinimum_should_match() 
    {
        return $this->minimum_should_match;
    }

    public function setMinimum_should_match($minimum_should_match)
    {
        $this->minimum_should_match = $minimum_should_match;
    }

    /**
     * 
     * @param string $query
     * @param string $index
     * @param string $type
     */
    public function query($query = null,$index = '_all', $type = null) 
    {

        $this->query = $query;
        $this->index = $index;
        $this->type = $type;
        return $this;
    }
    
    /**
     * 
     * @param type $index
     * @param type $type
     * @return $this
     */
    public function boolQuery($index = '_all', $type = null)
    {
        $this->query = ["query" => [
                "bool" => []
        ]]; 
        return $this;
    }
    
    /**
     * 
     * @param type $index
     * @param type $type
     * @return $this
     */
    public function matchAllQuery($index = '_all', $type = null)
    {
        $this->query = ["query" => [
                "match_all" => (object) null
        ]]; 
        return $this;
    }
    
    /**
     * 
     * @param type $query
     * @param type $index
     * @param type $type
     * @return type
     */
    private function buildParams()
    {
        $params = [
            'index' => $this->index,
            'body' => $this->buildQuery()
        ];
        
        if(!is_null($this->type))
        {
            $params ['type'] = $this->type;
        }
        if(!is_null($this->from))
        {
            $params ["from"] = $this->from;
        }
        if(!is_null($this->size))
        {
            $params ["size"] = $this->size;
        }
        return $params;
    }

    /**
     * 
     * @return string
     */
    protected function buildQuery()
    {
        if(is_null($this->query))
        {
            $this->matchAllQuery();
        }
                
	if(count($this->must))
        {
            $this->query["query"]["bool"]["must"] = $this->must;
        }
        if(count($this->filter))
        {
            $this->query["query"]["bool"]["filter"] = $this->filter;
        }
        if(count($this->should))
        {
            $this->query["query"]["bool"]["should"] = $this->should;
            $this-> query["query"]["bool"]['minimum_should_match'] = $this->minimum_should_match;
        }
        return \yii\helpers\Json::encode($this->query);
    }


    /**
     * 
     * @param array $query_result
     * @return array
     */
    private function buildResult(array $query_result)
    {
        $models = [];
        
        $elastic_models = $query_result['hits']['hits'];
        foreach($elastic_models as $emodel)
        {
            $transformer = $this->module->transformerManager->getTrasnformerManager(ElasticIndex::classFromIndexType($emodel['_type']));
            $models[] = $transformer->elasticToModel($emodel);
        }
        
        return $models;
    }
    
    
    /**
     * 
     * @return type
     */
    public function count() 
    {
        $results = $this->module->client->count($this->buildParams());
        return $results['count'];
    }
    
    /**
     * 
     * @return array
     */
    public function all()
    {
        $results = $this->module->client->search($this->buildParams());
        if($this->size)
        {
            $this->scroll_id = $results['_scroll_id'];
        }
        return $this->buildResult($results); 
    }
    
    /**
     * 
     * @return type
     */
    public function one()
    {
        $params =  $this->buildParams();
        $params['size'] = 1;
        unset($params['from']);
        $results = $this->module->client->search($params);
        return $this->buildResult($results); 
    }
    
    /**
     * 
     * @param int $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->size = $limit;
        return $this;
    }
    
    public function from($from)
    {
        $this->from = $from;
        return $this;
    }
    
    /**
     * 
     * @param type $field
     * @param type $condition
     * @param type $operation
     * @return $this
     */
    public function addShould($field, $condition, $operation = 'match')
    {
        if(is_array($condition))
        {
            foreach($condition as $value)
            {
                $this->should[] = (new ItemCondition(['field' => $field, 'condition' => $value, 'operation' => $operation]))->toArray();
            }
        }
        else
        {
            $this->should[] = (new ItemCondition(['field' => $field, 'condition' => $condition, 'operation' => $operation]))->toArray();
        }
        return $this;
    }
    
    /**
     * 
     * @param type $field
     * @param type $condition
     * @param type $as
     * @return $this
     */
    public function addAsBoolShould($field, $condition,$operation = "term" , $as = "should")
    {
        $toAdd = [
            'bool' => [
                $as => []
            ]
        ];
        
        if(is_array($condition))
        {
            foreach($condition as $value)
            {
                $toAdd['bool'][$as][] = (new ItemCondition(['field' => $field, 'condition' => $value, 'operation' => $operation]))->toArray();
            }
        }
        else
        {
            $toAdd['bool'][$as][] = (new ItemCondition(['field' => $field, 'condition' => $condition , 'operation' => $operation]))->toArray();
        }
        $this->should[] = $toAdd;
        return $this;
        
    }
    
    /**
     * 
     * @return $this
     */
    public function resetShoulds()
    {
        $this->should = [];
        return $this;
    }
    
    /**
     * 
     * @param type $field
     * @param type $condition
     * @param type $operation
     * @return $this
     */
    public function addMust($field, $condition,  $operation = 'match')
    {
        if(is_array($condition))
        {
            foreach($condition as $value)
            {
                $this->must[] = (new ItemCondition(['field' => $field, 'condition' => $value, 'operation' => $operation]))->toArray();
            }
        }
        else
        {
            $this->must[] = (new ItemCondition(['field' => $field, 'condition' => $condition , 'operation' => $operation]))->toArray();
        }
        return $this;
    }
    
    /**
     * 
     * @param type $field
     * @param type $condition
     * @param type $as
     * @return $this
     */
    public function addAsBoolMust($field, $condition,$operation = "term" , $as = "should")
    {
        $toAdd = [
            'bool' => [
                $as => []
            ]
        ];
        
        if(is_array($condition))
        {
            foreach($condition as $value)
            {
                $toAdd['bool'][$as][] = (new ItemCondition(['field' => $field, 'condition' => $value, 'operation' => $operation]))->toArray();
            }
        }
        else
        {
            $toAdd['bool'][$as][] = (new ItemCondition(['field' => $field, 'condition' => $condition , 'operation' => $operation]))->toArray();
        }
        $this->must[] = $toAdd;
        return $this;
        
    }
    
    /**
     * 
     * @param type $field
     * @param type $condition
     * @return $this
     */
    public function addMustLike($field, $condition)
    {
        $this->must[] = $this->addMust($field, self::WILDCARD_MORE . $condition . self::WILDCARD_MORE);
        return $this;
    }
    
    /**
     * 
     * @param string $condition
     * @return $this
     */
    public function addMustFullText($condition)
    {
        $this->resetMust();
        $this->must[] = (new ItemCondition(['field' => "query", 'condition' => $condition , 'operation' => "query_string"]))->toArray();
        return $this;
    }

    public function resetMust()
    {
        $this->must = [];
        return $this;
    }
    
    /**
     * 
     * @param type $field
     * @param type $condition
     * @return $this
     */
    public function addFilter($field, $condition)
    {
        if(is_array($condition))
        {
            foreach($condition as $value)
            {
                $this->filter[] = (new ItemCondition(['field' => $field, 'condition' => $value, 'operation' => 'term']))->toArray();
            }
        }
        else
        {
            $this->filter[] = (new ItemCondition(['field' => $field, 'condition' => $condition , 'operation' => 'term']))->toArray();
        }
        return $this;
    }
    
    /**
     * 
     * @param type $field
     * @param type $condition
     * @param type $as
     * @return $this
     */
    public function addAsBoolFilter($field, $condition,$operation = "term" , $as = "should")
    {
        $toAdd = [
            'bool' => [
                $as => []
            ]
        ];
        
        if(is_array($condition))
        {
            foreach($condition as $value)
            {
                $toAdd['bool'][$as][] = (new ItemCondition(['field' => $field, 'condition' => $value, 'operation' => $operation]))->toArray();
            }
        }
        else
        {
            $toAdd['bool'][$as][] = (new ItemCondition(['field' => $field, 'condition' => $condition , 'operation' => $operation]))->toArray();
        }
        $this->filter[] = $toAdd;
        return $this;
        
    }
    
    /**
     * 
     * @return $this
     */
    public function resetFilter()
    {
        $this->filter = [];
        return $this;
    }
}
