<?php
/**
 * Validator
 * Reusable server-side validation class.
 */

class Validator
{
    private array $errors = [];
    private array $data;
    private PDO $db;

    public function __construct(array $data, ?PDO $db = null)
    {
        $this->data = $data;
        if ($db) {
            $this->db = $db;
        }
    }

    public function required(string $field, string $label = ''): self
    {
        $label = $label ?: ucfirst($field);
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = "$label is required.";
        }
        return $this;
    }

    public function email(string $field, string $label = ''): self
    {
        $label = $label ?: ucfirst($field);
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$label must be a valid email address.";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label = ''): self
    {
        $label = $label ?: ucfirst($field);
        if (isset($this->data[$field]) && strlen(trim($this->data[$field])) < $min) {
            $this->errors[$field] = "$label must be at least $min characters.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label = ''): self
    {
        $label = $label ?: ucfirst($field);
        if (isset($this->data[$field]) && strlen(trim($this->data[$field])) > $max) {
            $this->errors[$field] = "$label must not exceed $max characters.";
        }
        return $this;
    }

    public function match(string $field1, string $field2, string $label = ''): self
    {
        $label = $label ?: ucfirst($field2);
        if (isset($this->data[$field1], $this->data[$field2]) && $this->data[$field1] !== $this->data[$field2]) {
            $this->errors[$field2] = "$label does not match.";
        }
        return $this;
    }

    public function unique(string $field, string $table, string $column = '', string $label = ''): self
    {
        $label  = $label ?: ucfirst($field);
        $column = $column ?: $field;

        if (isset($this->data[$field]) && isset($this->db)) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM $table WHERE $column = :value");
            $stmt->execute(['value' => trim($this->data[$field])]);
            if ($stmt->fetchColumn() > 0) {
                $this->errors[$field] = "$label is already taken.";
            }
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    public function getValue(string $field): string
    {
        return htmlspecialchars(trim($this->data[$field] ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
?>
