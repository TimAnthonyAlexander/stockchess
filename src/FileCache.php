<?php

namespace src;

class FileCache
{
    // static methods isset, get, set, delete
    // read from file ../filecache.json
    // Load filecache.json into $GLOBALS['filecache']
    // Save $GLOBALS['filecache'] to filecache.json on shutdown

    // Generate name from ...$args
    public static function generateName(...$args): string
    {
        return md5(serialize($args));
    }

    public static function isset(string $name): bool
    {
        return isset($GLOBALS['filecache'][$name]);
    }

    public static function get(string $name): mixed
    {
        return $GLOBALS['filecache'][$name] ?? null;
    }

    public static function set(string $name, mixed $data): mixed
    {
        $GLOBALS['filecache'][$name] = $data;
        return $data;
    }

    public static function delete(string $name): void
    {
        if (self::isset($name)) {
            unset($GLOBALS['filecache'][$name]);
        }
    }

    public static function save(): void
    {
        file_put_contents(__DIR__.'/../filecache.json', json_encode($GLOBALS['filecache']));
    }

    public static function load(): void
    {
        if (!file_exists(__DIR__.'/../filecache.json')) {
            file_put_contents(__DIR__.'/../filecache.json', '{}');
        }
        $GLOBALS['filecache'] = json_decode(file_get_contents(__DIR__.'/../filecache.json'), true);
    }
}
