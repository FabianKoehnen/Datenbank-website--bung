<?php
include "Adapter/Adapter/csv.php";
include "Adapter/Adapter/xml.php";

class DBHandler
{
    protected $db;

    function __construct()
    {
        try {
            $this->db = new \PDO('mysql:dbname=UploadDatabase;host=db', 'root', 'root');


        } catch (PDOException $e) {
            echo "Momentan keine Verbindung zur Datenbank";
        }
    }

    function uploadHandler($path, $type, $name)
    {
        $db = $this->db;
        if (!is_null($db)) {
            switch ($type) {
                case "text/csv":
                    $this->insertArrayIntoDb($this->convertCsvInArray($path), $name);
                    break;
                case "text/xml":
                    if ($this->testXmlNumberOfDimensions($path)) {
                        $this->insertArrayIntoDb($this->convertXmlInArray($path), $name);
                    } else {
                        die("<br>Datei kann nicht als Liste verarbeitet werden");
                    }
                    break;
            }
        } else {
            echo("<br>Datei nur zum Download verfügbar");
            switch ($type) {
                case "text/csv":
                    break;
                case "text/xml":
                    if ($this->testXmlNumberOfDimensions($path)) {

                    } else {
                        die("<br>Datei kann nicht als Liste verarbeitet werden");
                    }
                    break;
            }
        }
    }

    function testXmlNumberOfDimensions($path)
    {
        $xml = new xml();
        $valid = true;
        $content = $xml->read($path);
        foreach ($content as $item) {
            if (is_array($item)) {
                foreach ($item as $value) {
                    if (is_array($value)) {
                        foreach ($value as $entry)
                            if (is_array($entry)) $valid = false;
                    }
                }
            }
        }

        return $valid;
    }

    private function convertXmlInArray($path)
    {
        $xml = new xml();
        $content = $xml->read($path);
        return $content;

    }

    private function convertCsvInArray($path)
    {
        $csv = new csv();
        $content = $csv->read($path, ";");
        return $content;
    }

    private function insertArrayIntoDb($content, $name)
    {
        $this->createTable($content, $name);
    }

    private function createTable($content, $name)
    {
        $headerNames = array();

        foreach ($content as $item) {
            foreach ($item as $key => $value) {
                array_push($headerNames, $key);
            }
            break;
        }

        $queryLineHeader = array();

        foreach ($headerNames as $value) {
            array_push($queryLineHeader, "$value VARCHAR(255)");
        }
        $queryString = implode(",", $queryLineHeader);

        $sql = "CREATE TABLE " . $this->buildName($name) . " (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,$queryString)";

        if (!$this->db->query($sql) == true) {
            echo "Bitte erneut versuchen";
        }
        $allTable = $this->db->query('SHOW TABLES');

        while ($result = $allTable->fetch()) {
            echo $result[0] . '<br />';
        }
    }

    private function buildName($name)
    {
        $pointlessName = str_replace('.', '_', $name);
        $timestamp = time() + 7200;
        $datum = date("d_m_y_H_i_s", $timestamp);
        return "$pointlessName$datum";
    }

    function createCsvDownloadfile()
    {

    }

    function createXmlDownloadfile()
    {

    }

    function __destruct()
    {
        $allTables = array();
//          böse I
//               v
//        while($result = $this->db->query('SHOW TABLES')->fetch()) {
//            array_push($allTables,$result);
//        }
        print_r($allTables);
        $this->db = null;
    }
}