<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\admin\base
 * @category   CategoryName
 */
namespace open20\elasticsearch\transformer\modeltransformers;

use open20\elasticsearch\transformer\AbstractModelToElasticTransformer;

class BaseModelToElasticTransformer extends AbstractModelToElasticTransformer
{
    public $tagValuesSeparatorAttribute = "','";
    
    public function getTags($model)
    {
        $string_tags = '';
        
        $tags = $model->getTagValues(true); 
        $string_tags = sprintf("'%s'", implode($this->tagValuesSeparatorAttribute, $tags));
        return $string_tags;
    }
}
