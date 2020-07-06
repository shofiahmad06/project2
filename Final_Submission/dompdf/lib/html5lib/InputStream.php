<?php


class HTML5_InputStream {
    /**
     * The string data we're parsing.
     */
    private $data;

    /**
     * The current integer byte position we are in $data
     */
    private $char;

    /**
     * Length of $data; when $char === $data, we are at the end-of-file.
     */
    private $EOF;

    /**
     * Parse errors.
     */
    public $errors = array();

    /**
     * @param $data | Data to parse
     * @throws Exception
     */
    public function __construct($data) {

       
        if (extension_loaded('iconv')) {
            // non-conforming
            $data = @iconv('UTF-8', 'UTF-8//IGNORE', $data);
        } else {
            // we can make a conforming native implementation
            throw new Exception('Not implemented, please install iconv');
        }

        /* One leading U+FEFF BYTE ORDER MARK character must be
        ignored if any are present. */
        if (substr($data, 0, 3) === "\xEF\xBB\xBF") {
            $data = substr($data, 3);
        }

        /* All U+0000 NULL characters in the input must be replaced
        by U+FFFD REPLACEMENT CHARACTERs. Any occurrences of such
        characters is a parse error. */
        for ($i = 0, $count = substr_count($data, "\0"); $i < $count; $i++) {
            $this->errors[] = array(
                'type' => HTML5_Tokenizer::PARSEERROR,
                'data' => 'null-character'
            );
        }
     
        $data = str_replace(
            array(
                "\0",
                "\r\n",
                "\r"
            ),
            array(
                "\xEF\xBF\xBD",
                "\n",
                "\n"
            ),
            $data
        );

       
        if (extension_loaded('pcre')) {
            $count = preg_match_all(
                '/(?:
                    [\x01-\x08\x0B\x0E-\x1F\x7F] # U+0001 to U+0008, U+000B,  U+000E to U+001F and U+007F
                |
                    \xC2[\x80-\x9F] # U+0080 to U+009F
                |
                    \xED(?:\xA0[\x80-\xFF]|[\xA1-\xBE][\x00-\xFF]|\xBF[\x00-\xBF]) # U+D800 to U+DFFFF
                |
                    \xEF\xB7[\x90-\xAF] # U+FDD0 to U+FDEF
                |
                    \xEF\xBF[\xBE\xBF] # U+FFFE and U+FFFF
                |
                    [\xF0-\xF4][\x8F-\xBF]\xBF[\xBE\xBF] # U+nFFFE and U+nFFFF (1 <= n <= 10_{16})
                )/x',
                $data,
                $matches
            );
            for ($i = 0; $i < $count; $i++) {
                $this->errors[] = array(
                    'type' => HTML5_Tokenizer::PARSEERROR,
                    'data' => 'invalid-codepoint'
                );
            }
        } else {
            // XXX: Need non-PCRE impl, probably using substr_count
        }

        $this->data = $data;
        $this->char = 0;
        $this->EOF  = strlen($data);
    }

    /**
     * Returns the current line that the tokenizer is at.
     *
     * @return int
     */
    public function getCurrentLine() {
        // Check the string isn't empty
        if ($this->EOF) {
            // Add one to $this->char because we want the number for the next
            // byte to be processed.
            return substr_count($this->data, "\n", 0, min($this->char, $this->EOF)) + 1;
        } else {
            // If the string is empty, we are on the first line (sorta).
            return 1;
        }
    }


    public function getColumnOffset() {
       
        $lastLine = strrpos($this->data, "\n", $this->char - 1 - strlen($this->data));

    
        if ($lastLine !== false) {
            $findLengthOf = substr($this->data, $lastLine + 1, $this->char - 1 - $lastLine);
        } else {
            $findLengthOf = substr($this->data, 0, $this->char);
        }

        // Get the length for the string we need.
        if (extension_loaded('iconv')) {
            return iconv_strlen($findLengthOf, 'utf-8');
        } elseif (extension_loaded('mbstring')) {
            return mb_strlen($findLengthOf, 'utf-8');
        } elseif (extension_loaded('xml')) {
            return strlen(utf8_decode($findLengthOf));
        } else {
            $count = count_chars($findLengthOf);
            // 0x80 = 0x7F - 0 + 1 (one added to get inclusive range)
            // 0x33 = 0xF4 - 0x2C + 1 (one added to get inclusive range)
            return array_sum(array_slice($count, 0, 0x80)) +
                   array_sum(array_slice($count, 0xC2, 0x33));
        }
    }

    /**
     * Retrieve the currently consume character.
     * @note This performs bounds checking
     *
     * @return bool|string
     */
    public function char() {
        return ($this->char++ < $this->EOF)
            ? $this->data[$this->char - 1]
            : false;
    }

    /**
     * Get all characters until EOF.
     * @note This performs bounds checking
     *
     * @return string|bool
     */
    public function remainingChars() {
        if ($this->char < $this->EOF) {
            $data = substr($this->data, $this->char);
            $this->char = $this->EOF;
            return $data;
        } else {
            return false;
        }
    }

   
    public function charsUntil($bytes, $max = null) {
        if ($this->char < $this->EOF) {
            if ($max === 0 || $max) {
                $len = strcspn($this->data, $bytes, $this->char, $max);
            } else {
                $len = strcspn($this->data, $bytes, $this->char);
            }
            $string = (string) substr($this->data, $this->char, $len);
            $this->char += $len;
            return $string;
        } else {
            return false;
        }
    }

    
    public function charsWhile($bytes, $max = null) {
        if ($this->char < $this->EOF) {
            if ($max === 0 || $max) {
                $len = strspn($this->data, $bytes, $this->char, $max);
            } else {
                $len = strspn($this->data, $bytes, $this->char);
            }
            $string = (string) substr($this->data, $this->char, $len);
            $this->char += $len;
            return $string;
        } else {
            return false;
        }
    }

    /**
     * Unconsume one character.
     */
    public function unget() {
        if ($this->char <= $this->EOF) {
            $this->char--;
        }
    }
}
