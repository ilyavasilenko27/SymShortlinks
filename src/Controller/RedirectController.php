<?php

namespace App\Controller;

use App\Entity\Link;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends AbstractController
{
    public function searchRedirect(Request $oRequest, ManagerRegistry $oDoctrine): RedirectResponse
    {
        $sToken = $oRequest->get('token');

        if ($sToken !== null) {
            /** @var Link|null $oLink */
            $oLink = $oDoctrine->getRepository(Link::class)->find($sToken);
            if (!empty($oLink)) {
                return new RedirectResponse( $oLink->getUrl());
            }
        }

        return new RedirectResponse('/home');
    }
}