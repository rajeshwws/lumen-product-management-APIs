<?php

namespace App\Components;


class Response
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    const LABEL_STATUS = 'status';
    const LABEL_DATA = 'data';
    const LABEL_MESSAGE = 'message';
    const LABEL_CODE = 'code';

    /**
     * Returns a success response
     *
     * @param array $data
     * @param string|null $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data=[], $message=null)
    {
        return response()->json([
            self::LABEL_STATUS => self::STATUS_SUCCESS,
            self::LABEL_MESSAGE => $message,
            self::LABEL_DATA => $data,
        ]);
    }

    /**
     * Returns a error response
     *
     * @param string $code
     * @param string $message
     * @param array|null $data
     * @param int $http_status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($code, $message, $data = null, $http_status = 400)
    {
        return response()->json([
            self::LABEL_CODE => $code,
            self::LABEL_STATUS => self::STATUS_ERROR,
            self::LABEL_MESSAGE => $message,
            self::LABEL_DATA => $data
        ])->setStatusCode($http_status);
    }

}
