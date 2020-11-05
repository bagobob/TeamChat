<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
   /* /**
     * @Route("/home", name="home")
     */
    /*
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    */

    /**
     * @Route("/home", name="app_home")
     */
    public function home()
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/profil", name="app_profil")
     */
    public function show_profile()
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('home/profile.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/annuaire", name="app_annuary", methods="GET")
     */
    public function show_annuary(UserRepository $userRepository) :Response
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }
        $users = $userRepository->findBy([],['createdAt' => 'DESC']);

        return $this->render('home/annuary.html.twig', [
            'controller_name' => 'HomeController',
            'users'    => $users,
        ]);
    }
}
