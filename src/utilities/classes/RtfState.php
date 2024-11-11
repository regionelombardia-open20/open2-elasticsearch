<?php

namespace open20\elasticsearch\utilities\classes;

class RtfState {

    public function __construct() {
        $this->Reset();
    }

    public function Reset() {
        $this->bold = false;
        $this->italic = false;
        $this->underline = false;
        $this->end_underline = false;
        $this->strike = false;
        $this->hidden = false;
        $this->fontsize = 0;
        $this->par = false;

        $this->class = array();
    }
}
