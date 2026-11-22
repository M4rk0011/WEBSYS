<?php
// Simple OOP part: this class keeps complaint database queries in one place.
class Complaint
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // READ: get all complaints. visibleReports() later checks each user's access.
    public function all()
    {
        $sql = "SELECT body FROM php_reports";
        $prep = $this->pdo->prepare($sql);
        $prep->execute();
        $rows = $prep->fetchAll();

        $complaints = [];
        foreach ($rows as $row) {
            $complaint = json_decode($row["body"]);
            $complaints[] = normalReport($complaint);
        }

        // Newest complaints appear first.
        usort($complaints, function ($first, $second) {
            return strcmp($second->reportedAt, $first->reportedAt);
        });
        return $complaints;
    }

    // READ ONE: use a named placeholder, just like WHERE id = :id in a CRUD form.
    public function find($id)
    {
        $sql = "SELECT body FROM php_reports WHERE id = :id";
        $prep = $this->pdo->prepare($sql);
        $prep->execute([":id" => $id]);
        $row = $prep->fetch();

        if (!$row) {
            return null;
        }
        return normalReport(json_decode($row["body"]));
    }

    // CREATE / UPDATE: keep the existing record format so no database reset is needed.
    public function save($complaint)
    {
        // The old database keeps the complaint's fields together as JSON text.
        $body = json_encode($complaint, JSON_THROW_ON_ERROR);
        $existing = $this->find($complaint->id);

        if ($existing) {
            $sql = "UPDATE php_reports SET body = :body WHERE id = :id";
        } else {
            $sql = "INSERT INTO php_reports (id, body) VALUES (:id, :body)";
        }

        $prep = $this->pdo->prepare($sql);
        $prep->execute([
            ":id" => $complaint->id,
            ":body" => $body,
        ]);
    }
}
