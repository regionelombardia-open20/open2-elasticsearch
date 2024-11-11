<?php

namespace open20\elasticsearch\models;

class ItemCondition extends BaseItemCondition {

    private $field;
    private $condition;
    private $operation = 'must';
    private $lenient = false;
    private $tie_breaker;

    public function getTie_breaker() {
        return $this->tie_breaker;
    }

    public function setTie_breaker($tie_breaker) {
        $this->tie_breaker = $tie_breaker;
    }

    public function getField() {
        return $this->field;
    }

    public function getCondition() {
        return $this->condition;
    }

    public function setField($field) {
        $this->field = $field;
        return $this;
    }

    public function setCondition($condition) {
        $this->condition = $this->escapeElasticSearchReservedChars($condition);
        return $this;
    }

    public function getOperation() {
        return $this->operation;
    }

    public function setOperation($operation) {
        $this->operation = $operation;
        return $this;
    }

    public function getLenient() {
        return $this->lenient;
    }

    public function setLenient($lenient) {
        $this->lenient = $lenient;
        return $this;
    }

    /**
     * 
     * @return type
     */
    public function toArray() {
        $condition = [$this->field => $this->condition];
        if (!empty($this->analyzer)) {
            $condition["analyzer"] = $this->analyzer;
        }
        if (count($this->fields)) {
            $condition["fields"] = $this->fields;
        }
        if ($this->lenient) {
            $condition['lenient'] = $this->lenient;
        }
        if (!empty($this->tie_breaker)) {
            $condition['tie_breaker'] = $this->tie_breaker;
        }
        return [$this->operation => $condition];
    }
}
