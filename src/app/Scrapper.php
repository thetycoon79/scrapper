<?php

namespace App;

use App\Utilties\DataRequest\DataRequest;
use App\Utilties\Db\Db;
use Dotenv\Dotenv;

class Scrapper
{

    private $dbHost;
    private $dbUser;
    private $dbPass;
    private $dbName;
    protected $apiKey;
    private $pageCounter;
    private $db;
    private $dataSrc;
    protected $dotEnv;
    private $pageSize = 10;
    private $pageNumber;


    public function __construct() {

        $this->dotEnv = Dotenv::createImmutable(dirname(__DIR__));
        $this
            ->dotEnv
            ->load();
        $this->setDbHost($_ENV['DB_HOST']);
        $this->setDbUser($_ENV['DB_USERNAME']);
        $this->setDbPass($_ENV['DB_PASSWORD']);
        $this->setDbName($_ENV['DB_DATABASE']);
        $this->setApiKey($_ENV['API_KEY']);

        $this->db = New Db(
            $this->getDbHost(),
            $this->getDbUser(),
            $this->getDbPass(),
            $this->getDbName()
        );

        $this->dataSrc = New DataRequest();

    }

    public function getData(){

        $this
            ->dataSrc
            ->setQueryPath('/api/properties');
        $this->setPageCounter(
            $this->getTotalPage()
        );

        list($isPreviousCaptureFailed, $lastFailedPage) = $this->previousCaptureFailed();

        if($isPreviousCaptureFailed){

            $pageStart = $lastFailedPage;
            //TODO remove last failed setting
        }
        else{

            $pageStart = 1;

        }

        for ($x = $pageStart; $x <= $this->getPageCounter() ; $x++) {

            echo "Starting page : {$x} of {$this->getPageCounter()} pages \r\n";
            $this
                ->dataSrc
                ->setQueryString("page[size]={$this->getPageSize()}&page[number]={$x}&api_key={$this->getApiKey()}");
            $data = json_decode(
                $this
                    ->dataSrc
                    ->getData(),true
            );


            foreach($data as $key => $value)
            {

                if($key == "data") {

                    foreach ($value as $item) {

                        $this->saveData($item);

                    }
                }

            }

            echo "Ending page : {$x} of {$this->getPageCounter()} pages \r\n";

        }

    }

    private function saveData($data){

        if(!$this->isPropertyTypeExist($data["property_type_id"])) {

            $description = $this->remove_crap($data["property_type"]["description"]);
            $insertPropertyType = $this
                ->db
                ->query('INSERT INTO propertyType (
                      originTypeId,
                      title,
                      description,
                      createdAt,
                      updatedAt
                      ) 
                      VALUES 
                      (?,?,?,?,?)',
                    $data["property_type"]["id"],
                    $data["property_type"]["title"],
                    $description,
                    $data["property_type"]["created_at"],
                    $data["property_type"]["updated_at"]
                );


        }

        if(!$this->isPropertyDataExist($data["uuid"])){

            $long = floatval($data["longitude"]);
            $lat = floatval($data["latitude"]);
            $description = $this->remove_crap($data["description"]);

            $insertProperty = $this
                ->db
                ->query('INSERT INTO property (
                          uuid,
                          county,
                          country,
                          town,
                          description,
                          displayAddress,
                          image,
                          thumbnail,
                          longitude,
                          latitude,
                          bedroom,
                          bathroom,
                          price,
                          propertyTypeId,
                          propertyIntent,
                          createdAt,
                          updatedAt
                          ) 
                          VALUES 
                          (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                          $data["uuid"],
                          $data["county"],
                          $data["country"],
                          $data["town"],
                          $description,
                          $data["address"],
                          $data["image_full"],
                          $data["image_thumbnail"],
                          $long,
                          $lat,
                          $data["num_bedrooms"],
                          $data["num_bathrooms"],
                          $data["price"],
                          $data["property_type_id"],
                          $data["type"],
                          $data["created_at"],
                          $data["updated_at"]
                );

        }
    }

    private function isPropertyTypeExist($id){

        $check = $this
            ->db
            ->query('SELECT originTypeId FROM propertyType WHERE originTypeId= ?',$id);

        if($check->numRows() > 0) {

            return true;

        }
        else {

            return false;

        }

    }

    private function isPropertyDataExist($uuid){

        $check = $this
            ->db
            ->query('SELECT uuid FROM property WHERE uuid= ?',$uuid);

        if($check->numRows() > 0) {

            return true;

        }
        else {

            return false;

        }

    }

    private function getTotalPage(){

        $this
            ->dataSrc
            ->setQueryPath('/api/properties');

        $this
            ->dataSrc
            ->setQueryString("page[size]={$this->getPageSize()}&page[number]=1&api_key={$this->getApiKey()}");
        $data = json_decode(
            $this
                ->dataSrc
                ->getData(),true
        );

        return $data["last_page"];

    }

    private function remove_crap($Str) {
        $StrArr = str_split($Str); $NewStr = '';
        foreach ($StrArr as $Char) {
            $CharNo = ord($Char);
            if ($CharNo == 163) { $NewStr .= $Char; continue; } // keep £
            if ($CharNo > 31 && $CharNo < 127) {
                $NewStr .= $Char;
            }
        }
        return $NewStr;
    }

    private function previousCaptureFailed(){

        $check = $this
            ->db
            ->query('SELECT value FROM settings s WHERE s.name= ? AND s.key= ?','capture','failed');

        if($check->numRows() > 0) {

            $data = $check->fetchArray();
            return array(true, $data["value"]);

        }
        else {

            return array(false,0);

        }

    }

    public function getDbHost()
    {
        return $this->dbHost;
    }

    public function setDbHost($dbHost)
    {
        $this->dbHost = $dbHost;
    }

    public function getDbUser()
    {
        return $this->dbUser;
    }

    public function setDbUser($dbUser)
    {
        $this->dbUser = $dbUser;
    }

    public function getDbPass()
    {
        return $this->dbPass;
    }


    public function setDbPass($dbPass)
    {
        $this->dbPass = $dbPass;
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function setDbName($dbName)
    {
        $this->dbName = $dbName;
    }


    public function getDb()
    {
        return $this->db;
    }


    public function setDb($db)
    {
        $this->db = $db;
    }

    public function getPageCounter()
    {
        return $this->pageCounter;
    }

    public function setPageCounter($pageCounter)
    {
        $this->pageCounter = $pageCounter;
    }

    public function getDataSrc()
    {
        return $this->dataSrc;
    }

    public function setDataSrc($dataSrc)
    {
        $this->dataSrc = $dataSrc;
    }

    public function getDotEnv()
    {
        return $this->dotEnv;
    }

    public function setDotEnv($dotEnv)
    {
        $this->dotEnv = $dotEnv;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }


}
