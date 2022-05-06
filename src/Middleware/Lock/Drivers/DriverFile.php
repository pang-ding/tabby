<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Middleware\Lock\Drivers;

use Tabby\Tabby;
use Tabby\Utils\StrUtils;

final class DriverFile extends DriverAbstract
{
    private $_dir;
    private $_lockFpA;
    private $_lockFpB;
    private $_lockFileA;
    private $_lockFileB;

    protected function init(array $driverAgrs): void
    {
        $this->_dir = StrUtils::dirLastSeparator(($driverAgrs['dir'] ?? Tabby::$Conf['tabby']['tmpPath'])) . 'lock/';
    }

    private function getFileName(string $offset)
    {
        return $this->_dir . $this->_lockName . '.' . $offset . '.' . date('Ymd', (($offset + 1) * $this->_ttl));
    }

    private function lockFile(string $file)
    {
        $fp = fopen($file, 'c');
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            fputs($fp, $this->_pid);
            fflush($fp);

            return $fp;
        }
        unset($fp);

        return false;
    }

    public function lock(): bool
    {
        $offset = floor(time() / $this->_ttl);

        $this->_lockFileA = $this->getFileName($offset);
        $this->_lockFpA   = $this->lockFile($this->_lockFileA);
        if ($this->_lockFpA) {
            $this->_lockFileB = $this->getFileName($offset + 1);
            $this->_lockFpB   = $this->lockFile($this->_lockFileB);
            if ($this->_lockFpB) {
                return true;
            }
            unset($this->_lockFpA);
        }

        return false;
    }

    public function unlock(): bool
    {
        if ($this->_lockFpB) {
            unset($this->_lockFpB);
            unlink($this->_lockFileB);
        }
        if ($this->_lockFpA) {
            unset($this->_lockFpA);
            unlink($this->_lockFileA);
        }

        return true;
    }

    public function checkOwner(): bool
    {
        return ((int) @file_get_contents($this->getFileName(floor(time() / $this->_ttl)))) === $this->_pid;
    }
}
