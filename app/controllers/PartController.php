<?php

declare(strict_types=1);

namespace Pbackbone\Controller;

use Pbackbone\Model\PartModel as TableModel;
use Pbackbone\Validation\PartCreateDataValidation as CreateDataValidation;
use Pbackbone\Validation\PartReadDataValidation as ReadDataValidation;
use Pbackbone\Validation\PartReadDataByIdValidation as ReadDataByIdValidation;
use Pbackbone\Validation\PartUpdateDataByPutValidation as UpdateDataByPutValidation;
use Pbackbone\Validation\PartUpdateDataByPatchValidation as UpdateDataByPatchValidation;
use Pbackbone\Validation\PartDeleteDataValidation as DeleteDataValidation;

class PartController extends \Phalcon\Mvc\Controller
{
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
            $paramFilter = $this->getFilter($filter);
            $param1 = array_merge($param1, $paramFilter);
            $param2 = array_merge($param2, $paramFilter);
        }

        // * check field parameters
        if (isset($field)) {
            $paramField = [
                "columns" => $field,
            ];
            $param1 = array_merge($param1, $paramField);
        }

        // * check sorting parameters
        if (isset($sort)) {
            if (strpos($sort, '-') !== false) {
                $paramOrder = [
                    "order" => str_replace("-", " ", $sort) . " desc",
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
            if (isset($page['size']) && $page['size'] > 0) {
                $paramLimit = [
                    "limit" => $page['size'],
                ];
                $param1 = array_merge($param1, $paramLimit);

                if (isset($page['number']) && $page['number'] > 0) {
                    $paramOffset = [
                        "offset" => ($page['number'] - 1) * $page['size'],
                    ];
                    $param1 = array_merge($param1, $paramOffset);
                }

                if (isset($page['after']) || isset($page['before'])) {
                    $paramConditions = "";
                    if (array_key_exists('conditions', $param1)) {
                        $paramConditions = $param1["conditions"];
                    }

                    $paramBinds = [];
                    if (array_key_exists('bind', $param1)) {
                        $paramBinds = $param1["bind"];
                    }

                    if (isset($page['after'])) {
                        if ($paramConditions) {
                            $paramConditions .= " AND ";
                        }
                        $paramConditions .= " id > :after: ";
                        $bindAdd = [
                            "after" => $page['after'],
                        ];
                        $paramBinds = array_merge($paramBinds, $bindAdd);
                    }

                    if (isset($page['before'])) {
                        if ($paramConditions) {
                            $paramConditions .= " AND ";
                        }
                        $paramConditions .= " id < :before: ";
                        $bindAdd = [
                            "before" => $page['before'],
                        ];
                        $paramBinds = array_merge($paramBinds, $bindAdd);
                    }

                    if (isset($paramConditions)) {
                        $paramPage = [
                            "conditions" => $paramConditions,
                            "bind" => $paramBinds,
                        ];
                        $param1 = array_merge($param1, $paramPage);
                        $param2 = array_merge($param2, $paramPage);
                    }
                }
            }
        }

        // * Query
        $dataDbs = TableModel::find($param1);
        $dataTotal = count(TableModel::find($param2));
        $responseData = [];
        if ($dataTotal > 0) {
            foreach ($dataDbs as $dataDb) {
                if (isset($field)) {
                    $responseData[] = $dataDb;
                } else {
                    $responseData[] = [
                        "id" => $dataDb->getId(),
                        "createdAt" => $dataDb->getCreatedAt(),
                        "updatedAt" => $dataDb->getUpdatedAt(),
                        "name" => $dataDb->getName(),
                        "description" => $dataDb->getDescription(),
                        "isActive" => $dataDb->getIsActive(),
                        "links" => [
                            "self" => "/type/" . $dataDb->getId(),
                        ],
                    ];
                }
            }
        }

        // * Response page-based strategy
        $totalPages = 1;
        if (isset($page) && is_array($page)) {
            if (isset($page['size']) && $page['size'] > 0) {
                if ($dataTotal >= $page['size']) {
                    $item = $page['size'];
                } else {
                    $item = $page['size'] - ($page['size'] - $dataTotal);
                }
                $totalPages = ceil($dataTotal / $page['size']);
            }
        }
        $responsePage["totalData"] = $dataTotal;
        $responsePage["totalPages"] = $totalPages;
        $responsePage["links"] = [
            "first" => null,
            "last" => null,
            "prev" => null,
            "next" => null,
        ];

        // * resonse link
        $responseLink["self"] = "/type";

        // * create response
        if (isset($hide)) {
            if (is_array($hide)) {
                $response["status"] = "success";
                if ($hide["links"] != "true") {
                    $response["links"] = $responseLink;
                }
                if ($hide["page"] != "true") {
                    $response["page"] = $responsePage;
                }
                $response["data"] = $responseData;
            }
        } else {
            $response["status"] = "success";
            $response["links"] = $responseLink;
            $response["page"] = $responsePage;
            $response["data"] = $responseData;
        }

        // * send response
        $this->response->setStatusCode(200);
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setContent(json_encode($response));
        $this->response->send();
    }

    /**
     * getfilter function
     *
     * @param array $filter
     * @return array
     */
    public function getFilter(array $filter): array
    {
        // * get list of table model
        $tableModel = new TableModel();
        $metadata = $tableModel->getModelsMetaData();
        $attributes = $metadata->getColumnMap($tableModel);
        foreach ($attributes as $attributesData) {
            $fields[] = $attributesData;
        }

        // * default variable
        $conditions = null;
        $bind = [];

        foreach ($filter as $filterKey => $filterValue) {
            if (in_array($filterKey, $fields)) {
                if (is_array($filterValue)) {
                    foreach ($filterValue as $filterKey2 => $filterValue2) {
                        if ($conditions) {
                            $conditions .= " AND ";
                        }

                        if ($filterKey2 === "like") {
                            $conditions .= " ( " . $filterKey . " LIKE :$filterKey: ) ";
                            $bindAdd = [
                                $filterKey => '%' . $filterValue2 . '%',
                            ];
                            $bind = array_merge($bind, $bindAdd);
                        } else {
                            if ($filterKey2 === "equal") {
                                $operator = "=";
                            } elseif ($filterKey2 === "notequal") {
                                $operator = "!=";
                            } elseif ($filterKey2 === "greater") {
                                $operator = ">";
                            } elseif ($filterKey2 === "greaterNequal") {
                                $operator = ">=";
                            } elseif ($filterKey2 === "less") {
                                $operator = "<";
                            } elseif ($filterKey2 === "lessNequal") {
                                $operator = "<=";
                            }

                            $conditions .= $filterKey . $operator . " :$filterKey: ";
                            $bindAdd = [
                                $filterKey => $filterValue2,
                            ];
                            $bind = array_merge($bind, $bindAdd);
                        }
                    }
                }
            }

            if ($filterKey === "query" && !is_array($filterValue)) {
                if ($conditions) {
                    $conditions .= " AND ";
                }
                $conditions .= " ( name LIKE :filter: OR description LIKE :filter: ) ";
                $bindAdd = [
                    "filter" => '%' . $filterValue . '%',
                ];
                $bind = array_merge($bind, $bindAdd);
            }
        }
        $result = [
            "conditions" => $conditions,
            "bind" => $bind,
        ];
        return $result;
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
            if ($paramValue || is_numeric($paramValue)) {
                ${$paramKey} = $this->filters->sanitize($paramValue, ['trim']);
            } else {
                ${$paramKey} = null;
            }
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

        // * Create Data
        $data = [
            "id"          => $dataDb->getId(),
            "createdAt"   => $dataDb->getCreatedAt(),
            "updatedAt"   => $dataDb->getUpdatedAt(),
            "name"        => $dataDb->getName(),
            "description" => $dataDb->getDescription(),
            "isActive"    => $dataDb->getIsActive(),
            "link" => [
                "self" => $this->config->application->baseUri . "/type/" . $dataDb->getId(),
            ]
        ];

        // * send response
        $responseStatus = "success";
        $responseData[$this->dataName] = $data;
        $this->response->setStatusCode(200);
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setContent(json_encode(
            [
                "status" => $responseStatus,
                "data " => $responseData,
            ]
        ));
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
            if ($paramValue || is_numeric($paramValue)) {
                ${$paramKey} = $this->filters->sanitize($paramValue, ['trim']);
            } else {
                ${$paramKey} = null;
            }
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
        $responseData = [
            "id" => $id,
            "links" => [
                "self" => "/type/" . $id
            ],
        ];

        // * send response
        $this->response->setStatusCode(201);
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setHeader("Location", $this->config->application->baseUri . "/type/" . $id);
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
            if ($paramValue || is_numeric($paramValue)) {
                ${$paramKey} = $this->filters->sanitize($paramValue, ['trim']);
            } else {
                ${$paramKey} = null;
            }
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
        $responseData = [
            "id" => $id,
            "links" => [
                "self" => "/type/" . $id
            ],
        ];

        // * send response
        $this->response->setStatusCode(200);
        $this->response->setContentType('application/json', 'UTF-8');
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
            if ($paramValue || is_numeric($paramValue)) {
                ${$paramKey} = $this->filters->sanitize($paramValue, ['trim']);
            } else {
                ${$paramKey} = null;
            }
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
        $responseData = [
            "id" => $id,
            "links" => [
                "self" => "/type/" . $id
            ],
        ];

        // * send response
        $this->response->setStatusCode(200);
        $this->response->setContentType('application/json', 'UTF-8');
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
            if ($paramValue || is_numeric($paramValue)) {
                ${$paramKey} = $this->filters->sanitize($paramValue, ['trim']);
            } else {
                ${$paramKey} = null;
            }
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
        $this->response->setContentType('application/json', 'UTF-8');
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
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setContent(json_encode(
            [
                "status" => $responseStatus,
                "data " => $responseData,
            ]
        ));
        $this->response->send();
    }
}
