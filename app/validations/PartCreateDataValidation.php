<?php

declare(strict_types=1);

namespace Pbackbone\Validation;

use Pbackbone\Model\TypeModel as TableModel;

class PartCreateDataValidation extends \Pbackbone\Validation\BaseValidation
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
            "name",
            new \Phalcon\Validation\Validator\Uniqueness([
                "model"   => new TableModel(),
                "message" => ":field already exist",
            ])
        );

        // * just for string parameter
        $this->setFilters('name', ['trim']);
        $this->setFilters('description', ['trim']);
        $this->setFilters('isActive', ['trim']);
    }
}
