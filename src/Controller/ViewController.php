<?php

namespace Simples\Controller;

use Simples\Http\Controller;
use Simples\Http\Response;
use Simples\Template\View;

/**
 * Class ViewController
 * @package Simples\Controller
 */
abstract class ViewController extends Controller
{
    /**
     * @param string $template
     * @param array $data
     * @return Response
     */
    public function view(string $template, array $data = []): Response
    {
        $view = new View(path(true, App::config('app')->views['root']));

        return $this->response()->html($view->render($template, $data));
    }

}
