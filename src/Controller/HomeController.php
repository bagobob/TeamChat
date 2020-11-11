<?php

namespace App\Controller;


use App\Entity\Agenda;
use App\Form\UserFormType;
use App\Repository\AgendaRepository;
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
     * @Route("/annuaire", name="app_annuary", methods={"GET", "POST"})
     * @param UserRepository $userRepository
     * @param AgendaRepository $agendaRepository
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function show_annuary(UserRepository $userRepository,AgendaRepository $agendaRepository,Request $request,EntityManagerInterface $em) :Response
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }
        $users = $userRepository->findBy([],['createdAt' => 'DESC']);
        $agendaUser = new Agenda;

        $form = $this->createForm(UserFormType::class,$agendaUser);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($agendaUser);
            $em->flush();

            $this->addFlash('success', 'User successfully added to agenda');

            return $this->redirectToRoute('app_agenda');
        }

        return $this->render('home/annuary.html.twig', [
            'controller_name' => 'HomeController',
            'users'    => $users,
            'form' => $form->createView()
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
        $users = $agendaRepository->findBy([],['createdAt' => 'DESC']);



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
