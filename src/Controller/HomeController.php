<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
    
    /**
 * @Route("/profil/edit", name="update_pass")
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
}
