<?php

namespace Pbackbone\Model;

use Phalcon\Mvc\Model\Relation;

/**
 * PartModel
 * @package Pbackbone\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2020-03-12, 06:15:31
 */
class PartModel extends \Phalcon\Mvc\Model
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
     * @var string
     * @Column(column="updated_at", type="string", nullable=true)
     */
    protected $updatedAt;

    /**
     *
     * @var string
     * @Column(column="name", type="string", length=50, nullable=true)
     */
    protected $name;

    /**
     *
     * @var string
     * @Column(column="description", type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var string
     * @Column(column="is_active", type="string", length='yes','no', nullable=true)
     */
    protected $isActive;

    /**
     * Method to set the value of field id
     *
     * @param integer $id
     * @return PartModel
     */
    public function setId(int $id): PartModel
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Method to set the value of field created_at
     *
     * @param string $createdAt
     * @return PartModel
     */
    public function setCreatedAt(string $createdAt): PartModel
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Method to set the value of field updated_at
     *
     * @param string $updatedAt
     * @return PartModel
     */
    public function setUpdatedAt(string $updatedAt): PartModel
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Method to set the value of field name
     *
     * @param string $name
     * @return PartModel
     */
    public function setName(string $name): PartModel
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     * @return PartModel
     */
    public function setDescription(string $description): PartModel
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Method to set the value of field is_active
     *
     * @param string $isActive
     * @return PartModel
     */
    public function setIsActive(string $isActive): PartModel
    {
        $this->isActive = $isActive;

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
     * Returns the value of field updatedAt
     *
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return (string) $this->updatedAt;
    }

    /**
     * Returns the value of field name
     *
     * @return string
     */
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return (string) $this->description;
    }

    /**
     * Returns the value of field isActive
     *
     * @return string
     */
    public function getIsActive(): string
    {
        return (string) $this->isActive;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // $this->setSchema("phalcon-backbone");
        $this->setSource("part");

        $this->addBehavior(
            new \Phalcon\Mvc\Model\Behavior\Timestampable(
                [
                    'beforeCreate' => [
                        'field'  => 'createdAt',
                        'format' => 'Y-m-d H:i:s',
                    ],
                    'beforeUpdate' => [
                        'field'  => 'updatedAt',
                        'format' => 'Y-m-d H:i:s',
                    ],
                ]
            )
        );

        // * Sets a list of attributes that must be skipped from the generated INSERT statement
        $this->skipAttributesOnCreate(
            [
                'updatedAt',
            ]
        );

        // * Sets a list of attributes that must be skipped from the generated UPDATE statement
        $this->skipAttributesOnUpdate(
            [
                'createdAt',
            ]
        );

        // * Sets if a model must use dynamic update instead of the all-field update
        $this->useDynamicUpdate(true);

        $this->hasManyToMany(
            'id',
            '\Pbackbone\Model\RobotPartModel',
            'partId',
            'robotId',
            '\Pbackbone\Model\RobotModel',
            'id',
            [
                'alias' => 'robot',
            ]
        );

        $this->hasMany(
            'id',
            '\Pbackbone\Model\RobotPartModel',
            'partId',
            [
                'alias' => 'robotPart',
                'reusable' => true,
                'foreignKey' => [
                    'message' => 'data still being used',
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
            'updated_at' => 'updatedAt',
            'name' => 'name',
            'description' => 'description',
            'is_active' => 'isActive'
        ];
    }
}
