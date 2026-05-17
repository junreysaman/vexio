<?php

declare(strict_types=1);

namespace Framework;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;
use Throwable;

class Database
{
    /**
     * Raw PDO connection instance.
     */
    public PDO $connection;

    /**
     * Latest prepared statement.
     */
    private ?PDOStatement $stmt = null;

    /**
     * Query execution logs.
     */
    private array $queryLog = [];

    /**
     * Create database connection.
     *
     * Example:
     * -----------------------------------------------------
     * $db = new Database(
     *     'mysql',
     *     [
     *         'host' => '127.0.0.1',
     *         'dbname' => 'app'
     *     ],
     *     'root',
     *     ''
     * );
     * -----------------------------------------------------
     */
    public function __construct(
        string $driver,
        array $config,
        string $username,
        string $password
    ) {
        if ($driver === 'mysql' && !array_key_exists('charset', $config)) {
            $config['charset'] = 'utf8mb4';
        }

        $dsn = $this->buildDsn($driver, $config);

        try {
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

        } catch (PDOException $e) {
            throw new RuntimeException(
                'Database connection failed.',
                0,
                $e
            );
        }
    }

    /**
     * Return raw PDO instance.
     *
     * Example:
     * -----------------------------------------------------
     * $pdo = $db->pdo();
     * -----------------------------------------------------
     */
    public function pdo(): PDO
    {
        return $this->connection;
    }

    /**
     * Return latest PDO statement.
     *
     * Example:
     * -----------------------------------------------------
     * $stmt = $db->statement();
     * -----------------------------------------------------
     */
    public function statement(): PDOStatement
    {
        if ($this->stmt === null) {
            throw new RuntimeException(
                'No statement prepared.'
            );
        }

        return $this->stmt;
    }

    /**
     * Prepare and execute query.
     *
     * Example:
     * -----------------------------------------------------
     * $db->query(
     *     "SELECT * FROM users WHERE id = :id",
     *     ['id' => 1]
     * );
     * -----------------------------------------------------
     */
    public function query(
        string $query,
        array $params = []
    ): self {
        $start = microtime(true);

        $this->prepare($query)
            ->execute($params);

        $this->queryLog[] = [
            'query' => $query,
            'params' => $params,
            'time' => microtime(true) - $start,
        ];

        return $this;
    }

    /**
     * Prepare SQL statement.
     *
     * Example:
     * -----------------------------------------------------
     * $db->prepare(
     *     "SELECT * FROM users WHERE id = :id"
     * );
     * -----------------------------------------------------
     */
    public function prepare(string $query): self
    {
        $this->stmt = $this->connection->prepare($query);

        return $this;
    }

    /**
     * Bind values manually.
     *
     * Example:
     * -----------------------------------------------------
     * $db->prepare(
     *     "SELECT * FROM users WHERE id = :id"
     * )->bind([
     *     'id' => 1
     * ])->execute();
     * -----------------------------------------------------
     */
    public function bind(array $params): self
    {
        foreach ($params as $key => $value) {
            $placeholder = is_int($key)
                ? $key + 1
                : (
                    str_starts_with((string) $key, ':')
                    ? $key
                    : ':' . $key
                );

            $this->statement()
                ->bindValue($placeholder, $value);
        }

        return $this;
    }

    /**
     * Execute prepared statement.
     *
     * Example:
     * -----------------------------------------------------
     * $db->execute([
     *     'id' => 1
     * ]);
     * -----------------------------------------------------
     */
    public function execute(
        array $params = []
    ): self {
        $this->statement()->execute($params);

        return $this;
    }

    /**
     * Run SELECT query and fetch all rows.
     *
     * Example:
     * -----------------------------------------------------
     * $users = $db->select(
     *     "SELECT * FROM users"
     * );
     * -----------------------------------------------------
     */
    public function select(
        string $query,
        array $params = []
    ): array {
        return $this->query($query, $params)
            ->findAll();
    }

    /**
     * Run SELECT query and fetch first row.
     *
     * Example:
     * -----------------------------------------------------
     * $user = $db->selectOne(
     *     "SELECT * FROM users WHERE id = :id",
     *     ['id' => 1]
     * );
     * -----------------------------------------------------
     */
    public function selectOne(
        string $query,
        array $params = []
    ): ?array {
        return $this->query($query, $params)
            ->first();
    }

    /**
     * Return scalar value.
     *
     * Example:
     * -----------------------------------------------------
     * $count = $db->scalar(
     *     "SELECT COUNT(*) FROM users"
     * );
     * -----------------------------------------------------
     */
    public function scalar(
        string $query,
        array $params = []
    ): mixed {
        $value = $this->query($query, $params)
            ->statement()
            ->fetchColumn();

        return $value === false
            ? null
            : $value;
    }

    /**
     * Check if rows exist.
     *
     * Example:
     * -----------------------------------------------------
     * $exists = $db->exists(
     *     "SELECT 1 FROM users WHERE email = :email",
     *     ['email' => 'john@test.com']
     * );
     * -----------------------------------------------------
     */
    public function exists(
        string $query,
        array $params = []
    ): bool {
        $row = $this->query($query, $params)
            ->statement()
            ->fetch(PDO::FETCH_NUM);

        return $row !== false;
    }

    /**
     * Fetch all rows from table.
     *
     * Example:
     * -----------------------------------------------------
     * $users = $db->all('users');
     * -----------------------------------------------------
     */
    public function all(
        string $table,
        array $columns = ['*'],
        array $orderBy = [],
        ?int $limit = null,
        int $offset = 0
    ): array {
        $sql = sprintf(
            'SELECT %s FROM %s%s%s',
            $this->buildColumnList($columns),
            $this->quoteIdentifier($table),
            $this->buildOrderByClause($orderBy),
            $this->buildLimitClause($limit, $offset)
        );

        return $this->select($sql);
    }

    public function latest(
        string $table,
        string $column = 'created_at',
        int $limit = 10,
        array $columns = ['*']
    ): array {
        return $this->all($table, $columns, [$column => 'DESC'], $limit);
    }

    public function oldest(
        string $table,
        string $column = 'created_at',
        int $limit = 10,
        array $columns = ['*']
    ): array {
        return $this->all($table, $columns, [$column => 'ASC'], $limit);
    }

    public function value(
        string $table,
        string $column,
        array $where
    ): mixed {
        [$clause, $params] = $this->buildWhereClause(
            $where,
            'where_'
        );

        return $this->scalar(
            "SELECT {$this->quoteIdentifier($column)}
             FROM {$this->quoteIdentifier($table)}
             WHERE {$clause}
             LIMIT 1",
            $params
        );
    }

    public function pluck(
        string $table,
        string $column,
        ?string $key = null,
        array $where = []
    ): array {
        $columns = $key === null
            ? [$column]
            : [$key, $column];

        $sql = sprintf(
            'SELECT %s FROM %s',
            $this->buildColumnList($columns),
            $this->quoteIdentifier($table)
        );

        $params = [];

        if ($where !== []) {
            [$clause, $params] = $this->buildWhereClause(
                $where,
                'where_'
            );

            $sql .= " WHERE {$clause}";
        }

        $rows = $this->select($sql, $params);

        if ($key === null) {
            return array_column($rows, $column);
        }

        return array_column($rows, $column, $key);
    }

    public function countWhere(
        string $table,
        array $where = []
    ): int {
        $sql = sprintf(
            'SELECT COUNT(*) FROM %s',
            $this->quoteIdentifier($table)
        );

        $params = [];

        if ($where !== []) {
            [$clause, $params] = $this->buildWhereClause(
                $where,
                'where_'
            );

            $sql .= " WHERE {$clause}";
        }

        return (int) $this->scalar($sql, $params);
    }

    public function tableExists(string $table): bool
    {
        return $this->exists(
            'SELECT 1 FROM information_schema.tables
             WHERE table_schema = DATABASE()
             AND table_name = :table
             LIMIT 1',
            ['table' => $table]
        );
    }

    /**
     * Find row by primary id.
     *
     * Example:
     * -----------------------------------------------------
     * $user = $db->findById('users', 1);
     * -----------------------------------------------------
     */
    public function findById(
        string $table,
        int|string $id
    ): ?array {
        return $this->selectOne(
            "SELECT * FROM {$this->quoteIdentifier($table)}
             WHERE id = :id
             LIMIT 1",
            ['id' => $id]
        );
    }

    /**
     * Find rows by conditions.
     *
     * Example:
     * -----------------------------------------------------
     * $admins = $db->findWhere(
     *     'users',
     *     ['role' => 'admin']
     * );
     * -----------------------------------------------------
     */
    public function findWhere(
        string $table,
        array $where,
        array $columns = ['*'],
        array $orderBy = [],
        ?int $limit = null,
        int $offset = 0
    ): array {
        [$clause, $params] = $this->buildWhereClause(
            $where,
            'where_'
        );

        $sql = sprintf(
            'SELECT %s FROM %s WHERE %s%s%s',
            $this->buildColumnList($columns),
            $this->quoteIdentifier($table),
            $clause,
            $this->buildOrderByClause($orderBy),
            $this->buildLimitClause($limit, $offset)
        );

        return $this->select($sql, $params);
    }

    public function findOneWhere(
        string $table,
        array $where,
        array $columns = ['*'],
        array $orderBy = []
    ): ?array {
        $rows = $this->findWhere(
            $table,
            $where,
            $columns,
            $orderBy,
            1
        );

        return $rows[0] ?? null;
    }

    public function firstWhere(
        string $table,
        array $where,
        array $columns = ['*'],
        array $orderBy = []
    ): ?array {
        return $this->findOneWhere($table, $where, $columns, $orderBy);
    }

    public function existsWhere(
        string $table,
        array $where
    ): bool {
        [$clause, $params] = $this->buildWhereClause(
            $where,
            'where_'
        );

        return $this->exists(
            "SELECT 1 FROM {$this->quoteIdentifier($table)}
             WHERE {$clause}
             LIMIT 1",
            $params
        );
    }

    public function paginateTable(
        string $table,
        int $page = 1,
        int $perPage = 10,
        array $where = [],
        array $columns = ['*'],
        array $orderBy = []
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;
        $total = $this->countWhere($table, $where);

        if ($where === []) {
            $data = $this->all($table, $columns, $orderBy, $perPage, $offset);
        } else {
            $data = $this->findWhere($table, $where, $columns, $orderBy, $perPage, $offset);
        }

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ];
    }

    public function search(
        string $table,
        array $columns,
        string $term,
        array $select = ['*'],
        ?int $limit = null
    ): array {
        if ($columns === []) {
            throw new InvalidArgumentException(
                'Search columns cannot be empty.'
            );
        }

        $clauses = [];
        $params = [];

        foreach ($columns as $index => $column) {
            $placeholder = 'search_term_' . $index;
            $clauses[] = $this->quoteIdentifier((string) $column) . " LIKE :{$placeholder}";
            $params[$placeholder] = '%' . $term . '%';
        }

        return $this->select(
            sprintf(
                'SELECT %s FROM %s WHERE %s%s',
                $this->buildColumnList($select),
                $this->quoteIdentifier($table),
                implode(' OR ', $clauses),
                $this->buildLimitClause($limit, 0)
            ),
            $params
        );
    }

    /**
     * Return latest row count.
     *
     * Example:
     * -----------------------------------------------------
     * $count = $db->rowCount();
     * -----------------------------------------------------
     */
    public function rowCount(): int
    {
        return $this->statement()->rowCount();
    }

    /**
     * Return COUNT(*) as integer.
     *
     * Example:
     * -----------------------------------------------------
     * $count = $db->count();
     * -----------------------------------------------------
     */
    public function count(): int
    {
        return (int) $this->statement()
            ->fetchColumn();
    }

    /**
     * Return first row.
     *
     * Example:
     * -----------------------------------------------------
     * $user = $db->first();
     * -----------------------------------------------------
     */
    public function first(): ?array
    {
        $row = $this->statement()
            ->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : $row;
    }

    /**
     * Alias of first().
     */
    public function find(): ?array
    {
        return $this->first();
    }

    /**
     * Fetch all rows.
     *
     * Example:
     * -----------------------------------------------------
     * $rows = $db->findAll();
     * -----------------------------------------------------
     */
    public function findAll(): array
    {
        return $this->statement()
            ->fetchAll();
    }

    /**
     * Return latest insert id.
     *
     * Example:
     * -----------------------------------------------------
     * $id = $db->id();
     * -----------------------------------------------------
     */
    public function id(): string|false
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Insert row and return insert id.
     *
     * Example:
     * -----------------------------------------------------
     * $id = $db->insert('users', [
     *     'name' => 'John',
     *     'email' => 'john@test.com'
     * ]);
     * -----------------------------------------------------
     */
    public function insert(
        string $table,
        array $data
    ): string|false {
        if ($data === []) {
            throw new InvalidArgumentException(
                'Insert data cannot be empty.'
            );
        }

        $columns = [];
        $placeholders = [];
        $params = [];

        foreach ($data as $column => $value) {
            $placeholder = 'insert_' . $column;

            $columns[] = $this->quoteIdentifier(
                (string) $column
            );

            $placeholders[] = ':' . $placeholder;

            $params[$placeholder] = $value;
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->quoteIdentifier($table),
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->query($sql, $params);

        return $this->id();
    }

    /**
     * Insert row then fetch inserted row.
     *
     * Example:
     * -----------------------------------------------------
     * $user = $db->insertGet('users', [
     *     'name' => 'John'
     * ]);
     * -----------------------------------------------------
     */
    public function insertGet(
        string $table,
        array $data
    ): ?array {
        $id = $this->insert($table, $data);

        return $this->findById($table, $id);
    }

    /**
     * Bulk insert multiple rows.
     *
     * Example:
     * -----------------------------------------------------
     * $db->bulkInsert('users', [
     *     [
     *         'name' => 'John'
     *     ],
     *     [
     *         'name' => 'Jane'
     *     ]
     * ]);
     * -----------------------------------------------------
     */
    public function bulkInsert(
        string $table,
        array $rows
    ): bool {
        if ($rows === []) {
            return false;
        }

        $columns = array_keys($rows[0]);

        $quotedColumns = array_map(
            fn ($column) => $this->quoteIdentifier($column),
            $columns
        );

        $values = [];
        $params = [];

        foreach ($rows as $index => $row) {
            $placeholders = [];

            foreach ($row as $column => $value) {
                $key = "{$column}_{$index}";

                $placeholders[] = ':' . $key;

                $params[$key] = $value;
            }

            $values[] = '(' . implode(', ', $placeholders) . ')';
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES %s',
            $this->quoteIdentifier($table),
            implode(', ', $quotedColumns),
            implode(', ', $values)
        );

        $this->query($sql, $params);

        return true;
    }

    /**
     * Update rows.
     *
     * Example:
     * -----------------------------------------------------
     * $db->update(
     *     'users',
     *     ['name' => 'Updated'],
     *     ['id' => 1]
     * );
     * -----------------------------------------------------
     */
    public function update(
        string $table,
        array $data,
        array $where
    ): int {
        if ($data === []) {
            throw new InvalidArgumentException(
                'Update data cannot be empty.'
            );
        }

        $sets = [];
        $params = [];

        foreach ($data as $column => $value) {
            $placeholder = 'set_' . $column;

            $sets[] = $this->quoteIdentifier(
                (string) $column
            ) . ' = :' . $placeholder;

            $params[$placeholder] = $value;
        }

        [$clause, $whereParams] =
            $this->buildWhereClause(
                $where,
                'where_'
            );

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->quoteIdentifier($table),
            implode(', ', $sets),
            $clause
        );

        $this->query(
            $sql,
            [...$params, ...$whereParams]
        );

        return $this->rowCount();
    }

    public function updateById(
        string $table,
        int|string $id,
        array $data
    ): int {
        return $this->update($table, $data, ['id' => $id]);
    }

    /**
     * Insert or update existing row.
     *
     * Example:
     * -----------------------------------------------------
     * $db->updateOrInsert(
     *     'users',
     *     ['email' => 'john@test.com'],
     *     ['name' => 'John']
     * );
     * -----------------------------------------------------
     */
    public function updateOrInsert(
        string $table,
        array $where,
        array $data
    ): bool {
        [$clause, $params] = $this->buildWhereClause(
            $where,
            'where_'
        );

        $exists = $this->exists(
            "SELECT 1 FROM {$this->quoteIdentifier($table)}
             WHERE {$clause}",
            $params
        );

        if ($exists) {
            $this->update($table, $data, $where);
        } else {
            $this->insert(
                $table,
                [...$where, ...$data]
            );
        }

        return true;
    }

    /**
     * Delete rows.
     *
     * Example:
     * -----------------------------------------------------
     * $db->delete('users', [
     *     'id' => 1
     * ]);
     * -----------------------------------------------------
     */
    public function delete(
        string $table,
        array $where
    ): int {
        [$clause, $params] = $this->buildWhereClause(
            $where,
            'where_'
        );

        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $this->quoteIdentifier($table),
            $clause
        );

        $this->query($sql, $params);

        return $this->rowCount();
    }

    public function deleteById(
        string $table,
        int|string $id
    ): int {
        return $this->delete($table, ['id' => $id]);
    }

    public function increment(
        string $table,
        string $column,
        int|float $amount = 1,
        array $where = []
    ): int {
        return $this->adjustNumericColumn($table, $column, $amount, $where, '+');
    }

    public function decrement(
        string $table,
        string $column,
        int|float $amount = 1,
        array $where = []
    ): int {
        return $this->adjustNumericColumn($table, $column, $amount, $where, '-');
    }

    /**
     * Soft delete rows.
     *
     * Example:
     * -----------------------------------------------------
     * $db->softDelete('users', [
     *     'id' => 1
     * ]);
     * -----------------------------------------------------
     */
    public function softDelete(
        string $table,
        array $where
    ): int {
        return $this->update($table, [
            'deleted_at' => date('Y-m-d H:i:s')
        ], $where);
    }

    /**
     * Restore soft deleted rows.
     *
     * Example:
     * -----------------------------------------------------
     * $db->restore('users', [
     *     'id' => 1
     * ]);
     * -----------------------------------------------------
     */
    public function restore(
        string $table,
        array $where
    ): int {
        return $this->update($table, [
            'deleted_at' => null
        ], $where);
    }

    /**
     * Paginate query results.
     *
     * Example:
     * -----------------------------------------------------
     * $users = $db->paginate(
     *     "SELECT * FROM users",
     *     [],
     *     1,
     *     10
     * );
     * -----------------------------------------------------
     */
    public function paginate(
        string $query,
        array $params = [],
        int $page = 1,
        int $perPage = 10
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $query .= " LIMIT {$perPage} OFFSET {$offset}";

        return $this->select($query, $params);
    }

    /**
     * Execute transaction callback.
     *
     * Example:
     * -----------------------------------------------------
     * $db->transaction(function ($db) {
     *
     *     $db->insert('users', [
     *         'name' => 'John'
     *     ]);
     *
     *     $db->insert('profiles', [
     *         'user_id' => 1
     *     ]);
     * });
     * -----------------------------------------------------
     */
    public function transaction(
        callable $callback
    ): mixed {
        $this->beginTransaction();

        try {
            $result = $callback($this);

            $this->commit();

            return $result;

        } catch (Throwable $e) {

            if ($this->connection->inTransaction()) {
                $this->rollback();
            }

            throw $e;
        }
    }

    /**
     * Begin transaction manually.
     *
     * Example:
     * -----------------------------------------------------
     * $db->beginTransaction();
     * -----------------------------------------------------
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction.
     *
     * Example:
     * -----------------------------------------------------
     * $db->commit();
     * -----------------------------------------------------
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction.
     *
     * Example:
     * -----------------------------------------------------
     * $db->rollback();
     * -----------------------------------------------------
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Return MAX(column).
     *
     * Example:
     * -----------------------------------------------------
     * $max = $db->max('users', 'id');
     * -----------------------------------------------------
     */
    public function max(
        string $table,
        string $column
    ): mixed {
        return $this->scalar(
            "SELECT MAX(
                {$this->quoteIdentifier($column)}
            ) FROM {$this->quoteIdentifier($table)}"
        );
    }

    /**
     * Return MIN(column).
     */
    public function min(
        string $table,
        string $column
    ): mixed {
        return $this->scalar(
            "SELECT MIN(
                {$this->quoteIdentifier($column)}
            ) FROM {$this->quoteIdentifier($table)}"
        );
    }

    /**
     * Return SUM(column).
     */
    public function sum(
        string $table,
        string $column
    ): mixed {
        return $this->scalar(
            "SELECT SUM(
                {$this->quoteIdentifier($column)}
            ) FROM {$this->quoteIdentifier($table)}"
        );
    }

    /**
     * Return AVG(column).
     */
    public function avg(
        string $table,
        string $column
    ): mixed {
        return $this->scalar(
            "SELECT AVG(
                {$this->quoteIdentifier($column)}
            ) FROM {$this->quoteIdentifier($table)}"
        );
    }

    /**
     * Return executed query logs.
     *
     * Example:
     * -----------------------------------------------------
     * $logs = $db->getQueryLog();
     * -----------------------------------------------------
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    /**
     * Dump raw SQL query.
     *
     * Example:
     * -----------------------------------------------------
     * echo $db->dumpRawSql(
     *     "SELECT * FROM users WHERE id = :id",
     *     ['id' => 1]
     * );
     * -----------------------------------------------------
     */
    public function dumpRawSql(
        string $query,
        array $params = []
    ): string {
        foreach ($params as $key => $value) {

            $query = str_replace(
                ':' . $key,
                is_numeric($value)
                    ? (string) $value
                    : "'" . $value . "'",
                $query
            );
        }

        return $query;
    }

    /**
     * Build DSN string.
     */
    private function buildDsn(
        string $driver,
        array $config
    ): string {
        $this->assertIdentifier($driver);

        $parts = [];

        foreach ($config as $key => $value) {

            $this->assertIdentifier(
                (string) $key
            );

            $parts[] = $key . '=' . $value;
        }

        return $driver . ':' . implode(';', $parts);
    }

    /**
     * Build WHERE clause.
     */
    private function buildWhereClause(
        array $where,
        string $prefix
    ): array {
        if ($where === []) {
            throw new InvalidArgumentException(
                'Where conditions required.'
            );
        }

        $clauses = [];
        $params = [];

        foreach ($where as $column => $value) {

            $columnSql = $this->quoteIdentifier(
                (string) $column
            );

            if ($value === null) {
                $clauses[] = "{$columnSql} IS NULL";
                continue;
            }

            $placeholder = $prefix . $column;

            $clauses[] =
                "{$columnSql} = :{$placeholder}";

            $params[$placeholder] = $value;
        }

        return [
            implode(' AND ', $clauses),
            $params
        ];
    }

    private function buildColumnList(array $columns): string
    {
        if ($columns === [] || $columns === ['*']) {
            return '*';
        }

        return implode(', ', array_map(
            fn ($column) => $this->quoteIdentifier((string) $column),
            $columns
        ));
    }

    private function buildOrderByClause(array $orderBy): string
    {
        if ($orderBy === []) {
            return '';
        }

        $parts = [];

        foreach ($orderBy as $column => $direction) {
            $direction = strtoupper((string) $direction);

            if (!in_array($direction, ['ASC', 'DESC'], true)) {
                throw new InvalidArgumentException(
                    'Order direction must be ASC or DESC.'
                );
            }

            $parts[] = $this->quoteIdentifier((string) $column) . ' ' . $direction;
        }

        return ' ORDER BY ' . implode(', ', $parts);
    }

    private function buildLimitClause(?int $limit, int $offset): string
    {
        if ($limit === null) {
            return '';
        }

        if ($limit < 1 || $offset < 0) {
            throw new InvalidArgumentException(
                'Limit must be positive and offset cannot be negative.'
            );
        }

        return sprintf(' LIMIT %d OFFSET %d', $limit, $offset);
    }

    private function adjustNumericColumn(
        string $table,
        string $column,
        int|float $amount,
        array $where,
        string $operator
    ): int {
        if (!in_array($operator, ['+', '-'], true)) {
            throw new InvalidArgumentException(
                'Invalid numeric adjustment operator.'
            );
        }

        if ($where === []) {
            throw new InvalidArgumentException(
                'Where conditions required.'
            );
        }

        $sql = sprintf(
            'UPDATE %s SET %s = %s %s :amount',
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($column),
            $this->quoteIdentifier($column),
            $operator
        );

        $params = ['amount' => $amount];

        [$clause, $whereParams] = $this->buildWhereClause(
            $where,
            'where_'
        );

        $sql .= " WHERE {$clause}";
        $params = [...$params, ...$whereParams];

        $this->query($sql, $params);

        return $this->rowCount();
    }

    /**
     * Quote trusted identifier.
     */
    private function quoteIdentifier(
        string $identifier
    ): string {
        $this->assertIdentifier($identifier);

        return "`{$identifier}`";
    }

    /**
     * Validate identifiers.
     */
    private function assertIdentifier(
        string $identifier
    ): void {
        if (!preg_match(
            '/^[A-Za-z_][A-Za-z0-9_]*$/',
            $identifier
        )) {
            throw new InvalidArgumentException(
                'Invalid database identifier.'
            );
        }
    }
}
