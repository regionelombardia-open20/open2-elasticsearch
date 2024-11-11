<?php

namespace open20\elasticsearch\utilities\classes;

class RtfReader {

    public $root = null;

    protected function GetChar() {
        $this->char = $this->rtf[$this->pos++];
    }

    protected function ParseStartGroup() {
        // Store state of document on stack.
        $group = new RtfGroup();
        if ($this->group != null)
            $group->parent = $this->group;
        if ($this->root == null) {
            $this->group = $group;
            $this->root = $group;
        } else {
            array_push($this->group->children, $group);
            $this->group = $group;
        }
    }

    protected function is_letter() {
        if (ord($this->char) >= 65 && ord($this->char) <= 90)
            return TRUE;
        if (ord($this->char) >= 97 && ord($this->char) <= 122)
            return TRUE;
        return FALSE;
    }

    protected function is_digit() {
        if (ord($this->char) >= 48 && ord($this->char) <= 57)
            return TRUE;
        return FALSE;
    }

    protected function ParseEndGroup() {
        // Retrieve state of document from stack.
        $this->group = $this->group->parent;
    }

    protected function ParseControlWord() {
        $this->GetChar();
        $word = "";

        while ($this->is_letter()) {
            $word .= $this->char;
            $this->GetChar();
        }

        // Read parameter (if any) consisting of digits.
        // Paramater may be negative.
        $parameter = null;
        $negative = false;
        if ($this->char == '-') {
            $this->GetChar();
            $negative = true;
        }
        while ($this->is_digit()) {
            if ($parameter == null)
                $parameter = 0;
            $parameter = $parameter * 10 + $this->char;
            $this->GetChar();
        }
        if ($parameter === null)
            $parameter = 1;
        if ($negative)
            $parameter = -$parameter;

        // If this is \u, then the parameter will be followed by 
        // a character.
        if ($word == "u") {
            
        }
        // If the current character is a space, then
        // it is a delimiter. It is consumed.
        // If it's not a space, then it's part of the next
        // item in the text, so put the character back.
        else {
            if ($this->char != ' ')
                $this->pos--;
        }

        $rtfword = new RtfControlWord();
        $rtfword->word = $word;
        $rtfword->parameter = $parameter;
        array_push($this->group->children, $rtfword);
    }

    protected function ParseControlSymbol() {
        // Read symbol (one character only).
        $this->GetChar();
        $symbol = $this->char;

        // Symbols ordinarily have no parameter. However, 
        // if this is \', then it is followed by a 2-digit hex-code:
        $parameter = 0;
        if ($symbol == '\'') {
            $this->GetChar();
            $parameter = $this->char;
            $this->GetChar();
            $parameter = hexdec($parameter . $this->char);
        }

        $rtfsymbol = new RtfControlSymbol();
        $rtfsymbol->symbol = $symbol;
        $rtfsymbol->parameter = $parameter;
        array_push($this->group->children, $rtfsymbol);
    }

    protected function ParseControl() {
        // Beginning of an RTF control word or control symbol.
        // Look ahead by one character to see if it starts with
        // a letter (control world) or another symbol (control symbol):
        $this->GetChar();
        $this->pos--;
        if ($this->is_letter())
            $this->ParseControlWord();
        else
            $this->ParseControlSymbol();
    }

    protected function ParseText() {
        // Parse plain text up to backslash or brace,
        // unless escaped.
        $text = "";

        do {
            $terminate = false;
            $escape = false;

            // Is this an escape?
            if ($this->char == '\\') {
                // Perform lookahead to see if this
                // is really an escape sequence.
                $this->GetChar();
                switch ($this->char) {
                    case '\\': $text .= '\\';
                        break;
                    case '{': $text .= '{';
                        break;
                    case '}': $text .= '}';
                        break;
                    default:
                        // Not an escape. Roll back.
                        $this->pos = $this->pos - 2;
                        $terminate = true;
                        break;
                }
            } else if ($this->char == '{' || $this->char == '}') {
                $this->pos--;
                $terminate = true;
            }

            if (!$terminate && !$escape) {
                $text .= $this->char;
                $this->GetChar();
            }
        } while (!$terminate && $this->pos < $this->len);

        $rtftext = new RtfText();
        $rtftext->text = $text;

        // If group does not exist, then this is not a valid RTF file. Throw an exception.
        if ($this->group == NULL) {
            throw new Exception();
        }

        array_push($this->group->children, $rtftext);
    }

    /*
     * Attempt to parse an RTF string. Parsing returns TRUE on success or FALSE on failure
     */

    public function Parse($rtf) {
        try {
            $this->rtf = $rtf;
            $this->pos = 0;
            $this->len = strlen($this->rtf);
            $this->group = null;
            $this->root = null;

            while ($this->pos < $this->len) {
                // Read next character:
                $this->GetChar();

                // Ignore \r and \n
                if ($this->char == "\n" || $this->char == "\r")
                    continue;

                // What type of character is this?
                switch ($this->char) {
                    case '{':
                        $this->ParseStartGroup();
                        break;
                    case '}':
                        $this->ParseEndGroup();
                        break;
                    case '\\':
                        $this->ParseControl();
                        break;
                    default:
                        $this->ParseText();
                        break;
                }
            }

            return TRUE;
        } catch (Exception $ex) {
            return FALSE;
        }
    }
}
