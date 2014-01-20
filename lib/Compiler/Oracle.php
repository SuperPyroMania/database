<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013 Marius Sarca
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Database\Compiler;

use Opis\Database\SQL\Compiler;
use Opis\Database\SQL\SelectStatement;

class Oracle extends Compiler
{

    /**
     * Compiles a SELECT query.
     *
     * @access  public
     * @param   \Opis\Database\SQL\SelectStatament    $select  Query object.
     * @return  array
     */

    public function select(SelectStatement $select)
    {
        $limit = $select->getLimit();
        $offset = $select->getOffset();
        
        if($limit === null && $offset === null)
        {
            return parent::select($select);
        }
        
        $sql  =  $select->isDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
        $sql .= $this->handleColumns($select->getColumns());
        $sql .= ' FROM ';
        $sql .= $this->handleTables($select->getTables());
        $sql .= $this->handleJoins($select->getJoinClauses());
        $sql .= $this->handleWheres($select->getWhereClauses());
        $sql .= $this->handleGroupings($select->getGroupClauses());
        $sql .= $this->handleOrderings($select->getOrderClauses());
        $sql .= $this->handleHavings($select->getHavingClauses());
        
        if($offset === null)
        {
            return 'SELECT m1.* FROM (' . $sql . ') m1 WHERE rownum <= ' . $limit;
        }
        
        $limit += $offset;
        $offset++;
        
        return 'SELECT * FROM (SELECT m1.*, rownum AS opis_rownum FROM (' . $sql . ') m1 WHERE rownum <= ' . $limit . ') WHERE opis_rownum >= ' . $offset;
    }
}