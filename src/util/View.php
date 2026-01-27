<?php


namespace Util;
use Symfony\Component\HttpFoundation\Response;

class View
{
    private $viewsPath;

    public function __construct($viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?: __DIR__ . '/../../views';
    }

    public function render($view, $data = [])
    {
        extract($data);
        ob_start();
        include "{$this->viewsPath}/{$view}.php";
        $content = ob_get_clean();
        return new Response($content);
    }
}