<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    /**
     * @Route("/", name="app_login")
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     * @author Boris & Vanelle
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //1- LOAD THE USERS IN THE DATABASE

        //open the json file
        $data = json_decode(file_get_contents(__DIR__.'/user_data.json'), true);

        foreach ($data as $data_users){
            $user = new User();
            $user->setFirstName($data_users['firstname']);
            $user->setLastName($data_users['lastname']);
            $user->setUsername($data_users['username']);
            $user->setPassword($data_users['encryptPassword']);
            $user->setStatus($data_users['statut']);
            $user->setCreatedAt(new \DateTime());
            $user->setUpdatedAt(new \DateTime());

            //check if user already exists
            $AnUser = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneByUsername($user->getUsername());

            if (!($AnUser)) {
                // 4) save the User!
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                //$this->addFlash('success', 'Un utiliseur a été enregistré dans la base de données!!!!');
            }
        }


        //2- UPDATE THE DATABASE WHEN WE UPATE JSON FILE

        $data2 = json_decode(file_get_contents(__DIR__.'/user_data.json'), true);
        //get all users from the database
        $em = $this->getDoctrine()->getManager();
        $TheUsers = $em->getRepository(User::class)
            ->findAll();

        foreach($TheUsers as $theuser){
            //search an element which is doesn't exists in the json file
            foreach($data2 as $data_users){
                if(strcmp($theuser->getUsername(), $data_users['username']) === 0 ){
                    $found = false;
                    break 1;
                }
                else{
                    $found = true;
                }
            }
            //we found this element, we remove it from the database
            if($found){
                $em->remove($theuser);
                // $this->addFlash('info', 'Un utiliseur a été supprimé dans la base de données!!!!');
            }
        }
        $em->flush();


        //Begining of Authentication
         if ($this->getUser()) {
             $this->addFlash('error', 'Vous êtes déjà connecté');
             return $this->redirectToRoute('app_home');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     *
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
