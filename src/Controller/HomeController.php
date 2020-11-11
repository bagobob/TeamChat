<?php

namespace App\Controller;


use App\Entity\Agendauser;
use App\Entity\User;
use App\Form\UserFormType;
use App\Entity\Agendaparticipant;
use App\Repository\AgendauserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class HomeController extends AbstractController
{
    
    /**
     * @var AgendauserRepository
     */
    private $AgendauserRepository;
    /**
     * @Route("/", name="app_home")
     */
    public function index()
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
        $agendaUser = new Agendauser;

        // $form = $this->createForm(UserFormType::class,$agendaUser);

        // $form->handleRequest($request);

        // if($form->isSubmitted() && $form->isValid())
        // {
        //     $em->persist($agendaUser);
        //     $em->flush();

        //     $this->addFlash('success', 'User successfully added to agenda');

        //     return $this->redirectToRoute('app_agenda');
        // }

        return $this->render('home/annuary.html.twig', [
            'controller_name' => 'HomeController',
            'users'    => $users,
            //'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/addAgenda", name="addAgenda", methods="GET")
     * @param Request $request
     */

    public function addAgenda(Request $request){

        if ($request->isMethod('GET')){
           
            //dd($this->AgendauserRepository->findAgendaByUser($this->getuser));
            //if($agendauser->getUser($this->getUser())==null){

                $agendauser = new Agendauser();
                $participant1 = new Agendaparticipant();
                $userId = $request->get('iduser');
                $user=$this->getDoctrine()->getRepository(User::class)->find(['id' => $userId ]);
                $agendauser->setUser($this->getUser());
                $participant1->setAgendauser($agendauser);
                $participant1->setUser($user);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($agendauser);
                $entityManager->persist($participant1);
                $entityManager->flush();
                return $this->redirectToRoute('addAgenda');
            //}
           // else{
            //     $participant1 = new Agendaparticipant();
            //     $userId = $request->get('iduser');
            //     $user=$this->getDoctrine()->getRepository(User::class)->find(['id' => $userId ]);
                
            //     $agendauser=$this->getDoctrine()->getRepository(Agendauser::class)->find(['user_id' => $this->getUser()->getId() ]);
            //     $participant1->setAgendauser($agendauser);
            //     $participant1->setUser($user);
            //     $entityManager = $this->getDoctrine()->getManager();
            //     $entityManager->persist($participant1);
            //     $entityManager->flush();
            //     return $this->redirectToRoute('addAgenda');
            // }
        }
    }

    /**
     * @Route("/agenda", name="app_agenda", methods="GET")
     * @param AgendauserRepository $agendauserRepository
     * @return Response
     */
    public function show_agenda(AgendauserRepository $agendauserRepository) :Response
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }
        //dd()
        $users = $agendauserRepository->findBy([],['id' => 'DESC']);



        return $this->render('home/agenda.html.twig', [
            'controller_name' => 'HomeController',
            'users'    => $users,
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
    } else {
        $this->addFlash('error', 'Mot de passe incorrect ');
        return $this->redirectToRoute('update_profil');
     }
    
    
    return $this->render('home/profile.html.twig', [
        'controller_name' => 'HomeController',
    ]);
    
}

    /**
     * @Route("/profil/editprofile", name="update_pseudo")
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


}
