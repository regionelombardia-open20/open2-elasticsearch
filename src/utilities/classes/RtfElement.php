<?php

namespace open20\elasticsearch\utilities\classes;

class RtfElement {

    protected function Indent($level) {
        for ($i = 0; $i < $level * 2; $i++)
            echo "&nbsp;";
    }
}
