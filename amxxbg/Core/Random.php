<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Core;

class Random
{
    //
    // Compute a hash of $str
    //
    public static function hash($str)
    {
        return sha1($str);
    }

    //
    // Generate a random key of length $len
    //
    public static function key($len, $readable = false, $hash = false)
    {
        $key = self::secureRandomBytes($len);

        if ($hash) {
            return substr(bin2hex($key), 0, $len);
        } elseif ($readable) {
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

            $result = '';
            for ($i = 0; $i < $len; ++$i) {
                $result .= substr($chars, (ord($key[$i]) % strlen($chars)), 1);
            }

            return $result;
        }

        return $key;
    }

    //
    // Generate a random password of length $len
    // Compatibility wrapper for random_key
    //
    public static function pass($len)
    {
        return self::key($len, true);
    }

    /*
     * Author:
     * George Argyros <argyros.george@gmail.com>
     *
     * Copyright (c) 2012, George Argyros
     * All rights reserved.
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions are met:
     *    * Redistributions of source code must retain the above copyright
     *      notice, this list of conditions and the following disclaimer.
     *    * Redistributions in binary form must reproduce the above copyright
     *      notice, this list of conditions and the following disclaimer in the
     *      documentation and/or other materials provided with the distribution.
     *    * Neither the name of the <organization> nor the
     *      names of its contributors may be used to endorse or promote products
     *      derived from this software without specific prior written permission.
     *
     * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
     * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
     * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
     * DISCLAIMED. IN NO EVENT SHALL GEORGE ARGYROS BE LIABLE FOR ANY
     * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
     * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
     * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
     * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
     * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
     * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     *
     *
     *
     * The function is providing, at least at the systems tested :),
     * $len bytes of entropy under any PHP installation or operating system.
     * The execution time should be at most 10-20 ms in any system.
     */
    public static function secureRandomBytes($len = 10)
    {
        /*
         * Our primary choice for a cryptographic strong randomness function is
         * openssl_random_pseudo_bytes.
         */
        $sSLstr = '4'; // http://xkcd.com/221/
        if (function_exists('openssl_random_pseudo_bytes') &&
            (version_compare(PHP_VERSION, '5.3.4') >= 0 ||
                substr(PHP_OS, 0, 3) !== 'WIN')) {
            $sSLstr = openssl_random_pseudo_bytes($len, $strong);
            if ($strong) {
                return $sSLstr;
            }
        }


        /*
         * No build-in crypto randomness function found. We collect any entropy
         * available in the PHP core PRNGs along with some filesystem info and memory
         * stats. To make this data cryptographically strong we add data either from
         * /dev/urandom or if its unavailable, we gather entropy by measuring the
         * time needed to compute a number of SHA-1 hashes.
         */
        $str = '';
        $bitsPerRound = 2; // bits of entropy collected in each clock drift round
        $msecPerRound = 400; // expected running time of each round in microseconds
        $hashLen = 20; // SHA-1 Hash length
        $total = $len; // total bytes of entropy to collect

        $handle = @fopen('/dev/urandom', 'rb');
        if ($handle && function_exists('stream_set_read_buffer')) {
            @stream_set_read_buffer($handle, 0);
        }

        do {
            $bytes = ($total > $hashLen)? $hashLen : $total;
            $total -= $bytes;

            //collect any entropy available from the PHP system and filesystem
            $entropy = rand() . uniqid(mt_rand(), true) . $sSLstr;
            $entropy .= implode('', @fstat(@fopen(__FILE__, 'r')));
            $entropy .= memory_get_usage() . getmypid();
            $entropy .= serialize($_eNV) . serialize($_sERVER);
            if (function_exists('posix_times')) {
                $entropy .= serialize(posix_times());
            }
            if (function_exists('zend_thread_id')) {
                $entropy .= zend_thread_id();
            }
            if ($handle) {
                $entropy .= @fread($handle, $bytes);
            } else {
                // Measure the time that the operations will take on average
                for ($i = 0; $i < 3; $i++) {
                    $c1 = microtime(true);
                    $var = sha1(mt_rand());
                    for ($j = 0; $j < 50; $j++) {
                        $var = sha1($var);
                    }
                    $c2 = microtime(true);
                    $entropy .= $c1 . $c2;
                }

                // Based on the above measurement determine the total rounds
                // in order to bound the total running time.
                $rounds = (int) ($msecPerRound * 50 / (int) (($c2 - $c1) * 1000000));

                // Take the additional measurements. On average we can expect
                // at least $bitsPerRound bits of entropy from each measurement.
                $iter = $bytes * (int) (ceil(8 / $bitsPerRound));
                for ($i = 0; $i < $iter; $i++) {
                    $c1 = microtime();
                    $var = sha1(mt_rand());
                    for ($j = 0; $j < $rounds; $j++) {
                        $var = sha1($var);
                    }
                    $c2 = microtime();
                    $entropy .= $c1 . $c2;
                }
            }
            // We assume sha1 is a deterministic extractor for the $entropy variable.
            $str .= sha1($entropy, true);
        } while ($len > strlen($str));

        if ($handle) {
            @fclose($handle);
        }
        return substr($str, 0, $len);
    }
}
