<?php

namespace App\Redis;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\CacheItem;

class RedisViews
{
    private const VIEW_KEY_CODE = 'link_views';

    private RedisAdapter $oCache;

    private int $iUserId;

    private static self $oInstance;

    /**
     * @throws Exception
     */
    private function __construct(int $iUserId)
    {
        $oClient = RedisAdapter::createConnection('redis://redis');
        $oClient->connect();
        if ($oClient->isConnected()) {
            $this->oCache = new RedisAdapter(RedisAdapter::createConnection('redis://redis'));
        } else {
            throw new Exception('Redis не подключен');
        }
        $this->iUserId = $iUserId;
    }

    public static function getInstance(int $iUserId): RedisViews
    {
        if (!isset(self::$oInstance) || self::$oInstance->iUserId !== $iUserId) {
            self::$oInstance = new self($iUserId);
        }

        return self::$oInstance;
    }

    /**
     * Получить список просмотров
     *
     * @return array
     */
    public function getViewList(): array
    {
        $oObj = $this->getObj();
        return $oObj->get() ?: [];
    }

    /**
     * Добавить просмотр
     *
     * @param string $sId
     *
     * @return void
     */
    public function addView(string $sId): void
    {
        $oObj = $this->getObj();
        $aList = $this->getViewList();
        if (!isset($aList[$sId])) {
            $aList[$sId] = 1;
        } else {
            $aList[$sId]++;
        }
        $oObj->set($aList);
        $this->oCache->save($oObj);
    }

    /**
     * Получить объект
     *
     * @return CacheItem
     *
     * @throws InvalidArgumentException
     */
    private function getObj(): CacheItem
    {
        return $this->oCache->getItem(self::VIEW_KEY_CODE . '_' . $this->iUserId);
    }
}