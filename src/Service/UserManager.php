<?php
namespace App\Service;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager implements UserManagerInterface {

    public function __construct( #[Autowire('%folder_picture%')] private string $folderPicture, private  UserPasswordHasherInterface $passwordService
        //Injection du service UserPasswordHasherInterface
    ){}

    /**
     * Chiffre le mot de passe puis l'affecte au champ correspondant dans la classe de l'utilisateur
     */
    private function encryptPassword(User $user, ?string $plainPassword) : void {
        //On chiffre le mot de passe en clair
        $hashed = $this->passwordService->hashPassword($user, $plainPassword);
        //On met à jour l'attribut "password" de l'utilisateur
        $user->setPassword($hashed);
    }

    /**
     * Sauvegarde l'image de profil dans le dossier de destination puis affecte son nom au champ correspondant dans la classe de l'utilisateur
     */
    private function savepicture(User $user, ?UploadedFile $filePicture) : void {
        if($filePicture != null) {
            //On configure le nom de l'image à sauvegarder
            $nameLink=md5($user->getEmail());
            $nameFile= $nameLink.'.'.$filePicture->guessExtension();
            //$nameFile= uniqid() . '.' . $fichierPhotoProfil->guessExtension();
            //On la déplace vers son dossier de destination
            $filePicture->move($this->folderPicture, $nameFile );
            //On met à jour l'attribut "nomPhotoProfil" de l'utilisateur
            $user->setAvatarPath($nameFile);
            $user->setAvatarHash($nameLink);
        }
    }

    /**
     * Réalise toutes les opérations nécessaires avant l'enregistrement en base d'un nouvel utilisateur, après soumissions du formulaire (hachage du mot de passe, sauvegarde de la photo de profil...)
     */
    public function proccessNewUser(User $user, ?string $plainPassword, ?UploadedFile $filePicture) : void {
        //On chiffre le mot de passe
        $this->encryptPassword($user,$plainPassword);
        //On sauvegarde (et on déplace) l'image de profil
        $this->savepicture($user, $filePicture);
    }

}
?>