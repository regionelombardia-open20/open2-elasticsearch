<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\admin\base
 * @category   CategoryName
 */
namespace open20\elasticsearch\transformer;

use open20\elasticsearch\base\interfaces\TransformerManagerInterface;
use yii\base\BaseObject;


abstract class AbstractTransformerManager extends BaseObject implements TransformerManagerInterface
{
    
    /**
     * Class of the model to map to the elastica documents.
     *
     * @var string
     */
    protected $objectClass = null; 
    
    /**
     * Returns the object class that is used for conversion.
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }
    
    /**
     * 
     * @param type $model
     */
    public function modelToElastic($model)
    {
        return $this->getModelToElasticTransformer()->transform($model);
    }
    
    /**
     * 
     * @param type $model
     * @param type $index
     */
    public function elasticToModel($index)
    {
        return $this->getElasticToModelTransformer()->transform($index);
    }
}
