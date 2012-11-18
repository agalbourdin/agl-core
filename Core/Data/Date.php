<?php
namespace Agl\Core\Data;

/**
 * Generic methods to manipulate dates.
 *
 * @category Agl_Core
 * @package Agl_Core_Data
 * @version 0.1.0
 */

class Date
{
	/**
	 * The default timzeone used by AGL to generate dates and save them to
	 * database.
	 */
	const DEFAULT_TZ = 'UTC';

	/**
	 * The default date format used to return dates.
	 */
	const DATE_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Get the current date time on the default timezone.
	 *
	 * @return string
	 */
	public static function now()
	{
		return date(self::DATE_FORMAT);
	}

	/**
	 * Get the current date time on the application's timezone.
	 *
	 * @return string
	 */
	public static function nowTz()
    {
	    return self::toTz(self::now());
    }

    /**
	 * convert a date from the default timezone to the application's timezone.
	 *
	 * @param $pDate
	 * @return string
	 */
    public static function toTz($pDate)
    {
    	$time_object = new \DateTime($pDate, new \DateTimeZone(self::DEFAULT_TZ));
	    $time_object->setTimezone(new \DateTimeZone(\Agl::app()->getConfig('@app/global/timezone')));
	    return $time_object->format(self::DATE_FORMAT);
    }

    /**
	 * convert a date from the application's timezone to the default timezone.
	 *
	 * @param $pDate
	 * @return string
	 */
    public static function toDefault($pDate)
    {
    	$time_object = new \DateTime($pDate, new \DateTimeZone(\Agl::app()->getConfig('@app/global/timezone')));
	    $time_object->setTimezone(new \DateTimeZone(self::DEFAULT_TZ));
	    return $time_object->format(self::DATE_FORMAT);
    }

    /**
     * Format the date based on the locale and the requested format.
     *
     * @param string $pDate
     * @param string $pFormat
     * @return string
     */
    public static function format($pDate, $pFormat = 'short')
    {
    	switch ($pFormat) {
    		case 'short':
    			return strftime('%x', strtotime($pDate));
    			break;
    		case 'long':
    			return strftime('%x %H:%M', strtotime($pDate));
    			break;
    		case 'full':
    			return strftime('%x %H:%M:%S', strtotime($pDate));
    			break;
    	}

    	throw new \Agl\Exception("The requested date format is not correct");
    }
}
