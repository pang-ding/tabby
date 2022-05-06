<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Utils;

use ErrorSys;

class Upload
{
    protected $fileName;
    protected $fileSize;
    protected $fileType;
    protected $tempFile;
    protected $error;

    protected $path;

    public function __construct($key)
    {
        $file = $_FILES[$key];
        if ($file['error'] > 0) {
            $this->error = $file['error'];
        } else {
            $this->error    = null;
            $this->fileName = $file['name'];
            $this->fileSize = $file['size'];
            $this->fileType = $file['type'];
            $this->tempFile = $file['tmp_name'];
        }
    }

    public static function saveByKey(string $key, string $path): string
    {
        $upload = new static($key);
        if (!empty($upload->error)) {
            throw new ErrorSys("Upload: '{$key}' - '{$upload->error}'");
        }
        if (!$upload->saveTo($path)) {
            throw new ErrorSys("Upload: '{$key}' - '{$path}'");
        }

        return $upload->path;
    }

    public function saveTo(string $path): bool
    {
        $rst = move_uploaded_file($this->tempFile, $path);
        if ($rst) {
            $this->path = $path;
        }

        return $rst;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getFileSize()
    {
        return $this->fileSize;
    }

    public function getFileType()
    {
        return $this->fileType;
    }

    public function getTempFile()
    {
        return $this->tempFile;
    }

    public function getError()
    {
        return $this->error;
    }
}
