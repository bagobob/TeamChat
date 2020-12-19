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
     */
    public function index(UserRepository $userRepository,AgendaRepository $agendaRepository,ConversationRepository $conversationRepository)
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

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
     * @Route("/annuaire", name="app_annuary", methods={"GET", "POST"})
     * @param UserRepository $userRepository
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function show_annuary(UserRepository $userRepository,Request $request,EntityManagerInterface $em) :Response
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }
        $users = $userRepository->findBy([],['createdAt' => 'DESC']);

        $favorite = new Agenda;
        if($request->isMethod('POST'))
        {
            $userId = $request->get('idUser');
            $ownerId = $this->get('security.token_storage')->getToken()->getUser()->getId();  //Id de l'utilisateur connecté


            //On vérifies que celui qu'on veux ajouter comme utilisateur n'est pas l'utilisateur connecté
            if ($userId == $ownerId )
            {
                $this->addFlash('error', 'You cannot add yourself as favorite');
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

            $this->addFlash('success', 'User successfully added to agenda');

            return $this->redirectToRoute('app_agenda');
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
            $this->addFlash('error', 'You must logged in');

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
     * @Route("/profil/edit", name="update_pass")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
public function update_pass(Request $request,UserPasswordEncoderInterface $passwordEncoder)
{
    if (!($this->getUser())) {
        $this->addFlash('error', 'You must logged in');

        return $this->redirectToRoute('app_login');
    }
    $entityManager = $this->getDoctrine()->getManager();
    dump($request);
    $old_pwd = $request->get('old_password'); 
    $new_pwd = $request->get('new_password'); 
    $new_pwd_confirm = $request->get('new_password_confirm');
    $user = $this->getUser();
    $checkPass = $passwordEncoder->isPasswordValid($user, $old_pwd);
    if($checkPass === true) {
        $new_pwd_encoded = $passwordEncoder->encodePassword($user, $new_pwd_confirm);  
        $user->setPassword($new_pwd_encoded); 
        $entityManager->flush();
        $this->addFlash('info', 'Password successfully updated');
    } else {
        $this->addFlash('error', 'Mot de passe incorrect ');
        return $this->redirectToRoute('update_profil');
     }
    
       //update json file
       $data2 = json_decode(file_get_contents(__DIR__.'/user_data.json'), true);

       //search an element which the password has been modified in the database
       foreach($data2 as $key => $data_users){
           if(strcmp($user->getUsername(), $data_users['username']) === 0 ){
               if(strcmp($user->getPassword(), $data_users['encryptPassword']) !== 0){
                   $data_users[$key]['encryptPassword'] = $user->getPassword();

                   file_put_contents(__DIR__.'/user_data.json', json_encode($data2)); 
               }
            }
       }
    
    return $this->render('home/profile.html.twig', [
        'controller_name' => 'HomeController',
    ]);
    
}

    /**
     * @Route("/profil/editprofile", name="update_pseudo",methods = {"GET","POST"})
     * @param Request $request
     * @return RedirectResponse|Response
     */
public function update_pseudo(Request $request)
{
    if (!($this->getUser())) {
        $this->addFlash('error', 'You must logged in');

        return $this->redirectToRoute('app_login');
    }
    $entityManager = $this->getDoctrine()->getManager();
    dump($request);
    $new_username = $request->get('username'); 
    $user = $this->getUser();
    $user->setUsername($new_username);
    $entityManager->flush();
    $this->addFlash('info', 'Username successfully updated');

    //UUPDATE JSON FLE
    $data2 = json_decode(file_get_contents(__DIR__.'/user_data.json'), true);
    //get all users from the database
    $em = $this->getDoctrine()->getManager();
    $TheUsers = $em->getRepository(User::class)
        ->findAll();

    foreach($data2 as $key => $data_users){
        if((strcmp($user->getFirstName(), $data_users['firstname']) === 0) && (strcmp($user->getLastName(), $data_users['lastname']) === 0)) {
            $data2[$key]['username'] = $new_username; 
            file_put_contents(__DIR__.'/user_data.json', json_encode($data2)); 
            break;
        }
    }

    return $this->render('home/profile.html.twig', [
        'controller_name' => 'HomeController',
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

            $this->addFlash('info', 'User successfully deleted');
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
}
