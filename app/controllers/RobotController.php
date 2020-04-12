<?php

declare(strict_types=1);

namespace Pbackbone\Controller;

use Pbackbone\Model\RobotModel as TableModel;
use Pbackbone\Model\TypeModel;
use Pbackbone\Model\PartModel;
use Pbackbone\Validation\RobotCreateDataValidation as CreateDataValidation;
use Pbackbone\Validation\RobotReadDataValidation as ReadDataValidation;
use Pbackbone\Validation\RobotReadDataByIdValidation as ReadDataByIdValidation;
use Pbackbone\Validation\RobotUpdateDataByPutValidation as UpdateDataByPutValidation;
use Pbackbone\Validation\RobotUpdateDataByPatchValidation as UpdateDataByPatchValidation;
use Pbackbone\Validation\RobotDeleteDataValidation as DeleteDataValidation;

class RobotController extends \Pbackbone\Controller\BaseController
{
    /**
     * data name variable
     *
     * @var string
     */
    public $dataName = "robot";

    /**
     * link name variable
     *
     * @var string
     */
    public $linkName = "/robot";

    /**
     * options Action function
     *
     * @return void
     */
    public function optionsAction(): void
    {
        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,POST,DELETE,OPTIONS,HEAD'
        );
        $this->response->send();
    }

    /**
     * options By Id Action function
     *
     * @return void
     */
    public function optionsByIdAction(int $id): void
    {
        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,PUT,PATCH,DELETE,OPTIONS,HEAD'
        );
        $this->response->send();
    }

    public function optionsTypeRelatedAction(int $id): void
    {
        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,OPTIONS,HEAD'
        );
        $this->response->send();
    }

    public function optionsTypeRelatedByIdAction(int $id, int $typeId): void
    {
        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,OPTIONS,HEAD'
        );
        $this->response->send();
    }

    public function optionsPartRelatedAction(int $id): void
    {
        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,OPTIONS,HEAD'
        );
        $this->response->send();
    }

    public function optionsPartRelatedByIdAction(int $id, int $partId): void
    {
        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,OPTIONS,HEAD'
        );
        $this->response->send();
    }

    /**
     * readDataAction, get all data from table
     *
     * @return void
     */
    public function readDataAction(): void
    {
        // * get a request
        $requestData = $this->request->getQuery();

        // * do validation
        $validation = new ReadDataValidation();
        $validation->validate((object) $requestData);

        // * get param request
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * default variable
        $param1 = [];
        $param2 = [];

        // * check filter parameters
        if (isset($filter)) {
            $paramFilter = $this->getFilter(TableModel::class);
            $param1 = array_merge($param1, $paramFilter);
            $param2 = array_merge($param2, $paramFilter);
        }

        // * check sorting parameters
        $paramOrderField = "id";
        $paramOrderType = "asc";
        if (isset($sort)) {
            if (strpos($sort, '-') !== false) {
                $paramOrderType = "desc";
                $paramOrderField = str_replace("-", " ", $sort);
                $paramOrder = [
                    "order" => $paramOrderField . " " . $paramOrderType,
                ];
                $param1 = array_merge($param1, $paramOrder);
            } else {
                $paramOrder = [
                    "order" => $sort,
                ];
                $param1 = array_merge($param1, $paramOrder);
            }
        }

        // * create pagination
        if (isset($page) && is_array($page)) {
            $param1 = $this->createPagination(TableModel::class, $param1);
        }

        // * Query
        $dataDbs = TableModel::find($param1);
        $dataTotal = count(TableModel::find($param2));
        $responseData = [];
        $no = 0;
        $idFirst = 0;
        $id = 0;
        if ($dataTotal > 0) {
            foreach ($dataDbs as $dataDb) {
                if ($no === 0) {
                    $idFirst = $dataDb->getId();
                }
                $id = $dataDb->getId();
                $linkSelf = $this->linkName . '/' . $id;
                $responseData[$no]["links"]["self"] = $linkSelf;

                if (isset($field)) {
                    $fieldArray = explode(",", $field);
                    foreach ($fieldArray as $fieldData) {
                        $aku = "get" . ucfirst($fieldData);
                        $responseData[$no][$fieldData] = $dataDb->$aku();
                    }
                } else {
                    $responseData[$no]["id"] = $id;
                    $responseData[$no]["createdAt"] = $dataDb->getCreatedAt();
                    $responseData[$no]["updatedAt"] = $dataDb->getUpdatedAt();
                    $responseData[$no]["name"] = $dataDb->getName();
                    $responseData[$no]["description"] = $dataDb->getDescription();
                    $responseData[$no]["year"] = $dataDb->getYear();
                    $responseData[$no]["isActive"] = $dataDb->getIsActive();

                    // * relationships with type | one to many
                    $responseData[$no]["type"] = null;
                    $responseData[$no]["links"]["type"] = null;
                    if ($dataDb->getType()) {
                        $typeId = $dataDb->getType()->getId();
                        $linkType = $linkSelf . "/type";
                        $responseData[$no]["links"]["type"] = $linkType;
                        $responseData[$no]["type"]["id"] = $typeId;
                        $responseData[$no]["type"]["links"]["self"] = $linkType . '/' . $typeId;
                    }

                    // * relationships with part | many to many
                    $responseData[$no]["part"] = [];
                    $responseData[$no]["links"]["part"] = null;
                    if (count($dataDb->getPart()) > 0) {
                        $linkPart = $linkSelf . "/part";
                        $responseData[$no]["links"]["part"] = $linkPart;
                        $no2 = 0;
                        foreach ($dataDb->getPart() as $partData) {
                            $partId = $partData->getId();
                            $responseData[$no]["part"][$no2]["id"] = $partId;
                            $responseData[$no]["part"][$no2]["links"]["self"] = $linkPart . '/' . $partId;
                            $no2++;
                        }
                    }
                }
                $no++;
            }
        }

        // * response link
        $responseLink["self"] = $this->linkName;

        // * create response
        $response["status"] = "success";
        $response["links"] = $responseLink;
        $response["page"] = $this->getPagination(TableModel::class, $dataTotal, $idFirst, $id);
        $response["data"][$this->dataName] = $responseData;

        if (isset($hide) and is_array($hide)) {
            if (array_key_exists('links', $hide)) {
                if ((bool) $hide["links"] === true) {
                    unset($response["links"]);
                }
            }

            if (array_key_exists('page', $hide)) {
                if ((bool) $hide["page"] === true) {
                    unset($response["page"]);
                }
            }
        }

        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,PUT,PATCH,DELETE,OPTIONS,HEAD'
        );
        $this->response->setContent(json_encode($response));
        $this->response->send();
    }

    /**
     * readDataByIdAction, get data by id
     *
     * @param integer $id
     * @return void
     */
    public function readDataByIdAction(int $id): void
    {
        // * get a request
        $addRequestData = [
            "id" => $id,
        ];
        $requestData = (object) $addRequestData;

        // * do validation
        $validation = new ReadDataByIdValidation();
        $validation->validate($requestData);

        // * get param request
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * query, check table by id
        $dataDb = TableModel::findFirst([
            "conditions" => "id = :id:",
            "bind" => [
                "id" => $id,
            ],
        ]);
        if (!$dataDb) {
            throw new \Exception("data not found", 400);
        }

        // * response link
        $linkSelf = $this->linkName . '/' . $id;
        $responseLink["self"] = $linkSelf;
        $responseLink["type"] = null;
        $responseLink["part"] = null;

        // * Create Data
        $responseData["id"] = $id;
        $responseData["createdAt"] = $dataDb->getCreatedAt();
        $responseData["updatedAt"] = $dataDb->getUpdatedAt();
        $responseData["name"] = $dataDb->getName();
        $responseData["description"] = $dataDb->getDescription();
        $responseData["year"] = $dataDb->getYear();
        $responseData["isActive"] = $dataDb->getIsActive();

        // * relationships with type | one to many
        $responseData["type"] = null;
        if ($dataDb->getType()) {
            $typeId = $dataDb->getType()->getId();
            $linkType = $linkSelf . "/type";
            $responseLink["type"] = $linkType;
            $responseData["type"]["id"] = $typeId;
            $responseData["type"]["links"]["self"] = $linkType . '/' . $typeId;
        }

        // * relationships with part | many to many
        $responseData["part"] = [];
        if (count($dataDb->getPart()) > 0) {
            $linkPart = $linkSelf . "/part";
            $responseLink["part"] = $linkPart;
            $no2 = 0;
            foreach ($dataDb->getPart() as $partData) {
                $partId = $partData->getId();
                $responseData["part"][$no2]["id"] = $partId;
                $responseData["part"][$no2]["links"]["self"] = $linkPart . '/' . $partId;
                $no2++;
            }
        }

        // * create response
        $response["status"] = "success";
        $response["links"] = $responseLink;
        $response["data"][$this->dataName] = $responseData;

        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,POST,DELETE,OPTIONS,HEAD'
        );
        $this->response->setContent(json_encode($response));
        $this->response->send();
    }

    /**
     * readTypeRelatedAction function
     *
     * @param integer $id
     * @return void
     */
    public function readTypeRelatedAction(int $id): void
    {
        // * get a request
        $addRequestData = [
            "id" => $id,
        ];
        $requestData = (object) $addRequestData;

        // * do validation
        $validation = new ReadDataByIdValidation();
        $validation->validate($requestData);

        // * get param request
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * query, check table by id
        $dataDb = TableModel::findFirst([
            "conditions" => "id = :id:",
            "bind" => [
                "id" => $id,
            ],
        ]);
        if (!$dataDb) {
            throw new \Exception("data not found", 400);
        }

        // * response link
        $linkSelf = $this->linkName . '/' . $id . '/type';
        $responseLink["self"] = $linkSelf;

        // * relationships with type | one to many
        $responseData["type"] = null;
        if ($dataDb->getType()) {
            $typeId = $dataDb->getType()->getId();
            $responseData["type"]["id"] = $typeId;
            $responseData["type"]["name"] = $dataDb->getType()->getName();
            $responseData["type"]["createdAt"] = $dataDb->getType()->getCreatedAt();
            $responseData["type"]["updatedAt"] = $dataDb->getType()->getUpdatedAt();
            $responseData["type"]["description"] = $dataDb->getType()->getDescription();
            $responseData["type"]["isActive"] = $dataDb->getType()->getIsActive();
            $responseData["type"]["links"]["self"] = $linkSelf . '/' . $typeId;
        }

        // * create response
        $response["status"] = "success";
        $response["links"] = $responseLink;
        $response["data"] = $responseData;

        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,OPTIONS,HEAD'
        );
        $this->response->setContent(json_encode($response));
        $this->response->send();
    }

    /**
     * readTypeRelatedByIdAction function
     *
     * @param integer $id
     * @param integer $typeId
     * @return void
     */
    public function readTypeRelatedByIdAction(int $id, int $typeId): void
    {
        // * get a request
        $addRequestData = [
            "id" => $id,
        ];
        $requestData = (object) $addRequestData;

        // * do validation
        $validation = new ReadDataByIdValidation();
        $validation->validate($requestData);

        // * get param request
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * query, check table by id
        $dataDb = TableModel::findFirst([
            "conditions" => "id = :id:",
            "bind" => [
                "id" => $id,
            ],
        ]);
        if (!$dataDb) {
            throw new \Exception("data not found", 400);
        }

        // * response link
        $linkSelf = $this->linkName . '/' . $id . '/type/' . $typeId;
        $responseLink["self"] = $linkSelf;

        // * relationships with type | one to many
        $responseData["type"] = null;
        $typeData = $dataDb->getType([
            "conditions" => "id = :typeId:",
            "bind" => [
                "typeId" => $typeId,
            ],
        ]);
        if ($typeData) {
            $typeId = $typeData->getId();
            $responseData["type"]["id"] = $typeId;
            $responseData["type"]["name"] = $typeData->getName();
            $responseData["type"]["createdAt"] = $typeData->getCreatedAt();
            $responseData["type"]["updatedAt"] = $typeData->getUpdatedAt();
            $responseData["type"]["description"] = $typeData->getDescription();
            $responseData["type"]["isActive"] = $typeData->getIsActive();
        }

        // * create response
        $response["status"] = "success";
        $response["links"] = $responseLink;
        $response["data"] = $responseData;

        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,OPTIONS,HEAD'
        );
        $this->response->setContent(json_encode($response));
        $this->response->send();
    }

    /**
     * read Part Related Action function
     *
     * @param integer $id
     * @return void
     */
    public function readPartRelatedAction(int $id): void
    {
        // * get a request
        $addRequestData = [
            "id" => $id,
        ];
        $requestData = (object) $addRequestData;

        // * do validation
        $validation = new ReadDataByIdValidation();
        $validation->validate($requestData);

        // * get param request
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * query, check table by id
        $dataDb = TableModel::findFirst([
            "conditions" => "id = :id:",
            "bind" => [
                "id" => $id,
            ],
        ]);
        if (!$dataDb) {
            throw new \Exception("data not found", 400);
        }

        // * response link
        $linkSelf = $this->linkName . '/' . $id . '/part';
        $responseLink["self"] = $linkSelf;

        // * relationships with part | many to many
        $responseData["part"] = [];
        if (count($dataDb->getPart()) > 0) {
            $no2 = 0;
            foreach ($dataDb->getPart() as $partData) {
                $partId = $partData->getId();
                $responseData["part"][$no2]["id"] = $partId;
                $responseData["part"][$no2]["name"] = $partData->getName();
                $responseData["part"][$no2]["createdAt"] = $partData->getCreatedAt();
                $responseData["part"][$no2]["updatedAt"] = $partData->getUpdatedAt();
                $responseData["part"][$no2]["description"] = $partData->getDescription();
                $responseData["part"][$no2]["isActive"] = $partData->getIsActive();
                $responseData["part"][$no2]["links"]["self"] = $linkSelf . '/' . $partId;
                $no2++;
            }
        }

        // * create response
        $response["status"] = "success";
        $response["links"] = $responseLink;
        $response["data"] = $responseData;

        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,OPTIONS,HEAD'
        );
        $this->response->setContent(json_encode($response));
        $this->response->send();
    }

    /**
     * read Part Related By Id Action function
     *
     * @param integer $id
     * @param integer $partId
     * @return void
     */
    public function readPartRelatedByIdAction(int $id, int $partId): void
    {
        // * get a request
        $addRequestData = [
            "id" => $id,
        ];
        $requestData = (object) $addRequestData;

        // * do validation
        $validation = new ReadDataByIdValidation();
        $validation->validate($requestData);

        // * get param request
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * query, check table by id
        $dataDb = TableModel::findFirst([
            "conditions" => "id = :id:",
            "bind" => [
                "id" => $id,
            ],
        ]);
        if (!$dataDb) {
            throw new \Exception("data not found", 400);
        }

        // * response link
        $linkSelf = $this->linkName . '/' . $id . '/part/' . $partId;
        $responseLink["self"] = $linkSelf;

        // * relationships with part | many to many
        $responseData["part"] = null;
        $partData = $dataDb->getPart([
            "conditions" => " [\Pbackbone\Model\PartModel].id = :partId:",
            "bind" => [
                "partId" => $partId,
            ],
        ]);
        if (count($partData) > 0) {
            $no2 = 0;
            foreach ($partData as $partData) {
                $partId = $partData->getId();
                $responseData["part"]["id"] = $partId;
                $responseData["part"]["name"] = $partData->getName();
                $responseData["part"]["createdAt"] = $partData->getCreatedAt();
                $responseData["part"]["updatedAt"] = $partData->getUpdatedAt();
                $responseData["part"]["description"] = $partData->getDescription();
                $responseData["part"]["isActive"] = $partData->getIsActive();
            }
        }

        // * create response
        $response["status"] = "success";
        $response["links"] = $responseLink;
        $response["data"] = $responseData;

        // * send response
        $this->response->setStatusCode(200);
        $this->response->setHeader(
            'Allow',
            'GET,OPTIONS,HEAD'
        );
        $this->response->setContent(json_encode($response));
        $this->response->send();
    }

    /**
     * createDataAction, create new data
     *
     * @return void
     */
    public function createDataAction(): void
    {
        // * get a request
        $requestData = $this->request->getJsonRawBody();

        // * do validation
        $validation = new CreateDataValidation();
        $validation->validate($requestData);

        // * get param request
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * begin transaction
        $manager = $this->transactions;
        $transaction = $manager->get()->throwRollbackException(true);

        // * check type | relationships one to many
        $type = TypeModel::findFirst($type->id);

        // * check part | relationships many to many
        foreach ($part as $partData) {
            $part[] = PartModel::findFirst($partData->id);
        }

        // * save query
        $saveData = new TableModel();
        $saveData->setTransaction($transaction);
        $saveData->assign(
            [
                "name" => $name,
                "description" => $description,
                "year" => $year,
                "isActive" => $isActive,
            ]
        );
        $saveData->type = $type; // * save relationships type | one to many
        $saveData->part = $part; // * save relationships part | many to many
        if ($saveData->save() === false) {
            throw new \Exception("failed to save", 400);
        }
        $id = $saveData->id;

        // * commit transaction
        $transaction->commit();

        // * create response
        $responseStatus = "success";
        $responseData[$this->dataName] = [
            "id" => $id,
            "links" => [
                "self" => $this->linkName . "/" . $id
            ],
        ];

        // * send response
        $this->response->setStatusCode(201);
        $this->response->setContent(json_encode(
            [
                "status" => $responseStatus,
                "data " => $responseData,
            ]
        ));
        $this->response->send();
    }

    public function updateDataByPutAction(int $id): void
    {
        // * get a request
        $jsonRequest = $this->request->getJsonRawBody();
        $addRequest = [
            "id" => $id,
        ];
        $requestData = (object) array_merge((array) $jsonRequest, (array) $addRequest);

        // * do validation
        $validation = new UpdateDataByPutValidation();
        $validation->validate($requestData);

        // * get param request
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * begin transaction
        $manager = $this->transactions;
        $transaction = $manager->get()->throwRollbackException(true);

        // * check type | relationships one to many
        $type = TypeModel::findFirst($type->id);

        // * check part | relationships many to many
        foreach ($part as $partData) {
            $part[] = PartModel::findFirst($partData->id);
        }

        // * query, check table by id and update
        $updateData = TableModel::findFirst([
            "conditions" => "id = :id:",
            "bind" => [
                "id" => $id,
            ],
        ]);
        if (!$updateData) {
            throw new \Exception("data not found", 400);
        }
        $updateData->setTransaction($transaction);
        $updateData->getRobotPart()->delete();
        $updateData->assign(
            [
                "name" => $name,
                "description" => $description,
                "year" => $year,
                "isActive" => $isActive,
            ]
        );
        $updateData->type = $type; // * save relationships type | one to many
        $updateData->part = $part; // * save relationships part | many to many
        if ($updateData->save() === false) {
            throw new \Exception("failed to update", 400);
        }
        $id = $updateData->getId();

        // * commit transaction
        $transaction->commit();

        // * create response
        $responseStatus = "success";
        $responseData[$this->dataName] = [
            "id" => $id,
            "links" => [
                "self" => $this->linkName . "/" . $id
            ],
        ];

        // * send response
        $this->response->setStatusCode(200);
        $this->response->setContent(json_encode(
            [
                "status" => $responseStatus,
                "data " => $responseData,
            ]
        ));
        $this->response->send();
    }

    public function updateDataByPatchAction(int $id): void
    {
        // * get a request
        $jsonRequest = $this->request->getJsonRawBody();
        $addRequest = [
            "id" => $id,
        ];
        $requestData = (object) array_merge((array) $jsonRequest, (array) $addRequest);

        // * do validation
        $validation = new UpdateDataByPatchValidation();
        $validation->validate($requestData);

        // * get param request
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * begin transaction
        $manager = $this->transactions;
        $transaction = $manager->get()->throwRollbackException(true);

        // * query, check table by id and update
        $updateData = TableModel::findFirst([
            "conditions" => "id = :id:",
            "bind" => [
                "id" => $id,
            ],
        ]);
        if (!$updateData) {
            throw new \Exception("data not found", 400);
        }
        $updateData->setTransaction($transaction);
        $updateData->assign(
            (array) $jsonRequest,
            [
                "name", "description", "year", "isActive"
            ]
        );
        if (isset($type)) {
            $typeData = TypeModel::findFirst($type->id);
            $updateData->type = $typeData; // * save relationships type | one to many
        }
        if (isset($part)) {
            $updateData->getRobotPart()->delete();
            foreach ($part as $partData) {
                $partArray[] = PartModel::findFirst($partData->id);
            }
            $updateData->part = $partArray; // * save relationships part | many to many
        }
        if ($updateData->save() === false) {
            throw new \Exception("failed to update", 400);
        }
        $id = $updateData->getId();

        // * commit transaction
        $transaction->commit();

        // * create response
        $responseStatus = "success";
        $responseData[$this->dataName] = [
            "id" => $id,
            "links" => [
                "self" => $this->linkName . "/" . $id
            ],
        ];

        // * send response
        $this->response->setStatusCode(200);
        $this->response->setContent(json_encode(
            [
                "status" => $responseStatus,
                "data " => $responseData,
            ]
        ));
        $this->response->send();
    }

    public function deleteDataByIdAction(int $id): void
    {
        // * get a request
        $addRequestData = [
            "id" => $id,
        ];
        $requestData = (object) $addRequestData;

        // * do validation
        $validation = new DeleteDataValidation();
        $validation->validate($requestData);

        // * get param request
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * begin transaction
        $manager = $this->transactions;
        $transaction = $manager->get()->throwRollbackException(true);

        // * query, check table by id and delete
        $deleteData = TableModel::findFirst([
            "conditions" => "id = :id:",
            "bind" => [
                "id" => $id,
            ],
        ]);
        if (!$deleteData) {
            throw new \Exception("data not found", 400);
        }
        $deleteData->setTransaction($transaction);
        if ($deleteData->delete() === false) {
            throw new \Exception("failed to delete", 400);
        }

        // * commit transaction
        $transaction->commit();

        // * send response
        $responseStatus = "success";
        $responseData = null;
        $this->response->setStatusCode(200);
        $this->response->setContent(json_encode(
            [
                "status" => $responseStatus,
                "data " => $responseData,
            ]
        ));
        $this->response->send();
    }

    public function deleteAllDataAction(): void
    {
        // * begin transaction
        $manager = $this->transactions;
        $transaction = $manager->get()->throwRollbackException(true);

        // * get all data and delete
        $dataDbs = TableModel::find();
        $dataTotal = count(TableModel::find());
        if ($dataTotal > 0) {
            foreach ($dataDbs as $dataDb) {
                $dataDb->setTransaction($transaction);
                if ($dataDb->delete() === false) {
                    throw new \Exception("failed to delete", 400);
                }
            }
        }

        // * commit transaction
        $transaction->commit();

        // * send response
        $responseStatus = "success";
        $responseData = null;
        $this->response->setStatusCode(200);
        $this->response->setContent(json_encode(
            [
                "status" => $responseStatus,
                "data " => $responseData,
            ]
        ));
        $this->response->send();
    }
}
