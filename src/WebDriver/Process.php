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

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process as SymfonyProcess;
use timatanga\Headless\Exceptions\BinaryNotFoundException;
use timatanga\Headless\Exceptions\ProcessFailedException;

/*
 * The Process component executes commands in sub-processes.
 */
class Process {

    /**
     * @var process
     */ 
    protected $process;

    /**
     * Create Process Instance
     * 
     * @param string  $binary
     * @param array  $arguments
     * @param bool  $isShell
     * @param int  $timeout
     */
    public function __construct( string $binary, array $arguments = [], $isShell = true, int $timeout = 500 )
    {
        // resolve executable
        $executable = strpos($binary, '/') == false || strpos($binary, '\\') == false ? $binary : $this->findBinary($binary);

        // build command array
        $command = $this->buildCommand($executable, $arguments);

        // build command string
        $cmd = implode(' ', $command);

        if ( $isShell ) {
            $this->process = SymfonyProcess::fromShellCommandline($cmd);
        
        } else {
            $this->process = new SymfonyProcess([$cmd]);
        }

        $this->process->setTimeout($timeout);
    }


    /**
     * Build command
     * 
     * @param string  $executable
     * @param array  $params
     * @return void
     */
    public function buildCommand( string $executable, array $params = [] )
    {
        $command = [];

        $command[] = $executable;

        foreach ($params as $param) { $command[] = $param; };

        return $command;
    }


    /**
     * Execute process
     * 
     * @return void
     */
    public function execute()
    {
        $this->process->run();

        // executes after the command finishes
        if (! $this->process->isSuccessful() )
            throw new ProcessFailedException($this->process->getErrorOutput());

        return $this->process->getOutput();
    }


    /**
     * Find binary
     * 
     * @return string  $binary
     * @return string
     */
    public function findBinary( string $binary )
    {
        $finder = new ExecutableFinder();

        $path = $finder->find($binary);

        if (! $path )
            throw new BinaryNotFoundException('Could not find binary: ' . $binary);

        return $path;
    }

}
