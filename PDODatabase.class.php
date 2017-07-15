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
 * Creation date : 02/03/2015 (15:08)
 */

namespace vdegenne;


class PDODatabase {

    /**
     * @var \PDO
     */
    private static $_Database;
    private $_PDO;

    public static $credentialsFileName = '.credentials';


    public function __construct ($PDO) {
        $this->_PDO = $PDO;
    }


    /**
     * @param PDODriver $driver
     * @param string              $host
     * @param string              $dbname
     *
     * @return \vdegenne\PDODatabase
     * @throws \ErrorException
     */
    public static function get_database ($driver = null,
                                         $host = null,
                                         $dbname = null
    ) {
        if (is_null(PDODatabase::$_Database)) {


            if (is_null($driver) || is_null($host) || is_null($dbname)) {
                throw new \ErrorException('missing arguments.');
            }


            $credentialsPath
                = \FRAMEWORK::$INCLUDES_PATH
                . DS
                . PDODatabase::$credentialsFileName;

            /*
             * On vérifie les identifiants associés au serveur présent dans le
             * fichier '.credentials'.
             */
            if (!file_exists($credentialsPath)
            ) {
                throw new \ErrorException('Missing credentials file.');
            }

            $credentialsFile = file_get_contents($credentialsPath);
            $credentialSeqs = explode("\n", $credentialsFile);


            foreach ($credentialSeqs as $credentialSeq) {
                $credential = explode(':', $credentialSeq);

                // $credential[0] : host,
                // $credential[1] : username,
                // $credential[2] : password

                if ($credential[0] === $host) {
                    goto found;
                }
            }

            throw new \ErrorException('the credentials was not found.');

            found:

            $driverName = '';
            switch ($driver) {
            case PDODriver::POSTGRE_SQL:
                $driverName = 'pgsql';
                break;
            default:
                throw new \ErrorException('driver not supported.');
            }


            $PDO = null;
            try {

                $PDO = new \PDO(
                    "$driverName:host=$host;dbname=$dbname",
                    $credential[1],
                    $credential[2]
                );

                $PDO->setAttribute(
                    \PDO::ATTR_ERRMODE,
                    \PDO::ERRMODE_EXCEPTION
                );


            } catch (\PDOException $pdoException) {
                file_put_contents(
                    'database_error', $pdoException->getMessage()
                );
                throw new \ErrorException('Couldn\'t create the PDO object');
            };

            PDODatabase::$_Database = new PDODatabase($PDO);
        }

        return PDODatabase::$_Database;
    }


    public function prepare_IN_placeholders (&$query, $IN_name, $list) {


        $IN_list = array();
        for ($i = 0, $size = count($list); $i < $size; ++$i) {
            $IN_list[$IN_name . ($i + 1)] = $list[$i];
        }

        $query = preg_replace(
            '/:' . $IN_name . '/', ':' . implode(', :', array_keys($IN_list)), $query, 1
        );

        return $IN_list;
    }


    public function close () {
        $this->_PDO = null;
        PDODatabase::$_Database = null;
    }

    /**
     * @return \PDO
     */
    public function get_PDO () {
        return $this->_PDO;
    }
}


class PDODriver {
    const POSTGRE_SQL = 0;
}