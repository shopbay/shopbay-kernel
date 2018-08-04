<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SDateTime
 * Accept $timestamp rather than a date/time string (As in parent constructor)
 *
 * @author kwlok
 */
class SDateTime extends DateTime 
{
    public static $Format = 'Y/m/d h:i:s A';
    /**
     * Custom constructor 
     * 
     * @param int $timestamp parameter is an integer Unix timestamp, e.g. time()
     * @param DateTimeZone $dtz
     */
    public function __construct($timestamp = null, DateTimeZone $dtz = null)
    {
        if($dtz === null){
            $dtz = new DateTimeZone(date_default_timezone_get());
        }
        parent::__construct(date(self::$Format,$timestamp), $dtz);
    }

    public function __toString()
    {
        return (string)parent::format(self::$Format);
    }
}