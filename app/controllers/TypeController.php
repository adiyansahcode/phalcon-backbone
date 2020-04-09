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

class TypeController extends \Phalcon\Mvc\Controller
{
    /**
     * Link Name Variable
     *
     * @var string
     */
    public $linkName = "/type";

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
            $paramFilter = $this->getFilter();
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
            $param1 = $this->createPagination($param1);
        }

        // * Query
        $dataDbs = TableModel::find($param1);
        $dataTotal = count(TableModel::find($param2));
        $responseData = [];
        $no = 0;
        $idFirst = 1;
        $id = 1;
        if ($dataTotal > 0) {
            foreach ($dataDbs as $dataDb) {
                if ($no === 0) {
                    $idFirst = $dataDb->getId();
                }
                $id = $dataDb->getId();
                $linkSelf = $this->linkName . '/' . $id;

                $responseData[$no]["id"] = $id;
                $responseData[$no]["createdAt"] = $dataDb->getCreatedAt();
                $responseData[$no]["updatedAt"] = $dataDb->getUpdatedAt();
                $responseData[$no]["name"] = $dataDb->getName();
                $responseData[$no]["description"] = $dataDb->getDescription();
                $responseData[$no]["isActive"] = $dataDb->getIsActive();
                $responseData[$no]["links"]["self"] = $linkSelf;
                $no++;
            }
        }

        // * response link
        $responseLink["self"] = "/type";

        // * create response
        $response["status"] = "success";
        $response["links"] = $responseLink;
        $response["page"] = $this->getPagination($dataTotal, $idFirst, $id);
        $response["data"] = $responseData;

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
    public function getFilter(): array
    {
        // * get param request
        $requestData = $this->request->getQuery();
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

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

    public function createPagination(array $param): ?array
    {
        // * get param request
        $requestData = $this->request->getQuery();
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * check sorting parameters
        $paramOrderType = "asc";
        $paramOrderField = "id";
        if (isset($sort)) {
            if (strpos($sort, '-') !== false) {
                $paramOrderType = "desc";
                $paramOrderField = str_replace("-", " ", $sort);
            }
        }

        if (isset($page['size']) && $page['size'] > 0) {
            $paramLimit = [
                "limit" => $page['size'],
            ];
            $param = array_merge($param, $paramLimit);

            if (isset($page['number']) && $page['number'] > 0) {
                $paramOffset = [
                    "offset" => ($page['number'] - 1) * $page['size'],
                ];
                $param = array_merge($param, $paramOffset);
            }

            if (isset($page['after']) || isset($page['before'])) {
                $paramConditions = null;
                if (array_key_exists('conditions', $param)) {
                    $paramConditions = $param["conditions"];
                }

                $paramBinds = [];
                if (array_key_exists('bind', $param)) {
                    $paramBinds = $param["bind"];
                }

                if (isset($page['after'])) {
                    if ($paramConditions) {
                        $paramConditions .= " AND ";
                    }
                    if ($paramOrderType === "asc") {
                        $paramConditions .= " id > :after: ";
                    } else {
                        $paramConditions .= " id < :after: ";
                    }
                    $bindAdd = [
                        "after" => $page['after'],
                    ];
                    $paramBinds = array_merge($paramBinds, $bindAdd);
                }

                if (isset($page['before'])) {
                    $dataIds = [];
                    $beforeId[] = 0;
                    if ($paramOrderType === "asc") {
                        $dataIds = TableModel::find([
                            "conditions" => "id < :before:",
                            "bind" => [
                                "before" => $page['before'],
                            ],
                            "order" => "id desc",
                            "limit" => $page['size'],
                        ]);
                    } else {
                        $dataIds = TableModel::find([
                            "conditions" => "id > :before:",
                            "bind" => [
                                "before" => $page['before'],
                            ],
                            "order" => "id asc",
                            "limit" => $page['size'],
                        ]);
                    }
                    if (count($dataIds) > 0) {
                        foreach ($dataIds as $dataId) {
                            $beforeId[] = $dataId->getId();
                        }
                    }
                    if ($paramConditions) {
                        $paramConditions .= " AND ";
                    }
                    $paramConditions .= " id IN ({beforeId:array}) ";
                    $bindAdd = [
                        "beforeId" => $beforeId,
                    ];
                    $paramBinds = array_merge($paramBinds, $bindAdd);
                }
                if (isset($paramConditions)) {
                    $paramPage = [
                        "conditions" => $paramConditions,
                        "bind" => $paramBinds,
                    ];
                    $param = array_merge($param, $paramPage);
                }
            }
        }
        $result = $param;
        return $result;
    }

    public function getPagination(float $total, int $idFirst, int $idLast): ?array
    {
        $result = null;
        $linkAdd = null;

        // * get param request
        $requestData = $this->request->getQuery();
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * create paramQuery for link
        unset($requestData["_url"]);
        unset($requestData["page"]);
        $paramQuery = [];
        if (count($requestData) > 0) {
            $paramQuery = $requestData;
        }

        if (isset($page) && is_array($page)) {
            if (isset($page['size']) && $page['size'] > 0) {
                if ($total > 0) {
                    // * page-based pagination
                    if (isset($page['number']) && $page['number'] > 0) {
                        $totalPages = ceil($total / $page['size']);
                        $result["type"] = "page-based";
                        $result["totalData"] = $total;
                        $result["totalPages"] = $totalPages;

                        $result["links"]["first"] = null;
                        if ($page['number'] > 1) {
                            $dataLinksFirst = [
                                "page" => [
                                    "number" => 1,
                                    "size" => $page['size'],
                                ]
                            ];
                            $paramQuery = array_merge($paramQuery, $dataLinksFirst);
                            $result["links"]["first"] = $this->linkName . "?" . http_build_query($paramQuery, '', '&');
                        }

                        $result["links"]["last"] = null;
                        if ($page['number'] < $totalPages) {
                            $dataLinksLast = [
                                "page" => [
                                    "number" => $totalPages,
                                    "size" => $page['size'],
                                ]
                            ];
                            $paramQuery = array_merge($paramQuery, $dataLinksLast);
                            $result["links"]["last"] = $this->linkName . "?" . http_build_query($paramQuery, '', '&');
                        }

                        $result["links"]["prev"] = null;
                        if ($page['number'] > 1) {
                            $numberPrev =
                            $dataLinksPrev = [
                                "page" => [
                                    "number" => $page['number'] - 1,
                                    "size" => $page['size'],
                                ]
                            ];
                            $paramQuery = array_merge($paramQuery, $dataLinksPrev);
                            $result["links"]["prev"] = $this->linkName . "?" . http_build_query($paramQuery, '', '&');
                        }

                        $result["links"]["next"] = null;
                        if ($page['number'] < $totalPages) {
                            $dataLinksNext = [
                                "page" => [
                                    "number" => $page['number'] + 1,
                                    "size" => $page['size'],
                                ]
                            ];
                            $paramQuery = array_merge($paramQuery, $dataLinksNext);
                            $result["links"]["next"] = $this->linkName . "?" . http_build_query($paramQuery, '', '&');
                        }
                    }

                    // * cursor-based pagination
                    if (isset($page['after']) || isset($page['before'])) {
                        $result["type"] = "cursor-based";
                        $result["totalData"] = $total;

                        // * default variable
                        $param1 = [];
                        $paramPrev = [];
                        $paramNext = [];
                        // * check filter parameters
                        if (isset($filter)) {
                            $paramFilter = $this->getFilter();
                            $param1 = array_merge($param1, $paramFilter);
                        }
                        // * check sorting parameters
                        $paramOrderType = "asc";
                        $paramOrderField = "id";
                        if (isset($sort)) {
                            if (strpos($sort, '-') !== false) {
                                $paramOrderType = "desc";
                                $paramOrderField = str_replace("-", " ", $sort);
                                $paramOrder = [
                                    "order" => $paramOrderField . " desc",
                                ];
                                $param1 = array_merge($param1, $paramOrder);
                            } else {
                                $paramOrder = [
                                    "order" => $sort,
                                ];
                                $param1 = array_merge($param1, $paramOrder);
                            }
                        }

                        $paramConditionsPrev = null;
                        $paramConditionsNext = null;
                        if (array_key_exists('conditions', $param1)) {
                            $paramConditionsPrev = $param1["conditions"];
                            $paramConditionsNext = $param1["conditions"];
                        }

                        $paramBindsPrev = [];
                        $paramBindsNext = [];
                        if (array_key_exists('bind', $param1)) {
                            $paramBindsPrev = $param1["bind"];
                            $paramBindsNext = $param1["bind"];
                        }

                        // * for prev link
                        if ($paramConditionsPrev) {
                            $paramConditionsPrev .= " AND ";
                        }
                        if ($paramOrderType === "asc") {
                            $paramConditionsPrev .= " id < :id: ";
                        } else {
                            $paramConditionsPrev .= " id > :id: ";
                        }
                        $bindAdd = [
                            "id" => $idFirst,
                        ];
                        $paramBindsPrev = array_merge($paramBindsPrev, $bindAdd);

                        if (isset($paramConditionsPrev)) {
                            $paramPage = [
                                "conditions" => $paramConditionsPrev,
                                "bind" => $paramBindsPrev,
                            ];
                            $paramPrev = array_merge($param1, $paramPage);
                        }
                        $result["links"]["prev"] = null;
                        $dataPrev = TableModel::findFirst($paramPrev);
                        if ($dataPrev) {
                            $dataLinksPrev = [
                                "page" => [
                                    "before" => $idFirst,
                                    "size" => $page['size'],
                                ]
                            ];
                            $paramQuery = array_merge($paramQuery, $dataLinksPrev);
                            $result["links"]["prev"] = $this->linkName . "?" . http_build_query($paramQuery, '', '&');
                        }

                        // * for next link
                        if ($paramConditionsNext) {
                            $paramConditionsNext .= " AND ";
                        }
                        if ($paramOrderType === "asc") {
                            $paramConditionsNext .= " id > :id: ";
                        } else {
                            $paramConditionsNext .= " id < :id: ";
                        }
                        $bindAdd = [
                            "id" => $idLast,
                        ];
                        $paramBindsNext = array_merge($paramBindsNext, $bindAdd);

                        if (isset($paramConditionsNext)) {
                            $paramPage = [
                                "conditions" => $paramConditionsNext,
                                "bind" => $paramBindsNext,
                            ];
                            $paramNext = array_merge($param1, $paramPage);
                        }
                        $result["links"]["next"] = null;
                        $dataNext = TableModel::findFirst($paramNext);
                        if ($dataNext) {
                            $dataLinksNext = [
                                "page" => [
                                    "after" => $idLast,
                                    "size" => $page['size'],
                                ]
                            ];
                            $paramQuery = array_merge($paramQuery, $dataLinksNext);
                            $result["links"]["next"] = $this->linkName . "?" . http_build_query($paramQuery, '', '&');
                        }
                    }
                }
            }
        }

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
                "self" => "/type/" . $dataDb->getId(),
            ]
        ];

        // * send response
        $responseStatus = "success";
        $responseData = $data;
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
