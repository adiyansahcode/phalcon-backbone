<?php

declare(strict_types=1);

namespace Pbackbone\Validation;

class BaseValidation extends \Phalcon\Validation
{
    /**
     * Executed before validation
     *
     * @param array $data
     * @param object $entity
     * @param Phalcon\Validation\Message\Group $messages
     * @return bool
     */
    public function beforeValidation($data, $entity, $messages)
    {
        // ... Add additional messages or perform more validations
    }

    /**
     * Executed after validation
     *
     * @param array $data
     * @param object $entity
     * @param Phalcon\Validation\Message\Group $messages
     */
    public function afterValidation($data, $entity, $messages)
    {
        if (count($messages)) {
            $errors = [];
            foreach ($messages as $message) {
                $code     = $message->getCode();
                $field    = $message->getField();
                $msg      = $message->getMessage();
                $type     = str_replace("Phalcon\\Validation\\Validator\\", "", $message->getType());
                $metaData = $message->getMetaData();

                $errors[] = [
                    "code" => 400,
                    "source" => $field,
                    "title" => $type,
                    "detail" => $msg,
                ];
            }
            $responseStatus = "fail";
            $responseErrors = $errors;

            // * send response
            $response = new \Phalcon\Http\Response();
            $response->setStatusCode(400);
            $response->setContentType('application/json', 'UTF-8');
            $response->setContent(json_encode(
                [
                    "status" => $responseStatus,
                    "errors " => $responseErrors,
                ]
            ));
            $response->send();
            exit;
        }
    }
}
