<?php

namespace open20\elasticsearch\utilities\classes;

class RtfTables {

    public function Format($root) {
        $this->tables = array();
        $this->rowMatrix = array();
        $this->tableIdx = 0;
        $this->rowIdx = 0;
        $this->cellIdx = 0;

        // Create a stack of states:
        $this->states = array();
        // Put an initial standard state onto the stack:
        $this->state = new RtfTableState();
        array_push($this->states, $this->state);

        $this->FormatGroup($root);
        return $this->tables;
    }

    protected function FormatGroup($group) {
        // Can we ignore this group?
        if ($group->GetType() == "fonttbl")
            return;
        if ($group->GetType() == "colortbl")
            return;
        if ($group->GetType() == "stylesheet")
            return;
        if ($group->GetType() == "info")
            return;
        // Skip any pictures:
        if (substr($group->GetType(), 0, 4) == "pict")
            return;
        if ($group->IsDestination())
            return;

        // Push a new state onto the stack:
        $this->state = clone $this->state;
        array_push($this->states, $this->state);

        foreach ($group->children as $child) {
            if (get_class($child) == RtfGroup::class)
                $this->FormatGroup($child);
            if (get_class($child) == RtfControlWord::class)
                $this->FormatControlWord($child);
            if (get_class($child) == RtfControlSymbol::class)
                $this->FormatControlSymbol($child);
            if (get_class($child) == RtfText::class)
                $this->FormatText($child);
        }

        // Pop state from stack.
        array_pop($this->states);
        $this->state = $this->states[sizeof($this->states) - 1];
    }

    protected function FormatControlWord($word) {
        if ($word->word == "plain")
            $this->state->Reset();
        if ($word->word == "intbl")
            $this->state->in_table = true;

        if ($word->word == 'trowd') { // start new row
            $this->state->row_start = true;
            $this->state->row_end = false;
            $this->state->in_table = true;
        }

        if ($word->word == 'trhdr') { // start header row
            $this->tableIdx++;
            $this->rowIdx = 0;
            $this->cellIdx = 0;
            $this->state->headers_start = true;
            $this->state->headers_end = false;
        }

        if ($word->word == 'row') {  // end row
            if (!$this->state->headers_start)
                foreach ($this->cellMatrix as $matrixSlug)
                    if (!isset($this->tables[$this->tableIdx][$this->rowIdx][$matrixSlug]))
                        $this->tables[$this->tableIdx][$this->rowIdx][$matrixSlug] = false;

            $this->cellIdx = 0;
            $this->rowIdx++;

            $this->state->headers_start = false;
            $this->state->headers_end = true;
            $this->state->row_end = true;
        }

        if ($word->word == 'cell') {  // end cell
            $this->cellIdx++;
        }
    }

    protected function BeginState() {
        
    }

    protected function EndState($text = false) {
        if ($this->state->headers_start and !$this->state->headers_end) {
            $slug = $this->slug($text->text);
            $this->cellMatrix[$this->cellIdx] = $slug;
        }

        if ($this->state->in_table and !$this->state->headers_start)
            $this->tables[$this->tableIdx][$this->rowIdx][$this->cellMatrix[$this->cellIdx]] = $text->text;
    }

    protected function FormatControlSymbol($symbol) {
        if ($symbol->symbol == '\'') {
            $this->BeginState();
            $this->output .= htmlentities(chr($symbol->parameter), ENT_QUOTES, 'UTF-8');
            $this->EndState();
        }
    }

    protected function FormatText($text) {
        $this->BeginState();
        $text->text = trim($text->text);
        $this->EndState($text);
    }

    protected function slug($string, $replacement = '_') {
        if (is_string($string))
            $string = strtolower($string);

        $quotedReplacement = preg_quote($replacement, '/');

        $merge = array(
            '/[^\s\p{Zs}\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/[\s\p{Zs}]+/mu' => $replacement,
            sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
        );

        $map = $this->_transliteration + $merge;
        return preg_replace(array_keys($map), array_values($map), $string);
    }

    /**
     * Default map of accented and special characters to ASCII characters
     *
     * @var array
     */
    protected $_transliteration = array(
        '/À|Á|Â|Ã|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
        '/Æ|Ǽ/' => 'AE',
        '/Ä/' => 'Ae',
        '/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
        '/Ð|Ď|Đ/' => 'D',
        '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
        '/Ĝ|Ğ|Ġ|Ģ|Ґ/' => 'G',
        '/Ĥ|Ħ/' => 'H',
        '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ|І/' => 'I',
        '/Ĳ/' => 'IJ',
        '/Ĵ/' => 'J',
        '/Ķ/' => 'K',
        '/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
        '/Ñ|Ń|Ņ|Ň/' => 'N',
        '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
        '/Œ/' => 'OE',
        '/Ö/' => 'Oe',
        '/Ŕ|Ŗ|Ř/' => 'R',
        '/Ś|Ŝ|Ş|Ș|Š/' => 'S',
        '/ẞ/' => 'SS',
        '/Ţ|Ț|Ť|Ŧ/' => 'T',
        '/Þ/' => 'TH',
        '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
        '/Ü/' => 'Ue',
        '/Ŵ/' => 'W',
        '/Ý|Ÿ|Ŷ/' => 'Y',
        '/Є/' => 'Ye',
        '/Ї/' => 'Yi',
        '/Ź|Ż|Ž/' => 'Z',
        '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
        '/ä|æ|ǽ/' => 'ae',
        '/ç|ć|ĉ|ċ|č/' => 'c',
        '/ð|ď|đ/' => 'd',
        '/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
        '/ƒ/' => 'f',
        '/ĝ|ğ|ġ|ģ|ґ/' => 'g',
        '/ĥ|ħ/' => 'h',
        '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı|і/' => 'i',
        '/ĳ/' => 'ij',
        '/ĵ/' => 'j',
        '/ķ/' => 'k',
        '/ĺ|ļ|ľ|ŀ|ł/' => 'l',
        '/ñ|ń|ņ|ň|ŉ/' => 'n',
        '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
        '/ö|œ/' => 'oe',
        '/ŕ|ŗ|ř/' => 'r',
        '/ś|ŝ|ş|ș|š|ſ/' => 's',
        '/ß/' => 'ss',
        '/ţ|ț|ť|ŧ/' => 't',
        '/þ/' => 'th',
        '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
        '/ü/' => 'ue',
        '/ŵ/' => 'w',
        '/ý|ÿ|ŷ/' => 'y',
        '/є/' => 'ye',
        '/ї/' => 'yi',
        '/ź|ż|ž/' => 'z',
        '/“/' => '"',
    );
}
