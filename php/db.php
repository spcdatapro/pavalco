<?php
require_once 'vendor/catfan/medoo/medoo.php';
class dbcpm{

    private $dbHost = 'localhost';
    private $dbUser = 'root';
    //private $dbPass = 'Solon_06';
    private $dbPass = 'PoChoco2016';
    private $dbSchema = 'pavalco';
    private $dbConn;
    private $conConta = false;

    public function getDbHost() { return $this->dbHost; }
    public function getDbUser() { return $this->dbUser; }
    public function getDbPass() { return $this->dbPass; }
    public function getDbSchema() { return $this->dbSchema; }
    public function getConn() { return $this->dbConn; }
    public function getConConta(){ return $this->conConta; }

    function __construct() {
        $this->dbConn = new medoo([
            'database_type' => 'mysql',
            'database_name' => $this->dbSchema,
            'server' => $this->dbHost,
            'username' => $this->dbUser,
            'password' => $this->dbPass,
            'charset' => 'utf8'
        ]);
    }

    function __destruct() {
        unset($this->dbConn);
    }

    public function doSelectASJson($query){ return json_encode($this->dbConn->query($query)->fetchAll(5)); }

    public function doQuery($query) { $this->dbConn->query($query); }

    public function getQuery($query) { return $this->dbConn->query($query)->fetchAll(5); }

    public function getQueryAsArray($query) { return $this->dbConn->query($query)->fetchAll(3); }

    public function getLastId(){return $this->dbConn->query('SELECT LAST_INSERT_ID()')->fetchColumn(0);}

    public function getOneField($query){return $this->dbConn->query($query)->fetchColumn(0);}

    public function calculaISR($subtot, $tipocambio = 1.00){
        $query = "SELECT id, de, a, porcentaje, importefijo, enexcedente, FLOOR(de) AS excedente FROM isr WHERE ".($subtot * $tipocambio)." >= de AND ".($subtot * $tipocambio)." <= a LIMIT 1";
        $arrisr = $this->getQuery($query);
        if(count($arrisr) > 0){ $infoisr = $arrisr[0]; } else { return 0.00; }
        //var_dump($infoisr); return 0.00;
        if((int)$infoisr->enexcedente === 0){
            $isr = round( ((float)$infoisr->importefijo / $tipocambio) + ($subtot * (float)$infoisr->porcentaje / 100), 2);
        }else{
            $isr = round( ((float)$infoisr->importefijo / $tipocambio) + (($subtot - ((float)$infoisr->excedente) / $tipocambio) * (float)$infoisr->porcentaje / 100), 2);
        }
        return $isr;
    }
	
	public function diasHabiles($fd, $fa){
        $del = new DateTime($fd, new DateTimeZone('America/Guatemala'));
        $al = new DateTime($fa, new DateTimeZone('America/Guatemala'));
        $dh = 0;

        while($del <= $al){
            if((int)$del->format('N') != 7){$dh++;}
            $del->add(new DateInterval('P1D'));
        }
        return $dh;
    }

    public function initSession($userdata){
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION['uid'] = $userdata['id'];
        $_SESSION['nombre'] = $userdata['nombre'];
        $_SESSION['usuario'] = $userdata['usuario'];
        $_SESSION['correoe'] = $userdata['correoe'];
        $_SESSION['workingon'] = 0;
        $_SESSION['logged'] = true;
        //print 'Sesion iniciada...';
    }

    public function getSession(){
        try{
            session_start();
            $sess = array();
            $sess['uid'] = $_SESSION['uid'];
            $sess['nombre'] = $_SESSION['nombre'];
            $sess['usuario'] = $_SESSION['usuario'];
            $sess['correoe'] = $_SESSION['correoe'];
            $sess['workingon'] = $_SESSION['workingon'];
            $sess['logged'] = $_SESSION['logged'];
            return $sess;
        }catch(Exception $e){
            return ['Error' => $e->getMessage()];
        }
    }

    private function conConta($qIdEmpresa){
        $query = "SELECT conconta FROM empresa WHERE id = ".$qIdEmpresa;
        $this->conConta = (int)$this->getOneField($query) == 1;
    }

    public function setEmpreSess($qIdEmpresa){
        try{
            session_start();
            $_SESSION['workingon'] = (int) $qIdEmpresa;
            $this->conConta($qIdEmpresa);
            return ['workingon' => $_SESSION['workingon']];
        }catch(Exception $e){
            return ['Error' => $e->getMessage()];
        }
    }

    public function finishSession(){
        if (!isset($_SESSION)) {
            session_start();
        }
        if(isset($_SESSION['uid'])){
            unset($_SESSION['uid']);
            unset($_SESSION['nombre']);
            unset($_SESSION['usuario']);
            unset($_SESSION['correoe']);
            unset($_SESSION['workingon']);
            unset($_SESSION['logged']);
            $info='info';
            $cookie_time = 86400;
            if(isSet($_COOKIE[$info])){
                setcookie ($info, '', time() - $cookie_time);
            }
            $msg="Logged Out Successfully...";
        }
        else{
            $msg = "Not logged in...";
        }
        return $resultado[] = $msg;
    }
}