<?php

namespace App\Controller;

use App\Entity\Link;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    public function renderPage(ManagerRegistry $oDoctrine): Response
    {
        $oClient = RedisAdapter::createConnection('redis://localhost');

        $aResult = [];
        /** @var array $aLinks */
        $aLinks = $oDoctrine->getRepository(Link::class)->findAll();

        /** @var Link $oLink */
        foreach ($aLinks as $oLink) {
            $sDate = $oLink->getCreated() ? $oLink->getCreated()->format('d.m.Y h:i:s') : '-';
            $aResult[] = [
                'id' => $oLink->getId(),
                'name' => $oLink->getName(),
                'url' => $oLink->getUrl(),
                'date' => $sDate
            ];
        }

        return $this->render(
            'home.html.twig',
            [
                'links' => $aResult
            ]
        );
    }

    public function modifyLinks(Request $oRequest, ManagerRegistry $oDoctrine): RedirectResponse
    {
        if ($sAction = $oRequest->request->get('action')) {
            switch ($sAction) {
                case 'add':
                    if ($sUrl = $oRequest->request->get('url')) {
                        $oEntityManager = $oDoctrine->getManager();

                        $sName = $oRequest->request->get('name') ?: 'Короткая ссылка: ' . $sUrl;

                        $oLink = new Link();
                        $oLink->setUrl($sUrl);
                        $oLink->setCreated(new \DateTime());
                        $oLink->setName($sName);

                        $oEntityManager->persist($oLink);
                        $oEntityManager->flush();
                    }
                    break;
                case 'delete':
                    if ($iId = $oRequest->request->get('id')) {
                        $oEntityManager = $oDoctrine->getManager();
                        $oLink = $oDoctrine->getRepository(Link::class)->find($iId);
                        if (!empty($oLink)) {
                            $oEntityManager->remove($oLink);
                        }
                        $oEntityManager->flush();
                    }
                    break;
            }
        }

        return new RedirectResponse('/');
    }
}