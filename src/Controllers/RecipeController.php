<?php

namespace Johann\PhpAdvanced4Material;

use Johann\PhpAdvanced4Material\RecipeModel;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class RecipeController
{
    private RecipeModel $model;
    private Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Views/');
        $this->twig = new Environment($loader);
        //
    }

    public function browse(): string
    {
        $recipes = $this->model->getAll();
        return $this->twig->render('index.html.twig', [
            'recipes' => $recipes
        ]);
    }

    public function show(int $id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if (false === $id || null === $id) {
            header("Location: /");
            exit("Wrong input parameter");
        }

        // Fetching a recipe
        $recipe = $this->model->getById($id);

        // Result check
        if (!isset($recipe['title']) || !isset($recipe['description'])) {
            header("Location: /");
            exit("Recipe not found");
        }

        // Generate the web page
        require __DIR__ . '/../Views/show.html.twig';
    }

    public function add()
    {
        $errors = [];

        if ($_SERVER["REQUEST_METHOD"] === 'POST') {
            $recipe = array_map('trim', $_POST);
            $recipe = array_map('htmlentities', $recipe);

            // Validate data
            $errors = $this->validate($recipe);

            // Save the recipe
            if (empty($errors)) {
                $this->model->save($recipe);
                header('Location: /');
            }
        }

        // Generate the web page
        require __DIR__ . '/../Views/form.html.twig';
    }

    private function validate(array $recipe)
    {
        if (empty($recipe['title'])) {
            $errors[] = 'The title is required';
        }
        if (empty($recipe['description'])) {
            $errors[] = 'The description is required';
        }
        if (!empty($recipe['title']) && strlen($recipe['title']) > 255) {
            $errors[] = 'The title should be less than 255 characters';
        }

        return $errors ?? [];
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        header('Location: /');
    }

    public function update(int $id)
    {
        $errors = [];
        $recipe = $this->model->getById($id);

        if ($_SERVER["REQUEST_METHOD"] === 'POST') {
            $recipe = array_map('trim', $_POST);
            $recipe = array_map('htmlentities', $recipe);

            // Validate data
            $errors = $this->validate($recipe);

            // Update the recipe
            if (empty($errors)) {
                $this->model->update($recipe, $id);
                header('Location: /show?id=' . $id);
            }
        }

        // Generate the web page
        require __DIR__ . '/../Views/form.html.twig';
    }
}
