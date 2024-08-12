<?php
namespace Frenet\Services;

use Frenet\Exceptions\ValidationException;

abstract class BaseService {
    /**
     * Converte todos os valores do array para UTF-8.
     *
     * @param array $data
     * @return array
     */
    protected function convertToUtf8(array $data) {
        array_walk_recursive($data, function (&$item) {
            if (is_string($item)) {
                $item = mb_convert_encoding($item, 'UTF-8', 'auto');
            }
        });
        return $data;
    }

    protected function validateParams(array $params, array $requiredParams) {
        foreach ($requiredParams as $param => $type) {
            if (!isset($params[$param])) {
                throw new ValidationException("The {$param} parameter is required.");
            }

            $this->validateType($param, $params[$param], $type);
        }
    }

    protected function validateType($param, $value, $type) {
        switch ($type) {
            case 'boolean':
                if (!is_bool($value)) {
                    throw new ValidationException("The {$param} parameter must be a boolean.");
                }
                break;
            case 'string':
                if (!is_string($value)) {
                    throw new ValidationException("The {$param} parameter must be a string.");
                }
                break;
            case 'numeric':
                if (!is_numeric($value)) {
                    throw new ValidationException("The {$param} parameter must be numeric.");
                }
                break;
            case 'array':
                if (!is_array($value)) {
                    throw new ValidationException("The {$param} parameter must be an array.");
                }
                break;
            default:
                throw new ValidationException("Invalid type for {$param} parameter.");
        }
    }

    protected function validateArrayParams(array $items, array $requiredParams) {
        foreach ($items as $item) {
            $this->validateParams($item, $requiredParams);
        }
    }
}