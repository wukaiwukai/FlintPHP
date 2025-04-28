<?php
namespace Core\Database\Attribute;
use Attribute;
#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(
        public string $name,
        public ?string $primaryKey = 'id'
    ) {}
}