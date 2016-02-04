<?php
namespace App\Controllers;

class Controller
{
    protected $app;
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    protected $twig;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    public function __construct($app)
    {
        $this->app     = $app;
        $this->request = $app['request'];
        $this->twig    = $app['twig'];
        $this->session = $app['session'];
    }

    protected function redirect($route)
    {
        return $this->app->redirect($this->app['url_generator']->generate($route));
    }
}
