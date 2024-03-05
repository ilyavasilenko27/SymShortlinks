<?php

namespace App\Controller;

use App\Response\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

#[Route('/api', name: 'api_')]
class RegistrationController extends AbstractController
{
    /**
     * Регистрация пользователя
     *
     * @param ManagerRegistry $oDoctrine
     * @param Request $oRequest
     * @param UserPasswordHasherInterface $oPasswordHasher
     *
     * @return JsonResponse
     */
    #[Route('/register', name: 'register', methods: 'post')]
    public function index(ManagerRegistry $oDoctrine, Request $oRequest, UserPasswordHasherInterface $oPasswordHasher): JsonResponse
    {
        $oResponse = new ApiResponse();

        try {
            $oEm = $oDoctrine->getManager();
            $aDecoded = json_decode($oRequest->getContent());
            if (!isset($aDecoded->email)) {
                $oResponse->addError('Email не получен');
                return $oResponse;
            }
            if (!isset($aDecoded->password)) {
                $oResponse->addError('Пароль не получен');
                return $oResponse;
            }
            $sEmail = $aDecoded->email;
            $sPlaintextPassword = $aDecoded->password;
            if (empty($sEmail)) {
                $oResponse->addError('Пустой Email');
                return $oResponse;
            }
            if (empty($sPlaintextPassword)) {
                $oResponse->addError('Пустой пароль');
                return $oResponse;
            }

            $oUserExists = $oDoctrine->getRepository(User::class)->findOneBy([
                'email' => $sEmail
            ]);

            if (!empty($oUserExists)) {
                $oResponse->addError('Пользователь уже существует');
                return $oResponse;
            }

            $oUser = new User();
            $sHashedPassword = $oPasswordHasher->hashPassword(
                $oUser,
                $sPlaintextPassword
            );
            $oUser->setPassword($sHashedPassword);
            $oUser->setEmail($sEmail);
            $oUser->setUsername($sEmail);
            $oEm->persist($oUser);
            $oEm->flush();
            $oResponse->addMessage('Пользователь зарегистрирован');
        } catch (\Exception $oException) {
            $oResponse->addError('Ошибка регистрации пользователя');
        }

        return $oResponse;
    }
}
