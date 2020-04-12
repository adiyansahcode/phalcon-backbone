<?php

declare(strict_types=1);

namespace Pbackbone\Validation;

use Phalcon\Messages\Message;
use Pbackbone\Model\RobotModel as TableModel;
use Pbackbone\Model\TypeModel;
use Pbackbone\Model\PartModel;

class RobotUpdateDataByPatchValidation extends \Pbackbone\Validation\BaseValidation
{
    public function initialize()
    {
        $this->add(
            [
                "id",
            ],
            new \Phalcon\Validation\Validator\PresenceOf([
                "message" => ":field is required"
            ])
        );

        $this->add(
            [
                "id",
            ],
            new \Phalcon\Validation\Validator\Numericality([
                "message" => ":field must be numeric",
            ])
        );

        $this->add(
            [
                "isActive",
            ],
            new \Phalcon\Validation\Validator\InclusionIn([
                "domain" => [
                    "isActive" => ["yes", "no"],
                ],
                "message" => ":field must be :domain",
                "allowEmpty" => true,
            ])
        );

        $this->add(
            [
                "name",
                "description",
            ],
            new \Phalcon\Validation\Validator\StringLength([
                "max" => [
                    "name" => 50,
                    "description" => 500,
                ],
                "min" => [
                    "name" => 0,
                    "description" => 0,
                ],
                "messageMaximum" => "characters :field too long, must no more or equal than :max characters.",
                "messageMinimum" => "characters :field too short, must more or equal than :min characters.",
                "allowEmpty" => true,
            ])
        );

        $this->add(
            "id",
            new \Phalcon\Validation\Validator\Callback([
                "message" => "id not found",
                "callback" => function ($data) {
                    $result = true;
                    $id = $data->id;
                    $validateData = TableModel::findFirst($id);
                    if (!$validateData) {
                        $result = false;
                    }

                    return $result;
                },
            ])
        );

        $this->add(
            "name",
            new \Phalcon\Validation\Validator\Callback([
                "message" => ":field already exist",
                "callback" => function ($data) {
                    $result = true;
                    if (property_exists($data, 'name')) {
                        $id = $data->id;
                        $name = $data->name;
                        $validateData = TableModel::findFirst([
                            "conditions" => "id != :id: AND name = :name:",
                            "bind" => [
                                "id" => $id,
                                "name" => $name,
                            ],
                        ]);
                        if ($validateData) {
                            $result = false;
                        }
                    }
                    return $result;
                },
            ])
        );

        $this->add(
            "field",
            new \Phalcon\Validation\Validator\Callback([
                "message" => "unknown :field",
                "callback" => function ($data) {
                    $parameters  = (array) $data;
                    $allowedKeys = ["name", "description", "year", "isActive", "type", "part"];
                    $filteredParameters = array_filter(
                        $parameters,
                        function ($key) use ($allowedKeys) {
                            return in_array($key, $allowedKeys);
                        },
                        ARRAY_FILTER_USE_KEY
                    );
                    if (count($filteredParameters) != (count($parameters)) - 1) {
                        return false;
                    }
                    return true;
                }
            ])
        );

        // * check type | relationships one to many
        $this->add(
            "type",
            new \Phalcon\Validation\Validator\Callback([
                "message" => ":field is invalid",
                "callback" => function ($data) {
                    if (property_exists($data, 'type')) {
                        if (!property_exists($data->type, 'id')) {
                            $this->appendMessage(
                                new Message("type is required", 'type', 'PresenceOf')
                            );
                            return false;
                        }

                        $type = TypeModel::findFirst($data->type->id);
                        if (!$type) {
                            $this->appendMessage(
                                new Message("type is not exist", 'type', 'notexist')
                            );
                            return false;
                        }
                    }

                    return true;
                }
            ])
        );

        // * check part | relationships many to many
        $this->add(
            "part",
            new \Phalcon\Validation\Validator\Callback([
                "message" => ":field is invalid",
                "callback" => function ($data) {
                    if (property_exists($data, 'part')) {
                        if (!is_array($data->part) || count($data->part) <= 0) {
                            $this->appendMessage(
                                new Message("part is required", 'part', 'PresenceOf')
                            );
                            return false;
                        }

                        foreach ($data->part as $partData) {
                            $part = PartModel::findFirst($partData->id);
                            if (!$part) {
                                $this->appendMessage(
                                    new Message("part is not exist", 'part', 'notexist')
                                );
                                return false;
                            }
                        }
                    }

                    return true;
                }
            ])
        );

        // * just for string parameter
        $this->setFilters('name', ['trim']);
        $this->setFilters('description', ['trim']);
        $this->setFilters('isActive', ['trim']);
    }
}
