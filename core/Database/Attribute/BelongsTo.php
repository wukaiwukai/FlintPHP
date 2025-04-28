<?php
namespace Core\Database\Attribute;
use  Attribute;
#[Attribute(Attribute::TARGET_PROPERTY)]
class BelongsTo
{
    public function __construct(
        public string $related,
        public ?string $foreignKey = null,
        public ?string $ownerKey = null
    ) {}
}