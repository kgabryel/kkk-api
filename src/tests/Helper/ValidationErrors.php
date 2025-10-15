<?php

namespace App\Tests\Helper;

class ValidationErrors
{
    public const string ACCESS_DTO_BEFORE_VALIDATION = 'Cannot access DTO before successful validation.';
    public const string EMAIL_ALREADY_IN_USE = 'Email is already in use.';
    public const string EXACTLY_ONE_OF_INGREDIENT_OR_RECIPE = 'Exactly one of "ingredient" or "recipe" must be set.';
    public const string FIELD_MISSING = 'This field is missing.';
    public const string INVALID_BASE64_STRING_CONTENT = 'Invalid Base64 string - content.';
    public const string INVALID_BASE64_STRING_PREFIX = 'Invalid Base64 string - prefix.';
    public const string INVALID_CHOICE = 'The value you selected is not a valid choice.';
    public const string INVALID_EMAIL = 'This value is not a valid email address.';
    public const string INVALID_OLD_PASSWORD = 'Invalid password.';
    public const string NOT_BLANK = 'This value should not be blank.';
    public const string NOT_NULL = 'This value should not be null.';
    public const string PASSWORD_CONFIRMATION_MISMATCH = 'The password confirmation does not match.';
    public const string PASSWORD_MUST_BE_DIFFER = 'New password must be different from old password.';
    public const string STOP_MUST_BE_GREATER_THAN_START = 'Stop must be greater than start.';
    public const string TYPE_ARRAY = 'This value should be of type array|\Countable.';
    public const string TYPE_BOOL = 'This value should be of type bool.';
    public const string TYPE_INT = 'This value should be of type int.';
    public const string TYPE_ITERABLE = 'This value should be of type iterable.';
    public const string TYPE_NUMBER = 'This value should be of type float|int.';
    public const string TYPE_STRING = 'This value should be of type string.';
    public const string UNEXPECTED_FIELD = 'This field was not expected.';
    public const string VALUE_SHOULD_BE_POSITIVE = 'This value should be positive.';

    public static function invalidCount(int $count): string
    {
        return sprintf('This collection should contain exactly %s elements.', $count);
    }

    public static function nonUniqueValue(string $column): string
    {
        return sprintf('All values in (%s) must be unique.', $column);
    }

    public static function shouldBeBetween(int $min, int $max): string
    {
        return sprintf('This value should be between %d and %d.', $min, $max);
    }

    public static function shouldBeGreaterThan(float $value): string
    {
        return sprintf('This value should be greater than %s.', $value);
    }

    public static function tooLong(int $length): string
    {
        return sprintf('This value is too long. It should have %s characters or less.', $length);
    }
}
