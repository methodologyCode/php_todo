<?php
header("Content-Type: application/json");

class TaskManager
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->createTable();
    }

    private function createTable()
    {
        $query = 'CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL
        );';

        $this->db->exec($query);
    }

    public function handleRequest()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $this->handlePost();
                break;
            case 'GET':
                $this->handleGet();
                break;
            case 'DELETE':
                $this->handleDelete();
                break;
            default:
                http_response_code(405); // Method Not Allowed
                break;
        }
    }

    private function handlePost()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if ($data && isset($data['title'])) {
            $this->addTask($data['title']);
        } else {
            http_response_code(400); // Bad Request
        }
    }

    private function handleGet()
    {
        $tasks = $this->getTasks();
        echo json_encode($tasks);
    }

    private function handleDelete()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if ($data && isset($data['id'])) {
            $this->deleteTask($data['id']);
        } else {
            http_response_code(400);
        }
    }

    private function addTask($title)
    {
        $statement = $this->db->prepare('INSERT INTO tasks (title) VALUES (:title)');
        $statement->bindParam(':title', $title);
        $statement->execute();
        $tasks = $this->getTasks();
        echo json_encode($tasks);
    }

    private function getTasks()
    {
        $result = $this->db->query('SELECT * FROM tasks');
        $tasks = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $tasks[] = $row;
        }
        return $tasks;
    }

    private function deleteTask($id)
    {
        $statement = $this->db->prepare('DELETE FROM tasks WHERE id = :id');
        $statement->bindParam(':id', $id);
        $statement->execute();
        $tasks = $this->getTasks();
        echo json_encode($tasks);
    }
}

// Подключаемся к базе данных SQLite
$db = new SQLite3('task.db');

// Создаем экземпляр TaskManager и обрабатываем запрос
$taskManager = new TaskManager($db);
$taskManager->handleRequest();
?>




