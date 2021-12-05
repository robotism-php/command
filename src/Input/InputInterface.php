<?php


namespace RobotismPhp\Command\Input;


use Robotism\Contract\Entity\HumanLikeEntity;
use Robotism\Contract\Message\Message;

interface InputInterface
{
    public function getArgument(string $name);
    public function getSender():HumanLikeEntity;
    public function getMessage():Message;
}