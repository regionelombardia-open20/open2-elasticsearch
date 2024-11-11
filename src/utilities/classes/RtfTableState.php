<?php

namespace open20\elasticsearch\utilities\classes;

class RtfTableState {

    public function __construct() {
        $this->Reset();
    }

    public function Reset() {
        $this->in_table = false;
        $this->row_start = false;
        $this->row_end = false;
        $this->headers_start = false;
        $this->headers_end = false;

        $this->class = array();
    }
}
