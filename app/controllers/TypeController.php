<?php

declare(strict_types=1);

namespace Pbackbone\Controller;

use Pbackbone\Model\TypeModel as TableModel;
use Pbackbone\Validation\TypeCreateDataValidation as CreateDataValidation;
use Pbackbone\Validation\TypeReadDataValidation as ReadDataValidation;
use Pbackbone\Validation\TypeReadDataByIdValidation as ReadDataByIdValidation;
use Pbackbone\Validation\TypeUpdateDataByPutValidation as UpdateDataByPutValidation;
use Pbackbone\Validation\TypeUpdateDataByPatchValidation as UpdateDataByPatchValidation;
use Pbackbone\Validation\TypeDeleteDataValidation as DeleteDataValidation;

class TypeController extends \Pbackbone\Controller\BaseController
{
    /**
     * data name variable
     *
     * @var string
     */
    public $dataName = "type";

    /**
     * link name variable
     *
     * @var string
     */
    public $linkName = "/type";

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

    /**
     * readDataAction
     * get all data from table
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
                    $responseData[$no]["isActive"] = $dataDb->getIsActive();
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
            'GET,POST,DELETE,OPTIONS,HEAD'
        );
        $this->response->setContent(json_encode($response));
        $this->response->send();
    }

    /**
     * readDataByIdAction
     * get data by id from table
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

        // * Create Data
        $responseData["id"] = $id;
        $responseData["createdAt"] = $dataDb->getCreatedAt();
        $responseData["updatedAt"] = $dataDb->getUpdatedAt();
        $responseData["name"] = $dataDb->getName();
        $responseData["description"] = $dataDb->getDescription();
        $responseData["isActive"] = $dataDb->getIsActive();

        // * create response
        $response["status"] = "success";
        $response["links"] = $responseLink;
        $response["data"][$this->dataName] = $responseData;

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
     * createDataAction
     * create new data
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

        // * save query
        $saveData = new TableModel();
        $saveData->setTransaction($transaction);
        $saveData->assign(
            [
                "name" => $name,
                "description" => $description,
                "isActive" => $isActive,
            ]
        );
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

    /**
     * updateDataByPutAction
     * update data with id
     * method put
     *
     * @param integer $id
     * @return void
     */
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
            [
                "name" => $name,
                "description" => $description,
                "isActive" => $isActive,
            ]
        );
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
                "name", "description", "isActive"
            ]
        );
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
