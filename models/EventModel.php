<?php
class EventModel
{
    private $pdo;
    private $table = 'events';


    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    public function getAll($limit = 50, $offset = 0)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} ORDER BY date DESC LIMIT :l OFFSET :o");
        $stmt->bindValue(':l', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':o', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }


    public function create($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (title, date, location, description) VALUES (:title, :date, :location, :description)");
        $stmt->execute([
            ':title' => $data['title'],
            ':date' => $data['date'],
            ':location' => $data['location'] ?? null,
            ':description' => $data['description'] ?? null,
        ]);
        return $this->pdo->lastInsertId();
    }


    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET title = :title, date = :date, location = :location, description = :description, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([
            ':title' => $data['title'],
            ':date' => $data['date'],
            ':location' => $data['location'] ?? null,
            ':description' => $data['description'] ?? null,
            ':id' => $id,
        ]);
    }


    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
