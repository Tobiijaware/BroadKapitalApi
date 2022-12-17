<?php

namespace App\Helpers;
use Illuminate\Support\Facades\DB;


/**
 *
 *
 * This class contains functions helpers for the entire project
 *
 */

  /**
     * return success response.
     *

    */

class Functions{
    public static function sendResponse($result, $message)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public static function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }


    function _format_number($number)
    {
        $a = str_replace(" ", "", $number);
        $b = str_replace("+234", "0", $a);
        $c = str_replace(",", "", $b);

        $d = str_replace("+", "", $c);
        $e = str_replace("*", "", $d);
        $f = str_replace("#", "", $e);
        return '234' . substr($f, 1);
    }

    public static function getStates(){
        $data = DB::table('states')->get();
        if ($data->isEmpty()) {
           return self::sendError([], "No data found");
        }
        return self::sendResponse($data, "successful");
    }


}
