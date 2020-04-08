<?php

declare(strict_types=1);

namespace Pbackbone\Validation;

use Phalcon\Messages\Message;
use Pbackbone\Model\RobotModel as TableModel;
use Pbackbone\Model\TypeModel;
use Pbackbone\Model\PartModel;

class RobotCreateDataValidation extends \Pbackbone\Validation\BaseValidation
{
    public function initialize()
    {
        $this->add(
            [
                "isActive",
            ],
            new \Phalcon\Validation\Validator\InclusionIn([
                "domain" => [
                    "isActive" => ["yes", "no"],
                ],
                "message" => ":field must be :domain",
            ])
        );

        $this->add(
            [
                "name",
                "description",
                "year",
                "isActive"
            ],
            new \Phalcon\Validation\Validator\PresenceOf(
                ["message" => ":field is required"]
            )
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
            ])
        );

        $this->add(
            [
                "year",
            ],
            new \Phalcon\Validation\Validator\Numericality([
                "message" => ":field must numeric character",
            ])
        );

        $this->add(
            "name",
            new \Phalcon\Validation\Validator\Uniqueness([
                "model"   => new TableModel(),
                "message" => ":field already exist",
            ])
        );

        // * check type | relationships one to many
        $this->add(
            "type",
            new \Phalcon\Validation\Validator\Callback([
                "message" => ":field is invalid",
                "callback" => function ($data) {
                    if (!property_exists($data->relationships, 'type')) {
                        $this->appendMessage(
                            new Message("type is required", 'type', 'PresenceOf')
                        );
                        return false;
                    }

                    if (!property_exists($data->relationships->type, 'id')) {
                        $this->appendMessage(
                            new Message("type is required", 'type', 'PresenceOf')
                        );
                        return false;
                    }

                    $type = TypeModel::findFirst($data->relationships->type->id);
                    if (!$type) {
                        $this->appendMessage(
                            new Message("type is not exist", 'type', 'notexist')
                        );
                        return false;
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
                    if (
                        !property_exists($data->relationships, 'part') ||
                        !is_array($data->relationships->part) ||
                        count($data->relationships->part) <= 0
                    ) {
                        $this->appendMessage(
                            new Message("part is required", 'part', 'PresenceOf')
                        );
                        return false;
                    }

                    foreach ($data->relationships->part as $partData) {
                        $part = PartModel::findFirst($partData->id);
                        if (!$part) {
                            $this->appendMessage(
                                new Message("part is not exist", 'part', 'notexist')
                            );
                            return false;
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
