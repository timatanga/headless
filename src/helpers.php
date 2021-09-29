<?php

/*
 * This file is part of the Headless package.
 *
 * (c) Mark Fluehmann mark.fluehmann@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value, ...$args)
    {
        return $value instanceof \Closure ? $value(...$args) : $value;
    }
}


if (! function_exists('retry')) {

    /**
     * Retry an operation a given number of times.
     *
     * @param  int  $times
     * @param  callable  $callback
     * @param  int|\Closure  $sleepMilliseconds
     * @param  callable|null  $when
     * @return mixed
     *
     * @throws \Exception
     */
    function retry($times, callable $callback, $sleepMilliseconds = 0, $when = null)
    {
        $attempts = 0;

        beginning:
        $attempts++;
        $times--;

        try {
            return $callback($attempts);

        } catch (\Exception $e) {

            if ( $times < 1 || ($when && ! $when($e)) )
                throw $e;

            if ($sleepMilliseconds)
                usleep(value($sleepMilliseconds, $attempts) * 1000);

            goto beginning;
        }
    }
}