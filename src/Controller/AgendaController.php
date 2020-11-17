<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgendaController extends AbstractController
{
    /**
     * @Route("/agendaX", name="agenda")
     * @param UserRepository $userRepository
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function index(UserRepository $userRepository,Request $request,EntityManagerInterface $em): Response
    {
        $users = $userRepository->findBy([],['createdAt' => 'DESC']);

        $taille = count($users) ;
        //Je crée un tableau pour stocker les utilisateurs que l'utilisateur courant a rajouté à l'agenda
        $data = array();
        $i = 0 ;
        while ($i < $taille)
        {
            $data[$i] = $users[$i]->getUsername();

            $i++;
        }

        //dd($data);
        return $this->render('agenda/index.html.twig', [
            'controller_name' => 'AgendaController',
            'data'    => $data,
        ]);
    }
}
