<?php
/**
 * Step 2.
 * 
 * This class seeds data into the database for testing
 * It needs to be run once, after migrations, before the first app execution, as a part of setup.
 * 
 * Usage (from the application root directory): 
 * php app/commands/seed.php
 */

require_once __DIR__ . '/../Config.php';

class Seed 
{
    private $pdo;
    private $seedDir;

    public function __construct() 
    {
        $host = getenv('DB_HOST');
        $dbname = getenv('DB_NAME');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASS');

        $this->seedDir = __DIR__ . '/../../seed-data';

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
        echo "Seeding... \n\n";
        $this->seed();
    }

    private function seed(): void
    {
        $sqlFiles = glob("$this->seedDir/*.sql");

        echo "Checking files {$this->seedDir}/*.sql\n" ;

        if(empty($sqlFiles)) {
            echo "No seeding files found. \n\n";
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

$s = new Seed();
$s->run();

?>