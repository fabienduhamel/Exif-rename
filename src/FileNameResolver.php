<?php

namespace App;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class FileNameResolver
{
    public function resolve(ExifFile $file, $dest, $iteration = 0)
    {
        $newFileName = $file->getShotDate()->format('Ymd_His') .
            '_' . $file->getModelString() .
            ($iteration > 0 ? '_' . $iteration : '') .
            '.' .
            $file->getExtension();

        while (true) {
            try {
                new File($dest . DIRECTORY_SEPARATOR . $newFileName);
            } catch (FileNotFoundException $e) {

                return $newFileName;
            }

            return $this->resolve($file, $dest, ++$iteration);
        }
    }
}
