<?php


use RobotismPhp\Command\CommandParser;
use PHPUnit\Framework\TestCase;

class CommandParserTest extends TestCase
{

    public function testCompile()
    {
        //var_dump(CommandParser::compile('签到 <emtion:今日心情:text|emoji> [days:补签天数:text]'));
        $this->assertEquals(CommandParser::compile('测试 [A]')->name,'测试');
        $this->assertEquals(CommandParser::compile('测试 [A]')->arguments[0]->name,'A');
    }

    public function testParse(){

         $parsed=(CommandParser::parse(
            (new \Robotism\Contract\Message\Message())
                ->append(
                    new \Robotism\Contract\Message\Item\PlainText('签到 123  345')
                )
        ));
        $this->assertEquals($parsed['name'],'签到');
        $this->assertEquals($parsed['parameters'][0]->text,'123');
        $this->assertEquals($parsed['parameters'][1]->text,'345');
    }
    public function testFormat(){
        $args=CommandParser::parse(
            (new \Robotism\Contract\Message\Message())
                ->append(
                    new \Robotism\Contract\Message\Item\PlainText('签到 123 1234')
                )
        );
        $command=CommandParser::compile('签到 <param1:1:text> <param2:1:text> [param3:2:emoji]');
        $formatted_parameter=CommandParser::format($command,$args['parameters']);
        $this->assertEquals($formatted_parameter['param1']->text,'123');
        $this->assertEquals($formatted_parameter['param2']->text,'1234');
    }
}
