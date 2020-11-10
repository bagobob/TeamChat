<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Conversation;
use App\Entity\Participant;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\WebLink\Link;
use Symfony\Component\ErrorHandler\Exception\FlattenException;


class ConversationController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

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
     * @Route("/conv", name="newConversations" , methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */

    public function index(Request $request)
    {
        $otherUser = $request->get('otherUser',0);
        $otherUser = $this->userRepository->find($otherUser);
        if (is_null($otherUser)) {
            throw new \Exception("The user was not found");
        }

        // cannot create a conversation with myself
        if ($otherUser->getId() === $this->getUser()->getId()) {
            throw new \Exception("That's deep but you cannot create a conversation with yourself");
        }

        // Check if conversation already exists
        $conversation = $this->conversationRepository->findConversationByParticipants(
            $otherUser->getId(),
            $this->getUser()->getId()
        );

        if(count($conversation)){
            throw new \Exception("The conversation already exists");
        }

        $conversation = new Conversation();

        $participant = new Participant();
        $participant->setUser($this->getUser());
        $participant->setConversation($conversation);

        $otherParticipant = new Participant();
        $otherParticipant->setUser($otherUser);
        $otherParticipant->setConversation($conversation);

        $this->entityManager->getConnection()->beginTransaction();
        try{

        }catch(\Exception $e){
            throw $e;
        }
        $this->entityManager->commit();
        return $this->json();
    }

    /**
     * @Route("/getConversations",name="getConversations",methods={"GET"})
     */
    public function getConvs()
    {
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());
        //dd($conversations);
        $ent = $this->getDoctrine()->getRepository(Conversation::class)->find(1);

        return $this->json($conversations);
    }

}
