<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{
    #[Route('/profil', name: 'profil', methods: ['GET'])]
    public function profil(): Response
    {
        $user=$this->getUser();
        if($user==null){
           return $this->render('user/connexion.html.twig', [ 'controller_name' => 'UtilisateurController']);
        }
        return $this->render('user/profil.html.twig', [
            'controller_name' => 'UserController',
            'user'=> $user
        ]);
    }

    #[Route('/connexion', name: 'connexion', methods: ['GET', 'POST'])]
    public function connexion(): Response
    {
        if($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('profil');
        }

        return $this->render('user/connexion.html.twig', [ 'controller_name' => 'UtilisateurController']);
    }

    #[Route('/inscription', name: 'inscription', methods: ["GET", "POST"])]
    public function inscription(Request $request, UserManagerInterface $userManager, EntityManagerInterface $entityManager): Response
    {
        if($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('profil');
        }

        $newUser = new User();
        $form= $this->createForm(UserType::class, $newUser, ['method'=>'POST', 'action'=> $this->generateUrl("inscription")]);
        $errorsMsg=[];
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $plainPassword = $form["plainPassword"]->getData();
            $fileAvatar=$form["avatar"]->getData();
            $userManager->proccessNewUser($newUser,$plainPassword,$fileAvatar);
            $entityManager->persist($newUser);
            $entityManager->flush();
            //$this->addFlash('success',  "Inscription réussie ! :D");

            return $this->redirectToRoute("connexion");
        }else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error){
                array_push($errorsMsg, $error->getMessage());
            }
        }

        return $this->render('user/inscription.html.twig', [
            'controller_name' => 'UserController',
            'formulaire'=> $form,
            'errors'=>$errorsMsg
        ]);
    }

    #[Route('/deconnexion', name: 'deconnexion', methods: ['POST'])]
    public function deconnexion(): never
    {
        //Ne sera jamais appelée
        throw new \Exception("Cette route n'est pas censée être appelée.");
    }

    #[Route('/delete', name: 'deleteAccount', options: ["expose" => true], methods: ["DELETE"])]
    public function deleteAccount(EntityManagerInterface $entityManager, UserManagerInterface $userManager): Response
    {
        $user=$this->getUser();
        $userManager->deleteUser($user);
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->redirectToRoute("connexion");
    }
}
