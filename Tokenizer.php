<?php
    include "Token.php";
    class Tokenizer{
        private $e = array();
        private $i;
        public $currentChar;

        function __construct($s) {
            $this->e = str_split($s);
            $this->i = 0;
        }

        public function nextToken(){
            while($this->i < count($this->e) && ctype_space($this->e[$this->i]) == true){
                $this->i++;
            }
            if($this->i >= count($this->e)){
                return new Token("EOF","");
            }
            $inputString = "";
            while($this->i < count($this->e) && is_numeric($this->e[$this->i]) == true){
                $inputString .= $this->e[$this->i++]; 
            }
            if($inputString != ""){
                return new Token("INT",$inputString);
            }
            while($this->i < count($this->e) && ctype_alpha($this->e[$this->i]) == true){
                $inputString .= $this->e[$this->i++]; 
            }
            if($inputString != ""){
                if("output" == $inputString){
                    return new Token("OUTPUT", $inputString);
                }
                if("switch" == $inputString){
                    return new Token("SWITCH", $inputString);
                }
                if("case" == $inputString){
                    return new Token("CASE", $inputString);
                }
                if("break" == $inputString){
                    return new Token("BREAK", $inputString);
                }
                if("default" == $inputString){
                    return new Token("DEFAULT", $inputString);
                }
                return new Token("ID", $inputString);
            }

            switch($this->e[$this->i++]){
                case '{':
                    return new Token("LBRACKET", "{");
                case '}':
                    return new Token("RBRACKET", "}");
                case '[':
                    return new Token("LSQUAREBRACKET", "[");
                case ']':
                    return new Token("RSQUAREBRACKET", "]");
                case '=':
                    return new Token("EQUAL", "=");
                case ':':
                    return new Token("COLON", ":");
                case '"':
                    $value = "";
                    while($this->i < count($this->e) && $this->e[$this->i]!= '"'){
                        $c = $this->e[$this->i++];
                        if($this->i >= count($this->e)){
                            return new Token("OTHER","");
                        }
                        if($c == '\\' && $this->e[$this->i] == '"'){
                            $c = '"';
                            $this->i++;
                        }
                        $value .= $c;
                    }
                    $this->i++;
                    return new Token("STRING", $value);
                default:
                    return new Token("OTHER","");
            }
        }
    }
?>