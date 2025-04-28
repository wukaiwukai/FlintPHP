<?php

namespace Database;

use Core\Database\DB;
use Database\Attributes\BelongsTo;
use PDO;
use PDOStatement;
use Database\Attributes\Table;
use ReflectionProperty;

class QueryBuilder
{
    protected string $modelClass;
    protected array $wheres = [];
    protected array $orders = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $relations = [];
    protected array $select = ['*'];

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function where(string $column, string $operator, mixed $value = null): static
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = compact('column', 'operator', 'value');
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function with(string $relation): static
    {
        $this->relations[] = $relation;
        return $this;
    }

    public function select(array $columns): static
    {
        $this->select = $columns;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        $statement = $this->executeQuery($sql, $bindings);

        $results = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $model = $this->hydrateModel($row);

            if (!empty($this->relations)) {
                $this->loadRelations($model);
            }

            $results[] = $model;
        }

        return $results;
    }

    public function first(): ?Model
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }

    protected function hydrateModel(array $data): Model
    {
        $model = new $this->modelClass();

        foreach ($data as $key => $value) {
            $model->setAttribute($key, $value);
        }

        $model->exists = true;
        return $model;
    }

    protected function loadRelations(Model $model): void
    {
        foreach ($this->relations as $relation) {
            $model->$relation = $this->loadRelation($model, $relation);
        }
    }

    protected function loadRelation(Model $model, string $relation): mixed
    {
        // 简化的关联加载实现
        $reflection = new ReflectionProperty($model, $relation);

        if ($attribute = $reflection->getAttributes(BelongsTo::class)[0] ?? null) {
            $belongsTo = $attribute->newInstance();
            $foreignKey = $belongsTo->foreignKey ?: strtolower($belongsTo->related).'_id';
            return $belongsTo->related::query()
                ->where($belongsTo->ownerKey ?: 'id', $model->$foreignKey)
                ->first();
        }

        // 其他关联类型的实现...

        return null;
    }

    protected function toSql(): string
    {
        $table = $this->modelClass::getTableAttribute();
        $select = implode(', ', $this->select);
        $sql = "SELECT {$select} FROM {$table->name}";

        if (!empty($this->wheres)) {
            $whereClauses = array_map(
                fn($where) => "{$where['column']} {$where['operator']} ?",
                $this->wheres
            );
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        if (!empty($this->orders)) {
            $orderClauses = array_map(
                fn($order) => "{$order['column']} {$order['direction']}",
                $this->orders
            );
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;

            if ($this->offset !== null) {
                $sql .= " OFFSET " . $this->offset;
            }
        }

        return $sql;
    }

    protected function getBindings(): array
    {
        return array_map(
            fn($where) => $where['value'],
            $this->wheres
        );
    }

    protected function executeQuery(string $sql, array $bindings): PDOStatement
    {
        $pdo = DB::connection();
        $statement = $pdo->prepare($sql);
        $statement->execute($bindings);
        return $statement;
    }
    /**
     * 执行更新操作
     *
     * @param array $data 要更新的数据
     * @return bool 是否成功
     */
    public function update(array $data): bool
    {
        $table = $this->modelClass::getTableAttribute();
        $sql = "UPDATE {$table->name} SET ";

        $sets = [];
        $bindings = [];

        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $bindings[] = $value;
        }

        $sql .= implode(', ', $sets);

        if (!empty($this->wheres)) {
            $whereClauses = array_map(
                fn($where) => "{$where['column']} {$where['operator']} ?",
                $this->wheres
            );
            $sql .= " WHERE " . implode(' AND ', $whereClauses);

            foreach ($this->wheres as $where) {
                $bindings[] = $where['value'];
            }
        }

        $statement = $this->executeQuery($sql, $bindings);
        return $statement->rowCount() > 0;
    }

    /**
     * 执行插入操作
     *
     * @param array $data 要插入的数据
     * @return int|false 插入的ID或false
     */
    public function insert(array $data): int|false
    {
        $table = $this->modelClass::getTableAttribute();
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = "INSERT INTO {$table->name} (";
        $sql .= implode(', ', $columns);
        $sql .= ") VALUES (";
        $sql .= implode(', ', $placeholders);
        $sql .= ")";

        $statement = $this->executeQuery($sql, array_values($data));

        if ($statement->rowCount() > 0) {
            return DB::connection()->lastInsertId();
        }

        return false;
    }
    // 添加 orWhere 支持
    public function orWhere(string $column, string $operator, mixed $value = null): static
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => 'OR'];
        return $this;
    }

// 添加 insertBatch 支持
    public function insertBatch(array $rows): bool
    {
        if (empty($rows)) {
            return false;
        }

        $table = $this->modelClass::getTableAttribute();
        $columns = array_keys($rows[0]);
        $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $sql = "INSERT INTO {$table->name} (" . implode(', ', $columns) . ") VALUES ";
        $sql .= implode(', ', array_fill(0, count($rows), $placeholders));

        $bindings = [];
        foreach ($rows as $row) {
            foreach ($columns as $column) {
                $bindings[] = $row[$column];
            }
        }

        $statement = $this->executeQuery($sql, $bindings);
        return $statement->rowCount() > 0;
    }

}