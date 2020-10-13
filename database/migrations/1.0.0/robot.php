<?php

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class RobotMigration_100
 */
class RobotMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable(
            'robot',
            [
                'columns' => [
                    new Column(
                        'id',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'first' => true,
                        ]
                    ),
                    new Column(
                        'created_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'notNull' => false,
                            'after' => 'id',
                        ]
                    ),
                    new Column(
                        'updated_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'notNull' => false,
                            'after' => 'created_at',
                        ]
                    ),
                    new Column(
                        'name',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => false,
                            'size' => 50,
                            'after' => 'updated_at',
                        ]
                    ),
                    new Column(
                        'description',
                        [
                            'type' => Column::TYPE_TEXT,
                            'notNull' => false,
                            'after' => 'name',
                        ]
                    ),
                    new Column(
                        'year',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'notNull' => false,
                            'size' => 10,
                            'after' => 'description',
                        ]
                    ),
                    new Column(
                        'is_active',
                        [
                            'type' => Column::TYPE_ENUM,
                            'default' => "yes",
                            'notNull' => false,
                            'size' => "'yes','no'",
                            'after' => 'year',
                        ]
                    ),
                    new Column(
                        'type_id',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'unsigned' => true,
                            'notNull' => false,
                            'after' => 'is_active',
                        ]
                    ),
                ],
                'indexes' => [
                    new Index('PRIMARY', ['id'], 'PRIMARY'),
                    new Index('fk_type_id_01_idx', ['type_id'], ''),
                ],
                'references' => [
                    new Reference(
                        'fk_type_id_01',
                        [
                            'referencedTable' => 'type',
                            'columns' => ['type_id'],
                            'referencedColumns' => ['id'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'NO ACTION',
                        ]
                    ),
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'utf8_general_ci',
                ],
            ]
        );
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {
    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {
    }
}
