<?php
namespace App\Controllers;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthController extends Controller
{
    public function logoutAction()
    {
        $this->session->clear();

        return $this->redirect('GET_');
    }

    /**
     * Save Trello token into session
     *
     * Workaround for token as fragment.
     *
     * @return mixed
     */
    public function tokenAction()
    {
        $token = $this->request->get('token');
        if ($token) {
            $this->session->set('trello-token', $token);

            return $this->redirect('GET_');
        }

        return $this->twig->render('token.twig', ['apikey' => $this->app['config']['api-key']]);
    }
}
