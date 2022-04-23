<?php
/**
 * Engine.php
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

use \InvalidArgumentException;
use \RuntimeException;

class Engine
{

    public const VERSION = '0.3';

    protected static string $extension = '.template.php';

    protected static ?string $viewsDir = null;

    protected static ?string $cacheDir = null;

    protected static ?Parsed $parsed = null;

    protected static ?Cache $cache = null;

    protected static array $errors = [];

    protected static ?int $timeout = 86400;

    protected static bool $outputCompress = false;

    public ?Form $form = null;

    public function __construct(string $viewsDir, string $cacheDir)
    {
        self::$cacheDir = $cacheDir;
        self::$viewsDir = \rtrim(\rtrim($viewsDir, '/'), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
        $this->form = new Form();

    }

    public function directive(string $directive, \Closure $closure): self
    {
        self::getParsed()->addDirective($directive, $closure);
        return $this;
    }

    public function extension(string $extension = '.template.php'): self
    {
        self::$extension = $extension;
        return $this;
    }

    public function timeout(?int $ttl = 86400): self
    {
        if($ttl !== null & $ttl < 0){
            $ttl = 0;
        }
        self::$timeout = $ttl;
        return $this;
    }

    public function compress(bool $compress = false): self
    {
        self::$outputCompress = $compress;
        return $this;
    }

    /**
     * @param string $name
     * @param array|object $data
     * @return void
     */
    public function parse(string $name, $data = []): void
    {
        $path = self::viewPath($name);
        if(!\file_exists($path)){
            throw new RuntimeException('Template engine could not find the view file "'.$name.'".');
        }
        if(\is_object($data)){
            $data = \get_object_vars($data);
        }
        \extract($data);
        $cacheName = \md5($path) . '.php';
        if(self::getCache()->has($cacheName)){
            $cachingTime = self::getCache()->cachingTime($cacheName);
            if((\filemtime($path) === $cachingTime && self::$timeout === null) || ($cachingTime + self::$timeout) > \time()){
                require self::getCache()->cachePath($cacheName);
                return;
            }
            self::getCache()->delete($cacheName);
        }
        if(($content = @\file_get_contents($path)) === FALSE){
            throw new RuntimeException('Template engine could not read view file "'.$name.'".');
        }
        $content = self::getParsed()->setContent($content)->parse()->getContent();
        if(self::$outputCompress === TRUE){
            $content = \preg_replace('/\s+/', ' ', $content);
        }
        self::getCache()->write($cacheName, $content);
        require self::getCache()->cachePath($cacheName);
    }

    private static function getParsed(): Parsed
    {
        if(self::$parsed === null){
            self::$parsed = new Parsed();
        }
        return self::$parsed;
    }

    private static function getCache(): Cache
    {
        if(self::$cache === null){
            self::$cache = new Cache();
            self::$cache->setDir(self::$cacheDir);
        }
        return self::$cache;
    }

    public static function viewPath(string $name): string
    {
        $name = \ltrim($name, '/');
        $extensionLen = \strlen(self::$extension);
        if(\substr($name, -($extensionLen)) !== self::$extension){
            $name .= self::$extension;
        }
        return self::$viewsDir . $name;
    }

    public static function error(string $err): void
    {
        self::$errors[] = $err;
    }

}
