<?php

namespace Core\Http\Attributes;
use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
class Middleware
{
    public function __construct(
        public array $middlewares
    ) {}
}