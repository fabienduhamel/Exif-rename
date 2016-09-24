<?php

namespace App\Tests;

use App\ExifFile;

class ExifFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testExifFileThrowsExceptionIfNoMandatoryExifData()
    {
        new ExifFile(__DIR__ . '/test/fileWithoutExif.jpg');
    }

    public function testExifData()
    {
        $file = new ExifFile(__DIR__ . '/test/testFile.jpg');

        $this->assertEquals('Canon-PowerShot-S40', $file->getModelString());
        $this->assertInstanceOf(\DateTime::class, $file->getShotDate());
    }
}
