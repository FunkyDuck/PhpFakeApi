<?php
namespace JsonValidator;

class JsonValidator {
    public static function validate(array $data, array $schema): array {
        $errors =  [];

        foreach($schema['required'] ?? [] as $field) {
            if(!array_key_exists($field, $data)) {
                $errors[] = "Missing required field : '{$field}'";
            }
        }

        foreach ($schema['properties'] ?? [] as $field => $rules) {
            if(!array_key_exists($field, $data)) continue;

            $value = $data[$field];
            $expected = $rules['type'];

            $valid = match($expected) {
                'string' => is_string($value),
                'integer' => is_int($value),
                'number' => is_int($value) || is_float($value),
                'array' => is_array($value),
                'boolean' => is_bool($value),
                'object' => is_object($value),
                default => true
            };

            if(!$valid) {
                $errors[] = "Invalid type for '{$field}' : expected '{$expected}'";
                continue;
            }
        }

        return $errors;
    }
}