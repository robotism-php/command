<?php


namespace RobotismPhp\Command;

use Robotism\Contract\Message\Item\Emoji;
use Robotism\Contract\Message\Item\EntityReference;
use Robotism\Contract\Message\Item\PlainText;
use Robotism\Contract\Message\Item\RichText;
use Robotism\Contract\Message\Message;
use RobotismPhp\Command\Exception\FormatException;
use RobotismPhp\Command\Exception\SyntaxException;
use RobotismPhp\Command\Parsing\CommandSignature;
use RobotismPhp\Command\Parsing\InputParseContext;
use RobotismPhp\Command\Parsing\Parameter;
use RobotismPhp\Command\Parsing\ParameterType;
use RobotismPhp\Command\Parsing\ParseContext;

class CommandParser
{
    public static function compile(string $signature){
        $content=new ParseContext();
        $command_name="";
        $parameters=[];
        $parameter=new Parameter();
        $string_cache='';
        $optional_phase_started=false;
        for($i=0;$i<mb_strlen($signature);$i++){
            $character=mb_substr($signature,$i,1);
            switch($character){
                case '<':
                    if($optional_phase_started){
                        throw new SyntaxException('The necessary parameter should not define after the optional parameter');
                    }
                    if($content->getPhase()===ParseContext::PHASE_COMMAND_NAME) {
                        $command_name = $string_cache;
                    }
                    $string_cache='';
                    $parameter->optional=false;
                    $content->setNecessaryParameterBit();
                    break;
                case '[':
                    $optional_phase_started=true;
                    if($content->getPhase()===ParseContext::PHASE_COMMAND_NAME) {
                        $command_name = $string_cache;
                    }
                    $string_cache='';
                    $parameter->optional=true;
                    $content->setOptionalParameterBit();
                    break;
                case ':':
                    switch($content->getPhase()){
                        case ParseContext::PHASE_NAME:
                            if($string_cache==''){
                                throw new SyntaxException('The command name should not be null');
                            }
                            $parameter->name=$string_cache;
                            break;
                        case ParseContext::PHASE_TYPE:
                            $types=explode('|',$string_cache);
                            $parsed_types=[];
                            foreach($types as $type){
                                $strict=false;
                                if(substr($type,0,1)=='!') {
                                    $type = substr($type, 1);
                                    $strict = true;
                                }
                                array_push($parsed_types,new ParameterType(
                                    $type,
                                    self::getTypeId($type),
                                    $strict
                                ));
                            }
                            $parameter->types=$parsed_types;
                            break;
                        case ParseContext::PHASE_DISPLAY:
                            $parameter->display=$string_cache;
                            break;
                        case ParseContext::PHASE_DESCRIPTION:
                            $parameter->description=$string_cache;
                            break;
                    }
                    $string_cache = '';
                    $content->nextPhase();
                    break;
                case '>':
                    $content->unsetNecessaryParameterBit();
                    switch($content->getPhase()){
                        case ParseContext::PHASE_NAME:
                            if($string_cache==''){
                                throw new SyntaxException('The command name should not be null');
                            }
                            $parameter->name=$string_cache;
                            break;
                        case ParseContext::PHASE_DISPLAY:
                            $parameter->display=$string_cache;
                            break;
                        case ParseContext::PHASE_TYPE:
                            $types=explode('|',$string_cache);
                            $parsed_types=[];
                            foreach($types as $type){
                                $strict=false;
                                if(substr($type,0,1)=='!') {
                                    $type = substr($type, 1);
                                    $strict = true;
                                }
                                array_push($parsed_types,new ParameterType(
                                    $type,
                                    self::getTypeId($type),
                                    $strict
                                ));
                            }
                            $string_cache='';
                            $parameter->types=$parsed_types;
                            break;
                        case ParseContext::PHASE_DESCRIPTION:
                            $parameter->description=$string_cache;
                    }
                    array_push($parameters,$parameter);
                    $parameter=new Parameter();
                    break;
                case ']':
                    $content->unsetOptionalParameterBit();
                    switch($content->getPhase()){
                        case ParseContext::PHASE_NAME:
                            if($string_cache==''){
                                throw new SyntaxException('The command name should not be null');
                            }
                            $parameter->name=$string_cache;
                            $string_cache='';
                            break;
                        case ParseContext::PHASE_DISPLAY:
                            $parameter->display=$string_cache;
                            $string_cache='';
                            break;
                        case ParseContext::PHASE_TYPE:
                            $types=explode('|',$string_cache);
                            $parsed_types=[];
                            foreach($types as $type){
                                $strict=false;
                                if(substr($type,0,1)=='!') {
                                    $type = substr($type, 1);
                                    $strict = true;
                                }
                                array_push($parsed_types,new ParameterType(
                                    $type,
                                    self::getTypeId($type),
                                    $strict
                                ));
                            }
                            $string_cache='';
                            $parameter->types=$parsed_types;
                            break;
                        case ParseContext::PHASE_DESCRIPTION:
                            $parameter->description=$string_cache;
                            $string_cache='';
                    }
                    array_push($parameters,$parameter);
                    $parameter=new Parameter();
                    break;
                default:
                    if(!$content->inParameter() && $content->getPhase()!==ParseContext::PHASE_COMMAND_NAME && $character!=' '){
                        throw new SyntaxException('Invalid string either not in parameter nor in first of the signature');
                    }
                    if($content->getPhase()!=ParseContext::PHASE_COMMAND_NAME || $character!=' ')
                        if($content->inParameter() || $content->getPhase()==ParseContext::PHASE_COMMAND_NAME)$string_cache.=$character;
            }
        }
        if($content->inParameter()){
            throw new SyntaxException('A non-ended parameter block detected.');
        }
        if($content->getPhase()===ParseContext::PHASE_COMMAND_NAME) {
            $command_name = $string_cache;
            $string_cache = '';
        }
        foreach($parameters as $parameter){
            if(!isset($parameter->display)){
                $parameter->display=$parameter->name;
            }
        }
        return (new CommandSignature())
            ->setName($command_name)
            ->setArguments($parameters);
    }
    public static function parse(Message $command){
        $context=new InputParseContext();
        $items=$command->items;
        $name='';
        $parameters=[];
        while(count($items)>0) {
            $item=$items[0];
            switch ($context->getPhase()){
                case InputParseContext::PHASE_COMMAND_NAME:
                    if($item instanceof PlainText){
                        while($item->text[0]==' ')$item->text=substr($item->text,1);
                        while(strlen($item->text)!=0 && mb_substr($item->text,0,1)!=' '){
                            $name = $name . mb_substr($item->text,0,1);
                            $item->text=mb_substr($item->text,1);
                        }
                        if(strlen($item->text)!=0){
                            $item->text=substr($item->text,1);
                        }else{
                            array_shift($items);
                        }

                        $context->setPhase(InputParseContext::PHASE_COMMAND_PARAMETER);
                    }else{
                        array_shift($items);
                        continue 2;
                    }
                    break;
                case InputParseContext::PHASE_COMMAND_PARAMETER:
                    if($item instanceof PlainText){
                        while($item->text[0]==' ')$item->text=substr($item->text,1);
                        $value='';
                        while(strlen($item->text)!=0 && mb_substr($item->text,0,1)!=' '){
                            $value = $value . mb_substr($item->text,0,1);
                            $item->text=mb_substr($item->text,1);
                        }

                        if(strlen($item->text)!=0){
                            $item->text=substr($item->text,1);
                        }else{
                            array_shift($items);
                        }

                        array_push($parameters,(new PlainText($value)));
                    }else{
                        array_push($parameters,(new PlainText($value)));
                        array_shift($items);
                        continue 2;
                    }
            }

        }
        return [
            'name'=>$name,
            'parameters'=>$parameters
        ];
    }
    public static function format(CommandSignature $signature,array $parameter){
        $parameter_pointer=0;
        $parsed_parameters=[];
        foreach($signature->arguments as $argument){
            if(!isset($parameter[$parameter_pointer])){
                if($argument->optional)break;
                throw new FormatException($argument->display,FormatException::ERROR_PARAMETER_MISSED);
            }
            //$need_process_parameter=$parameter[$parameter_pointer];
            //$matched_parameter='';
            $parsed_parameter=null;
            foreach($argument->types as $type){
                switch ($type->getType()){
                    case ParameterType::PARAMETER_TYPE_PLAIN:
                        if($parameter[$parameter_pointer] instanceof PlainText){
                            $parsed_parameter=$parameter[$parameter_pointer++];
                            break 2;
                        }
                        if(!$type->strict){
                            if($parameter[$parameter_pointer] instanceof Emoji){
                                $parsed_parameter=$parameter[$parameter_pointer++]->toPlain();
                                break 2;
                            }else if($parameter[$parameter_pointer] instanceof  EntityReference){
                                $parsed_parameter=new PlainText('@'.$parameter[$parameter_pointer++]->target->id);
                                break 2;
                            }
                        }
                        break;
                    case ParameterType::PARAMETER_TYPE_EMOJI:

                        if($parameter[$parameter_pointer] instanceof Emoji){
                            $parsed_parameter=$parameter[$parameter_pointer++];
                            break 2;
                        }
                        if(!$type->strict){

                            if($parameter[$parameter_pointer] instanceof PlainText){

                                try{
                                    $parsed_parameter=Emoji::fromPlain($parameter[$parameter_pointer]);
                                    $parameter_pointer++;
                                    break 2;
                                }catch(\Exception $exception){
                                    break;
                                }
                            }
                        }
                        break;
                    case ParameterType::PARAMETER_TYPE_RICH:
                        if($parameter[$parameter_pointer] instanceof RichText){
                            $parsed_parameter=$parameter[$parameter_pointer++];
                            break 2;
                        }
                        break;
                    default:
                        break;
                }
            }
            if($parsed_parameter==null and count($argument->types)!=0){
                throw new FormatException($argument->display,FormatException::ERROR_PARAMETER_TYPE_ERROR);
            }
            if(count($argument->types)==0)
                $parsed_parameters[$argument->name]=$parameter[$parameter_pointer++];
            else
                $parsed_parameters[$argument->name]=$parsed_parameter;
        }
        return $parsed_parameters;
    }

    protected static function getTypeId(string $type)
    {
        switch (strtolower($type)){
            case 'text':
            case 'plain':
            case 'plaintext':
                return ParameterType::PARAMETER_TYPE_PLAIN;
            case 'emoji':
                return ParameterType::PARAMETER_TYPE_EMOJI;
            case 'reference':
            case 'ref':
                return ParameterType::PARAMETER_TYPE_REFERENCE;
            case 'image':
            case 'img':
                return ParameterType::PARAMETER_TYPE_RICH;
            default:
                throw new SyntaxException('Unknown argument type : '.$type.' provided.');
        }
    }
    public static function generateReadableSignature(CommandSignature $signature,$verbose=false){
        $readable_signature=$signature->name;
        foreach($signature->arguments as $argument){
            $readable_signature.=' ';
            if(!$argument->optional)
                $readable_signature.='<';
            else
                $readable_signature.='[';
            $readable_signature.=$argument->display;
            if(!$argument->optional)
                $readable_signature.='>';
            else
                $readable_signature.=']';
        }
        return $readable_signature;
    }

}