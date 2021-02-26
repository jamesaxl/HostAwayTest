<?php

namespace app\base;

/**
 * Class Request
 * @package app\base
 */
class Request
{
    /**
     * @return string
     */
    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');

        if (false === $position) {
            return $path;
        }

        return substr($path, 0, $position);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $params = [];
        $paramsStr = $_SERVER["QUERY_STRING"];
        $position = strpos($paramsStr, '&');

        if ($position) {
            $paramsArray = explode('&', $paramsStr);

            foreach ($paramsArray as $param) {
                $value = explode('=', $param);
                $params[$value[0]] = $value[1];
            }
        } else {
            $value = explode('=', $paramsStr);
            $params[$value[0]] = $value[1];
        }
        return $params;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        $content = [];
        if ('post' === $this->getMethod()) {

            $contentType = !empty($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
            if (strcasecmp($contentType, 'application/json') === 0) {
                $content = json_decode(trim(file_get_contents("php://input")),true);
            }

            foreach ($_POST as $key => $value ) {
                $content[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $content;
    }
}