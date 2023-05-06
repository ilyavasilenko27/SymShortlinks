<?php

namespace App\Generators;

use Doctrine\ORM\EntityManager;

class RandomTokenGenerator extends \Doctrine\ORM\Id\AbstractIdGenerator
{
    public function generate(EntityManager $oEm, $oEntity)
    {
        $iMaxAttempts = 10;
        $iAttempt = 0;

        while (true) {
            $sToken = self::generateRandomString();
            $oLink = $oEm->getRepository($oEntity::class)->find($sToken);

            if (!$oLink) {
                return $sToken;
            }

            $iAttempt++;
            if ($iAttempt > $iMaxAttempts) {
                throw new \Exception('Превышено ' . $iMaxAttempts . ' попыток');
            }
        }
    }

    private static function generateRandomString($iLength = 5) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($iLength/strlen($x)) )),1,$iLength);
    }
}