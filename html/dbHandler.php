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
            print_r($sql);
            print_r($this->db->errorInfo());
        }

    }

    private function buildName($name)
    {
        $pointlessName = str_replace('.', '_', $name);
        $datum = date("j_n_Y_H_i_s", $this->getTime());
        return "$pointlessName"."$"."$datum";
    }

    function createCsvDownloadfile()
    {

    }

    function createXmlDownloadfile()
    {

    }
    function getTableNameArray($tables){
        $returnTables=array();
        foreach ($tables as $item){
            $timestamp=explode('$',$item[0]);
            array_push($returnTables,$timestamp);
        }
        return $returnTables;
    }
    function selectOldTables($tables){
        $oldTables=array();
        $explodetablenames = $this->getTableNameArray($tables);
        $timestamp = $this->getTime();

        $currentdatum = getdate($this->getTime());

        foreach ($explodetablenames as $item){
            $explodeTimestemp=explode('_',$item[1]);
            if($explodeTimestemp[3]<=$currentdatum["year"]){
                array_push($oldTables,$item);
            }else{
                if($explodeTimestemp[2]<=$currentdatum["month"]){
                    array_push($oldTables,$item);
                }else{
                    if($explodeTimestemp[1]<=$currentdatum["mday"]){
                        array_push($oldTables,$item);
                    }else{
                        if($explodeTimestemp[4]<=$currentdatum["hours"]){
                            array_push($oldTables,$item);
                        }else{
                            if($explodeTimestemp[5]<=$currentdatum["minutes"]){
                                array_push($oldTables,$item);
                            }else{
                                if($explodeTimestemp[6]<=$currentdatum["seconds"]){
                                    array_push($oldTables,$item);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $oldTables;

    }
    function deleteOldTables(){

    }
    function getTime(){
        $timestamp = time() + 7200;

        return $timestamp;
    }
    function __destruct()
    {
        $sql = "SHOW TABLES";


        $statement = $this->db->prepare($sql);


        $statement->execute();


        $tablesnames = $statement->fetchAll(PDO::FETCH_NUM);

        $oldTables=$this->selectOldTables($tablesnames);



        $this->db = null;
    }
}