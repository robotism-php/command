<?php


namespace RobotismPhp\Command\Parsing;



use MyCLabs\Enum\Enum;

class ParameterType extends Enum
{
    public string $name='';
    public bool $strict;
    public function __construct(string $name,$type,bool $strict=false)
    {
        $this->name=$name;
        $this->strict=$strict;
        parent::__construct($type);
    }

    const PARAMETER_TYPE_PLAIN=0;
    const PARAMETER_TYPE_EMOJI=1;
    const PARAMETER_TYPE_REFERENCE=2;
    const PARAMETER_TYPE_RICH=3;

    public function getType():int{
        return $this->getValue();
    }
}