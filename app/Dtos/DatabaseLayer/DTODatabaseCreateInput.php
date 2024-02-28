<?php

namespace App\Dtos\DatabaseLayer;

/**
 * Class DTODatabaseCreateInput
 *
 * Data Transfer Object (DTO) to insert records in the database.
 *
 * @package App\Dtos
 */
class DTODatabaseCreateInput implements IDtoDatabase{

  /**
   * @var string The name of the database.
   */
  protected string $database;

  /**
   * @var string The name of the table.
   */
  protected string $table;

  /**
   * @var array The data to be inserted into the database.
   *            Associative array where keys are column names and values are column values.
   *            Example:
   *            [
   *                'column1' => 'value1',
   *                'column2' => 'value2',
   *                // ... additional columns and values
   *            ]
   */
  protected array $insert;

  /**
   * DTODatabaseCreateInput constructor.
   *
   * @param string $database The name of the database.
   * @param string $table The name of the table.
   * @param array $insert The data to be inserted into the database.
   *             Associative array where keys are column names and values are column values.
   *             Example:
   *             [
   *                 'column1' => 'value1',
   *                 'column2' => 'value2',
   *                 // ... additional columns and values
   *             ]
   */
  public function __construct(string $database, string $table, array $insert) {
    $this->database = $database;
    $this->table = $table;
    $this->insert = $insert;
  }

  public function getDatabase(): string
  {
    return $this->database;
  }

  public function getTable(): string
  {
    return $this->table;
  }

  public function getInsert(): array
  {
    return $this->insert;
  }

}

