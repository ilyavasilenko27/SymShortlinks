<?php

namespace App\Controller;

use App\Entity\Link;
use App\Redis\RedisViews;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RedirectController extends AbstractController
{
    /**
     * Перенаправление по токену
     *
     * @param Request $oRequest
     * @param ManagerRegistry $oDoctrine
     *
     * @return RedirectResponse
     */
    #[Route('/{token}', name: 'redirect')]
    public function searchRedirect(Request $oRequest, ManagerRegistry $oDoctrine): RedirectResponse
    {
        $sToken = $oRequest->get('token');

        if ($sToken !== null) {
            /** @var Link|null $oLink */
            $oLink = $oDoctrine->getRepository(Link::class)->find($sToken);
            if (!empty($oLink)) {
                RedisViews::getInstance($oLink->getUserId())->addView($sToken);
                return new RedirectResponse( $oLink->getUrl());
            }
        }

        return new RedirectResponse('/');
    }
}