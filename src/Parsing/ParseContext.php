<?php


namespace RobotismPhp\Command\Parsing;


use RobotismPhp\Command\Exception\SyntaxException;

class ParseContext
{
    const PHASE_COMMAND_NAME=-1;
    const PHASE_NAME=0;
    const PHASE_DISPLAY=1;
    const PHASE_TYPE=2;
    const PHASE_DESCRIPTION=3;
    protected bool $in_parameter=false;
    protected bool $necessary=false;
    protected int $phase=ParseContext::PHASE_COMMAND_NAME;
    public function setNecessaryParameterBit(){
        if($this->in_parameter){
            throw new SyntaxException('Syntax Error:Found parameter start flag (<) in a parameter');
        }
        $this->in_parameter=true;
        $this->necessary=true;
        $this->phase=ParseContext::PHASE_NAME;
    }
    public function setOptionalParameterBit(){
        if($this->in_parameter){
            throw new SyntaxException('Syntax Error:Found optional parameter start flag ([) in a parameter');
        }
        $this->in_parameter=true;
        $this->necessary=false;
        $this->phase=ParseContext::PHASE_NAME;
    }
    public function nextPhase(){
        if($this->phase>=4){
            throw new SyntaxException('Syntax Error:Invalid argument definition , provided settings should up to 3 parameters');
        }
        $this->phase++;
    }
    public function unsetNecessaryParameterBit(){
        if(!$this->in_parameter){
            throw new SyntaxException('Syntax Error:Found parameter end flag (>) without a parameter');
        }
        if(!$this->necessary){
            throw new SyntaxException('Syntax Error:Found parameter end flag (>) in a optional parameter');
        }
        $this->in_parameter=false;
    }
    public function unsetOptionalParameterBit(){
        if(!$this->in_parameter){
            throw new SyntaxException('Syntax Error:Found parameter end flag (]) without a parameter');
        }
        if($this->necessary){
            throw new SyntaxException('Syntax Error:Found optional parameter end flag (]) in a not-optional-parameter');
        }
        $this->in_parameter=false;
    }
    public function isNecessary(): bool
    {
        return $this->necessary;
    }
    public function getPhase(): int
    {
        return $this->phase;
    }
    public function inParameter():bool{
        return $this->in_parameter;
    }
}