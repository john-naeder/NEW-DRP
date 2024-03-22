<?php 
namespace App\Models;
// use autoload from composer
require_once($_SERVER['DOCUMENT_ROOT'] . '/App/Core/init.php');

class RecipeModel extends BaseModel {
    const TABLE = 'Recipes';
    private $id;
    private $name;
    private $description;
    private $image_url;
    private $preparation_time;
    private $cooking_time;
    private $direction;
    private $course;
    private $meal;
    private $method;
    private $timestamp;
    private $ingredientComponets = [];

    public function __construct($id = null, $name = null, $description = null, $image_url = null, 
            $preparation_time = null, $cooking_time = null, $direction = null, $course = null, 
            $meal = null, $method = null, $timestamp = null, $ingredientComponets = null) {
        parent::__construct();
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->image_url = $image_url;
        $this->preparation_time = $preparation_time;
        $this->cooking_time = $cooking_time;
        $this->direction = $direction;
        $this->course = $course;
        $this->meal = $meal;
        $this->method = $method;
        $this->timestamp = $timestamp;
        $this->ingredientComponets = $ingredientComponets; 
    }

    // get and set 
    public function getId() { return $this->id;  }
    public function setId($id) { $this->id = $id; }
    public function getActive() { return $this->isActive; }
    public function setActive($condition = 1) { $this->isActive = $condition; }
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }
    public function getImgUrl() { return $this->image_url; }
    public function setImgUrl($image_url) { $this->image_url = $image_url; }
    public function getPreparationTime() { return $this->preparation_time; }
    public function setPreparationTime($preparation_time) { 
        $this->preparation_time = $preparation_time; 
    }
    public function getCookingTime() { return $this->cooking_time; }
    public function setCookingTime($cooking_time) { 
        $this->cooking_time = $cooking_time; 
    }
    public function getDirection() { return $this->direction; }
    public function setDirection($direction) { $this->direction = $direction; }
    public function getcourse() { return $this->course; }
    public function setcourse($course) { $this->course = $course; }
    public function getmeal() { return $this->meal; }
    public function setmeal($meal) { $this->meal = $meal; }
    public function getmethod() { return $this->method; }
    public function setmethod($method) { $this->method = $method; }
    public function getTimestamp() { return $this->timestamp; }
    public function setTimestamp($timestamp) { $this->timestamp = $timestamp; }
    public function getIngredientComponets() { return $this->ingredientComponets; }
    public function setIngredientComponets($ingredientComponets) { 
        $this->ingredientComponets = self::addIngredient($ingredientComponets); 
    }

    public static function addIngredient($data) {
        if(isset($data)){
            $ingredientComponets = [];
            foreach ($data as $ingredient) {
                $ingredientComponets[] = array(
                    'ingredient_name'=> $ingredient['name'],
                    'quantity' => $ingredient['number_of_unit'],
                    'unit' => $ingredient['measurement_description']
                );
            }
        } else {
            $ingredientComponets [] = null;
        }
        return $ingredientComponets;
    }

    public static function createObjectByRawArray($data){
        $object = new RecipeModel();
        $object->setId($data['id']);
        $object->setActive($data['isActive'] ?? 1);
        $object->setName($data['name']);
        $object->setDescription($data['description'] ?? "Unknown");
        $object->setImgUrl($data['image_url'] ?? null);
        $object->setPreparationTime($data['preparation_time'] ?? "Unknown");
        $object->setCookingTime($data['cooking_time'] ?? "Unknown");
        $object->setDirection($data['directions'] ?? "Unknown");
        $object->setcourse($data['course'] ?? "Unknown");
        $object->setmeal($data['meal'] ?? "Unknown");
        $object->setmethod($data['method'] ?? "Unknown");
        $object->setTimestamp($data['timestamp'] ?? "Unknown");
        if (isset($data['ingredientComponents']))
            $object->setIngredientComponets(($data['ingredientComponents']));
        return $object;
    }
}
