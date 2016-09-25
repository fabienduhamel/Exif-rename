<?php

namespace App;

use Symfony\Component\HttpFoundation\File\File;

class ExifFile extends File
{
    private $data;

    public function __construct($path, $checkPath = true)
    {
        parent::__construct($path, $checkPath);
        $this->data = @exif_read_data($this->getRealPath());
        if (!$this->getModelString() || !$this->getShotDate()) {
            throw new \RuntimeException(
                sprintf("File '%s' does not have mandatory exif data.", $path)
            );
        }
    }

    public function getModelString()
    {
        $model = $this->data['Model'];

        // Trim at first slash if exists.
        if ($indexOfSlash = strpos($model, '/')) {
            $model = substr($model, 0, $indexOfSlash);
        }

        $model = trim($model);

        return str_replace(' ', '-', $model);
    }

    public function getShotDate()
    {
        return \DateTime::createFromFormat('Y:m:d H:i:s', $this->data['DateTimeOriginal']);
    }
}
