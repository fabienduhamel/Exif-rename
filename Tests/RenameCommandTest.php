<?php

namespace Tests;

use App\ExifFile;
use App\RenameCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class RenameCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    public function setUp()
    {
        $this->clearDir(__DIR__ . DIRECTORY_SEPARATOR . 'output');

        $application = new Application();
        $application->add(new RenameCommand());

        $this->command = $application->find('rename');
        $this->commandTester = new CommandTester($this->command);
    }

    private function clearDir($path)
    {
        $finder = new Finder();
        $finder->files()->in($path);

        $fs = new Filesystem();
        $fs->remove($finder);
    }

    public function testExecuteDry()
    {
        $this->commandTester->execute(array(
            'dir'  => __DIR__ . DIRECTORY_SEPARATOR . 'test',
            'dest'  => __DIR__ . DIRECTORY_SEPARATOR . 'test',
            '--dry-run' => true,
        ));
        $output = $this->commandTester->getDisplay();

        $this->assertContains("20031214_120144_Canon-PowerShot-S40.jpg does not contain valid exif data. Skipping", $output);
        $this->assertContains("fileWithoutExif.jpg does not contain valid exif data. Skipping", $output);
        $this->assertContains("20031214_120144_Canon-PowerShot-S40_1.jpg does not contain valid exif data. Skipping", $output);
        $this->assertContains("testFile.jpg has been copied to 20031214_120144_Canon-PowerShot-S40_2.jpg", $output);
        $this->assertContains("1 file updated.", $output);

        $this->assertNotContains("anotherFile.txt", $output);
    }

    public function testExecute()
    {
        $this->commandTester->execute(array(
            'dir'  => __DIR__ . DIRECTORY_SEPARATOR . 'test',
            'dest'  => __DIR__ . DIRECTORY_SEPARATOR . 'output',
            '--dry-run' => false,
        ));

        // Asserts that file exists in dest dir.
        new ExifFile(__DIR__ . DIRECTORY_SEPARATOR . 'output/20031214_120144_Canon-PowerShot-S40.jpg');
    }
}
