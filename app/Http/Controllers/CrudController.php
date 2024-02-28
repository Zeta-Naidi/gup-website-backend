<?php

namespace App\Http\Controllers;

use App\Models\User;
use http\Client\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CrudController extends Controller
{
  private string $dbConnection = 'mysql';
  private string $model = '';

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function list($filters = [])
  {
    $check = $this->_setDbConnectionFromAuthUser();
    if (!$check)
      return 'error';
    return $this->_getModel()::on($this->_getDbConnection())->get();
  }

  /**
   * Show the form for creating a new resource.
   * @param $attributes
   * @return Response
   */
  public function create($attributes)
  {
    return $this->_getModel()::on($this->_getDbConnection())
      ->create($attributes);
  }

  /**
   * Update the specified resource in DB.
   *
   * @param int $id
   * @return bool
   */
  public function update($id, $params): bool
  {
    try {
      $this->_getModel()::on($this->_getDbConnection())->where('id', $id)->update([
        ...$params
      ]);
      return true;
    }
    catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return false;
    }
  }

  /**
   * Remove the specified resource from DB.
   *
   * @param int $id
   */
  public function delete($id)
  {
    $this->_getModel()::on($this->_getDbConnection())->where('id', $id)->delete();
  }

  /**
   * get the specified resource from DB.
   *
   * @param int $id
   */
  public function get($id)
  {
    $this->_setDbConnectionFromAuthUser();
    return $this->_getModel()::on($this->_getDbConnection())
      ->find($id);
  }

  /**
   * @param String $newModel
   */
  public function setModel($newModel)
  {
    if (gettype($newModel) == 'string')
      $this->model = $newModel;
  }

  /**
   * @return string
   */
  protected function _getModel()
  {
    return $this->model;
  }

  /**
   * @return string
   */
  protected function _getDbConnection()
  {
    return $this->dbConnection;
  }

  /**
   * @param string $dbConnection
   */
  protected function _setDbConnection($dbConnection)
  {
    if (gettype($dbConnection) == 'string')
      $this->dbConnection = $dbConnection;
  }

  /**
   * @return bool if the database connection is valid
   */
  protected function _setDbConnectionFromAuthUser(): bool
  {
    $user = auth()->user();
    if (!is_null($user))
      $this->_setDbConnection($user->nameDatabaseConnection);
    return !is_null($user);
  }

}
