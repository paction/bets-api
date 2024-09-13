<?php
/**
 * Step 1.
 * This class loads and runs all sql statements which it finds under migrations directory. 
 * It needs to be run once, before seeding, before the first app execution, as a part of setup.
 * 
 * Usage (from the application root directory): 
 * php app/commands/migrate.php
 * 
 * This could be done in a nicer way. For example with a general cli tool 
 * which would load all necessary classes, and then by passing the require command like this:
 * php commandlinetool migrate
 */

require_once __DIR__ . '/../Config.php';

class Migrate 
{
    private $pdo;
    private $migrationsDir;

    public function __construct() 
    {
        $host = getenv('DB_HOST');
        $dbname = getenv('DB_NAME');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASS');

        $this->migrationsDir = __DIR__ . '/../../migrations';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
        try {
            $this->pdo = new \PDO($dsn, $username, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function run(): void
    {
        echo "Executing migrations... \n\n";
        $this->migrate('down');
        $this->migrate('up');
    }

    private function migrate(string $direction): void
    {
        $sqlFiles = glob("$this->migrationsDir/*-$direction.sql");

        echo "Checking files {$this->migrationsDir}/*-$direction.sql\n" ;

        if(empty($sqlFiles)) {
            echo "No $direction migration files found. \n\n";
            return;
        }

        // iterate over each .sql file
        foreach ($sqlFiles as $file) {
            $sql = file_get_contents($file);
            try {
                $this->pdo->exec($sql);
                echo "Successfully executed: " . basename($file) . "\n\n";
            } catch (\PDOException $e) {
                echo "Error executing file " . basename($file) . ": " . $e->getMessage() . "\n";
            }
        }
    }
}

$m = new Migrate();
$m->run();

?>