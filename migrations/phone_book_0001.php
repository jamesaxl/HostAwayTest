<?php

/**
 * Class phone_book_0001
 */
class phone_book_0001
{
    public function create()
    {
        $query = 'CREATE TABLE phone_books (
            id INT AUTO_INCREMENT PRIMARY KEY,
            firstName VARCHAR(255) NOT NULL,
            lastName VARCHAR(255) NOT NULL,
            phoneNumber VARCHAR(255) UNIQUE NOT NULL,
            countryCode VARCHAR(255) NOT NULL,
            timezone VARCHAR(255) NOT NULL,
            createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updatedOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP          
        )  ENGINE=INNODB;';

        \app\base\Engine::$engine->database->pdo->exec($query);
    }

    public function drop()
    {
        $query = 'DROP TABLE phone_books;';
        \app\base\Engine::$engine->database->pdo->exec($query);
    }
}