<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AvatarController extends AbstractController
{
    #[Route('/avatar/{md5}', name: 'avatar')]
    public function index(String $md5, UserRepository $repository) : Response
    {
        $user=$repository->findOneBy(["avatarHash"=>$md5]);
        $link = 'img/uploads/' . $user->getAvatarHash() . '.png';
        return new BinaryFileResponse($link);
    }
}
