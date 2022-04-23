<?php
/**
 * Cache.php
 *
 * This file is part of IceEngine.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 IceEngine
 * @license    https://github.com/muhametsafak/IceEngine/blob/main/LICENSE  MIT
 * @version    0.3
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace IceEngine;

use \RuntimeException;
use \InvalidArgumentException;

class Cache
{
    protected static ?string $dir = null;

    public function setDir(string $dir)
    {
        if(!\is_dir($dir)){
            throw new InvalidArgumentException('An existing directory path must be specified for TemplateEngine caching.');
        }
        self::$dir = \rtrim(\rtrim($dir, '/'), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
    }

    public function cachePath(string $name): string
    {
        self::dirValidation();
        $name = \ltrim(\ltrim($name, '/'), \DIRECTORY_SEPARATOR);
        return self::$dir . $name;
    }

    /**
     * @param string $name
     * @return false|int
     */
    public function cachingTime(string $name)
    {
        self::dirValidation();
        $path = $this->cachePath($name);
        if(!\file_exists($path)){
            return false;
        }
        return \filemtime($path);
    }

    public function has(string $name): bool
    {
        self::dirValidation();
        return \file_exists($this->cachePath($name));
    }

    public function write(string $name, string $content): bool
    {
        self::dirValidation();
        $path = $this->cachePath($name);
        if(@\file_put_contents($path, $content) === FALSE){
            return false;
        }
        return true;
    }

    public function delete(string $name): bool
    {
        self::dirValidation();
        $path = $this->cachePath($name);
        return @\unlink($path);
    }

    private static function dirValidation()
    {
        if(self::$dir === null){
            throw new RuntimeException('An existing directory path must be specified for TemplateEngine caching.');
        }
    }

}
