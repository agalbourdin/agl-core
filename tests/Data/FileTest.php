<?php
class FileTest
    extends PHPUnit_Framework_TestCase
{
    const TMP_FILE = '/tmp/file.txt';

    public function testSubPath3()
    {
        $path = Agl\Core\Data\File::getSubPath('0a5fg4trxo9');
        $this->assertEquals('0/a/5/', $path);
    }

    public function testSubPath4()
    {
        $path = Agl\Core\Data\File::getSubPath('0a5fg4trxo9', 4);
        $this->assertEquals('0/a/5/f/', $path);
    }

    public function testCreate()
    {
        $created = Agl\Core\Data\File::create(self::TMP_FILE);
        $this->assertTrue($created);
    }

    public function testDelete()
    {
        $deleted = Agl\Core\Data\File::delete(self::TMP_FILE);
        $this->assertTrue($deleted);
    }
}
