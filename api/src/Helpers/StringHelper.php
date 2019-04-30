<?php


namespace App\Helpers;


class StringHelper
{

    /**
     * @param string $string
     * @param string $query
     * @return bool
     */
    public static function startWith($string, $query)
    {
        return substr($string, 0, strlen($query)) === $query;
    }

    /**
     * @param string $string
     * @param string $query
     * @return bool
     */
    public static function endWith($string, $query)
    {
        return substr($string, strlen($string) - strlen($query)) === $query;
    }

    /**
     * @param $string
     * @return string
     */
    public static function camelCaseToSnakeCase($string)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    /**
     * @param $string
     * @return string
     */
    public static function snakeCaseToWords($string)
    {
        return implode(' ', explode('_', $string));
    }

}
