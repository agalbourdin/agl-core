<?php
class ArrTest
    extends PHPUnit_Framework_TestCase
{
    public function testArraySearch()
    {
        $result = Agl\Core\Data\Arr::arraySearch(
            'value3',
            array(
                'key1' => array(
                    'value1', 'value2'
                ),
                'key2' => array(
                    'subKey' => array('value3')
                )
            )
        );

        $this->assertEquals('key2', $result);
    }
}
