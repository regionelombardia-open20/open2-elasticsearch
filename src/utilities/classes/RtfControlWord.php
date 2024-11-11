<?php

namespace open20\elasticsearch\utilities\classes;

class RtfControlWord extends RtfElement {

    public $word;
    public $parameter;

    public function dump($level) {
        echo "<div style='color:green'>";
        $this->Indent($level);
        echo "WORD {$this->word} ({$this->parameter})";
        echo "</div>";
    }
}
