<?php

declare(strict_types=1);

namespace Pbackbone\Controller;

class BaseController extends \Phalcon\Mvc\Controller
{
    /**
     * getfilter function
     *
     * @param array $filter
     * @return array
     */
    public function getFilter(string $model): array
    {
        // * get param request
        $requestData = $this->request->getQuery();
        foreach ($requestData as $paramKey => $paramValue) {
            ${$paramKey} = $paramValue;
        }

        // * get list of table model
        $tableModel = new $model();
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

                            if (\DateTime::createFromFormat('Y-m-d', $filterValue2) !== false) {
                                $conditions .= "DATE(" . $filterKey . ")" . $operator . " :$filterKey: ";
                                $bindAdd = [
                                    $filterKey => $filterValue2,
                                ];
                                $bind = array_merge($bind, $bindAdd);
                            } elseif (is_numeric($filterValue2)) {
                                $conditions .= $filterKey . $operator . " :$filterKey: ";
                                $bindAdd = [
                                    $filterKey => $filterValue2,
                                ];
                                $bind = array_merge($bind, $bindAdd);
                            } else {
                                $conditions .= "LOWER(" . $filterKey . ")" . $operator . " :$filterKey: ";
                                $bindAdd = [
                                    $filterKey => strtolower($filterValue2),
                                ];
                                $bind = array_merge($bind, $bindAdd);
                            }
                        }
                    }
                }
            }
        }
        $result = [
            "conditions" => $conditions,
            "bind" => $bind,
        ];
        return $result;
    }

    /**
     * create Pagination function
     *
     * @param string $model
     * @param array $param
     * @return array|null
     */
    public function createPagination(string $model, array $param): ?array
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
                    $paramPrev = [];
                    $dataIds = [];
                    $beforeId[] = 0;

                    $paramConditionsPrev = null;
                    if (array_key_exists('conditions', $param)) {
                        $paramConditionsPrev = $param["conditions"];
                    }
                    $paramBindsPrev = [];
                    if (array_key_exists('bind', $param)) {
                        $paramBindsPrev = $param["bind"];
                    }

                    if ($paramConditionsPrev) {
                        $paramConditionsPrev .= " AND ";
                    }
                    if ($paramOrderType === "asc") {
                        $paramConditionsPrev .= " id < :before: ";
                        $bindAdd = [
                            "before" => $page['before'],
                        ];
                        $paramBindsPrev = array_merge($paramBindsPrev, $bindAdd);

                        $paramAdd = [
                            "order" => "id desc",
                            "limit" => $page['size'],
                        ];
                        $paramPrev = array_merge($paramPrev, $paramAdd);
                    } else {
                        $paramConditionsPrev .= " id > :before: ";
                        $bindAdd = [
                            "before" => $page['before'],
                        ];
                        $paramBindsPrev = array_merge($paramBindsPrev, $bindAdd);

                        $paramAdd = [
                            "order" => "id asc",
                            "limit" => $page['size'],
                        ];
                        $paramPrev = array_merge($paramPrev, $paramAdd);
                    }
                    if (isset($paramConditionsPrev)) {
                        $paramPage = [
                            "conditions" => $paramConditionsPrev,
                            "bind" => $paramBindsPrev,
                        ];
                        $paramPrev = array_merge($paramPrev, $paramPage);
                    }
                    $dataIds = $model::find($paramPrev);
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

    /**
     * get Pagination function
     *
     * @param string $model
     * @param float $total
     * @param integer $idFirst
     * @param integer $idLast
     * @return array|null
     */
    public function getPagination(string $model, float $total, int $idFirst, int $idLast): ?array
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
                if ($total > 0 and $idFirst > 0 and $idLast > 0) {
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
                            $paramFilter = $this->getFilter($model);
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
                        $dataPrev = $model::findFirst($paramPrev);
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
                        $dataNext = $model::findFirst($paramNext);
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
}
