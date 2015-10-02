<?php

 /* ***********
  * Class:  State Machine
  * Author:  grignoli@gmail.com
  * */

class StateMachine {
  protected $started = false;
  protected $state = array();
  protected $transition = array();
  protected $transitionAny = array();
  protected $current = "";
  protected $previous = "";
  protected $last = "";

  public function addState($name){
    if(!in_array($name, $this->state)) $this->state[] = $name;
    if(!isset($this->transition[$name])) $this->transition[$name] = array();
    return $this;
  }

  public function addEvent($state_from, $event_name, $state_to = null){
    if(in_array($state_from, $this->state) &&
      in_array($state_to,   $this->state) &&
      !isset($this->transition[$state_from][$event_name]))
    {
      $this->transition[$state_from][$event_name] = $state_to;
    }
    return $this;
  }

  public function addEventAny($state_from, $state_to = null){
    $this->transitionAny[$state_from] = $state_to;
    return $this;
  }

  public function start($name){
    if(!$this->started){
      $this->started = true;
      $this->current = $name;
    }
    return $this;
  }

  public function canHappen($name){
    return     isset($this->transition[$this->current][$name])
      || isset($this->transitionAny[$this->current]);
  }

  public function canHappenThis($name){
    return isset($this->transition[$this->current][$name]);
  }

  public function canHappenAny(){
    return array_key_exists($this->current, $this->transitionAny);
  }

  public function trigger($event){
    $this->last = $this->current;
    if($this->canHappenThis($event)) {
      $this->change($this->targetFor($event));
    } elseif($this->canHappenAny()) {
      $this->change($this->targetForAny());
    }
    return $this;
  }

  protected function change($target){
    if(is_null($target)) $target = $this->previous;
    if($this->current != $target){
      $this->previous = $this->current;
      $this->current = $target;
    }
    return $this;
  }

  protected function targetFor($event){
    return $this->transition[$this->current][$event];
  }

  protected function targetForAny(){
    return $this->transitionAny[$this->current];
  }

  public function changed(){
    return ($this->current != $this->last);
  }

  public function state(){
    return $this->current;
  }

  public function previous(){
    return $this->previous;
  }

  public function dump(){
    echo var_export($this->transitionAny);
    echo var_export($this->transition);
  }
}




