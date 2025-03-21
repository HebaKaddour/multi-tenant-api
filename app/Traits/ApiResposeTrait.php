<?php

namespace App\Traits;

trait ApiResposeTrait
{public function successResponse($data, $message, $code) {
    return response()->json(['data' => $data, 'message' => $message], $code);
}

public function errorResponse($message ,$code) {
    return response()->json(['message' => $message], $code);
}


}
