<?php

require_once("StateMachine.class.php");

echo "StateMachine\n\n";

/*  Set up:  */

$state = new StateMachine();
$state->addState("idle")
  ->addState("symbol")
  ->addState("variable")
  ->addState("doublequoted")
  ->addState("singlequoted")
  ->addState("escaped")

  ->addEvent("idle", "dollarsign",  "variable")
  ->addEvent("idle", "doublequote", "doublequoted")
  ->addEvent("idle", "singlequote", "singlequoted")
  ->addEvent("idle", "alpha",       "symbol")
  ->addEvent("idle", "nonalpha",    "symbol")

  ->addEvent("symbol", "alpha", "symbol") // alpha will continue
  ->addEventAny("symbol",       "idle")   // anything else will break the symbol

  ->addEvent("variable", "alpha",     "variable")  // alpha will continue
  ->addEventAny("variable")  // anything else will break the variable
  // omit target state to send back to previous state

  ->addEvent("doublequoted", "escape",      "escaped")
  ->addEvent("doublequoted", "dollarsign",  "variable")
  ->addEvent("doublequoted", "doublequote", "idle")

  ->addEvent("singlequoted", "singlequote", "idle")
  ->addEvent("singlequoted", "singlequote", "idle")

  ->addEventAny("escaped")  // omit target state to send back to previous state

  ->start("idle");




/*  Example: */

$parse_this = "This text has some \"DOBLE QUOTED PARTS WITH \$vars AND \\e\\s\\capes ON IT\", along with some 'single quoted text too with futile e\\s\\c\\a\\p\\e\\s'\"(because they don't work on single quotes)\" and some \$variable_names\n";

echo $parse_this;

$triggers = array(
  // char => event
  "\\" => "escape",
  '"'  => "doublequote",
  "'"  => "singlequote",
  '$'  => "dollarsign"
);

// first character intentionally left blank to add 1 to position count of the
// rest
$alpha = " abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789";

$stored = "";

for($i = 0; $i < strlen($parse_this); $i++){
  $char = $parse_this[$i];

  if(isset($triggers[$char])) {
    $state->trigger($triggers[$char]);
  } else {
    $state->trigger(strpos($alpha, $char)?"alpha":"nonalpha");
  }

  if(!$state->changed() || $state->previous() == "escaped"){
    $stored .= $char;
  } elseif($state->state() == "escaped"){
  } else {
    switch ($state->previous()){
    case "idle":
      $stored = "";
      break;
    case "symbol":
      echo "--> symbol: ".$stored."\n";
      $stored = "";
      break;
    case "variable":
      echo "--> variable: ".$stored."\n";
      $stored = "";
      break;
    case "doublequoted":
      echo "--> string: \"".$stored."\"\n";
      $stored = "";
      break;
    case "singlequoted":
      echo "--> string: '".$stored."'\n";
      $stored = "";
      break;
    case "escaped":
      $stored .= $char;
      break;
    }
    $stored .= $char;
    switch ($state->state()){
    case "doublequoted":
    case "singlequoted":
      $stored = "";
      break;
    }
  }
}












/*


echo "\n\nTest Pipe2Table\n\n";

class Pipe2Table {

  protected $state = false;
  protected $options = array();
  protected $lines = array();
  protected $status = "";
  protected $transforming = array();
  protected $transformed = array();

  public function __construct($options = array()){
    $this->options = array_merge($this->options, $options);
    $this->state = new StateMachine();
    $this->state->addState("texto")
      ->addState("tabla")
      ->addEvent("pipe","texto","tabla")
      ->addEvent("pipe","tabla","tabla")
      ->addEvent("nopipe","tabla","texto")
      ->addEvent("nopipe","texto","texto")
      ->start("texto");
  }

  public function feed($line = ""){
    $this->lines[] = $this->$line;
    return $this;
  }

  public function read(){
    return $this->transform($this->lines[0]);
  }

  public function consume(){
    return $this->transform(array_shift($this->lines));
  }

  public function readAll(){
    $o = "";
    foreach($this->lines as $line){
      $o .= $this->transform($line);
    }
    $this->lines = array();
    return $o;
  }

  public function consumeAll(){
    return implode("\n", $this->lines);
  }

  protected function transform($line){
    return strtoupper($line);
  }

}






$pipe = new Pipe2Table();

$text = <<<HERE
hola
123
456
a|b
a|b|c
a|bb|cc
HERE;

foreach(explode("\n",$text) as $line){
  echo $pipe->feed($line."\n")->consume();
}

echo $pipe->consumeAll();





 */




