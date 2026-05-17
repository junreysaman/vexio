<?php

declare(strict_types=1);

namespace Framework\Rules;

use Framework\Contracts\RuleInterface;
use InvalidArgumentException;

class MinRule implements RuleInterface
{
    public function validate(array $data, string $field, array $params): bool
    {
        if (empty($params[0])){
            throw new InvalidArgumentException('Minimum lenght not specified!');
        }
        $lenght = (int) $params[0];
        return strlen($data[$field]) >= $lenght;
    }

    public function message(array $data, string $field, array $params): string
    {
       return 'The ' . $field . ' must be at least ' . $params[0] . ' characters long.';
    }
}