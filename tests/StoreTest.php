<?php
namespace keyvaluestore;


/**
 * Created by PhpStorm.
 * User: ercling
 * Date: 10/22/15
 * Time: 22:40
 */
class StoreTest extends \PHPUnit_Framework_TestCase
{
    public function testPushBasic()
    {
        $store = new Store();
        $pair = new Pair('key','value');
        $store->push($pair);
        $store[2] = 'value1';
        $store[2] = $pair;
        $this->assertSame($pair->getValue(),$store->get('key'));
        $this->assertSame($pair, $store->get(2));
        $this->assertSame(null,$store->get('asd'));

    }

    public function testPushAdvanced()
    {
        $store = new Store();
        $pair = new Pair('key','value');
        $store->push($pair);
        $store->push(__DIR__ . '/data/php.ini');
        $store->push(__DIR__ . '/data/test.xml');
        $this->assertEquals('John', $store->get('firstName'));
        $store->push(new Pair('firstName','Paul'));
        $this->assertEquals('Paul', $store->get('firstName'));
        $this->assertEquals('Off', $store->get('asp_tags'));
    }

    public function testIterable()
    {
        $string =
<<<STR
firstName:John
lastName:Sellick
streetAddress:Московское ш., 101, кв.101

STR;

        $store = new Store();
        $store->push(__DIR__ . '/data/test.xml');
        unset($store['Session']);
        $str='';
        foreach ($store as $key=>$val){
            $str .= $key . ':' . $val . PHP_EOL;
        }
        $this->assertEquals($string, $str);
    }

    public function testDumpArray()
    {
        $array = [
            'firstName' => 'John',
            'lastName' => 'Sellick',
            'streetAddress' => 'Московское ш., 101, кв.101'
        ];
        $store = new Store();
        $store->push(__DIR__ . '/data/test.xml');
        unset($store['Session']);
        $this->assertEquals($array, $store->dump());
    }

    public function testDumpIni()
    {
        $string =
<<<INI
firstName=John
lastName=Sellick
streetAddress=Московское ш., 101, кв.101

INI;
        $store = new Store();
        $store->push(__DIR__ . '/data/test.xml');
        unset($store['Session']);
        $this->assertEquals($string, $store->dump('ini'));
    }

    public function testDumpXml()
    {
        $this->markTestIncomplete('How to test it?');
    }

    public function testDumpJson()
    {
        $store = new Store();
        $store->push(__DIR__ . '/data/php.ini');
        unset($store['Session']);
        $this->assertEquals($store->dump(), (array)json_decode($store->dump('json')));
    }
}