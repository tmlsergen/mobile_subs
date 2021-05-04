<?php

function response_success($data = null, $message = 'success', $status = 200)
{
    return response()->json([
        'data' => $data,
        'message' => $message
    ], $status);
}

function response_error($error = null, $message = 'error', $status = 400)
{
    return response()->json([
        'error' => $error,
        'message' => $message
    ], $status);
}

function checkExpireDate($date) {
    return $date > now();
}
