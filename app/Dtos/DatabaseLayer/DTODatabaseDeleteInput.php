<?php

namespace App\Dtos\DatabaseLayer;

/**
 * Class DTODatabaseDeleteInput
 *
 * Data Transfer Object (DTO) for deleting records in the database.
 *
 * @package App\Dtos
 */
class DTODatabaseDeleteInput implements IDtoDatabase{

  /**
   * @var string The name of the database.
   */
  protected string $database;

  /**
   * @var string The name of the table.
   */
  protected string $table;

  /**
   * @var array|null The conditions for the WHERE clause.
   */
  protected ?array $where;


  /**
   * DTODatabaseSelectInput constructor.
   *
   * @param string $database The name of the database.
   * @param string $table The name of the table.
   * @param array $where The conditions for the WHERE clause.
   */
  public function __construct(string $database, string $table, array $where) {
    $this->database = $database;
    $this->table = $table;
    $this->where = $where;
  }

  public function getDatabase(): string
  {
    return $this->database;
  }

  public function getTable(): string
  {
    return $this->table;
  }

  public function getWhere(): array
  {
    return $this->where;
  }


}

