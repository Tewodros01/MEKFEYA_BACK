<?php

namespace app\Helpers;

class Helper
{

    public static function IDGenerator($model, $trow, $length, $prefix)
    {
        $search = 'PRC-';
        $data = $model::orderBy($trow, 'desc')->first();
        if (!$data || strlen($data->$trow) <= strlen($prefix)) {
            $og_length = $length;
            $last_number = ' ';
        } else {
            $code = substr($data->$trow, strlen($prefix) + 1);
            $actial_last_number = ($code / 1) * 1;
            $increment_last_number = $actial_last_number + 1;
            $last_number_lenght = strlen($increment_last_number);
            $og_length = $length - $last_number_lenght;
            $last_number = $increment_last_number;
        }
        $zeros = "-";
        for ($i = 0; $i < $og_length; $i++) {
            $zeros .= "0";
        }
        return $prefix . $zeros . $last_number;
    }
}
