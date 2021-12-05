<?php


namespace RobotismPhp\Command\Input;


use Robotism\Contract\Entity\HumanLikeEntity;
use Robotism\Contract\Message\Message;

class ChatCommandInput implements InputInterface
{
    protected Message $message;
    protected array $arguments;
    public function __construct(Message $message,array $arguments=[]){
        $this->message=$message;
        $this->arguments=$arguments;
    }
    public function getArgument(string $name)
    {
        return $this->arguments[$name]??null;
    }

    public function getSender(): HumanLikeEntity
    {
        return $this->message->sender;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }
}