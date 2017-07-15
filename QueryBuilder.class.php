<?php
/**
 * Copyright (C) 2015 Degenne Valentin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Creation date : 04/03/2015 (18:00)
 */

namespace vdegenne;


class QueryBuilder {


    private $selects = array();
    private $froms = array();
    private $wheres = array();
    private $groupBys = array();
    private $having = '';
    private $limit = null;
    private $offset = null;

    private $lastMadeQuery = null;




    public function __construct () {}

    public function clean () {
        $this->selects = array();
        $this->froms = array();
        $this->wheres = array();
        $this->groupBys = array();
        $this->having = '';
        $this->limit = null;
        $this->offset = null;
    }

    public function select ($select) {
        array_push($this->selects, $select);
    }



    public function from ($from) {
        array_push($this->froms, $from);
    }

    public function where ($where) {
        array_push($this->wheres, $where);
    }

    public function group_by ($groupBy) {
        array_push($this->groupBys, $groupBy);
    }

    public function having ($having) {
        $this->having = $having;
    }

    public function limit ($limit) {
        $this->limit = $limit;
    }

    public function offset ($offset) {
        $this->offset = $offset;
    }

    public function make () {

        $query = '';

        /*
         * SELECT
         */
        if (count($this->selects) === 0) {
            throw new \ErrorException('No select clause.');
        }
        $query .= 'SELECT ' . implode(', ', $this->selects) . "\n";

        /*
         * FROM
         */
        if (count($this->froms) === 0) {
            throw new \ErrorException('No from clause.');
        }
        $query .= 'FROM ' . implode("\n", $this->froms) . "\n";

        /*
         * WHERE
         */
        if (count($this->wheres) !== 0) {
            $query .= 'WHERE ' . implode("\n", $this->wheres) . "\n";
        }

        /*
         * GROUP BY
         */
        if (count($this->groupBys) !== 0) {
            $query .= 'GROUP BY ' . implode(', ', $this->groupBys) . "\n";
        }

        /*
         * HAVING
         */
        if (strlen($this->having) !== 0) {
            $query .= 'HAVING ' . $this->having . "\n";
        }

        /*
         * LIMIT
         */
        if (!is_null($this->limit)) {
            $query .= 'LIMIT ' . $this->limit;
            if (is_null($this->offset)) {
                $query .= "\n";
            }
        }

        if (!is_null($this->offset)) {
            if (!is_null($this->limit)) {
                $query .= ' ';
            }
            $query .= 'OFFSET ' . $this->offset;
        }

        $this->lastMadeQuery = $query;

        return $query;
    }
} 