<?php

declare(strict_types=1);

namespace Framework;

use Framework\Contracts\RuleInterface;
use Framework\Exceptions\ValidationException;

class Validator
{
    private array $rules = [];

    public function add(string $alias, RuleInterface $rule)
    {
        $this->rules[$alias] = $rule;
    }

    public function validate(array $formData, array $fields)
    {
        $errors = [];
        foreach ($fields as $fieldName => $rules) {
            foreach ($rules as $rule) {
                $ruleParam = [];
                if (str_contains($rule, ":")) {
                    [$rule, $ruleParam] = explode(":", $rule);
                    $ruleParam = explode(",", $ruleParam);
                }
                $ruleValidator = $this->rules[$rule];
                if ($ruleValidator->process($formData, $fieldName, $ruleParam)) {
                    continue;
                }

                $errors[$fieldName][] = $ruleValidator->getMessage($formData, $fieldName, $ruleParam);
            }
        }
        if (count($errors)) {
            throw new ValidationException($errors);
        }
    }
}
