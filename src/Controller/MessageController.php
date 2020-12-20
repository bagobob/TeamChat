<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Participant;
use App\Entity\Groupe;
use App\Entity\User;
use App\Form\SearchFormType;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Search;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Mercure\CookieGenerator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageController extends AbstractController
{
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;
    private $entityManager;
    private $userRepository;
    public function __construct(UserRepository $userRepository,EntityManagerInterface $entityManager
        ,ConversationRepository $conversationRepository)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->conversationRepository = $conversationRepository;
    }
    /**
     * @Route("/message", name="message")
     */
    public function index(CookieGenerator $cookieGenerator,Request $request,UserRepository $userRepository,EntityManagerInterface $em): Response
    {
        $conversationArray = [];
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());
        $flag=true;
        $idConvActive=0;
        dump($conversations);
        foreach ($conversations as $conversation)
        {

            array_push($conversationArray,$this->getDoctrine()->getRepository(Conversation::class)->find($conversation['conversationId']));
            if($flag)
            {
                $idConvActive=$conversationArray[0]->getId();
                $flag=false;
            }
        }
        //dd( $conversationArray[0]);
        $userId=$this->getUser()->getId();
        //Pour le select
        $users = $userRepository->findBy([],['createdAt' => 'DESC']);
        dump($conversationArray);
        $taille = count($users) ;
        //Je crée un tableau pour stocker les utilisateurs que l'utilisateur courant a rajouté à l'agenda
        $data = array();
        $i = 0 ;
        while ($i < $taille)
        {
            $data[$i] = $users[$i]->getUsername();

            $i++;
        }

        $response = $this->render('message/index.html.twig', [
            'IdConvActive' => $idConvActive,
            'controller_name' => 'MessageController',
            'conversations' => $conversations,
            'conversationArray' =>$conversationArray,
            'userId' => $userId,
            'data'    => $data,
        ]);
        $response->headers->setCookie($cookieGenerator->generate());


        return $response;
    }

    /**
     * @Route("/chat", name="chat")
     */
    public function show()
    {
        return $this->render('message/chat.html.twig', [
            'controller_name' => 'MessageController',
        ]);
    }


    /**
     * @Route("/send-message", name="sendMessage", methods={"POST"})
     */
    public function __invoke(MessageBusInterface $bus, Request $request): RedirectResponse
    {
        $content = $request->request->get('message');
        $id_conv = $request->request->get('id-conv');
        $user = $this->getUser();
        $conversation = $this->getDoctrine()->getRepository(Conversation::class)->find($id_conv);

        $message = new Message();

        $message->setContent($content);
        $message->setUser($user);
        $message->setConversation($conversation);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($message);
        $entityManager->flush();

        $update = new Update($id_conv, json_encode([
            'message' => $content,
            'userId' => $user->getId(),
            'convId' => $id_conv
        ]));
        $bus->dispatch($update);

        return $this->redirectToRoute('message');
    }

    /**
     * @Route("/message/nvconv", name="converpers",methods = {"GET","POST"})
     * @param Request $request
     * @return RedirectResponse|Response
     * @author khadija-nisrine
     */
    public function converpers(Request $request)
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }
        //ajouter une conversation
        if ($request->isMethod('POST')) {
            //dd($request);
             $conversation1 = new Conversation();
             $message1=new Message();
             $participant1 = new Participant();
             $participant2 = new Participant();
             $username = $request->get('username');
             $content=$request->get('msg');
             $user=$this->getDoctrine()->getRepository(User::class)->findOneByUsername($username);
             $participant1->setConversation($conversation1);
             $participant1->setUser($user);
             $participant2->setConversation($conversation1);
             $participant2->setUser($this->getUser());
             $message1->setContent($content);
             $message1->setUser($this->getUser());
             $message1->setConversation($conversation1);
             $entityManager = $this->getDoctrine()->getManager();
             $entityManager->persist($conversation1);
             $entityManager->persist($participant1);
             $entityManager->persist($message1);
             $entityManager->persist($participant2);
             $entityManager->flush();
             return $this->redirectToRoute('message');
        }
    }
        /**
         * @Route("/message/nvconvG", name="convergroupe",methods = {"GET","POST"})
         * @param Request $request
         * @return RedirectResponse|Response
         * @author khadija
         */
        public function convergroupe(Request $request)
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }
        //ajouter une conversation groupe
        if ($request->isMethod('POST')){
            $conversation = new Conversation();
            $message=new Message();
            $groupe=new Groupe();
            $participant = new Participant();
            $content=$request->get('msg');
            $nom=$request->get('nomgrp');
            $participant->setConversation($conversation);
            $participant->setUser($this->getUser());
            $message->setContent($content);
            $message->setUser($this->getUser());
            $message->setConversation($conversation);
            $groupe->setNom($nom);
            $groupe->setUser($this->getUser());
            $groupe->setConversation($conversation);
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($request->get('username') as $username)
            {
                $participant1 = new Participant();
                $user=$this->getDoctrine()->getRepository(User::class)->findOneByUsername($username);
                $participant1->setConversation($conversation);
                $participant1->setUser($user);
                //dump($user);
                $entityManager->persist($participant1);
            }
            $entityManager->persist($conversation);
            $entityManager->persist($participant);
            $entityManager->persist($message);
            $entityManager->persist($groupe);
            $entityManager->flush();

            return $this->redirectToRoute('message');


        }

    }

    /**
     * @Route("/chat_search", name="app_chat_search")
     * @param Request $request
     * @return Response
     */
    public function searchMessage(Request $request)
    {
        
        $messages = '';
        $search = new Search();
        $form = $this->createForm(SearchFormType::class,$search);
        $mot = $request->query->get('string');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $messages = $this->entityManager->getRepository(Message::class)->findWithSearch(2,$mot);
        }
        return $this->render('user/search.html.twig',[
            'form' => $form->createView(),
            'messages' => $messages
        ]);
    }


}
