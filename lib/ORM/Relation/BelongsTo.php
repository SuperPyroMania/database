<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013-2015 Marius Sarca
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

namespace Opis\Database\ORM\Relation;

use Opis\Database\Model;
use Opis\Database\ORM\Relation;
use Opis\Database\ORM\Select;
use Opis\Database\SQL\Expression;
use Opis\Database\ORM\LazyLoader;

class BelongsTo extends Relation
{
    public function hasMany()
    {
        return false;
    }
    
    public function getRelatedColumn(Model $model, $name)
    {
        return $this->getForeignKey();
    }
    
    public function getLazyLoader(Select $query)
    {        
        $fk = $this->getForeignKey();
        $pk = $this->owner->getPrimaryKey();
        
        $select = new Select($this->compiler, $this->model->getTable());
        
        $expr = new Expression($this->compiler);
        $expr->op($query->select($fk));
        
        $select->where($pk)->in(array($expr));
        
        return new LazyLoader($this->connection, (string) $select,
                              $this->compiler->getParams(), $this->hasMany(),
                              get_class($this->model), $pk, $fk);
    }
    
    public function getForeignKey()
    {
        if($this->foreignKey === null)
        {
            $this->foreignKey = $this->model->getForeignKey();
        }
        
        return $this->foreignKey;
    }
    
    public function getResult()
    {
        $this->query->where($this->model->getPrimaryKey())->is($this->owner->{$this->getForeignKey()});
        
        return $this->query()
                    ->fetchClass(get_class($this->model), array(false))
                    ->first();
    }
}