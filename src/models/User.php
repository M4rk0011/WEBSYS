<?php
/**
 * Provides database operations for the User domain.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// Simple OOP part: all account INSERT, SELECT and UPDATE queries are here.
class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // READ: return every account for the administrator's account screen.
    public function all()
    {
        $sql = "SELECT body FROM php_users ORDER BY username";
        $prep = $this->pdo->prepare($sql);
        $prep->execute();
        $rows = $prep->fetchAll();

        $users = [];
        foreach ($rows as $row) {
            $users[] = json_decode($row["body"]);
        }
        return $users;
    }

    // READ ONE: fetch the account whose ID matches :id.
    public function find($id)
    {
        $sql = "SELECT body FROM php_users WHERE id = :id";
        $prep = $this->pdo->prepare($sql);
        $prep->execute([":id" => $id]);
        $row = $prep->fetch();

        if (!$row) {
            return null;
        }
        return json_decode($row["body"]);
    }

    // CREATE / UPDATE: validate the account before saving it.
    public function save($user, $password = null)
    {
        $username = trim($user->username);
        $email = strtolower(trim($user->email));

        if ($username === "" || strlen($username) > 100) {
            throw new RuntimeException("Provide a username and valid email address.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("Provide a username and valid email address.");
        }
        if ($password !== null && strlen($password) < 6) {
            throw new RuntimeException("Password must contain at least 6 characters.");
        }

        // Another account must not already have this username or email.
        $sql = "SELECT id FROM php_users
                WHERE (LOWER(username) = LOWER(:username) OR LOWER(email) = LOWER(:email))
                AND id <> :id";
        $prep = $this->pdo->prepare($sql);
        $prep->execute([
            ":username" => $username,
            ":email" => $email,
            ":id" => $user->id,
        ]);
        if ($prep->fetch()) {
            throw new RuntimeException("Username or email already exists.");
        }

        $user->username = $username;
        $user->email = $email;
        unset($user->password); // Never save a plain-text password in the profile.
        $body = json_encode($user, JSON_THROW_ON_ERROR);
        $existing = $this->find($user->id);

        $values = [
            ":id" => $user->id,
            ":username" => $username,
            ":email" => $email,
            ":body" => $body,
        ];

        if (!$existing) {
            if ($password === null) {
                throw new RuntimeException("Password required.");
            }
            $sql = "INSERT INTO php_users (id, username, email, password_hash, body)
                    VALUES (:id, :username, :email, :password_hash, :body)";
            $values[":password_hash"] = password_hash($password, PASSWORD_DEFAULT);
        } elseif ($password !== null) {
            $sql = "UPDATE php_users SET username = :username, email = :email,
                    password_hash = :password_hash, body = :body WHERE id = :id";
            $values[":password_hash"] = password_hash($password, PASSWORD_DEFAULT);
        } else {
            $sql = "UPDATE php_users SET username = :username, email = :email,
                    body = :body WHERE id = :id";
        }

        $prep = $this->pdo->prepare($sql);
        $prep->execute($values);
    }
}
