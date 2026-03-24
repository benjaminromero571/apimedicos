<?php

require_once __DIR__ . '/BaseRepository.php';

class PasswordResetRepository extends BaseRepository
{
    protected $table = 'password_resets';
    protected $primaryKey = 'email';

    public function createResetToken(string $email, string $token): bool
    {
        $this->delete($email); // Eliminar token anterior si existe
        $sql = "INSERT INTO {$this->table} (email, token) VALUES (?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('ss', $email, $token);
        return $stmt->execute();
    }

    public function getResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE token = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
