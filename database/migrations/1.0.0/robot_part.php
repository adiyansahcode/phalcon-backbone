<?php

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class RobotPartMigration_100
 */
class RobotPartMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable(
            'robot_part',
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
                        'robot_id',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'unsigned' => true,
                            'notNull' => false,
                            'after' => 'created_at',
                        ]
                    ),
                    new Column(
                        'part_id',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'unsigned' => true,
                            'notNull' => false,
                            'after' => 'robot_id',
                        ]
                    ),
                ],
                'indexes' => [
                    new Index('PRIMARY', ['id'], 'PRIMARY'),
                    new Index('robots_id', ['robot_id'], ''),
                    new Index('parts_id', ['part_id'], ''),
                ],
                'references' => [
                    new Reference(
                        'fk_parts_id_01',
                        [
                            'referencedTable' => 'part',
                            'columns' => ['part_id'],
                            'referencedColumns' => ['id'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'NO ACTION',
                        ]
                    ),
                    new Reference(
                        'fk_robots_id_01',
                        [
                            'referencedTable' => 'robot',
                            'columns' => ['robot_id'],
                            'referencedColumns' => ['id'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'CASCADE',
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
