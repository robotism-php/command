<?php


namespace RobotismPhp\Command;

use Robotism\Contract\Message\Item\PlainText;
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
    public function __construct(TransportFactory $factory){
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
        if($command==null){
            $this->factory->createOutput($message)->sendMessage(
                (new Message())
                    ->append(
                        new PlainText('未知命令，请使用“菜单”指令查询!')
                    )
            );
            return false;
        }
        $signature=$this->signatures[sha1($command_information['name'])];
        try {
            $input = CommandParser::format($signature, $command_information['parameters']);
        } catch (Exception\FormatException $e) {
            $this->factory->createOutput($message)->sendMessage(
                (new Message())
                ->append(
                    new PlainText('命令格式有误,正确格式:'.CommandParser::generateReadableSignature($signature))
                )
            );
            return false;
        }
        $command->execute($this->factory->createInput($message,$input),$this->factory->createOutput($message));
        return true;
    }
}