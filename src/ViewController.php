<?php

namespace Simples\Controller;

use Simples\Http\Controller;
use Simples\Http\Response;
use Simples\Template\View;

/**
 * Class ViewController
 * @package Simples\Controller
 */
class ViewController extends Controller
{
    /**
     * @param string $template
     * @param array $data
     * @return Response
     */
    public function view(string $template, array $data = []): Response
    {
        $view = new View(resources('view'));
        $html = $view->render($template, $data);
        return $this->answer($html);
    }

    /**
     * @param mixed $content (null)
     * @param array $meta
     * @param int $code
     * @return Response
     * @SuppressWarnings("unused")
     */
    protected function answer($content = null, $meta = [], $code = 200): Response
    {
        return $this->response()->html($content)->withStatus($code);
    }
}
