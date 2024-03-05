<?php

namespace App\Response;

use ReflectionClass;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    public string $status = self::STATUS_SUCCESS;

    public array $messages = [];

    private array $ignore = [
        'headers'
    ];

    public function __construct(mixed $data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        parent::__construct($data, $status, $headers, $json);
        $this->setResult();
    }

    /**
     * Добавление ошибки
     *
     * @param string $sMessage
     *
     * @return self
     */
    public function addError(string $sMessage): self
    {
        $this->status = self::STATUS_ERROR;
        return $this->addMessage($sMessage);
    }

    /**
     * Добавление ошибок
     *
     * @param array $aErrors
     *
     * @return $this
     */
    public function addErrors(array $aErrors): self
    {
        $this->status = self::STATUS_ERROR;
        foreach ($aErrors as $sError) {
            $this->addError($sError);
        }

        return $this;
    }

    /**
     * Добавление сообщения
     *
     * @param string $sMessage
     *
     * @return self
     */
    public function addMessage(string $sMessage): self
    {
        $this->messages[] = $sMessage;
        return $this->setResult();
    }

    /**
     * Установка результата
     *
     * @return self
     */
    protected function setResult(): self
    {
        $oRefClass = new ReflectionClass($this);
        $aResult = [];
        foreach ($oRefClass->getProperties() as $oProperty) {
            if ($oProperty->getModifiers() === $oProperty::IS_PUBLIC && !in_array($oProperty->getName(), $this->ignore, true)) {
                $aResult[$oProperty->getName()] = $oProperty->getValue($this);
            }
        }

        return $this->setData($aResult);
    }
}