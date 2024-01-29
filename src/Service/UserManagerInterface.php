<?php
namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UserManagerInterface {

    public function proccessNewUser(User $user, ?string $plainPassword, ?UploadedFile $filePicture);

    public function deletePicture(User $user);

    public function editPassword(User $user,String $plainPassword);

    public function editAvatar(User $user, ?UploadedFile $filePicture);

    public function editMail(User $user,String $newMail);
}
?>