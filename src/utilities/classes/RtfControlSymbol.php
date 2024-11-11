<?php

namespace open20\elasticsearch\utilities\classes;

class RtfControlSymbol extends RtfElement {

    public $symbol;
    public $parameter = 0;

    public function dump($level) {
        echo "<div style='color:blue'>";
        $this->Indent($level);
        echo "SYMBOL {$this->symbol} ({$this->parameter})";
        echo "</div>";
    }
}
