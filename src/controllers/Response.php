<?php declare(strict_types=1);

namespace Knob\Controllers;

class Response
{
    /** @var string */
    private $response;

    public function __construct(string $response)
    {
        $this->response = $response;
    }

    public function __toString(): string
    {
        return $this->response;
    }
}