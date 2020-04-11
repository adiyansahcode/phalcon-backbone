<?php

declare(strict_types=1);

namespace Pbackbone\Validation;

use Pbackbone\Model\TypeModel as TableModel;

class TypeReadDataValidation extends \Pbackbone\Validation\BaseValidation
{
    public function initialize()
    {
        $this->add(
            [
                "sort",
            ],
            new \Phalcon\Validation\Validator\InclusionIn([
                "domain" => [
                    "sort" => [
                        "id",
                        "-id",
                        "createdAt",
                        "-createdAt",
                        "updatedAt",
                        "-updatedAt",
                        "name",
                        "-name",
                        "description",
                        "-description",
                    ],
                ],
                "message" => ":field must be :domain",
                "allowEmpty" => true,
            ])
        );

        $this->add(
            "page",
            new \Phalcon\Validation\Validator\Callback([
                "message" => ":field is invalid",
                "callback" => function ($data) {
                    if (isset($data->page)) {
                        if (!is_array($data->page)) {
                            return false;
                        }

                        if (array_key_exists('number', $data->page) && !array_key_exists('size', $data->page)) {
                            return false;
                        }

                        if (array_key_exists('before', $data->page) && !array_key_exists('size', $data->page)) {
                            return false;
                        }

                        if (array_key_exists('after', $data->page) && !array_key_exists('size', $data->page)) {
                            return false;
                        }

                        if (array_key_exists('number', $data->page)) {
                            if (!is_numeric($data->page['number']) || $data->page['number'] <= 0) {
                                return false;
                            }
                        }

                        if (array_key_exists('size', $data->page)) {
                            if (!is_numeric($data->page['size']) || $data->page['size'] <= 0) {
                                return false;
                            }
                        }

                        if (array_key_exists('before', $data->page)) {
                            if (!is_numeric($data->page['before']) || $data->page['before'] <= 0) {
                                return false;
                            }
                        }

                        if (array_key_exists('after', $data->page)) {
                            if (!is_numeric($data->page['after']) || $data->page['after'] <= 0) {
                                return false;
                            }
                        }
                    }

                    return true;
                }
            ])
        );

        $this->add(
            "field",
            new \Phalcon\Validation\Validator\Callback([
                "message" => ":field is invalid",
                "callback" => function ($data) {
                    if (isset($data->field)) {
                        if (empty($data->field)) {
                            return false;
                        }

                        // * get list of table model
                        $tableModel = new TableModel();
                        $metadata = $tableModel->getModelsMetaData();
                        $attributes = $metadata->getColumnMap($tableModel);
                        foreach ($attributes as $attributesData) {
                            $fields[] = $attributesData;
                        }

                        $fieldArray = explode(",", $data->field);
                        foreach ($fieldArray as $fieldData) {
                            if (!in_array($fieldData, $fields)) {
                                return false;
                            }
                        }
                    }

                    return true;
                }
            ])
        );

        // * filters param, just for string parameter
        $this->setFilters('sort', ['trim']);
    }
}
