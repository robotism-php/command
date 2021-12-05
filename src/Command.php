<?php


namespace RobotismPhp\Command;


use PharIo\Manifest\Application;
use RobotismPhp\Command\Input\InputInterface;
use RobotismPhp\Command\Output\OutputInterface;

abstract class Command
{
    protected Application $app;
    protected string $signature='';
    protected string $description='';

    public function __construct(Application $application){
        $this->configure();
        $this->app=$application;
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