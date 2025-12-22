<?php
/**
 * Input Validation Helpers
 * 
 * Centralized validation functions to eliminate redundancy.
 * All validation functions return consistent error arrays.
 */

/**
 * Validate email address
 * 
 * @param string $email Email address to validate
 * @param string $fieldName Field name for error message (optional)
 * @return array Empty array if valid, array with error message if invalid
 */
function validateEmail($email, $fieldName = 'email') {
    if (empty($email)) {
        return ["Field '{$fieldName}' is required"];
    }
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return ["Field '{$fieldName}' must be a valid email address"];
    }
    
    return [];
}

/**
 * Validate required fields
 * 
 * Checks that all required fields are present and not empty.
 * 
 * @param array $data Data to validate
 * @param array $required Required field names
 * @return array Errors (empty if valid)
 */
function validateRequired($data, $required) {
    $errors = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
            $errors[] = "Field '{$field}' is required";
        }
    }
    
    return $errors;
}

/**
 * Sanitize string input
 * 
 * Removes HTML tags and trims whitespace.
 * 
 * @param string $input Input string
 * @return string Sanitized string
 */
function sanitizeString($input) {
    if (!is_string($input)) {
        return '';
    }
    return trim(strip_tags($input));
}

/**
 * Validate integer with range
 * 
 * @param mixed $value Value to validate
 * @param string $fieldName Field name for error message
 * @param int|null $min Minimum value (optional)
 * @param int|null $max Maximum value (optional)
 * @return array Empty array if valid, array with error message if invalid
 */
function validateInt($value, $fieldName = 'value', $min = null, $max = null) {
    if ($value === null || $value === '') {
        return ["Field '{$fieldName}' is required"];
    }
    
    if (!is_numeric($value)) {
        return ["Field '{$fieldName}' must be a valid integer"];
    }
    
    $int = (int)$value;
    
    if ($min !== null && $int < $min) {
        return ["Field '{$fieldName}' must be at least {$min}"];
    }
    
    if ($max !== null && $int > $max) {
        return ["Field '{$fieldName}' must be at most {$max}"];
    }
    
    return [];
}

/**
 * Validate enum value
 * 
 * Checks if value is in allowed list.
 * 
 * @param mixed $value Value to validate
 * @param array $allowed Allowed values
 * @param string $fieldName Field name for error message
 * @return array Empty array if valid, array with error message if invalid
 */
function validateEnum($value, $allowed, $fieldName = 'value') {
    if ($value === null || $value === '') {
        return ["Field '{$fieldName}' is required"];
    }
    
    if (!in_array($value, $allowed, true)) {
        $allowedList = implode(', ', $allowed);
        return ["Field '{$fieldName}' must be one of: {$allowedList}"];
    }
    
    return [];
}

/**
 * Validate date string
 * 
 * @param string $date Date string to validate
 * @param string $fieldName Field name for error message
 * @param string $format Date format (default: 'Y-m-d H:i:s')
 * @return array Empty array if valid, array with error message if invalid
 */
function validateDate($date, $fieldName = 'date', $format = 'Y-m-d H:i:s') {
    if (empty($date)) {
        return ["Field '{$fieldName}' is required"];
    }
    
    $d = DateTime::createFromFormat($format, $date);
    if (!$d || $d->format($format) !== $date) {
        return ["Field '{$fieldName}' must be a valid date in format {$format}"];
    }
    
    return [];
}

/**
 * Validate string length
 * 
 * @param string $value String to validate
 * @param string $fieldName Field name for error message
 * @param int $minLength Minimum length (optional)
 * @param int $maxLength Maximum length (optional)
 * @return array Empty array if valid, array with error message if invalid
 */
function validateStringLength($value, $fieldName = 'value', $minLength = null, $maxLength = null) {
    if ($value === null) {
        return ["Field '{$fieldName}' is required"];
    }
    
    $length = mb_strlen($value);
    
    if ($minLength !== null && $length < $minLength) {
        return ["Field '{$fieldName}' must be at least {$minLength} characters"];
    }
    
    if ($maxLength !== null && $length > $maxLength) {
        return ["Field '{$fieldName}' must be at most {$maxLength} characters"];
    }
    
    return [];
}

/**
 * Validate multiple fields at once
 * 
 * Combines validation results from multiple validators.
 * 
 * @param array $data Data to validate
 * @param array $rules Validation rules: ['field' => ['validator' => 'function', 'params' => [...]]]
 * @return array Combined errors from all validators
 */
function validateMultiple($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? null;
        $validator = $rule['validator'] ?? null;
        $params = $rule['params'] ?? [];
        
        if (!$validator || !function_exists($validator)) {
            continue;
        }
        
        // Prepend field name to params
        array_unshift($params, $value, $field);
        
        $fieldErrors = call_user_func_array($validator, $params);
        $errors = array_merge($errors, $fieldErrors);
    }
    
    return $errors;
}

/**
 * Validate and sanitize input data
 * 
 * Validates and sanitizes all fields in data array.
 * 
 * @param array $data Input data
 * @param array $rules Validation rules
 * @return array ['valid' => bool, 'data' => array, 'errors' => array]
 */
function validateAndSanitize($data, $rules) {
    $errors = validateMultiple($data, $rules);
    $sanitized = [];
    
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $sanitized[$key] = sanitizeString($value);
        } else {
            $sanitized[$key] = $value;
        }
    }
    
    return [
        'valid' => empty($errors),
        'data' => $sanitized,
        'errors' => $errors
    ];
}

