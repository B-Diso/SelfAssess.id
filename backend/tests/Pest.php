<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeCamelCaseJson', function () {
    $value = $this->value;

    // Convert objects to arrays to handle JSON-like structures
    if (is_object($value)) {
        $value = json_decode(json_encode($value), true);
    }

    if (!is_array($value)) {
        // If it's not an array (e.g. null or scalar), we can't check keys.
        // We assume it's valid or irrelevant for this check.
        return $this;
    }

    $check = function (array $data, string $path = '') use (&$check) {
        foreach ($data as $key => $val) {
            // Construct path for error reporting
            // Ensure key is treated as string for concatenation
            $keyStr = (string) $key;
            $currentPath = $path !== '' ? "{$path}.{$keyStr}" : $keyStr;

            if (!is_int($key)) {
                // Check if key is camelCase
                // Allow simple camelCase: starts with lowercase, alphanumeric
                if (!preg_match('/^[a-z][a-zA-Z0-9]*$/', $keyStr)) {
                    return $currentPath;
                }
            }

            if (is_array($val)) {
                $failedKey = $check($val, $currentPath);
                if ($failedKey) {
                    return $failedKey;
                }
            }
        }

        return null;
    };

    $failedKey = $check($value);

    if ($failedKey) {
        throw new \Exception("Key '{$failedKey}' is not camelCase.");
    }

    return $this;
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
