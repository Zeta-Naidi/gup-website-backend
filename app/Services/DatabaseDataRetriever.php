<?php

namespace App\Services;

use App\Dtos\DatabaseLayer\DTODatabaseCreateInput;
use App\Dtos\DatabaseLayer\DTODatabaseDeleteInput;
use App\Dtos\DatabaseLayer\DTODatabaseOutput;
use App\Dtos\DatabaseLayer\DTODatabaseSelectInput;
use App\Dtos\DatabaseLayer\DTODatabaseUpdateInput;
use App\Dtos\DatabaseLayer\IDtoDatabase;
use App\Exceptions\CatchedExceptionHandler;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipStream\Test\Assertions;

class DatabaseDataRetriever implements IDataRetriever
{
  protected string $DtoType;
  protected bool $isLaravelModel = false;
  protected string $database;
  protected string $table;
  protected array $select;
  protected array $where;
  protected array $join;
  protected array $orderBy;
  protected array $groupBy;
  protected array $pagination;
  protected array $insert;
  protected array $delete;
  protected array $updates;
  protected string $pluckToArray;

  private $query;
  private int $statusCode = 0;

  public function __construct(IDtoDatabase $dto)
  {
    $this->buildAttributes($dto);
  }

  public function updateDTO(IDtoDatabase $parameters): void
  {
    $this->buildAttributes($parameters);
  }

  public function isLaravelModel(): bool
  {
    return $this->isLaravelModel;
  }

  private function getDatabase(): string
  {
    return $this->database;
  }

  private function getTable(): string
  {
    return $this->table;
  }

  private function getSelect()
  {
    return $this->select;
  }

  private function getWhere()
  {
    return $this->where;
  }

  private function getJoin()
  {
    return $this->join;
  }

  private function getOrderBy()
  {
    return $this->orderBy;
  }

  private function getGroupBy()
  {
    return $this->groupBy;
  }

  private function getPagination()
  {
    return $this->pagination;
  }

  private function getInsert()
  {
    return $this->insert;
  }

  private function getUpdates()
  {
    return $this->updates;
  }

  public function execute(): DTODatabaseOutput
  {
    try {
      $this->query = DB::connection($this->getDatabase())->table($this->getTable());
      switch ($this->DtoType) {
        case "DTODatabaseSelectInput":
          $startTime = microtime(true);
          $this->DTOSelect();
          $additionalInfos = [];
          if ($this->DtoType === "DTODatabaseSelectInput" && !empty($this->pluckToArray)) {
            $result = $this->query->pluck($this->pluckToArray)->all();
          } else if ($this->DtoType === "DTODatabaseSelectInput" && !empty($this->getPagination())) {
            $pagination = $this->getPagination();
            $pageSize = array_key_exists('rowsPerPage', $pagination) ? (int)$pagination['rowsPerPage'] : 15;
            $currentPage = array_key_exists('page', $pagination) ? (int)$pagination['page'] : 1;
            $result = $this->query->paginate($pageSize, ['*'], 'page', $currentPage);
            $paginationData = [
              'current_page' => $result->currentPage(),
              'last_page' => $result->lastPage(),
              'total' => $result->total(),
              'per_page' => $result->perPage(),
            ];

            $result = $result->items();
            $additionalInfos = $paginationData;
          } else {
            $result = $this->query->get()->all();
            $this->statusCode = 200;
          }
          $endTime = microtime(true);
          $executionTime = round(($endTime - $startTime) * 1000, 2);
          return new DTODatabaseOutput($result, true, $executionTime, $this->statusCode, null, $additionalInfos);
        case "DTODatabaseDeleteInput":
          $startTime = microtime(true);
          $this->DTODelete();
          $endTime = microtime(true);
          $executionTime = round(($endTime - $startTime) * 1000, 2);
          return new DTODatabaseOutput([], true, $executionTime, 200);
          break;
        case "DTODatabaseUpdateInput":
          $startTime = microtime(true);
          $this->DTOUpdate();
          $endTime = microtime(true);
          $executionTime = round(($endTime - $startTime) * 1000, 2);
          return new DTODatabaseOutput([], true, $executionTime, 200);
        case "DTODatabaseCreateInput":
          $startTime = microtime(true);
          $idRowCreated = $this->DTOCreate();
          $endTime = microtime(true);
          $executionTime = round(($endTime - $startTime) * 1000, 2);
          return new DTODatabaseOutput([$idRowCreated], true, $executionTime, 200);
        default:
          return new DTODatabaseOutput([], false, 0, 400, 'INVALID_DTO');

      }
    } catch (\Exception|\Throwable $exception) {
      CatchedExceptionHandler::handle($exception);
      return new DTODatabaseOutput([], false, 0, 400, $exception->getMessage());
    }
  }

  private function DTOSelect(): void
  {
    $this->GetSelectQuery();
    $this->GetWhereQuery();
    $this->GetJoinQuery();
    $this->GetOrderByQuery();
    $this->GetGroupByQuery();
  }

  /**
   * @return int the id of
   */
  private function DTOCreate(): int
  {
    return $this->GetInsertQuery();
  }

  private function DTOUpdate(): void
  {
    $this->GetWhereQuery();
    $this->GetUpdateQuery();
  }

  private function DTODelete(): void
  {
    $this->GetWhereQuery();
    $this->GetDeleteQuery();
  }

  private function GetSelectQuery(): void
  {
    $selectColumns = $this->getSelect();
    if ($selectColumns) {
      $this->query->select($selectColumns);
    }
  }

  private function GetWhereQuery(): void
  {
    $whereConditions = $this->getWhere();
    if ($whereConditions) {
      foreach ($whereConditions as $key => $whereCondition) {
        //TODO: check valid params [ attribute => 'name', operator => =|!=|in|notIn|isNull|like|isNotNull|between, value => $value|[$v1,$v2...,$vn]]

        switch ($whereCondition['operator']) {
          case "=":
          case "!=":
          case ">":
          case "<":
          case "like":
          case "LIKE":
            // For the first condition, use 'where', for subsequent conditions use 'orWhere'
            if ($key === 0) {
              $this->query->where($whereCondition['attribute'], $whereCondition['operator'], $whereCondition['value']);
            } else {
              $this->query->orWhere($whereCondition['attribute'], $whereCondition['operator'], $whereCondition['value']);
            }
            break;
          case "in":
            $this->query->whereIn($whereCondition['attribute'], $whereCondition['value']);
            break;
          case "notIn":
            $this->query->whereNotIn($whereCondition['attribute'], $whereCondition['value']);
            break;
          case "isNull":
            $this->query->whereNull($whereCondition['attribute']);
            break;
          case "isNotNull":
            $this->query->whereNotNull($whereCondition['attribute']);
            break;
          case "between":
            $this->query->whereBetween($whereCondition['attribute'], $whereCondition['value']);
            break;
          default:
            throw new \Exception('WHERE_CONDITION_NOT_VALID');
        }
      }
    }
  }

  private function GetJoinQuery(): void
  {
    // Add join conditions if provided
    $joinConditions = $this->getJoin();
    if ($joinConditions) {
      foreach ($joinConditions as $joinCondition) {
        $joinType = $joinCondition['type'] ?? 'join';
        $table = $joinCondition['table'];
        $first = $joinCondition['first'];
        $operator = $joinCondition['operator'];
        $second = $joinCondition['second'];

        $this->query->$joinType($table, $first, $operator, $second);
      }
    }
  }

  private function GetOrderByQuery(): void
  {
    $orderByColumns = $this->getOrderBy();
    if ($orderByColumns) {
      foreach ($orderByColumns as $column => $direction) {
        $this->query->orderBy($column, $direction);
      }
    }
  }

  private function GetGroupByQuery(): void
  {
    // Add group by if provided
    $groupByColumns = $this->getGroupBy();
    if ($groupByColumns) {
      $this->query->groupBy($groupByColumns);
    }
  }

  private function GetInsertQuery(): int
  {
    // if type == array , json_encode
    $insertColumns = $this->getInsert();
    foreach ($insertColumns as $key => &$value) {
      if (is_array($value)) {
        $value = json_encode($value);
      }
    }

    if ($insertColumns) {
      return $this->query->insertGetId($insertColumns);
    }
  }

  private function GetUpdateQuery(): void
  {
    // Update specific columns if provided
    $updateColumns = $this->getUpdates();
    if ($updateColumns) {
      $this->query->update($updateColumns);
    }
  }

  private function GetDeleteQuery(): void
  {
    $this->query->delete();
  }

  private function buildAttributes(IDtoDatabase $dto): void
  {
    $this->database = $dto->getDatabase();
    $this->table = $dto->getTable();


    if ($dto instanceof DTODatabaseSelectInput) {
      $this->DtoType = "DTODatabaseSelectInput";
      $this->select = $dto->getSelect() ?? [];
      $this->join = $dto->getJoin() ?? [];
      $this->orderBy = $dto->getOrderBy() ?? [];
      $this->groupBy = $dto->getGroupBy() ?? [];
      $this->pagination = $dto->getPagination() ?? [];
      $this->where = $dto->getWhere() ?? [];
      $this->pluckToArray = $dto->getPluckToArray() ?? '';

    } elseif ($dto instanceof DTODatabaseDeleteInput) {
      $this->DtoType = "DTODatabaseDeleteInput";
      $this->where = $dto->getWhere() ?? [];

    } elseif ($dto instanceof DTODatabaseUpdateInput) {
      $this->DtoType = "DTODatabaseUpdateInput";
      $this->updates = $dto->getUpdates() ?? [];
      $this->where = $dto->getWhere() ?? [];

    } elseif ($dto instanceof DTODatabaseCreateInput) {
      $this->DtoType = "DTODatabaseCreateInput";
      $this->insert = $dto->getInsert() ?? [];

    }
  }
}
