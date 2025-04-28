<?php
//
//namespace Database\Attributes;
//
//use Attribute;
//
//#[Attribute(Attribute::TARGET_CLASS)]
//class Table
//{
//    public function __construct(
//        public string $name,
//        public ?string $primaryKey = 'id'
//    ) {}
//}
//
//#[Attribute(Attribute::TARGET_PROPERTY)]
//class Column
//{
//    public function __construct(
//        public ?string $name = null,
//        public ?string $type = null,
//        public bool $primary = false,
//        public bool $unique = false,
//        public bool $nullable = false,
//        public mixed $default = null,
//        public ?int $length = null,
//        public ?string $comment = null
//    ) {}
//}
//
//#[Attribute(Attribute::TARGET_PROPERTY)]
//class HasOne
//{
//    public function __construct(
//        public string $related,
//        public ?string $foreignKey = null,
//        public ?string $localKey = null
//    ) {}
//}
//
//#[Attribute(Attribute::TARGET_PROPERTY)]
//class HasMany
//{
//    public function __construct(
//        public string $related,
//        public ?string $foreignKey = null,
//        public ?string $localKey = null
//    ) {}
//}
//
//#[Attribute(Attribute::TARGET_PROPERTY)]
//class BelongsTo
//{
//    public function __construct(
//        public string $related,
//        public ?string $foreignKey = null,
//        public ?string $ownerKey = null
//    ) {}
//}