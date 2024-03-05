<?php

namespace App\Controller;

use App\Entity\Link;
use App\Entity\User;
use App\Redis\RedisViews;
use App\Response\ApiLinkResponse;
use App\Response\ApiResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api', name: 'api_')]
class DashboardController extends AbstractController
{
    /**
     * Получить список ссылок
     *
     * @param Request $oRequest
     * @param ManagerRegistry $oDoctrine
     * @param User|null $oUser
     *
     * @return JsonResponse
     */
    #[Route('/dashboard', name: 'app_dashboard')]
    public function getLinks(Request $oRequest, ManagerRegistry $oDoctrine, #[CurrentUser] ?User $oUser): JsonResponse
    {
        $oResponse = new ApiLinkResponse();

        if ($oUser) {
            $iUserId = $oUser->getId();

            $aErrors = [];
            $aResult = [];
            /** @var array $aLinks */
            $aLinks = $oDoctrine->getRepository(Link::class)->findBy([
                'user_id' => $iUserId
            ]);

            /** @var Link $oLink */
            foreach ($aLinks as $oLink) {
                $sDate = $oLink->getCreated() ? $oLink->getCreated()->format('d.m.Y h:i:s') : '-';
                $sId = $oLink->getId();
                $aResult[$sId] = [
                    'id' => $sId,
                    'link' => $oRequest->getSchemeAndHttpHost() . '/' . $sId,
                    'name' => $oLink->getName(),
                    'url' => $oLink->getUrl(),
                    'date' => $sDate,
                    'view' => 0
                ];
            }

            try {
                $aViews = RedisViews::getInstance($iUserId)->getViewList();
                foreach ($aViews as $sId => $iCount) {
                    if (isset($aResult[$sId])) {
                        $aResult[$sId]['view'] = $iCount;
                    }
                }
            } catch (\Exception $oException) {
                $aErrors[] = $oException->getMessage();
            }

            if (empty($aErrors)) {
                $oResponse->setLinks(array_values($aResult));
            } else {
                $oResponse->addErrors($aErrors);
            }
        } else {
            $oResponse->addError('Пользователь не найден');
        }

        return $oResponse;
    }

    /**
     * Добавить ссылку
     *
     * @param Request $oRequest
     * @param ManagerRegistry $oDoctrine
     * @param User|null $oUser
     *
     * @return JsonResponse
     */
    #[Route('/add', name: 'add')]
    public function add(Request $oRequest, ManagerRegistry $oDoctrine, #[CurrentUser] ?User $oUser): JsonResponse
    {
        $oResponse = new ApiResponse();

        $oDecoded = json_decode($oRequest->getContent());
        if ($oUser) {
            $iUserId = $oUser->getId();

            if (!isset($oDecoded->url)) {
                $oResponse->addError('Url ссылки не получен');
                return $oResponse;
            }

            if ($sUrl = $oDecoded->url) {
                try {
                    $oEntityManager = $oDoctrine->getManager();

                    $sName = 'Короткая ссылка: ' . $sUrl;
                    if (isset($oDecoded->name)) {
                        $sName = $oDecoded->name;
                    }

                    $oLink = new Link();
                    $oLink->setUrl($sUrl);
                    $oLink->setUserId($iUserId);
                    $oLink->setCreated(new \DateTime());
                    $oLink->setName($sName);

                    $oEntityManager->persist($oLink);
                    $oEntityManager->flush();

                    $oResponse->addMessage('Ссылка добавлена');
                } catch (\Exception $exception) {
                    $oResponse->addError('Ошибка добавления ссылки');
                }
            }
        } else {
            $oResponse->addError('Пользователь не найден');
        }

        return $oResponse;
    }

    /**
     * Удалить ссылку
     *
     * @param Request $oRequest
     * @param ManagerRegistry $oDoctrine
     * @param User|null $oUser
     *
     * @return JsonResponse
     */
    #[Route('/delete', name: 'delete')]
    public function delete(Request $oRequest, ManagerRegistry $oDoctrine, #[CurrentUser] ?User $oUser): JsonResponse
    {
        $oResponse = new ApiResponse();

        $oDecoded = json_decode($oRequest->getContent());
        if ($oUser) {
            $iUserId = $oUser->getId();

            if (!isset($oDecoded->id)) {
                $oResponse->addError('Токен ссылки не получен');
                return $oResponse;
            }

            if ($iId = $oDecoded->id) {
                $oEntityManager = $oDoctrine->getManager();
                $oLink = $oDoctrine->getRepository(Link::class)->findOneBy([
                    'id' => $iId,
                    'user_id' => $iUserId
                ]);
                if (!empty($oLink)) {
                    try {
                        $oEntityManager->remove($oLink);
                        $oEntityManager->flush();
                        $oResponse->addMessage('Ссылка удалена');
                    } catch (\Exception $oException) {
                        $oResponse->addError('Ошибка удаления ссылки');
                    }
                } else {
                    $oResponse->addError('Ссылка не найдена');
                }
            } else {
                $oResponse->addError('Токен ссылки не получен');
            }
        } else {
            $oResponse->addError('Пользователь не найден');
        }

        return $oResponse;
    }
}
