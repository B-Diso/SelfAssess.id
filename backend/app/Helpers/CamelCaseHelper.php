<?php

namespace App\Helpers;

class CamelCaseHelper
{
    /**
     * Convert array keys from snake_case, PascalCase, and kebab-case to camelCase.
     *
     * @param array|object $data
     * @return array|object
     */
    public static function convertToCamelCase($data)
    {
        if (is_object($data)) {
            // Convert object to array, process, then convert back to object
            $array = json_decode(json_encode($data), true);
            $converted = self::convertArrayKeys($array);
            return json_decode(json_encode($converted));
        }

        if (is_array($data)) {
            return self::convertArrayKeys($data);
        }

        return $data;
    }

    /**
     * Convert array keys recursively.
     *
     * @param array $array
     * @return array
     */
    private static function convertArrayKeys(array $array): array
    {
        $converted = [];

        foreach ($array as $key => $value) {
            // Convert the key to camelCase
            $camelKey = self::toCamelCase($key);

            // Recursively convert nested arrays/objects
            if (is_array($value)) {
                $converted[$camelKey] = self::convertArrayKeys($value);
            } elseif (is_object($value)) {
                // Convert object to array, process, then convert back to object
                $objectArray = json_decode(json_encode($value), true);
                $converted[$camelKey] = self::convertArrayKeys($objectArray);
            } else {
                $converted[$camelKey] = $value;
            }
        }

        return $converted;
    }

    /**
     * Convert a string from snake_case, PascalCase, or kebab-case to camelCase.
     *
     * @param string $string
     * @return string
     */
    public static function toCamelCase(string $string): string
    {
        // Handle kebab-case (replace dashes with underscores first)
        $string = str_replace('-', '_', $string);
        
        // Handle snake_case
        if (strpos($string, '_') !== false) {
            return lcfirst(str_replace('_', '', ucwords($string, '_')));
        }
        
        // Handle PascalCase (already capitalized)
        if (ctype_upper($string[0])) {
            return lcfirst($string);
        }
        
        // Return as-is if it's already camelCase or doesn't match patterns
        return $string;
    }

    /**
     * Convert pagination metadata to camelCase.
     *
     * @param array $paginationData
     * @return array
     */
    public static function convertPaginationMetadata(array $paginationData): array
    {
        $camelCaseMapping = [
            'current_page' => 'currentPage',
            'per_page' => 'perPage',
            'last_page' => 'lastPage',
            'first_page_url' => 'firstPageUrl',
            'last_page_url' => 'lastPageUrl',
            'next_page_url' => 'nextPageUrl',
            'prev_page_url' => 'prevPageUrl',
            'has_more_pages' => 'hasMorePages',
        ];

        $converted = [];

        foreach ($paginationData as $key => $value) {
            $newKey = $camelCaseMapping[$key] ?? self::toCamelCase($key);
            
            if (is_array($value)) {
                $converted[$newKey] = self::convertArrayKeys($value);
            } else {
                $converted[$newKey] = $value;
            }
        }

        return $converted;
    }
}