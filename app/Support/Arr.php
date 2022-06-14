<?php
namespace App\Support;

class Arr extends \Illuminate\Support\Arr
{

    /**
     * Key an associative array by a field or using a callback.
     *
     * @param  array  $array
     * @param  callable|array|string
     * @return array
     */
    public static function keyByMultiple($array, $keyBy)
    {
        return Collection::make($array)->keyByMultiple($keyBy)->all();
    }

}
