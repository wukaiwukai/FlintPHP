<?php

namespace Core\Database\Attribute;
use  Attribute;
#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public ?string $name = null,
        public ?string $type = null,
        public bool $primary = false,
        public bool $unique = false,
        public bool $nullable = false,
        public mixed $default = null,
        public ?int $length = null,
        public ?string $comment = null
    ) {}
}