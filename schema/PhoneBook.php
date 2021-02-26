<?php

namespace app\schema;

class PhoneBook extends Schema
{
    public string $firstName;
    public string $lastName;
    public string $phoneNumber;
    public string $countryCode;
    public string $timezone;
    public string $insertedOn;
    public string $updatedOn;

    /**
     * @return string
     */
    public function tableName(): string
    {
        return 'phone_books';
    }

    public function fill(): array
    {
        return [
            'firstName', 'lastName', 'phoneNumber', 'countryCode', 'timezone',
        ];
    }
    /*
     *
     */
    public function register(): bool
    {
        return $this->save();
    }

    public function rulesStore(): array
    {
        return [
            'firstName' => [
                self::RULE_REQUIRED,
                [self::RULE_MIN, 'min' => 3],
                [self::RULE_MAX, 'max' => 100],
            ],
            'lastName' => [
                self::RULE_REQUIRED,
                [self::RULE_MIN, 'min' => 3],
                [self::RULE_MAX, 'max' => 100],
            ],
            'phoneNumber' => [
                self::RULE_REQUIRED,
                [self::RULE_REGEXP, 'pattern' => '/^\+\d{2}\s\d{3}\s\d{9}$/'],
                [self::RULE_UNIQUE, 'table' => 'phone_books'],
            ],
            'countryCode' => [
                self::RULE_REQUIRED,
                [self::RULE_COUNTRY_CODE, 'api' => 'https://api.hostaway.com/countries',]
            ],
            'timezone' => [
                self::RULE_REQUIRED,
                [self::RULE_TIME_ZONE, 'api' => 'https://api.hostaway.com/timezones',]
            ],
        ];
    }

    public function rulesUpdate(): array
    {
        return [
            'firstName' => [
                [self::RULE_MIN, 'min' => 3],
                [self::RULE_MAX, 'max' => 100],
            ],
            'lastName' => [
                [self::RULE_MIN, 'min' => 3],
                [self::RULE_MAX, 'max' => 100],
            ],
            'phoneNumber' => [
                [self::RULE_REGEXP, 'pattern' => '/^\+\d{2}\s\d{3}\s\d{9}$/'],
                [self::RULE_UNIQUE, 'table' => 'phone_books', 'id' => $this->id],
            ],
            'countryCode' => [
                [self::RULE_COUNTRY_CODE, 'api' => 'https://api.hostaway.com/countries',]
            ],
            'timezone' => [
                [self::RULE_TIME_ZONE, 'api' => 'https://api.hostaway.com/timezones',]
            ],
        ];
    }
}
