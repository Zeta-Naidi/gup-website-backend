<?php

namespace App\Dtos\DatabaseLayer;

/**
 * Class DTODatabaseUpdateInput
 *
 * Data Transfer Object (DTO) for updating records in the database.
 *
 * @package App\Dtos
 */
class DTODatabaseUpdateInput implements IDtoDatabase
{
  /**
   * @var string The database connection name.
   */
  protected string $database;

  /**
   * @var string The table name in the database.
   */
  protected string $table;

  /**
   * @var array The data to be updated. Associative array where keys are column names and values are new values.
   *            Example:
   *            [
   *                'name' => 'John', // Set column 'name' to 'John'
   *                'age' => 30, // Set column 'age' to 30
   *                // ... additional updates
   *            ]
   */
  protected array $updates;

  /**
   * @var array The conditions to be applied in the WHERE clause for updating records.
   *            Associative array where keys are column names and values are conditions.
   *            Example:
   *            [
   *                'id' => 5, // Update records where column 'id' equals 5
   *                'status' => 'active', // Update records where column 'status' equals 'active'
   *                // ... additional conditions
   *            ]
   */
  protected array $where;

  /**
   * DTODatabaseUpdateInput constructor.
   *
   * @param string $database The database connection name.
   * @param string $table The table name in the database.
   * @param array $update The data to be updated. Associative array where keys are column names and values are new values.
   *
   * @param array $where The conditions to be applied in the WHERE clause for updating records.
   *             Associative array where keys are column names and values are conditions.

   */
  public function __construct(string $database, string $table, array $where, array $updates)
  {
    $this->database = $database;
    $this->table = $table;
    $this->updates = $updates;
    $this->where = $where;
  }

  /**
   * Get the database connection name.
   *
   * @return string
   */
  public function getDatabase(): string
  {
    return $this->database;
  }

  /**
   * Get the table name in the database.
   *
   * @return string
   */
  public function getTable(): string
  {
    return $this->table;
  }

  /**
   * Get the data to be updated.
   *
   * @return array
   */
  public function getUpdates(): array
  {
    return $this->updates;
  }

  /**
   * Get the conditions for updating records.
   *
   * @return array
   */
  public function getWhere(): array
  {
    return $this->where;
  }
}

