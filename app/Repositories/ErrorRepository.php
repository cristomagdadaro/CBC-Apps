<?php

namespace App\Repositories;

use Exception;
use Throwable;

class ErrorRepository
{
    protected Exception $exception;

    public function __construct(Exception $e)
    {
        $this->exception = $e;
    }

    public function getErrorMessage(): array
    {
        return $this->findErrorCodeMessage($this->getErrorCode());
    }

    private function findErrorCodeMessage($code): array
    {
        $listOfErrorCodes = config('error_codes');
        //
        if (array_key_exists($code, $listOfErrorCodes)) {
            return [
                'errno' => $code,
                'message' => $listOfErrorCodes[$code],
            ];
        } else {
            return [
                'errno' => 500,
                'message' => 'Internal Server Error',
                'error' => $this->exception,
            ];
        }


    }

    public function getErrorCode()
    {
        return $this->exception->getCode();
    }

}
