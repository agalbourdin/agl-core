<?php
class DirTest
    extends PHPUnit_Framework_TestCase
{
    const TMP_DIR = '/tmp/agl-tests/';

    public function testListDirs()
    {
        $list = Agl\Core\Data\Dir::listDirs('./');
        $this->assertContains('./src/', $list);
    }

    public function testCreate()
    {
        $created = Agl\Core\Data\Dir::create(self::TMP_DIR);
        $this->assertTrue($created);
    }

    public function testDeleteDir()
    {
        $deleted = Agl\Core\Data\Dir::deleteRecursive(self::TMP_DIR);
        $this->assertTrue($deleted);
    }
}
