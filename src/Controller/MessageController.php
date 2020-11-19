<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Participant;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
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
    public function index(CookieGenerator $cookieGenerator,Request $request): Response
    {
        $conversationArray = [];
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());
        $flag=true;
        $idConvActive=0;
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

        $response = $this->render('message/index.html.twig', [
            'IdConvActive' => $idConvActive,
            'controller_name' => 'MessageController',
            'conversationArray' =>$conversationArray,
            'userId' => $userId,
        ]);
        $response->headers->setCookie($cookieGenerator->generate());
        //ajouter une conversation
        if ($request->isMethod('POST')){
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


}
