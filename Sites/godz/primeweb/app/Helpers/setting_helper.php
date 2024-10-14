<?php

use App\Models\Setting;
use Faker\Provider\Image;


function getSettings($name = '')
{
    $settingList = array();
    if ($name == '') {
        $setting = Setting::get();
    } else {
        $setting = Setting::where('name', $name)->get();
    }
    foreach ($setting as $row) {
        $settingList[$row->name] = $row->value;
    }
    return $settingList;
}

function getadminSettings($name = '')
{
    $settingList = array();
    if ($name == '') {
        $setting = Setting::get();
    } else {
        $setting = Setting::where('name', $name)->get();
    }
    foreach ($setting as $row) {
        $settingList[$row->name] = $row->value;
    }
    return $settingList;
}
function changeEnv($data = array())
{
    if (count($data) > 0) {

        // Read .env-file
        $env = file_get_contents(base_path() . '/.env');
        // Split string on every " " and write into array
        $env = explode(PHP_EOL, $env);
        // $env = preg_split('/\s+/', $env);
        foreach ($env as $env_key => $env_value) {
            $entry = explode("=", $env_value);
            $temp_env_keys[] = $entry[0];

        }
        // Loop through given data
        foreach ((array)$data as $key => $value) {
            $key_value = $key . "=" . $value;

            if (in_array($key, $temp_env_keys)) {
                // Loop through .env-data
                foreach ($env as $env_key => $env_value) {
                    // Turn the value into an array and stop after the first split
                    // So it's not possible to split e.g. the App-Key by accident
                    $entry = explode("=", $env_value);
                    // // Check, if new key fits the actual .env-key
                    if ($entry[0] == $key) {

                        // If yes, overwrite it with the new one

                        if($key != 'APP_NAME'){
                            $env[$env_key] = $key . "=" . str_replace('"', '', $value);
                        }else{
                            $env[$env_key] = $key . "=" . $value;
                        }

                    } else {
                        // If not, keep the old one
                        $env[$env_key] = $env_value;
                    }
                }
            } else {
                $env[] = $key_value;
            }
        }
        // Turn the array back to an String
        $env = implode("\n", $env);

        // And overwrite the .env with the new data
        file_put_contents(base_path() . '/.env', $env);

        return true;
    } else {
        return false;
    }
}

function resizeImage($image)
{
    Image::make($image)->save(null,50);
}

function getTimezoneList()
{
    static $timezones = null;

    if ($timezones === null) {
        $list = DateTimeZone::listAbbreviations();
        $idents = DateTimeZone::listIdentifiers();

        $data = $offset = $added = array();
        foreach ($list as $abbr => $info) {
            foreach ($info as $zone) {
                if (!empty($zone['timezone_id']) and !in_array($zone['timezone_id'], $added) and in_array($zone['timezone_id'], $idents)) {
                    $z = new DateTimeZone($zone['timezone_id']);
                    $c = new DateTime('', $z);
                    $zone['time'] = $c->format('H:i a');
                    $offset[] = $zone['offset'] = $z->getOffset($c);
                    $data[] = $zone;
                    $added[] = $zone['timezone_id'];
                }
            }
        }

        array_multisort($offset, SORT_ASC, $data);
        $i = 0;
        $temp = array();
        foreach ($data as $key => $row) {
            $temp[0] = formatOffset($row['offset']);
            $temp[1] = $row['timezone_id'];
            $timezones[$i++] = $temp;
        }
    }
    return $timezones;
}

function formatOffset($offset)
{
    $hours = $offset / 3600;
    $remainder = $offset % 3600;
    $sign = $hours > 0 ? '+' : '-';
    $hour = (int)abs($hours);
    $minutes = (int)abs($remainder / 60);

    if ($hour == 0 and $minutes == 0) {
        $sign = ' ';
    }
    return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0');
}
