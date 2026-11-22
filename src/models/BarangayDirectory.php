<?php
// Simple PDO CRUD used by the separate resident, business and location modules.
// Only known table/field lists may be passed to this class.
class BarangayDirectory
{
    private PDO $db;
    private string $table;
    private array $fields;

    public function __construct(PDO $db, string $type)
    {
        $definitions = [
            'residents' => ['php_residents', ['first_name', 'middle_name', 'last_name', 'birth_date', 'sex', 'phone', 'address', 'household_no', 'notes']],
            'businesses' => ['php_businesses', ['business_name', 'owner_name', 'business_type', 'address', 'contact', 'permit_no', 'renewal_date', 'cctv_status', 'cctv_count', 'notes']],
            'places' => ['php_map_places', ['name', 'kind', 'hazard_type', 'description', 'latitude', 'longitude', 'review_status', 'photo_name']],
        ];
        if (!isset($definitions[$type])) {
            throw new RuntimeException('Invalid directory type.');
        }
        [$this->table, $this->fields] = $definitions[$type];
        $this->db = $db;
    }

    public function all(): array
    {
        $sql = 'SELECT * FROM ' . $this->table . ' ORDER BY updated_at DESC';
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(string $id): ?array
    {
        $query = $this->db->prepare('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $query->execute([':id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function save(array $record): string
    {
        $id = (string) ($record['id'] ?? '');
        $old = $id !== '' ? $this->find($id) : null;
        if ($id !== '' && !$old) {
            throw new RuntimeException('Record not found. Reload and try again.');
        }
        if ($id === '') {
            $id = bin2hex(random_bytes(12));
        }
        $now = date(DATE_ATOM);
        $values = [':id' => $id, ':updated_at' => $now];
        foreach ($this->fields as $field) {
            $values[':' . $field] = $record[$field] ?? '';
        }
        if ($old) {
            $assignments = array_map(fn($f) => $f . ' = :' . $f, $this->fields);
            $assignments[] = 'updated_at = :updated_at';
            $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $assignments) . ' WHERE id = :id';
        } else {
            $values[':created_at'] = $now;
            $columns = array_merge(['id'], $this->fields, ['created_at', 'updated_at']);
            $sql = 'INSERT INTO ' . $this->table . ' (' . implode(', ', $columns) . ') VALUES (:' . implode(', :', $columns) . ')';
        }
        $query = $this->db->prepare($sql);
        $query->execute($values);
        return $id;
    }

    public function delete(string $id): bool
    {
        $query = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE id = :id');
        $query->execute([':id' => $id]);
        return $query->rowCount() > 0;
    }
}
