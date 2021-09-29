<?php

/*
 * This file is part of the Headless package.
 *
 * (c) Mark Fluehmann mark.fluehmann@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace timatanga\Headless\WebDriver;

use Symfony\Component\Finder\Finder;

class DetectChrome
{

    /**
     * OS dependent lookups
     * 
     * PHP class constant PHP_OS_FAMILY returns one of:
     * 'windows', 'bsd', 'darwin', 'solaris', 'linux' or 'unknown'
     * 
     * @var string
     */
    private static $osLookup = [
        'window'    => [
            ['path' => '%ProgramFiles(x86)%\Google\Chrome\Application', 'binary' => 'chrome.exe']
        ],
        'bsd'       => [
            ['path' => '/usr/bin', 'binary' => 'google-chrome'],
            ['path' => '/usr/bin', 'binary' => 'google-chrome-stable'],
            ['path' => '/usr/bin', 'binary' => 'chromium'],
            ['path' => '/usr/bin', 'binary' => 'chromium-browser'],
        ],
        'darwin'    => [
            ['path' => '/Applications', 'binary' => 'Google Chrome']
        ],
        'solaris'   => [
            ['path' => '/usr/bin', 'binary' => 'google-chrome'],
            ['path' => '/usr/bin', 'binary' => 'google-chrome-stable'],
            ['path' => '/usr/bin', 'binary' => 'chromium'],
            ['path' => '/usr/bin', 'binary' => 'chromium-browser'],
        ],
        'linux'     => [
            ['path' => '/usr/bin', 'binary' => 'google-chrome'],
            ['path' => '/usr/bin', 'binary' => 'google-chrome-stable'],
            ['path' => '/usr/bin', 'binary' => 'chromium'],
            ['path' => '/usr/bin', 'binary' => 'chromium-browser'],
        ],
    ];



    /**
     * Get operating system family
     * 
     * @return string
     */
    public static function os()
    {
        return strtolower(PHP_OS_FAMILY);
    }


    /**
     * Get platform
     * 
     * @return string
     */
    public static function platform()
    {
        $os = PHP_OS;

        if ( PHP_OS_FAMILY == 'Windows' )
            return 'win32';

        if ( $os == 'Darwin' && php_uname('m') == 'arm64' )
            return 'mac64_m1';

        if ( $os == 'Darwin' && php_uname('m') == 'x86_64' )
            return 'mac64';

        if ( $os == 'Darwin' ) 
            return 'mac64';

        return 'linux64';
    }


    /**
     * Get browser binary path
     * 
     * @return string
     */
    public static function detect()
    {
        $osFamily = strtolower(PHP_OS_FAMILY);

        if ( array_key_exists('CHROME_PATH', $_SERVER) )
            return $_SERVER['CHROME_PATH'];

        if ( $osFamily == 'windows' && $path = self::queryRegistry() )
            return $path;

        if (! $queries = self::$osLookup[$osFamily] )
            throw new BinaryNotFoundException('Chrome binary could not been found');

        foreach ($queries as $os => $param) {
            
            $path = self::findBinary($param['path'], $param['binary']);

            if ( $path ) break;
        }

        return $path;
    }


    /**
     * Get browser version
     * 
     * @return string
     */
    private static function queryRegistry()
    {
        try {
            $registryKey = \shell_exec(
                'reg query "HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe" /ve'
            );

            \preg_match('/.:(?!.*:).*/', $registryKey, $matches);

            if ( isset($matches[0]) )
                return $matches[0];


        } catch (\Throwable $e) {}

        return null;
    }    


    /**
     * Find binary
     * 
     * @param string $path
     * @param string $file
     * @return string
     */
    private static function findBinary( string $path, string $file )
    {
        // create finder instance
        $finder = new Finder();

        // ignore directories without permission to read:
        $finder->ignoreUnreadableDirs()->name($file)->in($path);

        // return null if search failed
        if (! $finder->hasResults() ) 
            return null;

        // return iterators first real path
        foreach ($finder as $file) {
            $path = $file->getRealPath();
            break;
        }

        return $path;
    }
}
