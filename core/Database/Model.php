<?php
namespace Database;

use Core\Database\Attribute\Column;
use Core\Database\Attribute\Table;
use DateTime;
use ReflectionClass;
use ReflectionProperty;

abstract class Model
{
    protected array $attributes = [];
    protected array $original = [];
    public bool $exists = false;

    protected static array $propertyCache = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::class);
    }

    public static function find(int $id): ?static
    {
        return static::query()->where(static::getPrimaryKey() ?? 'id', $id)->first();
    }

    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $properties = $this->getProperties();
        foreach ($properties as $property) {
            if ($property->getName() === $key) {
                $this->$key = $this->prepareValueForStorage($value, $property);
                $this->attributes[$key] = $this->prepareDatabaseValue($this->$key);
                return;
            }
        }
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->$key ?? $this->attributes[$key] ?? null;
    }

    protected function prepareValueForStorage(mixed $value, ReflectionProperty $property): mixed
    {
        $type = $property->getType()?->getName();

        if ($type === DateTime::class && !($value instanceof DateTime)) {
            return new DateTime($value);
        }

        if ($type === 'int') {
            return (int)$value;
        }
        if ($type === 'float') {
            return (float)$value;
        }
        if ($type === 'bool') {
            return (bool)$value;
        }

        return $value;
    }

    protected function prepareDatabaseValue(mixed $value): mixed
    {
        if ($value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        return $value;
    }

    protected function getProperties(): array
    {
        $class = static::class;
        if (!isset(static::$propertyCache[$class])) {
            static::$propertyCache[$class] = (new ReflectionClass($this))->getProperties();
        }
        return static::$propertyCache[$class];
    }

    public function save(): bool
    {
        $primaryKey = static::getPrimaryKey() ?? 'id';
        if ($this->exists) {
            return static::query()->where($primaryKey, $this->getAttribute($primaryKey))->update($this->attributes);
        } else {
            $id = static::query()->insert($this->attributes);
            if ($id !== false) {
                $this->exists = true;
                $this->setAttribute($primaryKey, $id);
                return true;
            }
            return false;
        }
    }

    public static function getTableAttribute(): Table
    {
        $attribute = (new ReflectionClass(static::class))->getAttributes(Table::class)[0] ?? null;
        if (!$attribute) {
            throw new \RuntimeException("Model " . static::class . " missing Table attribute.");
        }
        return $attribute->newInstance();
    }

    // 获取所有字段属性
    protected static function getColumnAttributes(): array
    {
        $reflection = new ReflectionClass(static::class);
        $columns = [];

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Column::class);
            if (!empty($attributes)) {
                $column = $attributes[0]->newInstance();
                $columns[$property->getName()] = $column;
            }
        }

        return $columns;
    }

    // 获取主键字段名
    public static function getPrimaryKey(): ?string
    {
        foreach (static::getColumnAttributes() as $property => $column) {
            if ($column->primary) {
                return $column->name ?? $property;
            }
        }
        return null;
    }

    // 获取表字段名和默认值
    public static function getFieldDefaults(): array
    {
        $defaults = [];
        foreach (static::getColumnAttributes() as $property => $column) {
            if ($column->default !== null) {
                $defaults[$column->name ?? $property] = $column->default;
            }
        }
        return $defaults;
    }
}
