<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\User;
use App\Entity\Conversation;
use App\Entity\Participant;
use App\Repository\AgendaRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Form\UserFormType;

class HomeController extends AbstractController
{
    
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;
    public function __construct(EntityManagerInterface $entityManager
        ,ConversationRepository $conversationRepository)
    {
        $this->entityManager = $entityManager;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @Route("/", name="app_home")
     * @param UserRepository $userRepository
     * @param AgendaRepository $agendaRepository
     * @param ConversationRepository $conversationRepository
     * @return RedirectResponse|Response
     */
    public function index(UserRepository $userRepository,AgendaRepository $agendaRepository,ConversationRepository $conversationRepository)
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'Vous devez être connecté');

            return $this->redirectToRoute('app_login');
        }
        $countUser = $userRepository->count([],['createdAt' => 'DESC']);
        //On recupère l'id de l'utilisateur connecté
        $userId =  intval($this->get('security.token_storage')->getToken()->getUser()->getId());

        $users = $agendaRepository->findBy([],['createdAt' => 'DESC']);
              
        $taille = count($users) ;
        //Je crée un tableau pour stocker les utilisateurs que l'utilisateur courant a rajouté à l'agenda
        $data = array();
         $i = 0 ;
        while ($i < $taille)
        {
            if ( $users[$i]->getUser()->getId() == $userId)
            {
                $data[] = $users[$i];
            }
            $i++;
        }
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());
        $convCount = count($conversations);
        //dump($conversations);
        $countagenda = sizeof($data);
        $countprive = 0;
        $countgroupe = 0;
        foreach($conversations as $conv){
            dump($conv);
            if($conv['nom'] == null){
                $countprive++;
            }
            else{
                $countgroupe++;
            }
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'count_users' => $countUser,
            'count_fav' =>  $countagenda,
            'count_conv' => $convCount,
            'countprive' => $countprive,
            'countgroupe' => $countgroupe,
        ]);
    }


    /**
     * @Route("/annuaire", name="app_annuary", methods={"GET", "POST"})
     * @param UserRepository $userRepository
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param AgendaRepository $agenda
     * @return Response
     * @throws \Exception
     */
    public function show_annuary(UserRepository $userRepository,Request $request,EntityManagerInterface $em,AgendaRepository $agenda) :Response
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'Vous devez vous connectez!');

            return $this->redirectToRoute('app_login');
        }

        // Liste des utilisateurs a afficher dans notre agenda
        $users = $userRepository->findBy([],['createdAt' => 'DESC']);

        /* On recupère la liste des utilisateurs de l'agenda afin de verifier si une personne
        qu'on veux ajouter parmis nos favoris ne s'y trouve pas déja.
        */
        $agendaUser = $agenda->findBy([],['createdAt' => 'DESC']);
        $taille = count($agendaUser);
        $i = 0;

        $favorite = new Agenda;
        if($request->isMethod('POST'))
        {
            $userId = $request->get('idUser');
            $ownerId = $this->get('security.token_storage')->getToken()->getUser()->getId();  //Id de l'utilisateur connecté

            // On cree une variable afin de recupérer le nom de l'utilisateur qu'on veux rajouter à l'agenda
            $doublon = $userRepository->find($userId);
            $userName = $doublon->getFirstName();
        /*
          *On verifies que l'utilisateur qu'on veux rajouter aux favoris ne fait pas déja partie des favoris.
        */
           while ($i < $taille )
           {
               if ($agendaUser[$i]->getFirstName() == $userName)
               {
                   $this->addFlash('error', 'Cette personne fait déjà partie de vos favoris');
                   return $this->redirectToRoute('app_annuary');
               }
               $i++;
           }


            //On vérifies que celui qu'on veux ajouter comme utilisateur n'est pas l'utilisateur connecté
            if ($userId == $ownerId )
            {
                $this->addFlash('error', 'Vous ne pouvez pas vous ajouter comme favori');
                return $this->redirectToRoute('app_annuary');
            }
            $user = $userRepository->find(['id' => $userId]);
            $favoriteOwner =  $this->get('security.token_storage')->getToken()->getUser();
            $favorite->setUser($favoriteOwner);
            $favorite->setFirstName($user->getFirstName());
            $favorite->setLastName($user->getLastName());
            $favorite->setUsername($user->getUsername());
            $favorite->setCreatedAt(new  \DateTime());
            $favorite->setUpdatedAt(new  \DateTime());
            $em->persist($favorite);
            $em->flush();

            $this->addFlash('success', 'Favori ajouté avec succès à l agenda');

        }

        return $this->render('home/annuary.html.twig', [
            'controller_name' => 'HomeController',
            'users'    => $users,
        ]);
    }

    /**
     * @Route("/agenda", name="app_agenda", methods="GET")
     * @param AgendaRepository $agendaRepository
     * @return Response
     */
    public function show_agenda(AgendaRepository $agendaRepository) :Response
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'Vous devez vous connectez!');

            return $this->redirectToRoute('app_login');
        }
        //On recupère l'id de l'utilisateur connecté
        $userId =  intval($this->get('security.token_storage')->getToken()->getUser()->getId());

        $users = $agendaRepository->findBy([],['createdAt' => 'DESC']);
        $countusers = $agendaRepository->count([],['createdAt' => 'DESC']);
        
        $taille = count($users) ;
        //Je crée un tableau pour stocker les utilisateurs que l'utilisateur courant a rajouté à l'agenda
        $data = array();
         $i = 0 ;
        while ($i < $taille)
        {
            if ( $users[$i]->getUser()->getId() == $userId)
            {
                $data[] = $users[$i];
            }
            $i++;
        }
        dump(sizeof($data));

        return $this->render('home/agenda.html.twig', [
            'controller_name' => 'HomeController',
            'data'    => $data,
        ]);
    }


    /**
     * @Route("/users/{id<[0-9]+>}/deleteUser", name="delete_user", methods={"DELETE"})
     * @param Agenda $user
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function deleteEntryAgenda(Agenda $user, Request $request,EntityManagerInterface $em) :Response
    {
        if ($this->isCsrfTokenValid('user_deletion_'. $user->getId(), $request->request->get('csrf_token')))
        {
            $em->remove($user);
            $em->flush();

            $this->addFlash('info', 'Favori supprimé avec succès');
        }
        return $this->redirectToRoute('app_agenda') ;
    }

    public function picture(){
        $upload = new User();
        $form = $this->createForm(UserFormType::class,$upload);
        return $this->render('home/profile.html.twig',array(
            'form' => $form->createView(),
        ));
    }
    /**
     * @Route("/update", name="update_statut")
     * @param Request $request
     * @return RedirectResponse|Response
     * @author khadija
     */
    public function update_pass(Request $request)
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }
        $entityManager = $this->getDoctrine()->getManager();
        ///dump($request);
        //die();
        $statut = $request->get('stat');
        $user = $this->getUser();
        $user->setStatus($statut);
        $entityManager->flush();
        $this->addFlash('info', 'Votre statut a été modifié');


        return $this->redirectToRoute('app_home') ;

    }
}
