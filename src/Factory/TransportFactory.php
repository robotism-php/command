<?php


namespace RobotismPhp\Command\Factory;


use Robotism\Contract\Message\Message;
use RobotismPhp\Command\Input\InputInterface;
use RobotismPhp\Command\Output\OutputInterface;

interface TransportFactory
{
    public function createInput(Message $message,array $arguments=[]):InputInterface;
    public function createOutput(Message $message):OutputInterface;
}