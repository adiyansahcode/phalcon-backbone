<?php

namespace Pbackbone\Model;

/**
 * RobotPartModel
 * @package Pbackbone\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2020-03-12, 06:15:37
 */
class RobotPartModel extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(column="id", type="integer", length=10, nullable=false)
     */
    protected $id;

    /**
     *
     * @var string
     * @Column(column="created_at", type="string", nullable=true)
     */
    protected $createdAt;

    /**
     *
     * @var integer
     * @Column(column="robot_id", type="integer", length=10, nullable=true)
     */
    protected $robotId;

    /**
     *
     * @var integer
     * @Column(column="part_id", type="integer", length=10, nullable=true)
     */
    protected $partId;

    /**
     * Method to set the value of field id
     *
     * @param integer $id
     * @return RobotPartModel
     */
    public function setId(int $id): RobotPartModel
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Method to set the value of field created_at
     *
     * @param string $createdAt
     * @return RobotPartModel
     */
    public function setCreatedAt(string $createdAt): RobotPartModel
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Method to set the value of field robot_id
     *
     * @param integer $robotId
     * @return RobotPartModel
     */
    public function setRobotId(?int $robotId): RobotPartModel
    {
        $this->robotId = $robotId;

        return $this;
    }

    /**
     * Method to set the value of field part_id
     *
     * @param integer $partId
     * @return $this
     */
    public function setPartId(?int $partId): RobotPartModel
    {
        $this->partId = $partId;

        return $this;
    }

    /**
     * Returns the value of field id
     *
     * @return integer
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * Returns the value of field createdAt
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return (string) $this->createdAt;
    }

    /**
     * Returns the value of field robotId
     *
     * @return integer
     */
    public function getRobotId(): ?int
    {
        return $this->typeId;
    }

    /**
     * Returns the value of field partId
     *
     * @return integer|null
     */
    public function getPartId(): ?int
    {
        return $this->typeId;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // $this->setSchema("phalcon-backbone");
        $this->setSource("robot_part");

        $this->addBehavior(
            new \Phalcon\Mvc\Model\Behavior\Timestampable(
                [
                    'beforeCreate' => [
                        'field'  => 'createdAt',
                        'format' => 'Y-m-d H:i:s',
                    ],
                ]
            )
        );

        // * Sets a list of attributes that must be skipped from the generated UPDATE statement
        $this->skipAttributesOnUpdate(
            [
                'createdAt',
            ]
        );

        // * Sets if a model must use dynamic update instead of the all-field update
        $this->useDynamicUpdate(true);

        $this->belongsTo(
            'partId',
            '\Pbackbone\Model\PartModel',
            'id',
            [
                'alias' => 'part',
                'reusable' => true,
                'foreignKey' => [
                    'allowNulls' => true,
                    'message' => "relation id doesn't exist",
                ],
            ]
        );

        $this->belongsTo(
            'robotId',
            '\Pbackbone\Model\RobotModel',
            'id',
            [
                'alias' => 'robot',
                'reusable' => true,
                'foreignKey' => [
                    'allowNulls' => true,
                    'message' => "relation id doesn't exist",
                ],
            ]
        );
    }

    /**
     * Independent Column Mapping.
     * Keys are the real names in the table and the values their names in the application
     *
     * @return array
     */
    public function columnMap()
    {
        return [
            'id' => 'id',
            'created_at' => 'createdAt',
            'robot_id' => 'robotId',
            'part_id' => 'partId'
        ];
    }
}
