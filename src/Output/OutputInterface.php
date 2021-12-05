<?php


namespace RobotismPhp\Command\Output;


use Robotism\Contract\Message\Message;

interface OutputInterface
{
    public function sendMessage(Message $message);
}