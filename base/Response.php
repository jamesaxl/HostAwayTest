<?php

namespace app\base;

/**
 * Class Response
 * @package app\base
 */
class Response
{
    /**
     * @param int $status
     */
    public function httpStatus(int $status): void
    {
        http_response_code($status);
    }

    /**
     * @param string $header
     */
    public function httpHeader(string $header): void
    {
        header($header);
    }

    /**
     * @param array $array
     * @param int $status
     * @return string
     */
    public function json(array $array, int $status = 200): string
    {
        $this->httpStatus($status);
        $this->httpHeader('Content-Type: application/json');
        return json_encode($array);
    }
}