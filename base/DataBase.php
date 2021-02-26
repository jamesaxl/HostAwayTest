<?php

namespace app\base;
/**
 * Class DataBase
 * @package app\base
 */
class DataBase
{
    public \PDO $pdo;

    /**
     * DataBase constructor.
     */
    public function __construct()
    {
        $dsn = Config::get('DB_DRIVER').':host='.Config::get('DB_HOST').';port='.Config::get('DB_PORT').';dbname='.Config::get('DB_DATABASE') ?? '';
        $user = Config::get('DB_USERNAME') ?? '';
        $password = Config::get('DB_PASSWORD') ?? '';

        $this->pdo = new \PDO($dsn, $user, $password);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     *
     */
    public function applyMigrations(): void
    {
        $this->createMigrationTable();
        $this->getAppliedMigrations();

        $appliedMigrations = $this->getAppliedMigrations();

        $newMigrations = [];
        $files = scandir(Engine::$ROOT_DIR . '/migrations');
        $toApplyMigrations = array_diff($files, $appliedMigrations);

        foreach ($toApplyMigrations as $migration) {
            if ($migration === '.' || $migration === '..') {
                continue;
            }

            require_once Engine::$ROOT_DIR . '/migrations/' . $migration;

            $className = pathinfo($migration, PATHINFO_FILENAME);
            $instance = new $className();
            $this->log("Applying migration $migration");
            $instance->create();
            $this->log("Applied migration $migration");
            $newMigrations[] = $migration;
        }

        if (!empty($newMigrations)) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log("There are no migrations to apply");
        }
    }

    /**
     *
     */
    public function createMigrationTable(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )  ENGINE=INNODB;");

    }

    /**
     * @return array
     */
    public function getAppliedMigrations(): array
    {
        $statement = $this->pdo->prepare("SELECT migration FROM migrations;");
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param array $newMigrations
     */
    protected function saveMigrations(array $newMigrations): void
    {
        $str = implode(',', array_map(fn($m) => "('$m')", $newMigrations));
        $statement = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES 
            $str
        ");
        $statement->execute();
    }

    /**
     * @param $sql
     * @return \PDOStatement
     */
    public function prepare($sql): \PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * @param $message
     */
    private function log($message): void
    {
        echo "[" . date("Y-m-d H:i:s") . "] - " . $message . PHP_EOL;
    }
}
