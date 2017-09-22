<?php


function possible_values ($value, array $possible) : bool
{
    switch (gettype($value)) {

        case 'array':

            foreach ($value as $v) {
                $found = false;
                foreach ($possible as $p) {
                    if ($v === $p) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    return false;
                }
            }
            return true;



        case 'string':
        case 'integer':

            foreach ($possible as $p) {
                if ($value === $p) { return true; }
            }
            return false;


        default: break;
    }

}