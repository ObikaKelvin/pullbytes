<?php

namespace App\Http\Controllers;

class Util{
    static function send_request($data, $status_code)
    {
        if(gettype($data) === 'Array'){
            return response()->json($data, $status_code);
        }

        return response()->json([
            'data' => $data
        ], $status_code);
    }
}

?>
