<?php

namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

class RenameCommand extends Command
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var FileNameResolver
     */
    private $fileNameResolver;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->fileNameResolver = new FileNameResolver();
        $this->fs = new Filesystem();
    }

    protected function configure()
    {
        $this
            ->setName('rename')
            ->setDescription('Rename files based on exif data')
            ->addArgument('dir', InputArgument::REQUIRED, 'The directory to explore')
            ->addArgument('dest', InputArgument::REQUIRED, 'The destination directory')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getArgument('dir');
        $dest = $input->getArgument('dest');

        $finder = new Finder();

        $dryRun = false;
        if ($input->getOption('dry-run')) {
            $output->writeln("<info>Dry run activated.</info>");
            $dryRun = true;
        }

        $finder->files()->name('/(.jpg|.png|.dng)$/i')->in($dir)->depth("< 1");

        $updatedFilesCount = 0;

        foreach ($finder as $fileName) {
            try {
                $file = new ExifFile($fileName);
            } catch (\RuntimeException $e) {
                $file = new File($fileName);
                $output->writeln(
                    sprintf("<comment>%s does not contain valid exif data. Skipping</comment>", $file->getFilename())
                );
                continue;
            }

            $updatedFilesCount++;

            $newFileName = $this->rename($file, $dest, $dryRun);

            $output->writeln(
                sprintf(
                    "<info>%s</info> has been copied to <info>%s</info>",
                    $this->generateFullPathName($dir, $file->getFilename()),
                    $this->generateFullPathName($dest, $newFileName)
                )
            );
        }

        $output->writeln('<info>' . $updatedFilesCount . ' file' . ($updatedFilesCount > 1 ? "s" : "") . ' updated.</info>');
    }

    private function rename(ExifFile $file, $dest, $dryRun)
    {
        $newFileName = $this->fileNameResolver->resolve($file, $dest);

        if (!$dryRun) {
            $fullPathFile = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
            $newFullPathFile = $dest . DIRECTORY_SEPARATOR . $newFileName;

            $this->fs->mkdir($dest, 0755);

            $this->copy($fullPathFile, $newFullPathFile);
        }

        return $newFileName;
    }

    private function generateFullPathName($dir, $fileName)
    {
        if ($this->endsWith($dir, DIRECTORY_SEPARATOR)) {
            return $dir . $fileName;
        }

        return $dir . DIRECTORY_SEPARATOR . $fileName;
    }

    private function endsWith($haystack, $needle) {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    private function copy($fullPathFile, $newFullPathFile)
    {
        $process = new Process('rsync -av "' . $fullPathFile . '" "' . $newFullPathFile . '"');
        $process->mustRun();
    }
}
