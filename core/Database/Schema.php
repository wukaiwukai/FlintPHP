<?php

namespace Database;

use Core\Database\DB;
use Database\Attributes\{Table, Column};
use ReflectionClass;
use ReflectionProperty;

class Schema
{
    public static function create(string $modelClass): bool
    {
        $reflection = new ReflectionClass($modelClass);
        $table = self::getTableAttribute($reflection);

        $columns = [];
        foreach ($reflection->getProperties() as $property) {
            if ($column = self::getColumnAttribute($property)) {
                $columns[] = self::buildColumnDefinition($property, $column);
            }
        }

        if (empty($columns)) {
            throw new \RuntimeException('No columns defined for table');
        }

        $sql = "CREATE TABLE IF NOT EXISTS {$table->name} (";
        $sql .= implode(', ', $columns);

        if ($table->primaryKey) {
            $sql .= ", PRIMARY KEY ({$table->primaryKey})";
        }

        $sql .= ")";

        return DB::statement($sql);
    }

    protected static function getTableAttribute(ReflectionClass $reflection): Table
    {
        $attributes = $reflection->getAttributes(Table::class);

        if (empty($attributes)) {
            throw new \RuntimeException('Table attribute is required');
        }

        return $attributes[0]->newInstance();
    }

    protected static function getColumnAttribute(ReflectionProperty $property): ?Column
    {
        $attributes = $property->getAttributes(Column::class);
        return $attributes ? $attributes[0]->newInstance() : null;
    }

    protected static function buildColumnDefinition(ReflectionProperty $property, Column $column): string
    {
        $name = $column->name ?: $property->name;
        $type = $column->type ?: self::detectType($property);

        $definition = "{$name} {$type}";

        if ($column->length) {
            $definition .= "({$column->length})";
        }

        if ($column->primary) {
            $definition .= " AUTO_INCREMENT";
        }

        if (!$column->nullable) {
            $definition .= " NOT NULL";
        }

        if ($column->default !== null) {
            $definition .= " DEFAULT " . self::formatDefaultValue($column->default);
        }

        if ($column->comment) {
            $definition .= " COMMENT '{$column->comment}'";
        }

        return $definition;
    }

    protected static function detectType(ReflectionProperty $property): string
    {
        $type = $property->getType();

        if ($type instanceof \ReflectionNamedType) {
            return match($type->getName()) {
                'int' => 'INT',
                'float' => 'FLOAT',
                'bool' => 'TINYINT(1)',
                'string' => 'VARCHAR',
                'array' => 'JSON',
                DateTime::class => 'DATETIME',
                default => 'VARCHAR(255)'
            };
        }

        return 'VARCHAR(255)';
    }

    protected static function formatDefaultValue(mixed $value): string
    {
        if (is_string($value)) {
            return "'{$value}'";
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value === null) {
            return 'NULL';
        }

        return (string)$value;
    }
}