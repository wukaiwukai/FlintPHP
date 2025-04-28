<?php

namespace Core\Database\Attribute;
use  Attribute;
#[Attribute(Attribute::TARGET_PROPERTY)]
class HasMany
{
    public function __construct(
        public string $related,
        public ?string $foreignKey = null,
        public ?string $localKey = null
    ) {}
}