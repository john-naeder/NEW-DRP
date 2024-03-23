<?php

namespace App\Controllers;

use App\Operations\IngredientReadOperation;
use App\Operations\IngredientUpdateOperation;
use App\Operations\UserOperation;
use App\Operations\RecipeReadOperation;
use App\Operations\RecipeUpdateOperation;
use App\Operations\UploadImageOperation;
use App\Operations\ValidateIngredientDataHolder;

class AdminController extends BaseController
{
    public function index()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }
        return $this->loadView('admin.index');
    }

    // User
    public function userManager()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }

        if ($_GET['id'] != '') {
            $users = UserOperation::getUserById($_GET['id']);
        } else if ($_GET['username'] != '') {
            $users = UserOperation::getUserByUsername($_GET['username']);
        } else if ($_GET['email'] != '') {
            $users = UserOperation::getUserByEmail($_GET['email']);
        }
        if (!$users) {
            $users = UserOperation::getAllUser();
        }

        return $this->loadView('admin.user', ['users' => $users]);
    }

    public function userManagerUpdateUI()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }
        $users = UserOperation::getUserById($_GET['id']);
        return $this->loadView('admin.userUpdate', ['user' => $users]);
    }

    public function userManagerUpdate()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }
        $data = $_POST;
        UserOperation::update($data);
        header("Location: /manager/user");
    }

    public function userManagerAdd()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }
        $data = $_POST;

        if (UserOperation::checkEmail($data['email'])) {
            echo '<script>
            alert("Email already exist!");
            window.location.href = "/manager/user";
            </script>';
        } else if (UserOperation::checkUserName($data['username'])) {
            echo '<script>
            alert("Username Already Existed");
            window.location.href = "/manager/user";
            </script>';
        } else if (UserOperation::addUser($data)) {
            echo '<script>
                alert("Register Success!");
                window.location.href = "/manager/user";
            </script>';
            exit();
        } else {
            echo '<script>
                alert("Register Fail!, Please try again!");
                window.location.href = "/manager/user";
            </script>';
            exit();
        }

        header("Location: /manager/user");
    }

    public function setUserLevel()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
            $data = $_POST;
            UserOperation::setLevel($data);
            header("Location: /manager/user");
        }
    }

    private function isAdmin()
    {
        return isset($_SESSION['level']) && $_SESSION['level'] == 1;
    }

    /*
        Quản lý recipe
    */
    public function recipeManager()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }

        if ($_GET['id'] != '') {
            $recipes = RecipeReadOperation::getSingleObjectByIdForAdmin($_GET['id']);
        } else if ($_GET['name'] != '') {
            $recipes = RecipeReadOperation::getAllObjectsByFieldAndValue('name', $_GET['name']);
        } else if ($_GET['course'] != '') {
            $recipes = RecipeReadOperation::getAllObjectsByFieldAndValue('course', $_GET['course']);
        } else if ($_GET['meal'] != '') {
            $recipes = RecipeReadOperation::getAllObjectsByFieldAndValue('meal', $_GET['meal']);
        } else if ($_GET['method'] != '') {
            $recipes = RecipeReadOperation::getAllObjectsByFieldAndValue('method', $_GET['method']);
        }

        if (!$recipes) {
            $recipes = RecipeReadOperation::getAllObjectsForAdmin();
        }

        return $this->loadView('admin.recipe', ['recipes' => $recipes]);
    }

    public function setRecipeActive()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }

        $data = $_POST;
        RecipeUpdateOperation::setRecipeActive($data);

        header("Location: /manager/recipe");
    }

    public function recipeManagerUpdateUI()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }

        $recipe = RecipeReadOperation::getSingleObjectByIdForAdmin($_GET['id']);
        return $this->loadView('admin.recipeUpdate', ['recipe' => $recipe]);
    }

    public function recipeManagerUpdate()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }
        $data = $_POST;

        if ($_FILES['file']['name'] != null){
            $data['image_url'] = UploadImageOperation::process();
        }
        if (RecipeUpdateOperation::execute($data)) {
            echo '<script>
            alert("Update recipes succesful!");
            window.location.href = "/manager/recipe";
            </script>';
        }
    }

    /*
        Quản lý ingredient
    */
    public function ingredientManager()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }

        $ingredients = null;
        if (isset($_GET['s_id']) && $_GET['s_id'] != '') {
            $ingredients = IngredientReadOperation::getSingleObjectById($_GET['s_id']);
        } else if (isset($_GET['s_name']) && $_GET['s_name'] != '') {
            $ingredients = IngredientReadOperation::getAllObjectsByFieldAndValue('name', $_GET['s_name']);
        } else if (isset($_GET['s_category']) && $_GET['s_category'] != ''){
            $ingredients = IngredientReadOperation::getAllObjectsByFieldAndValue('category', $_GET['s_category']);
        } else if (isset($_GET['s_measurement_desciption']) && $_GET['s_measurement_desciption'] != ''){
            $ingredients = IngredientReadOperation::getAllObjectsByFieldAndValue('measurement_unit', $_GET['s_measurement_desciption']);
        } else if (isset($_GET['s_name']) && $_GET['s_name'] != ''){
            $ingredients = IngredientReadOperation::getAllObjectsByFieldAndValue('name', $_GET['s_name']);
        }

        if (!$ingredients) {
            $ingredients = IngredientReadOperation::getAllObjects();
        }

        return $this->loadView('admin.ingredient', ['ingredients' => $ingredients]);
    }

    public function setIngredientActive()
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }
        $data = $_POST;
        IngredientUpdateOperation::setIngredientActive($data);
    }

    public function ingredientManagerUpdateUI() 
    {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }
        $ingredientOpt = ValidateIngredientDataHolder::getInstance();
        $data = $_GET;
        $ingredient = IngredientReadOperation::getSingleObjectById($data['id']);

        return $this->loadView('admin.ingredientUpdate', ['ingredient' => $ingredient, 'opts' => $ingredientOpt]);
    }

    public function ingredientManagerUpdate() {
        if (!$this->isAdmin()) {
            return parent::loadError('404');
        }
        $data = $_POST;
        IngredientUpdateOperation::execute($data);
    }
}
