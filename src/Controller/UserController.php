<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/profil/{id<[0-9]+>}", name="app_profil",methods={"GET","PUT"})
     * @param $id
     * @param UserRepository $repository
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param User $user
     * @return RedirectResponse|Response
     */
    public function show_profile($id,UserRepository $repository, Request $request,EntityManagerInterface $manager,User $user)
    {
        if (!($this->getUser())) {
            $this->addFlash('error', 'You must logged in');

            return $this->redirectToRoute('app_login');
        }

        $user = $repository->find($id);
        $form = $this->createForm(UserFormType::class,$user,[
            'method' => 'PUT'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $manager->flush();
        }

        return $this->render('user/index.html.twig', [
            'controller_name' => 'HomeController',
            'user' => $user,
            'form' => $form->createView()
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

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
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

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);

    }
}
