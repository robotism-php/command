<?php


namespace RobotismPhp\Command\Exception;


use Exception;

class FormatException extends Exception
{
    const ERROR_PARAMETER_MISSED=1;
    const ERROR_PARAMETER_TYPE_ERROR=2;
    const ERROR_USER_COSTUMED_ERROR=3;
    public string $parameter_name='';
    public int $error_type=0;
    public function __construct(string $parameter_name,int $error_type)
    {
        parent::__construct(
            'The parameter '.$parameter_name.' has a '.$error_type.' error.'
        );
        $this->parameter_name=$parameter_name;
        $this->error_type=$error_type;
    }
}