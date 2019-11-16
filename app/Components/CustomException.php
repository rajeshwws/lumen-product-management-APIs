<?php

namespace App\Components;


use Throwable;

class CustomException extends \Exception
{
    protected $special_code;
    protected $data;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->data = null;
        $this->special_code = $code;
    }

    public function getSpecialCode()
    {
        return $this->special_code;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

}
