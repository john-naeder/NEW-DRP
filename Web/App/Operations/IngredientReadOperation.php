<?php

namespace App\Operations;

use App\Models\IngredientModel;

class IngredientReadOperation extends DatabaseRelatedOperation implements I_ReadOperation {

  const BASE_SQL_QUERY = "SELECT DISTINCT ingredients.id, ingredients.isActive, ingredients.name, ingredient_categories.detail AS category, ingredient_measurement_unit.detail AS measurementUnit
                          FROM ingredients 
                          LEFT JOIN ingredient_categories ON ingredients.category = ingredient_categories.id
                          LEFT JOIN ingredient_measurement_unit ON ingredients.measurement_unit = ingredient_measurement_unit.id";
  const getSingleObjectById = self::BASE_SQL_QUERY . " WHERE ingredients.id = :id AND ingredients.isActive = 1";
  const getSingleObjectByIdIgnoreActiveMode = self::BASE_SQL_QUERY . " WHERE ingredients.id = :id";
  const getAllObjectsByFieldAndValue = self::BASE_SQL_QUERY . " WHERE :name = :value AND ingredients.isActive = 1";
  const getAllObjectsByFieldAndValueIgnoreActiveMode = self::BASE_SQL_QUERY . " WHERE :name = :value";
  const getObjectsWithOffset = self::BASE_SQL_QUERY . " limit :limit offset :offset AND ingredients.isActive = 1";
  const getObjectsWithOffsetIgnoreActiveMode = self::BASE_SQL_QUERY . " limit :limit offset :offset";
  const getObjectWithOffsetByFielAndValue = self::BASE_SQL_QUERY . " WHERE :name = :value limit :limit offset :offset AND ingredients.isActive = 1";
  const getObjectWithOffsetByFielAndValueIgnoreActiveMode = self::BASE_SQL_QUERY . " WHERE :name = :value limit :limit offset :offset";
  
  public function __construct() {
    parent::__construct();
  }


  /**
   * Retrieves the nutrition information for a specific ingredient.
   *
   * @param int $id The ID of the ingredient.
   * @throws \PDOException If there is an error connecting to the database.
   * @throws \Exception If there is an error executing the SQL statement.
   */
  static public function getNutrition($id) :?array {

    $sql = "SELECT nutrition_types.id as nutrition_id, nutrition_types.detail AS nutrition_name, ingredient_nutritions.quantity AS nutrition_quantity
            FROM ingredient_nutritions INNER JOIN nutrition_types ON ingredient_nutritions.nutrition_id = nutrition_types.id
            WHERE ingredient_nutritions.ingredient_id = :id";            
    
    $nutritionComponents = parent::query($sql, 1, [':id' => $id]);
    return $nutritionComponents;
  }


  /**
   * Retrieves a single object from the database based on the provided SQL query.
   *
   * @param string $sql The SQL query to execute.
   * @param bool $getNutriOrNot Determines whether to fetch nutrition components or not. Default is true.
   * @param array $params An optional array of parameters to bind to the SQL query.
   * @return IngredientModel The retrieved IngredientModel object.
   * @throws \PDOException If there is an error connecting to the database.
   * @throws \Exception If there is an error executing the SQL query.
   */

  static public function getSingleObject($sql, bool $getNutriOrNot = true, $params = [])  : null|IngredientModel{ 
    $ingredient = self::querySingle($sql, 4, $params, "IngredientModel");
    if ($getNutriOrNot == true){
      if (!is_object($ingredient)) {
        return null;
      }
      $ingredient->setNutritionComponents(self::getNutrition($ingredient->getId()));
    }
    return $ingredient;
  }


  /**
   * Retrieves a single IngredientModel object by its ID.
   *
   * @param int $id The ID of the ingredient to retrieve.
   * @return IngredientModel|null The retrieved IngredientModel object, or null if an error occurred.
   */
  static public function getSingleObjectById(int $id, bool $ignoreActiveStatus = false) : null|IngredientModel{
    try {
      if ($ignoreActiveStatus) 
        return self::getSingleObject(self::getSingleObjectByIdIgnoreActiveMode, true, [':id' => $id]);
      else 
        return self::getSingleObject(self::getSingleObjectById, true, [':id' => $id]);
    } catch (\PDOException $PDOException) {
      handlePDOException($PDOException);
      echo \App\Views\ViewRender::errorViewRender('500');
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }

  /**
   * Retrieves a single IngredientModel object by its ID without including nutritional information.
   *
   * @param int $id The ID of the ingredient to retrieve.
   * @return IngredientModel|null The retrieved IngredientModel object, or null if an error occurs.
   */
  static public function getSingleObjectByIdWithoutNutri(int $id, bool $ignoreActiveStatus = false) : null|IngredientModel{
    try {
      if ($ignoreActiveStatus) 
        return self::getSingleObject(self::getSingleObjectByIdIgnoreActiveMode, false, [':id' => $id]);
      else 
        return self::getSingleObject(self::getSingleObjectById, false, [':id' => $id]);
    } catch (\PDOException $PDOException) {
      handlePDOException($PDOException);
      echo \App\Views\ViewRender::errorViewRender('500');
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }



  /**
   * Retrieves multiple objects from the database based on the given SQL query.
   *
   * @param string $sql The SQL query to execute.
   * @param bool $getNutriOrNot Determines whether to fetch nutrition components for each ingredient. Default is true.
   * @param array $params An array of parameters to bind to the SQL query. Default is an empty array.
   * @return array An array of IngredientModel objects representing the retrieved ingredients.
   * @throws \PDOException If there is an error connecting to the database.
   * @throws \Exception If there is an error executing the SQL query.
   */
  static public function getMultipleObject($sql, bool $getNutriOrNot = true, $params = []) : ?array{
    $ingredients = self::query($sql, 4, $params, "IngredientModel");
    foreach ($ingredients as $ingredient) {
      if ($getNutriOrNot == true){
        $ingredient->setNutritionComponents(self::getNutrition($ingredient->getId()));
      }
    }
    return $ingredients;
  }


  /**
   * Retrieves all objects from the database.
   *
   * @return array|null An array of objects if successful, null otherwise.
   */
  static public function getAllObjects(bool $ignoreActiveStatus = false) : ?array {
    try {
      if ($ignoreActiveStatus) 
        return self::getMultipleObject(self::BASE_SQL_QUERY);
      else 
        return self::getMultipleObject(self::BASE_SQL_QUERY . " WHERE ingredients.isActive = 1");
    } catch (\PDOException $exception) {
      handleException($exception);
      echo \App\Views\ViewRender::errorViewRender('500');
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }


  /**
   * Retrieves all objects without nutritional information from the database.
   *
   * @return array|null An array of objects without nutritional information, or null if an error occurs.
   */
  static public function getAllObjectsWithoutNutri(bool $ignoreActiveStatus = false) : ?array {
    try {
      if ($ignoreActiveStatus) 
        return self::getMultipleObject(self::BASE_SQL_QUERY, false);
      else 
        return self::getMultipleObject(self::BASE_SQL_QUERY . " WHERE ingredients.isActive = 1", false);
    } catch (\PDOException $exception) {
      handleException($exception);
      echo \App\Views\ViewRender::errorViewRender('500');
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }


  /**
   * Retrieves an array of objects with an offset and optional limit.
   *
   * @param int $offset The offset value to start retrieving objects from.
   * @param int|null $limit The maximum number of objects to retrieve. If not provided, defaults to offset + 5.
   * @return array|null An array of objects retrieved with the specified offset and limit, or null if an error occurs.
   */
  public static function getObjectWithOffset(int $offset = 0, int $limit = null, bool $ignoreActiveStatus = false) : ?array {
    try{
      if ($limit === null) {
        $limit = $offset + 5;
      }
      if ($ignoreActiveStatus) 
        return self::getMultipleObject(self::getObjectsWithOffsetIgnoreActiveMode, true, [':offset' => $offset, ':limit' => $limit]);
      else 
        return self::getMultipleObject(self::getObjectsWithOffset, true, [':offset' => $offset, ':limit' => $limit]);
    } catch (\PDOException $exception) {
      handleException($exception);
      echo \App\Views\ViewRender::errorViewRender('500');
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }


  /**
   * Retrieves an array of objects with offset without including nutritional information.
   *
   * @param int $offset The starting offset for retrieving objects.
   * @param int|null $limit The maximum number of objects to retrieve. If null, defaults to offset + 5.
   * @return array|null An array of objects or null if an exception occurs.
   */
  public static function getObjectWithOffsetWithoutNutri(int $offset = 0, int $limit = null, bool $ignoreActiveStatus = false) : ?array {
    try{
      if ($limit === null) {
        $limit = $offset + 5;
      }
      if ($ignoreActiveStatus) 
        return self::getMultipleObject(self::getObjectsWithOffsetIgnoreActiveMode, false, [':offset' => $offset, ':limit' => $limit]);
      else
        return self::getMultipleObject(self::getObjectsWithOffset, false, [':offset' => $offset, ':limit' => $limit]);
    } catch (\PDOException $exception) {
      handleException($exception);
      echo \App\Views\ViewRender::errorViewRender('500');
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }


  /**
   * Retrieves all objects from the database table based on a specified column name and value.
   *
   * @param string $columnName The name of the column to search for.
   * @param mixed $value The value to match in the specified column.
   * @return array|null An array of objects matching the specified column name and value, or null if an error occurred.
   */
  static public function getAllObjectsByFieldAndValue(string $columnName, $value, bool $ignoreActiveStatus = false) : ?array {
    try {
      if ($ignoreActiveStatus) 
        return self::getMultipleObject(self::getAllObjectsByFieldAndValueIgnoreActiveMode, true, [':name' => $columnName, ':value' => $value]);
      else
        return self::getMultipleObject(self::getAllObjectsByFieldAndValue, true, [':name' => $columnName, ':value' => $value]);
    } catch (\PDOException $exception) {
      handleException($exception);
      echo \App\Views\ViewRender::errorViewRender('500');
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }


  /**
   * Retrieves all ingredients without nutri info from the database table based on a specified column name and value.
   *
   * @param string $columnName The name of the column to search for.
   * @param mixed $value The value to match in the specified column.
   * @return array|null An array of objects matching the specified column name and value, or null if an error occurred.
   */
  static public function getAllObjectsByFieldAndValueWithoutNutri(string $columnName, $value, bool $ignoreActiveStatus = false) : ?array {
    try {
      if ($ignoreActiveStatus) 
        return self::getMultipleObject(self::getAllObjectsByFieldAndValueIgnoreActiveMode, false, [':name' => $columnName, ':value' => $value]);
      else
        return self::getMultipleObject(self::getAllObjectsByFieldAndValue, false, [':name' => $columnName, ':value' => $value]);
    } catch (\PDOException $exception) {
      handleException($exception);
      echo \App\Views\ViewRender::errorViewRender('500');
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }


  /**
   * Retrieves an array of objects with a specified offset, field, and value.
   *
   * @param string $name The name of the field to search for.
   * @param mixed $value The value to search for in the specified field.
   * @param int $offset The starting offset for retrieving the objects. Default is 0.
   * @param int|null $limit The maximum number of objects to retrieve. Default is null, which retrieves 5 objects.
   * @return array|null An array of objects matching the specified field and value, or null if an error occurs.
   */
  static public function getObjectWithOffsetByFielAndValue(string $name, $value, int $offset = 0, int $limit = null, bool $ignoreActiveStatus= false) : ?array{
   try{
      if ($limit === null) {
        $limit = $offset + 5;
      }
      if ($ignoreActiveStatus) 
        return self::getMultipleObject(self::getObjectWithOffsetByFielAndValueIgnoreActiveMode, true, 
          [':name' => $name, ':value' => $value, ':offset' => $offset, ':limit' => $limit]);
      else
        return self::getMultipleObject(self::getObjectWithOffsetByFielAndValue, true, 
          [':name' => $name, ':value' => $value, ':offset' => $offset, ':limit' => $limit]);
    } catch (\PDOException $exception) {
      handleException($exception);
      echo \App\Views\ViewRender::errorViewRender('500');
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }


  /**
   * Retrieves an array of ingredient without nutri info with a specified offset, field, and value.
   *
   * @param string $name The name of the field to search for.
   * @param mixed $value The value to search for in the specified field.
   * @param int $offset The starting offset for retrieving the objects. Default is 0.
   * @param int|null $limit The maximum number of objects to retrieve. Default is null, which retrieves 5 objects.
   * @return array|null An array of objects matching the specified field and value, or null if an error occurs.
   */
  static public function getObjectWithOffsetByFielAndValueWithoutNutri(string $name, $value, int $offset = 0, int $limit = null, bool $ignoreActiveStatus=false) : ?array{
    try{
      if ($limit === null) 
        $limit = $offset + 5;

      if ($ignoreActiveStatus)
        return self::getMultipleObject(self::getObjectWithOffsetByFielAndValueIgnoreActiveMode, false, 
          [':name' => $name, ':value' => $value, ':offset' => $offset, ':limit' => $limit]);
      else  
        return self::getMultipleObject(self::getObjectWithOffsetByFielAndValue, false, 
          [':name' => $name, ':value' => $value, ':offset' => $offset, ':limit' => $limit]);
    } catch (\PDOException $exception) {
      handleException($exception);
      echo \App\Views\ViewRender::errorViewRender('500');
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
      return null;
    }
  }
 

  /**
   * Retrieves the ID and name of all ingredients from the database.
   *
   * @return array|null An array of associative arrays containing the ID and name of each ingredient,
   *                   or null if an error occurred.
   */
  static public function getIdAndNameAllObject(bool $ignoreActiveStatus = false) : ?array {
    try {
      $sql = "select id, name from ingredients";

      $sql .= ($ignoreActiveStatus)  ? "" : " WHERE ingredients.isActive = 1";

      $pairs = self::query($sql, 1);
      return $pairs;
    } catch (\PDOException $PDOException) {
      handlePDOException($PDOException);
      echo \App\Views\ViewRender::errorViewRender('500');
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }


  /**
   * Retrieves the ID and name of all ingredients from the database with pagination.
   *
   * @param int $offset The starting offset for retrieving ingredients.
   * @param int|null $limit The maximum number of ingredients to retrieve. If not provided, defaults to offset + 5.
   */
  static public function getPaging(int $offset, int $limit = null, bool $ignoreActiveStatus = false) { 
    try {
      if($limit === null) {
        $limit = $offset + 10;
      }

      $sql = ($ignoreActiveStatus) ? self::getObjectsWithOffsetIgnoreActiveMode : self::getObjectsWithOffset;

      // Fetch Data
      $data = self::query($sql, 4, [':offset' => $offset, ':limit' => $limit], "IngredientModel");

      // Response data JSON
      return json_encode($data);
    } catch (\PDOException $PDOException) {
      handlePDOException($PDOException);
      return json_encode(["error" => "Database error: " . $PDOException->getMessage()]);
    } catch (\Exception $exception) {
      handleException($exception);
      return json_encode(["error" => "Internal server error: " . $exception->getMessage()]);
    }
  }


  /**
   * Retrieves the IDs of all ingredient categories from the database.
   *
   * @return array|null An array of category IDs, or null if an error occurred.
   */
  static public function getCategoryID(){
    try {
      $sql = "SELECT id, detail FROM ingredient_categories";

      $categoryIDs = [];
      $categoryIDs = self::query($sql, 1);
      return $categoryIDs;
    } catch (\PDOException $PDOException) {
      handlePDOException($PDOException);
      echo \App\Views\ViewRender::errorViewRender('500');
      return null;
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }

  static public function getNutritionType(){
    try {
      $sql = "SELECT id, detail FROM nutrition_types";

      $nutritionType = [];
      $nutritionType = self::query($sql, 1);
      return $nutritionType;
    } catch (\PDOException $PDOException) {
      handlePDOException($PDOException);
      echo \App\Views\ViewRender::errorViewRender('500');
      return null;
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }
  
  static public function getMeasurementUnit(){
    try {
      $sql = "SELECT id, detail FROM ingredient_measurement_unit";

      $measurementUnit = [];
      $measurementUnit = self::query($sql, 1);
      return $measurementUnit;
    } catch (\PDOException $PDOException) {
      handlePDOException($PDOException);
      echo \App\Views\ViewRender::errorViewRender('500');
      return null;
    } catch (\Exception $exception) {
      handleException($exception);
    } catch (\Throwable $throwable) {
      handleError($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
    }
    return null;
  }

}
      