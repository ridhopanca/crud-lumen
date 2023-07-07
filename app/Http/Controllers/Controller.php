<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function failedValidation($response_code = 500, $message = "", $errors = null)
    {
        $response = [];
        $response['response_code'] = $response_code;
        $response['response_desc'] = $message;
        if($errors){
            $response['response_error'] = $errors;
        }
        return response()->json($response,$response_code);
    }
}
