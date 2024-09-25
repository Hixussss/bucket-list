<?php
namespace App\Message;

class GenerateImageMessage
{
    private $query;

    public function __construct(string $query)
    {
        $this->query = $query;
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}