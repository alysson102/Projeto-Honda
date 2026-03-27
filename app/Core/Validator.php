<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        foreach ($rules as $field => $fieldRules) {
            $value = trim((string)($data[$field] ?? ''));

            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && $value === '') {
                    $this->errors[$field][] = 'Campo obrigatório.';
                }

                if ($rule === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = 'E-mail inválido.';
                }

                if (str_starts_with($rule, 'min:')) {
                    $min = (int) str_replace('min:', '', $rule);
                    if (mb_strlen($value) < $min) {
                        $this->errors[$field][] = "Mínimo de {$min} caracteres.";
                    }
                }

                if (str_starts_with($rule, 'max:')) {
                    $max = (int) str_replace('max:', '', $rule);
                    if (mb_strlen($value) > $max) {
                        $this->errors[$field][] = "Máximo de {$max} caracteres.";
                    }
                }

                if (str_starts_with($rule, 'regex:')) {
                    $pattern = substr($rule, 6);
                    if ($value !== '' && @preg_match($pattern, '') !== false && preg_match($pattern, $value) !== 1) {
                        $this->errors[$field][] = 'Formato inválido.';
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
