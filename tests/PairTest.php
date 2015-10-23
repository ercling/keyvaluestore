<?php
namespace keyvaluestore;

/**
 * Created by PhpStorm.
 * User: ercling
 * Date: 10/22/15
 * Time: 19:41
 */
class PairTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatePair()
    {
        $number = 42;
        $string = 'string';
        $pair = new Pair($number, $string);
        $this->assertSame($number, $pair->getKey());
        $this->assertSame($string, $pair->getValue());
    }

    public function testCreatePairException()
    {
        $this->setExpectedException('keyvaluestore\exceptions\WrongTypeException');
        $pair = new Pair(null, 42);
    }

    public function testCreatePairException2()
    {
        $this->setExpectedException('keyvaluestore\exceptions\WrongTypeException');
        $pair = new Pair(['php'], 42);
    }

    public function testCreatePairException3()
    {
        $this->setExpectedException('keyvaluestore\exceptions\WrongTypeException');
        $pair = new Pair(new \stdClass(), 42);
    }
}