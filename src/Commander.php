<?php


namespace RobotismPhp\Command;

use Robotism\Contract\Message\Message;
use RobotismPhp\Command\Factory\TransportFactory;
use RobotismPhp\Command\Parsing\CommandSignature;

class Commander
{
    protected TransportFactory $factory;
    /**
     * @var Command[]
     */
    protected array $commands=[];

    /**
     * @var CommandSignature[]
     */
    protected array $signatures=[];
    public function __constructor(TransportFactory $factory){
        $this->factory=$factory;
    }
    public function add(Command $command){
        $command_signature=$command->getSignature();
        $compiled=CommandParser::compile($command_signature);
        $this->commands[sha1($compiled->name)]=$command;
        $this->signatures[sha1($compiled->name)]=$compiled;
    }
    public function run(Message $message){
        $command_information=CommandParser::parse($message);
        $command=$this->commands[sha1($command_information['name'])]??null;
        if($command==null)return false;
        $signature=$this->signatures[sha1($command_information['name'])];
        $input=CommandParser::format($signature,$command_information['parameters']);
        $command->execute($this->factory->createInput($message,$input),$this->factory->createOutput($message));
        return true;
    }
}