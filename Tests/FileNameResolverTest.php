<?php

namespace Tests;

use App\ExifFile;
use App\FileNameResolver;
use Mockery as m;

class FileNameResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileNameResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->resolver = new FileNameResolver();
    }

    public function testResolveDoesntIterate()
    {
        // mock
        $shotDate = new \DateTime('2016-09-20 12:00:00');
        $file = $this->buildFile('unique.jpg', '.', 'SuperCamera', $shotDate);

        $newName = $this->resolver->resolve($file, __DIR__ . '/test');

        $this->assertEquals('20160920_120000_SuperCamera.jpg', $newName);
    }

    public function testResolveIteratesTwice()
    {
        // real file
        $file = new ExifFile(__DIR__ . '/test/testFile.jpg');

        $newName = $this->resolver->resolve($file, __DIR__ . '/test');

        $this->assertEquals('20031214_120144_Canon-PowerShot-S40_2.jpg', $newName);
    }

    public function testResolveWithSameNameThanOriginal()
    {
        // mock
        $shotDate = new \DateTime('2003-12-14 12:01:44');
        $file = $this->buildFile('20031214_120144_Canon-PowerShot-S40_2.jpg', __DIR__ . '/test', 'Canon-PowerShot-S40', $shotDate);

        $newName = $this->resolver->resolve($file, __DIR__ . '/test');

        $this->assertEquals('20031214_120144_Canon-PowerShot-S40_2.jpg', $newName);
    }

    private function buildFile($basename, $path, $modelString, $shotDate, $extension = 'jpg')
    {
        $file = m::mock('App\ExifFile');
        $file->shouldReceive('getExtension')->once()->andReturn($extension);
        $file->shouldReceive('getBasename')->once()->andReturn($basename);
        $file->shouldReceive('getPath')->once()->andReturn($path);
        $file->shouldReceive('getModelString')->once()->andReturn($modelString);
        $file->shouldReceive('getShotDate')->once()->andReturn($shotDate);

        return $file;
    }
}
