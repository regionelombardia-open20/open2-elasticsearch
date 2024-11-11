<?php
namespace open20\elasticsearch\models;

class ItemCondition extends \yii\base\BaseObject
{    
    private $field;
    private $condition;
    private $operation = 'must';
    
    
    public function getField() 
    {
        return $this->field;
    }

    public function getCondition() 
    {
        return $this->condition;
    }

    public function setField($field)
    {
        $this->field = $field;
    }

    public function setCondition($condition)
    {
        $this->condition = $condition;
    }
   
    public function getOperation() 
    {
        return $this->operation;
    }

    public function setOperation($operation) 
    {
        $this->operation = $operation;
    }
    
    public function toArray()
    {
        return [$this->operation => [$this->field => $this->condition]];
    }
}
