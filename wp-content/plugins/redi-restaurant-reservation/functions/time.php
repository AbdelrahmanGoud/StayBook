<?php

class ReDiTime
{
    public static function getReservationTime($persons, $default_duration)
    {
        // Override duration based on number of persons visiting
        $parentDirectory = dirname(plugin_dir_path(__FILE__));
        $filename = $parentDirectory . '/reservationtime.json';

        if (file_exists($filename) && $persons) {
            $json = json_decode(file_get_contents($filename), true);
            if ($json !== null) {
                if (array_key_exists($persons, $json)) {
                    return (int)$json[$persons];
                }
            }
        }

        return $default_duration;
    }

    public static function loadCustomDurations()
    {
        // Load durations from config and show to users to select
        $parentDirectory = dirname(plugin_dir_path(__FILE__));
        $filename = $parentDirectory . '/customduration.json';

        if (file_exists($filename)) {
            return json_decode(file_get_contents($filename), true);
        }

        return null;
    }

    public static function convert_to_js_format($format)
    {
        $convert = array(
//Day       ---     ---
//Week      ---     ---
//Month     ---     ---
//Year      ---     ---
//Time      ---     ---

//%I    Two digit representation of the hour in 12-hour format  01 through 12
//hh    Hours; leading zero for single-digit hours (12-hour clock).
            'I' => 'hh',
//%l (lower-case 'L')   Hour in 12-hour format, with a space preceding single digits    1 through 12
//h     Hours; no leading zero for single-digit hours (12-hour clock).
            'l' => 'h',
//%k    Two digit representation of the hour in 24-hour format, with a space preceding single digits    0 through 23
//H     Hours; no leading zero for single-digit hours (24-hour clock).
            'k' => 'H',
//%H    Two digit representation of the hour in 24-hour format  00 through 23
//HH    Hours; leading zero for single-digit hours (24-hour clock).
            'H' => 'HH',
//%M    Two digit representation of the minute  00 through 59
//MM    Minutes; leading zero for single-digit minutes.
            'M' => 'MM',
//%P    lower-case 'am' or 'pm' based on the given time     Example: am for 00:31, pm for 22:23
//tt    Lowercase, two-character time marker string: am or pm.
            'P' => 'tt',
//%p    UPPER-CASE 'AM' or 'PM' based on the given time     Example: AM for 00:31, PM for 22:23
//TT    Uppercase, two-character time marker string: AM or PM.
            'p' => 'TT',
        );

        $result = '';
        foreach (str_split($format) as $char) {
            if ($char == '%') {
                $result .= '';
            } elseif (array_key_exists($char, $convert)) {
                $result .= $convert[$char];
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    public static function dropdown_time_format()
    {
        $wp_time_format = get_option('time_format');
        $wp_time_format_array = str_split($wp_time_format);
        foreach ($wp_time_format_array as $index => $format_char) // some users have G \h i \m\i\n
        {
            if ($format_char === '\\') {
                $wp_time_format_array[$index] = '';
                if (isset($wp_time_format_array[$index + 1])) {
                    $wp_time_format_array[$index + 1] = '';
                }
            }
        }
        $wp_time_format = implode('', $wp_time_format_array);
        $is_am_pm = strpos($wp_time_format, 'g');
        $is_am_pm_lead_zero = strpos($wp_time_format, 'h');

        $is_24 = strpos($wp_time_format, 'G');
        $is_24_lead_zero = strpos($wp_time_format, 'H');

        if ($is_am_pm !== false || $is_am_pm_lead_zero !== false) {
            $a = stripos($wp_time_format, 'a');
            $am_text = '';
            if ($a !== false) {
                $am_text = $wp_time_format[$a];
            }
            if ($is_am_pm !== false) {
                return $wp_time_format[$is_am_pm] . ' ' . $am_text;
            }
            if ($is_am_pm_lead_zero !== false) {
                return $wp_time_format[$is_am_pm_lead_zero] . ' ' . $am_text;
            }
        }
        if ($is_24 !== false) {
            return $wp_time_format[$is_24];
        }
        if ($is_24_lead_zero !== false) {
            return $wp_time_format[$is_24_lead_zero];
        }

        return 'H'; //if no time format found use 24 h with lead zero
    }

    public static function format_time($start_time, $language, $format)
    {

        global $q_config;
        if (function_exists('qtrans_convertTimeFormat')) {// time format from qTranslate
            $format = $q_config['time_format'][$language];

            return qtrans_strftime($format, strtotime($start_time));
        } elseif (function_exists('ppqtrans_convertTimeFormat')) { //and qTranslate Plus
            $format = $q_config['time_format'][$language];

            return ppqtrans_strftime($format, strtotime($start_time));
        }

        return date($format, strtotime($start_time));
    }

    public static function load_time_format($lang, $default_time_format)
    {
        $parentDirectory = dirname(plugin_dir_path(__FILE__));
        $filename = $parentDirectory . '/time_format.json';        

        if (file_exists($filename)) {
            $json = json_decode(file_get_contents($filename), true);
            if (array_key_exists($lang, $json)) {
                return $json[$lang];
            }
        }

        return $default_time_format;
    }
}
?>