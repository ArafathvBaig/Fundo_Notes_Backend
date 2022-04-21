<?php

namespace App\Exceptions;

use Exception;
class FundoNotesException extends Exception
{
    // public function errorMessage()
    // {
    //     return response()->json([
    //         'status' => $this->getCode(),
    //         'message' => $this->getMessage()
    //     ], $this->getCode());
    // }

    public function message()
    {
        return $this->getMessage();
    }
    public function statusCode()
    {
        return $this->getCode();
    }
}