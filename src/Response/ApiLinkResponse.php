<?php

namespace App\Response;

use App\Response\ApiResponse;

class ApiLinkResponse extends ApiResponse
{
    public array $links = [];

    /**
     * Запись ссылок
     *
     * @param array $aLinks
     *
     * @return $this
     */
    public function setLinks(array $aLinks): self
    {
        $this->links = $aLinks;
        $this->setResult();

        return $this;
    }
}