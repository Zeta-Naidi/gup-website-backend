<?php

namespace App\Dtos\DatabaseLayer;

/**
 * Class DTODatabaseSelectInput
 *
 * Data Transfer Object (DTO) to select records in the database.
 *
 * @package App\Dtos
 */
class DTODatabaseSelectInput implements IDtoDatabase{

  /**
   * @var string The name of the database.
   */
  protected string $database;

  /**
   * @var string The name of the table.
   */
  protected string $table;

//  /**
//   * @var array[]|null The table to join with. If null, no joins are made.
//   *                  Each element is a table name
//   */
//  protected array $secondTable;

  /**
   * @var array[]|null The columns to select. If null, select all columns.
   *                  Each element is an array representing a column, with the format:
   *                  ['tags.*', DB::raw('COUNT(users.id) as users_count'), ...]
   */
  protected ?array $select;

  /**
   * @var array|null The conditions for the WHERE clause.
   *                 Each element is an array representing a condition, with the format:
   *                 ['id' => 'between 3 and 20', 'batch' => 1, 'migration' => 'like 2023_%', ...]
   */
  protected ?array $where;

  /**
   * @var array|null The join conditions.
   *                Each element is an array representing a join condition, with the format:
   *                [['type' => 'leftJoin', 'table' => 'profile', 'first' => 'users.id', 'operator' => '=', 'second' => 'profile.user_id'], ...]
   */
  protected ?array $join;

  /**
   * @var array|null The columns and directions for the ORDER BY clause.
   *                Each element is an array with the column name as the key and the direction ('asc' or 'desc') as the value.
   *                Example: ['TagName' => 'asc', 'columnName' => 'desc', ...]
   */
  protected ?array $orderBy;

  /**
   * @var array|null The columns for the GROUP BY clause.
   *                Example: ['username', 'role', ... ]
   */
  protected ?array $groupBy;

  /**
   * @var array|null The pagination options.
   *                Example: [ 'page' => 1, 'rowsPerPage' => 10]
   */
  protected ?array $pagination;

  protected ?string $pluckToArray;

  /**
   * DTODatabaseSelectInput constructor.
   *
   * @param string $database The name of the database.
   * @param string $table The name of the table.
   * @param array $select The columns to select. If null, select all columns.
   *                   Each element is an array representing a column, with the format:
   *                   ['tags.*', DB::raw('COUNT(users.id) as users_count'), ...]
   * @param array|null $where The conditions for the WHERE clause.
   *                  Each element is an array representing a condition, with the format:
   *                  [[ attribute => 'name', operator => =|!=|in|notIn|isNull|like|isNotNull|between, value => $value|[$v1,$v2...,$vn],...]
   * @param array|null $join The join conditions.
   *                 Each element is an array representing a join condition, with the format:
   *                 [['type' => 'leftJoin', 'table' => 'profile', 'first' => 'users.id', 'operator' => '=', 'second' => 'profile.user_id'], ...]
   * @param array|null $orderBy The columns and directions for the ORDER BY clause.
   *                 Each element is an array with the column name as the key and the direction ('asc' or 'desc') as the value.
   *                 Example: ['TagName' => 'asc', 'columnName' => 'desc', ...]
   * @param array|null $groupBy The columns for the GROUP BY clause.
   *                 Example: ['username', 'role', ... ]
   * @param array|null $pagination The pagination options.
   *                 Example: ['page' => 1, 'rowsPerPage' => 10]
   * @param string|null $pluckToArray
   */
  public function __construct(string $database, string $table, array $select = ['*'], ?array $where = null, array $join = null, array $orderBy = null, array $groupBy = null, array $pagination = null, string $pluckToArray = null) {
    $this->database = $database;
    $this->table = $table;
    $this->select = $select;
    $this->where = $where;
    $this->join = $join;
    $this->orderBy = $orderBy;
    $this->groupBy = $groupBy;
    $this->pagination = $pagination;
    $this->pluckToArray = $pluckToArray;
  }

  public function getDatabase(): string
  {
    return $this->database;
  }

  public function getTable(): string
  {
    return $this->table;
  }

  public function getSelect(): ?array
  {
    return $this->select;
  }

  public function getWhere(): ?array
  {
    return $this->where;
  }

  public function getJoin(): ?array
  {
    return $this->join;
  }

  public function getOrderBy(): ?array
  {
    return $this->orderBy;
  }

  public function getGroupBy(): ?array
  {
    return $this->groupBy;
  }

  public function getPagination(): ?array
  {
    return $this->pagination;
  }

  public function getPluckToArray(): ?string
  {
    return $this->pluckToArray;
  }

}

