<?php

namespace App\Helpers;


class DateHelper
{
    const UTC_TZ = 'UTC';
    const UTC_PARIS_TZ = 'Europe/Paris';

    /**
     * @param \DateTime $date
     * @param $language
     * @return bool|string
     */
    public static function getDateByLanguage(\DateTime $date, $language)
    {
        $formatter = new \IntlDateFormatter($language, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        return $formatter->format($date);
    }

    /**
     * @return string
     */
    public static function getServerTimeZone()
    {
        return date_default_timezone_get();
    }

    /**
     * @param \DateTime $dateTime
     * @return \DateTime
     */
    public static function dateToUtc(\DateTime $dateTime)
    {
        $dateTime->setTimezone(new \DateTimeZone(self::UTC_TZ));
        return $dateTime;
    }

    /**
     * @param \DateTime $dateTime
     * @return \DateTime
     */
    public static function dateToParis(\DateTime $dateTime)
    {
        $dateTime->setTimezone(new \DateTimeZone(self::UTC_PARIS_TZ));
        return $dateTime;
    }

    /**
     * @param $timezone
     * @return \DateTime
     * @throws \Exception
     */
    public static function getToday($timezone = self::UTC_TZ)
    {
        $dateTime = new \DateTime();
        $dateTime->setTimezone(new \DateTimeZone($timezone));
        return $dateTime;
    }
}
