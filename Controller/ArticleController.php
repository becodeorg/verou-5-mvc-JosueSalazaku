<?php

declare(strict_types=1);

class ArticleController
{
    private DatabaseManager $databaseManager;

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    public function index()
    {
        // Load all required data
        $articles = $this->getArticles();

        // Load the view
        require 'View/articles/index.php';
    }

    private function getArticles()
    {
        try {
            $query = "SELECT * FROM articles;";

            $statement = $this->databaseManager->connection->prepare($query);
            $statement->execute();
            $rawArticles = $statement->fetchAll();

            $articles = [];
            foreach ($rawArticles as $rawArticle) {
                // We are converting an article from a "dumb" array to a much more flexible class
                $articles[] = new Article($rawArticle['id'], $rawArticle['title'], $rawArticle['description'], $rawArticle['publish_date']);
            }

            return $articles;
        } catch (Exception $exception) {
            echo $exception->getMessage(); // Handle or log the exception appropriately
        }
    }

    public function show()
    {
        $article = $this->selectOne();

        require 'View/articles/show.php';
    }

    private function selectOne()
    {
        try {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

            if ($id === false || $id === null) {
                // Handle invalid or missing ID
                throw new Exception('Invalid or missing article ID.');
            }

            $query = "SELECT * FROM articles WHERE id = :id;";

            $statement = $this->databaseManager->connection->prepare($query);

            $statement->bindParam(":id", $id, PDO::PARAM_INT);
            $statement->execute();
            $rawArticles = $statement->fetchAll();

            if (empty($rawArticles)) {
                // Handle no article found with the given ID
                throw new Exception('Article not found.');
            }

            $article = new Article($rawArticles[0]['id'], $rawArticles[0]['title'], $rawArticles[0]['description'], $rawArticles[0]['publish_date']);

            return $article;
        } catch (Exception $exception) {
            echo $exception->getMessage(); // Handle or log the exception appropriately
        }
    }
}
