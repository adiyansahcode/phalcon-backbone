<?php

declare(strict_types=1);

namespace Pbackbone\Validation;

use Pbackbone\Model\PartModel as TableModel;

class PartDeleteDataValidation extends \Pbackbone\Validation\BaseValidation
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

        // * just for string parameter
        // $this->setFilters('id', ['trim']);
    }
}
