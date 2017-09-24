<?php

namespace Simples\Controller;

use Simples\Http\Controller;
use Simples\Http\Response;

/**
 * Class QueryController
 * @package Simples\Controller
 */
abstract class QueryController extends Controller
{
    /**
     * @param mixed $content (null)
     * @param array $meta
     * @param int $code
     * @return Response
     */
    protected function answer($content = null, $meta = [], $code = 200): Response
    {
        return $this
            ->response()
            ->api($content, $code, $meta);
    }
}