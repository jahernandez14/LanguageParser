<?php
include "Tokenizer.php";
include "evalSectionException.php";

    class Fall20phpProg{
            static $currentToken;
            static $t;
            static $map = array();
            static $oneIndent = "   ";
            static $result;
            static $EOL = PHP_EOL;
        public static function main(){
            $inputSource = "http://localhost/SecureWeb/test2.txt";
            $in = fopen($inputSource, "r") or die("Unable to open file.");
            $header = "<html>" . Fall20phpProg::$EOL
        . "  <head>" . Fall20phpProg::$EOL
        . "    <title>CS 4339/5339 PHP assignment</title>" . Fall20phpProg::$EOL
        . "  </head>" . Fall20phpProg::$EOL
        . "  <body>" . Fall20phpProg::$EOL
        . "    <pre>\n";

            $footer = "    </pre>" . Fall20phpProg::$EOL
        . "  </body>" . Fall20phpProg::$EOL
        . "</html>";
            $inputFile = "";

            while (!feof($in)) {
                $inputFile .= fgets($in);
            }
            fclose($in);
            Fall20phpProg::$currentToken= new Token("","");
            Fall20phpProg::$t = new Tokenizer($inputFile);
            print($header);
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            $section = 0;

            while (Fall20phpProg::$currentToken->type != "EOF") {
                echo "section " . ++$section;
                try {
                    Fall20phpProg::evalSection();
                    echo "Section Result:\n";
                    echo Fall20phpProg::$result . "\n";
                } catch (EvalSectionException $ex) {
                    while (Fall20phpProg::$currentToken->type != "RSQUAREBRACKET" && Fall20phpProg::$currentToken->type != "EOF") {
                        Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
                    }
                    Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
                }
            }
            echo $footer . "\n";
        }

        public static function evalSection(){
            Fall20phpProg::$result = "";
            if (Fall20phpProg::$currentToken->type != "LSQUAREBRACKET"){
                throw new EvalSectionException("A section must start with \"[\"");
            }
            echo "\n[\n";
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            while (Fall20phpProg::$currentToken->type != "RSQUAREBRACKET" && Fall20phpProg::$currentToken->type != Fall20phpProg::$EOL){
                Fall20phpProg::evalStatement(Fall20phpProg::$oneIndent, true);
            }
            echo "]\n";
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
        }

        public static function evalStatement($indent, $exec){
            switch (Fall20phpProg::$currentToken->type){
                case "ID":
                    Fall20phpProg::evalAssignment($indent,$exec);
                    break;
                case "SWITCH":
                    Fall20phpProg::evalSwitch($indent,$exec);
                    break;
                case "OUTPUT":
                    Fall20phpProg::evalOutput($indent,$exec);
                    break;
                default:
                    throw new EvalSectionException("invalid statement");
            }
        }
        public static function evalAssignment($indent, $exec){
            $key = Fall20phpProg::$currentToken->value;
            echo $indent . $key;
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            if(Fall20phpProg::$currentToken->type != "EQUAL"){
                throw new EvalSectionException("equal sign is expected");
            }
            echo "=";
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            if(Fall20phpProg::$currentToken->type == "INT"){
                $value = Fall20phpProg::$currentToken->value;
                echo $value . "\n";
                Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
                if ($exec){
                    Fall20phpProg::$map[$key]= $value;
                }
            }
            else if (Fall20phpProg::$currentToken->type == "ID"){
                $key2 = Fall20phpProg::$currentToken->value;
                echo $key2 . "\n";
                Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
                if($exec){
                    if (!array_key_exists($key2, Fall20phpProg::$map)){
                        throw new EvalSectionException("undefined variable");
                    }
                    $value = Fall20phpProg::$map[$key2];
                    Fall20phpProg::$map[$key]=$value;
                }
            }
            else{
                throw new EvalSectionException("ID or Integer expected");
            }
        }
        public static function evalOutput($indent, $exec){
            echo $indent . "output ";
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            switch (Fall20phpProg::$currentToken->type){
                case "STRING":
                    if ($exec){
                        Fall20phpProg::$result .= Fall20phpProg::$currentToken->value . Fall20phpProg::$EOL;
                    }
                    echo "\"" . str_replace("\"","\\\"",Fall20phpProg::$currentToken->value) . "\"";
                    Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
                    break;
                case "INT":
                    if ($exec){
                        Fall20phpProg::$result .= Fall20phpProg::$currentToken->value . Fall20phpProg::$EOL;
                    }
                    echo Fall20phpProg::$currentToken->value . "\n";
                    Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
                    break;
                case "ID":
                    $key = Fall20phpProg::$currentToken->value;
                    echo $key . "\n";
                    if($exec){
                        if(!array_key_exists($key, Fall20phpProg::$map)){
                            throw new EvalSectionException("undefined variable");
                        }
                        $value = Fall20phpProg::$map[$key];
                        Fall20phpProg::$result .= $value . Fall20phpProg::$EOL;
                    }
                    Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
                    break;
                default:
                    throw new EvalSectionException("expected a string, integer, or ID");
            }
        }
        public static function evalSwitch($indent, $exec){
            echo $indent . "switch ";
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            if (Fall20phpProg::$currentToken->type != "ID"){
                throw new EvalSectionException("ID expected");
            }
            $key =Fall20phpProg::$currentToken->value;
            echo $key;
            if($exec){
                if(!array_key_exists($key, Fall20phpProg::$map)){
                    throw new EvalSectionException("undefined variable");
                }
                $value = Fall20phpProg::$map[$key];
            }
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            if(Fall20phpProg::$currentToken->type != "LBRACKET"){
                throw new EvalSectionException("Left bracket expected");
            }
            echo " {\n";
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            while(Fall20phpProg::$currentToken->type == "CASE"){
                Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
                echo $indent . Fall20phpProg::$oneIndent . "case ";
                $exec = Fall20phpProg::evalCase($indent . Fall20phpProg::$oneIndent . Fall20phpProg::$oneIndent, $exec, $value); 
            }
            if(Fall20phpProg::$currentToken->type == "DEFAULT"){
                echo $indent . Fall20phpProg::$oneIndent . "default";
                Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
                if(Fall20phpProg::$currentToken != "COLON"){
                    throw new EvalSectionException("colon expected");
                }
                echo ":\n";
                Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
                while(Fall20phpProg::$currentToken->type != "RBRACKET"){
                    Fall20phpProg::evalStatement($indent . Fall20phpProg::$oneIndent . Fall20phpProg::$oneIndent, $exec);
                }
            }
            if (Fall20phpProg::$currentToken->type == "RBRACKET"){
                echo $indent . "}\n";
                Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            }
            else {
                throw new EvalSectionException("right bracket expected");
            }
        }
        public static function evalCase($indent, $exec, $target){
            if (Fall20phpProg::$currentToken->type != "INT"){
                throw new EvalSectionException("Integer Expected");
            }
            $value = Fall20phpProg::$currentToken->value;
            echo $value;
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            if (Fall20phpProg::$currentToken->type != "COLON"){
                throw new EvalSectionException("colon expected");
            }
            echo ":\n";
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            while (Fall20phpProg::$currentToken->type != "BREAK"){
                Fall20phpProg::evalStatement($indent, $exec && $value == $target);
            }
            echo $indent . "break\n";
            Fall20phpProg::$currentToken = Fall20phpProg::$t->nextToken();
            return $exec && !($value == $target);
        }
}
Fall20phpProg::main();
?>