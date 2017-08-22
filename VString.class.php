<?php
namespace vcms;

class VString {

    static function ToCamelCase (string $string, string $delimiter = '-'): string
    {
        $pieces = explode($delimiter, $string);
        $pieces = array_map(function ($p) {
            $p[0] = strtoupper($p[0]);
            return $p;
        }, $pieces);
        return join('', $pieces);
    }

    static function to_hyphens (string $string, string $delimiter = '-'): string
    {
        $newString = '';

        for ($i = 0; $i < strlen($string); ++$i) {
            $l = ord($string[$i]);


            if ($l >= 65 && $l <= 90)
            {
                $i !== 0 && ($newString .= $delimiter);
                $newString .= chr(97 + ($l - 65));
                continue;
            }

            $newString .= $string[$i];
        }
        return $newString;
    }
}