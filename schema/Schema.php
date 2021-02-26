<?php

namespace app\schema;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use app\base\Engine;

/**
 * Class Schema
 * @package app\schema
 */
abstract class Schema
{
    const RULE_REQUIRED = 'required';
    const RULE_MIN = 'min';
    const RULE_MAX = 'max';
    const RULE_REGEXP= 'regexp';
    const RULE_UNIQUE = 'unique';
    const RULE_TIME_ZONE = 'timezone';
    const RULE_COUNTRY_CODE = 'countryCode';
    const RULE_TIME_ZONE_REACH_ERROR = 'timezoneReachError';
    const RULE_COUNTRY_CODE_REACH_ERROR = 'countryCodeReachError';

    protected int $id;
    abstract public function tableName(): string;
    abstract public function fill(): array;
    abstract public function rulesStore(): array;
    abstract public function rulesUpdate(): array;

    public array $errors = [];

    /**
     *
     * @param array $data
     */
    public function loadData(array $data) :void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Validate for store
     *
     * @return bool
     */
    public function validateStore(): bool
    {
        foreach ($this->rulesStore() as $attribute => $rules) {
            $value = $this->{$attribute} ?? null;

            foreach ($rules as $rule) {
                $ruleName = $rule;

                if (!is_string($rule)) {
                    $ruleName = $rule[0];
                }

                if ($ruleName === self::RULE_REQUIRED && !$value) {
                    $this->addErrorByRule($attribute, self::RULE_REQUIRED);
                }

                if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
                    $this->addErrorByRule($attribute, self::RULE_MIN, ['min' => $rule['min']]);
                }

                if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
                    $this->addErrorByRule($attribute, self::RULE_MAX, ['max' => $rule['max']]);
                }

                if ($ruleName === self::RULE_REGEXP) {
                    preg_match($rule['pattern'], $value, $matches, PREG_OFFSET_CAPTURE);
                    if (!$matches) {
                        $this->addErrorByRule($attribute, self::RULE_REGEXP, ['pattern' => $rule['pattern']]);
                    }
                }

                if ($ruleName === self::RULE_UNIQUE) {
                    $tableName = $rule['table'];
                    $uniqueAttr = $rule['attribute'] ?? $attribute;

                    $query = 'SELECT * FROM '.$tableName. ' WHERE '.$uniqueAttr.' = :'.$uniqueAttr;
                    $statement = Engine::$engine->database->prepare($query);
                    $statement->bindValue(':'.$uniqueAttr, $value);
                    $statement->execute();
                    $record = $statement->fetchObject();
                    if ($record) {
                        $this->addErrorByRule($attribute, self::RULE_UNIQUE, ['value' => $value]);
                    }
                }

                if ($ruleName === self::RULE_TIME_ZONE) {
                    $httpClient = new Client([
                        'timeout'  => 5.0,
                    ]);

                    try {
                        $response = $httpClient->request('GET', $rule['api']);
                        $timezones = json_decode($response->getBody(), true);

                        if (!isset($timezones['result'][$value])) {
                            $this->addErrorByRule($attribute, self::RULE_TIME_ZONE, ['api' => $rule['api']]);
                        }

                    } catch (ClientException | ConnectException $e) {
                        $this->addErrorByRule($attribute, self::RULE_TIME_ZONE_REACH_ERROR, [ 'error' => $e->getMessage()]);
                    }
                }

                if ($ruleName === self::RULE_COUNTRY_CODE) {
                    $httpClient = new Client([
                        'timeout'  => 5.0,
                    ]);

                    try {
                        $response = $httpClient->request('GET', $rule['api']);
                        $countries = json_decode($response->getBody(), true);

                        if (!isset($countries['result'][$value])) {
                            $this->addErrorByRule($attribute, self::RULE_COUNTRY_CODE, ['api' => $rule['api']]);
                        }

                    } catch (ClientException | ConnectException $e) {
                        $this->addErrorByRule($attribute, self::RULE_COUNTRY_CODE_REACH_ERROR, [ 'error' => $e->getMessage()]);
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     *
     * @return bool
     */
    public function validateUpdate(): bool
    {
        foreach ($this->rulesUpdate() as $attribute => $rules) {
            $value = $this->{$attribute} ?? null;

            if (!$value) {
                continue;
            }

            foreach ($rules as $rule) {
                $ruleName = $rule;

                if (!is_string($rule)) {
                    $ruleName = $rule[0];
                }

                if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
                    $this->addErrorByRule($attribute, self::RULE_MIN, ['min' => $rule['min']]);
                }

                if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
                    $this->addErrorByRule($attribute, self::RULE_MAX, ['max' => $rule['max']]);
                }

                if ($ruleName === self::RULE_REGEXP) {
                    preg_match($rule['pattern'], $value, $matches, PREG_OFFSET_CAPTURE);
                    if (!$matches) {
                        $this->addErrorByRule($attribute, self::RULE_REGEXP, ['pattern' => $rule['pattern']]);
                    }
                }

                if ($ruleName === self::RULE_UNIQUE) {
                    $tableName = $rule['table'];
                    $uniqueAttr = $rule['attribute'] ?? $attribute;
                    $statement = Engine::$engine->database->prepare('SELECT * FROM '.$tableName. '
                    WHERE '.$uniqueAttr.' = :'.$uniqueAttr. ' AND id != :id');
                    $statement->bindValue(':id', $this->id);
                    $statement->bindValue(':'.$uniqueAttr, $value);
                    $statement->execute();
                    $record = $statement->fetchObject();
                    if ($record) {
                        $this->addErrorByRule($attribute, self::RULE_UNIQUE, ['value' => $value]);
                    }
                }

                if ($ruleName === self::RULE_TIME_ZONE) {
                    $httpClient = new Client([
                        'timeout'  => 5.0,
                    ]);

                    try {
                        $response = $httpClient->request('GET', $rule['api']);
                        $timezones = json_decode($response->getBody(), true);

                        if (!isset($timezones['result'][$value])) {
                            $this->addErrorByRule($attribute, self::RULE_TIME_ZONE, ['api' => $rule['api']]);
                        }

                    } catch (ClientException | ConnectException $e) {
                        $this->addErrorByRule($attribute, self::RULE_TIME_ZONE_REACH_ERROR, [ 'error' => $e->getMessage()]);
                    }
                }

                if ($ruleName === self::RULE_COUNTRY_CODE) {
                    $httpClient = new Client([
                        'timeout'  => 5.0,
                    ]);

                    try {
                        $response = $httpClient->request('GET', $rule['api']);
                        $countries = json_decode($response->getBody(), true);

                        if (!isset($countries['result'][$value])) {
                            $this->addErrorByRule($attribute, self::RULE_COUNTRY_CODE, ['api' => $rule['api']]);
                        }

                    } catch (ClientException | ConnectException $e) {
                        $this->addErrorByRule($attribute, self::RULE_COUNTRY_CODE_REACH_ERROR, [ 'error' => $e->getMessage()]);
                    }
                }
            }
        }
        return empty($this->errors);
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $tableName = $this->tableName();
        $fields = $this->fill();
        $params = array_map(fn($attr) => ':'.$attr, $fields);
        $query = 'INSERT INTO '.$tableName.'('.implode(',', $fields).') VALUES('.implode(',', $params).')';

        $statement = Engine::$engine->database->pdo->prepare($query);

        foreach ($fields as $field) {
            $statement->bindValue(':'.$field, $this->{$field});
        }

        $statement->execute();

        if ($statement->errorCode() !== '00000') {
            return false;
        }

        return true;
    }

    public function findAll(): array
    {
        $tableName = $this->tableName();
        $query = 'SELECT * FROM '.$tableName.';';

        $statement = Engine::$engine->database->pdo->prepare($query);
        $statement->execute();
        $record = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if ($record) {
            return $record;
        }
        return [];
    }

    /**
     * @param int $id
     * @return array
     */
    public function find(int $id): array
    {
        $tableName = $this->tableName();
        $query = 'SELECT * FROM '.$tableName.' WHERE id = :id';

        $statement = Engine::$engine->database->pdo->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->execute();
        $record = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($record) {
            $this->id = $record['id'];
            return $record;
        }
        return [];
    }

    /**
     * @param string $keyword
     * @return array
     */
    public function search(string $keyword): array
    {
        $tableName = $this->tableName();
        $query = 'SELECT * FROM '.$tableName.' 
        WHERE firstName LIKE :keyword 
        OR lastName LIKE :keyword 
        OR phoneNumber LIKE :keyword 
        OR timezone LIKE :keyword
        OR countryCode LIKE :keyword;';

        $statement = Engine::$engine->database->pdo->prepare($query);
        $statement->bindValue(':keyword', '%'.$keyword.'%');
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $id
     * @return bool
     */
    public function update($id): bool
    {
        $tableName = $this->tableName();
        $fields = $this->fill();

        foreach ($fields as $index => $field) {
            if (!isset($this->{$field})) {
                unset($fields[$index]);
            }
        }

        $params = array_map(fn($attr) => $attr.' = :'.$attr, $fields);
        $query = 'UPDATE '.$tableName.' SET updatedOn = :updatedOn, ' .implode(', ', $params). ' WHERE id = :id';

        $statement = Engine::$engine->database->pdo->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->bindValue(':updatedOn', date('Y-m-d H:i:s', time()));

        foreach ($fields as $field) {
            $statement->bindValue(':'.$field, $this->{$field});
        }

        $statement->execute();

        if ($statement->errorCode() !== '00000') {
            return false;
        }

        return true;
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id) :bool
    {
        $tableName = $this->tableName();
        $query = 'DELETE FROM '.$tableName.' WHERE id = :id';
        $statement = Engine::$engine->database->pdo->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->execute();

        if ($statement->errorCode() !== '00000') {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function errorMessages(): array
    {
        return [
            self::RULE_REQUIRED => '{field} is required',
            self::RULE_MIN => 'Min length of {field} must be {min}',
            self::RULE_MAX => 'Max length of {field} must be {max}',
            self::RULE_REGEXP=> '{field} must be the same as {pattern}',
            self::RULE_UNIQUE => '{field} {value} already exists',
            self::RULE_TIME_ZONE => 'The value of {field} does not exist in {api}',
            self::RULE_COUNTRY_CODE => 'The value of {field} does not exist in {api}',
            self::RULE_TIME_ZONE_REACH_ERROR => 'timezone {error}',
            self::RULE_COUNTRY_CODE_REACH_ERROR => 'countryCode {error}',
        ];
    }

    /**
     * @param $rule
     * @return string
     */
    public function errorMessage($rule): string
    {
        return $this->errorMessages()[$rule];
    }

    /**
     * @param string $attribute
     * @param string $rule
     * @param array $params
     */
    protected function addErrorByRule(string $attribute, string $rule, $params = []) :void
    {
        $params['field'] ??= $attribute;
        $errorMessage = $this->errorMessage($rule);

        foreach ($params as $key => $value) {
            $errorMessage = str_replace("{{$key}}", $value, $errorMessage);
            $errorMessage = str_replace("{field}", $attribute, $errorMessage);
        }

        $this->errors[] = $errorMessage;
    }

    /**
     * @return string
     */
    public function getFirstError(): string
    {
        return $this->errors[0] ?? '';
    }
}
