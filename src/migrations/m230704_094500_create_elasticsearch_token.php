<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\news\migrations
 * @author    Aria S.p.A. <protocollo@pec.ariaspa.it>
 * @license   GNU General Public License v3.0 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @link      https://www.ariaspa.it/
 *
 * This file is part of OPEN 2.0.
 *
 * OPEN 2.0 is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OPEN 2.0 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OPEN 2.0. If not, see <https://www.gnu.org/licenses/>.
 */
use open20\amos\core\migration\AmosMigrationTableCreation;

/**
 * Class m230704_094500_create_elasticsearch_token
 */
class m230704_094500_create_elasticsearch_token extends AmosMigrationTableCreation {

    /**
     * set table name
     *
     * @return void
     */
    protected function setTableName() {

        $this->tableName = '{{%elasticsearch_token%}}';
    }

    /**
     * set table fields
     *
     * @return void
     */
    protected function setTableFields() {

        $this->tableFields = [
            // PK
            'id' => $this->primaryKey(),
            // COLUMNS
            'token' => $this->string()->null()->defaultValue(null)->comment('Token'),
            'url' => $this->text()->null()->defaultValue(null)->comment('Token'),
            'time' => $this->integer()->null()->defaultValue(null)->comment('Time'),
            'consumed' => $this->integer()->null()->defaultValue(0)->comment('Consumed'),
        ];
    }

    /**
     * Timestamp
     */
    protected function beforeTableCreation() {

        parent::beforeTableCreation();
        $this->setAddCreatedUpdatedFields(true);
    } 

    public function afterTableCreation() {
        $this->createIndex('idx_token', $this->tableName, 'token');
        $this->createIndex('idx_consumed', $this->tableName, 'consumed');
        $this->createIndex('idx_deleted_at', $this->tableName, 'deleted_at');
        $this->createIndex('idx_url', $this->tableName, 'url');
    }
}
 