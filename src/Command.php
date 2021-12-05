<?php


namespace RobotismPhp\Command;


use RobotismPhp\Command\Input\InputInterface;
use RobotismPhp\Command\Output\OutputInterface;

abstract class Command
{
    protected string $signature='';
    protected string $description='';

    public function __construct(){
        $this->configure();
    }

    public abstract function configure();

    public abstract function execute(InputInterface $input,OutputInterface $output);

    public function getSignature():string{
        return $this->signature;
    }

    protected function setSignature(string $signature){
        $this->signature=$signature;
    }

    public function getDescription():string{
        return $this->description;
    }

    protected function setDescription(string $description){
        $this->description=$description;
    }

}