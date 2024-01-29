<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\UserManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Webmozart\Assert\Tests\StaticAnalysis\length;


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

    #[Route('/', name: 'default', methods: ['GET', 'POST'])]
    public function default(): Response
    {
        return $this->redirectToRoute('profil');
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

    #[Route('/delete', name: 'deleteAccount', options: ["expose" => true], methods: ["DELETE", "GET"])]
    public function deleteAccount(EntityManagerInterface $entityManager, UserManagerInterface $userManager, UserRepository $repository)
    {

        $user=$this->getUser();
        if($user!=null){
            $userManager->deletePicture($user);
            $entityManager->remove($user);
            $entityManager->flush();
            return new JsonResponse(null, 204);
        }
        return new JsonResponse(null, 404);
    }

    #[Route('/edit', name: 'edit', methods: ['GET','POST'])]
    public function edit(Request $request, UserManagerInterface $userManager, EntityManagerInterface  $entityManager): Response
    {
        if(!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('connexion');
        }

        $user=$this->getUser();
        $editUser = new User();
        $form= $this->createForm(UserType::class, $editUser, ['method'=>'POST', 'action'=> $this->generateUrl("edit")]);
        $errorsMsg=[];
        $form->handleRequest($request);
        if($form->isSubmitted() &&  filter_var($editUser->getEmail(), FILTER_VALIDATE_EMAIL) && (($form["plainPassword"]->getData()!=null && empty($this->errorPassword($form["plainPassword"]->getData()))) |$form["plainPassword"]->getData()==null)){
            $user->setFirstName($editUser->getFirstname());
            $user->setLastName($editUser->getLastname());
            if($user->getEmail()!=$editUser->getEmail()){
                $userManager->editMail($user,$editUser->getEmail());
                $user->setEmail($editUser->getEmail());
            }
            if($form["avatar"]->getData()!=null){
                $userManager->editAvatar($user,$form["avatar"]->getData());
            }
            if( $form["plainPassword"]->getData()!=null){
                $userManager->editPassword($user,$form["plainPassword"]->getData());
            }else{
                $errors = $form->getErrors(true);
                foreach ($errors as $error){
                    array_push($errorsMsg, $error->getMessage());
                }
            }
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute("profil");
        }
        if($form->isSubmitted() && !filter_var($editUser->getEmail(), FILTER_VALIDATE_EMAIL)){
            array_push($errorsMsg," Le format de l'email ne convient pas.");
        }
        if($form->isSubmitted() && $form["plainPassword"]->getData()!=null && !empty($this->errorPassword($form["plainPassword"]->getData()))){
            foreach ($this->errorPassword($form["plainPassword"]->getData()) as $error){
                array_push($errorsMsg,$error);
            }
        }
        return $this->render('user/edit.html.twig', [
            'controller_name' => 'UserController',
            'user'=> $user,
            'formulaire'=>$form,
            'errors'=>$errorsMsg
        ]);
    }

    public function errorPassword(String $password): Array{
        $errorMsg=[];
        if(strlen($password)< 8 |strlen($password)>30){
            array_push($errorMsg,"Le mot de passe doit contenir entre 8 et 30 characters");
        }
        $passwordTab=str_split($password);
        $containsUp=false;
        $containsDown=false;
        $containsNumber=false;
        foreach ($passwordTab as $char){
            if(ctype_upper($char)) $containsUp=true;
            if(ctype_lower($char)) $containsDown=true;
            if(is_numeric($char)) $containsNumber=true;
        }
        if(!$containsUp | !$containsDown | !$containsNumber){
            array_push($errorMsg,"Le mot de passe doit contenir au minimum une majuscule, une minuscule et un chiffre.");
        }
        return $errorMsg;
    }

}
