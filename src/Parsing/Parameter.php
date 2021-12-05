<?php


namespace RobotismPhp\Command\Parsing;


class Parameter
{
    public ?string $name;
    public ?string $display;
    public ?string $description;
    /**
     * @var ParameterType[]
     */
    public array $types=[];
    public bool $optional=false;
}