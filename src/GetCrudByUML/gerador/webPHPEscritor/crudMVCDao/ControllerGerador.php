<?php


namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao;

use GetCrudByUML\model\Atributo;
use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Software;

class ControllerGerador{
    private $software;
    private $listaDeArquivos;
    private $diretorio;
    
    public static function main(Software $software, $diretorio){
        $gerador = new ControllerGerador($software, $diretorio);
        $gerador->gerarCodigo();
    }
    public function __construct(Software $software, $diretorio){
        $this->software = $software;
        $this->diretorio = $diretorio;
    }

    public function gerarCodigo(){
        foreach ($this->software->getObjetos() as $objeto){
            $this->geraControllers($objeto);
        }
        $this->criarArquivos();
        
    }
    private function criarArquivos(){
        
        $caminho = $this->diretorio.'/AppWebPHP/src/classes/controller';
        if(!file_exists($caminho)) {
            mkdir($caminho, 0777, true);
        }
        
        foreach ($this->listaDeArquivos as $path => $codigo) {
            if (file_exists($path)) {
                unlink($path);
            }
            $file = fopen($path, "w+");
            fwrite($file, stripslashes($codigo));
            fclose($file);
        }
    }
   
    private function construct(Objeto $objeto){
        $codigo = '

	public function __construct(){
		$this->dao = new ' . ucfirst($objeto->getNome()) . 'DAO();
		$this->view = new ' . ucfirst($objeto->getNome()). 'View();
	}

';
        return $codigo;
    }
    private function addAjax(Objeto $objeto){
        $codigo = '';
        $nomeDoObjeto = lcfirst($objeto->getNome());
        $nomeDoObjetoMa = ucfirst($objeto->getNome());
        
        $atributosComuns = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }
            else if($atributo->isObjeto()){
                $atributosObjetos[] = $atributo;
                
            }
        }
        
        $codigo .= '
            
	public function addAjax() {
            
        if(!isset($_POST[\'enviar_' . $objeto->getNomeSnakeCase() . '\'])){';
            $codigo .= '
            return;    
        }
        
		    
		
		if (! ( ';
        $i = 0;
        $numDeComunsSemPK = 0;
        $issetList = array();
        foreach ($atributosComuns as $atributo) {
            $i ++;
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            $numDeComunsSemPK++;
            $issetList[] = 'isset ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] )';
            
        }
        $codigo .= implode(' && ', $issetList);
        $i = 0;
        foreach($atributosObjetos as $atributoObjeto){
            foreach($this->software->getObjetos() as $objeto3){
                if($atributoObjeto->getTipo() == $objeto3->getNome())
                {
                    foreach($objeto3->getAtributos() as $atributo2){
                        if($atributo2->getIndice() == Atributo::INDICE_PRIMARY){
                            
                            if($numDeComunsSemPK > 0 && $i == 0){
                                $codigo .= ' && ';
                            }else if($i > 0){
                                $codigo .= ' && ';
                            }
                            $i++;
                            $codigo .= ' isset($_POST [\'' . $atributoObjeto->getNomeSnakeCase() . '\'])';
                            break;
                        }
                    }
                    break;
                }
            }
        }
        
        $codigo .= ')) {
			echo \':incompleto\';
			return;
		}';
        
        $codigo .= '
            
		$' . $nomeDoObjeto . ' = new ' . $nomeDoObjetoMa . ' ();';
        foreach ($atributosComuns as $atributo) {
            
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            $codigo .= '
		$' . $nomeDoObjeto . '->set' . ucfirst($atributo->getNome()) . ' ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] );';
        }
        foreach($atributosObjetos as $atributoObjeto){
            foreach($this->software->getObjetos() as $objeto3){
                if($atributoObjeto->getTipo() == $objeto3->getNome())
                {
                    foreach($objeto3->getAtributos() as $atributo2){
                        if($atributo2->getIndice() == Atributo::INDICE_PRIMARY){
                            $codigo .= '
		$' . $nomeDoObjeto . '->get' .ucfirst($atributoObjeto->getNome()) . '()->set'.ucfirst ($atributo2->getNome()).' ( $_POST [\'' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                            break;
                        }
                    }
                    break;
                }
            }
        }
        
        $codigo .= '
            
		if ($this->dao->insert ( $' . $nomeDoObjeto . ' ))
        {
			$id = $this->dao->getConnection()->lastInsertId();
            echo \':sucesso:\'.$id;
            
		} else {
			 echo \':falha\';
		}
	}
            
            
';
        
        return $codigo;
    }
    private function add(Objeto $objeto){
        $codigo = '';
        $nomeDoObjeto = lcfirst($objeto->getNome());
        $nomeDoObjetoMa = ucfirst($objeto->getNome());
        
        $atributosComuns = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }
            else if($atributo->isObjeto()){
                $atributosObjetos[] = $atributo;
                
            }
        }
        
        $codigo .= '

	public function add() {
            
        if(!isset($_POST[\'enviar_' . $objeto->getNomeSnakeCase() . '\'])){';
        $listaParametros = array();
        foreach($atributosObjetos as $atributoObjeto){
            $codigo .= '
            $'.strtolower($atributoObjeto->getTipo()).'Dao = new '.ucfirst ($atributoObjeto->getTipo()).'DAO($this->dao->getConnection());
            $list'.ucfirst ($atributoObjeto->getTipo()).' = $'.strtolower($atributoObjeto->getTipo()).'Dao->fetch();
';
            $listaParametros[] = '$list'.ucfirst ($atributoObjeto->getTipo());
            
            
        }
        $codigo .= '
            $this->view->showInsertForm(';
        
        $codigo .= implode(', ', $listaParametros);
        $codigo .= ');';
        $codigo .= '
		    return;
		}
		if (! ( ';
        $i = 0;
        $numDeComunsSemPK = 0;
        $issetLista = array();
        foreach ($atributosComuns as $atributo) {
            $i ++;
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            $numDeComunsSemPK++;
            $issetLista[] = 'isset ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] )';
            
        }
        $codigo .= implode(' && ', $issetLista);
        $i = 0;
        foreach($atributosObjetos as $atributoObjeto){
            foreach($this->software->getObjetos() as $objeto3){
                if($atributoObjeto->getTipo() == $objeto3->getNome())
                {
                    foreach($objeto3->getAtributos() as $atributo2){
                        if($atributo2->getIndice() == Atributo::INDICE_PRIMARY){
                            
                            if($numDeComunsSemPK > 0 && $i == 0){
                                $codigo .= ' && ';
                            }else if($i > 0){
                                $codigo .= ' && ';
                            }
                            $i++;
                            $codigo .= ' isset($_POST [\'' . $atributoObjeto->getNomeSnakeCase() . '\'])';
                            break;
                        }
                    }
                    break;
                }
            }
        }
        
        $codigo .= ')) {
			echo \'
                <div class="alert alert-danger" role="alert">
                    Failed to register. Some field must be missing. 
                </div>

                \';
			return;
		}';
        
        $codigo .= '
            
		$' . $nomeDoObjeto . ' = new ' . $nomeDoObjetoMa . ' ();';
        foreach ($atributosComuns as $atributo) {
            
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            $codigo .= '
		$' . $nomeDoObjeto . '->set' . ucfirst($atributo->getNome()) . ' ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] );';
        }
        foreach($atributosObjetos as $atributoObjeto){
            foreach($this->software->getObjetos() as $objeto3){
                if($atributoObjeto->getTipo() == $objeto3->getNome())
                {
                    foreach($objeto3->getAtributos() as $atributo2){
                        if($atributo2->getIndice() == Atributo::INDICE_PRIMARY){
                            $codigo .= '
		$' . $nomeDoObjeto . '->get' .ucfirst($atributoObjeto->getNome()) . '()->set'.ucfirst ($atributo2->getNome()).' ( $_POST [\'' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                            break;
                        }
                    }
                    break;
                }
            }
        }
        
        $codigo .= '
            
		if ($this->dao->insert ( $' . $nomeDoObjeto . ' ))
        {
			echo \'

<div class="alert alert-success" role="alert">
  Sucesso ao inserir '.$objeto->getNomeTextual().'
</div>

\';
		} else {
			echo \'

<div class="alert alert-danger" role="alert">
  Falha ao tentar Inserir '.$objeto->getNomeTextual().'
</div>

\';
		}
        echo \'<META HTTP-EQUIV="REFRESH" CONTENT="3; URL=index.php?pagina=' . $objeto->getNomeSnakeCase() . '">\';
	}


';
        
        return $codigo;
    }
    private function delete(Objeto $objeto) : string
    {
        $codigo = '';
        $atributoPrimaryKey = null;
        foreach($objeto->getAtributos() as $atributo){
            if($atributo->isPrimary()){
                $atributoPrimaryKey = $atributo;
            }
        }
        if($atributoPrimaryKey == null){
            $codigo .= '
        //Object without PrimaryKey has no implementation of the delete method.
        public function delete(){}

';
            return $codigo;
        }
        
        $codigo .= '
    public function delete(){
	    if(!isset($_GET[\'delete\'])){
	        return;
	    }
        $selected = new '.ucfirst($objeto->getNome()).'();
	    $selected->set'.ucfirst ($atributoPrimaryKey->getNome()).'($_GET[\'delete\']);
        if(!isset($_POST[\'delete_' . $objeto->getNomeSnakeCase() . '\'])){
            $this->view->confirmDelete($selected);
            return;
        }
        if($this->dao->delete($selected))
        {
			echo \'

<div class="alert alert-success" role="alert">
  Sucesso ao excluir '.$objeto->getNomeTextual().'
</div>

\';
		} else {
			echo \'

<div class="alert alert-danger" role="alert">
  Falha ao tentar excluir '.$objeto->getNomeTextual().'
</div>

\';
		}
    	echo \'<META HTTP-EQUIV="REFRESH" CONTENT="2; URL=index.php?pagina=' . $objeto->getNomeSnakeCase() . '">\';
    }

';
        return $codigo;
        
    }
    private function list() : string {
        $codigo = '

	public function list() 
    {
		$list = $this->dao->fetch();
		$this->view->showList($list);
	}
';
        return $codigo;
    }
    private function edit(Objeto $objeto) : string {
        $atributosComuns = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }else if($atributo->isObjeto()){
                $atributosObjetos[] = $atributo;
                
            }
        }
        
        
        $codigo = '';
        $codigo .= '
            
    public function edit(){
	    if(!isset($_GET[\'edit\'])){
	        return;
	    }
        $selected = new '.ucfirst($objeto->getNome()).'();
	    $selected->set'.ucfirst ($objeto->getAtributos()[0]->getNome()).'($_GET[\'edit\']);
	    $this->dao->fillBy'.ucfirst ($objeto->getAtributos()[0]->getNome()).'($selected);
	        
        if(!isset($_POST[\'edit_' . $objeto->getNomeSnakeCase() . '\'])){';
        $listaParametros = array();
        foreach($atributosObjetos as $atributoObjeto){
            $codigo .= '
            $'.strtolower($atributoObjeto->getTipo()).'Dao = new '.ucfirst ($atributoObjeto->getTipo()).'DAO($this->dao->getConnection());
            $list'.ucfirst ($atributoObjeto->getTipo()).' = $'.strtolower($atributoObjeto->getTipo()).'Dao->fetch();
';
            $listaParametros[] = '$list'.ucfirst ($atributoObjeto->getTipo());
            
            
        }
        $listaParametros[] = '$selected';
        $codigo .= '
            $this->view->showEditForm(';
        
        $codigo .= implode(', ', $listaParametros);
        
        $codigo .= ');
            return;
        }
            
		if (! ( ';
        $campos = array();
        foreach ($atributosComuns as $atributo) {    
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }else{
                $campos[] = 'isset ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] )';
            }
        }
        
        foreach($atributosObjetos as $atributoObjeto){
            foreach($this->software->getObjetos() as $objeto3){
                if($atributoObjeto->getTipo() == $objeto3->getNome())
                {
                    foreach($objeto3->getAtributos() as $atributo2){
                        if($atributo2->getIndice() == Atributo::INDICE_PRIMARY){

                            $campos[] = ' isset($_POST [\'' . $atributoObjeto->getNomeSnakeCase() . '\'])';
                            break;
                        }
                    }
                    break;
                }
            }
        }
        $codigo .= implode(" && ", $campos);
        $codigo .= ')) {
			echo "Incompleto";
			return;
		}
';
        foreach ($atributosComuns as $atributo) 
        {
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            $codigo .= '
		$selected->set' . ucfirst($atributo->getNome()). ' ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] );';
        }
        
        $codigo .= '
            
		if ($this->dao->update ($selected ))
        {
			echo \'

<div class="alert alert-success" role="alert">
  Sucesso 
</div>

\';
		} else {
			echo \'

<div class="alert alert-danger" role="alert">
  Falha 
</div>

\';
		}
        echo \'<META HTTP-EQUIV="REFRESH" CONTENT="3; URL=index.php?pagina=' . $objeto->getNomeSnakeCase() . '">\';
            
    }
        ';
        return $codigo;
    }
    private function geraControllers(Objeto $objeto)
    {
        $codigo = '';
        $nomeDoObjeto = lcfirst($objeto->getNome());
        $nomeDoObjetoMa = ucfirst($objeto->getNome());
        
        $atributosComuns = array();
        $atributosNN = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }else if($atributo->isArrayNN()){
                $atributosNN[] = $atributo;
            }
            else if($atributo->isObjeto()){
                $atributosObjetos[] = $atributo;
                
            }
        }
        
        
        $codigo = '<?php
            
/**
 * Classe feita para manipulação do objeto ' . $nomeDoObjetoMa . '
 * feita automaticamente com programa gerador de software inventado por
 * @author Jefferson Uchôa Ponte <j.pontee@gmail.com>
 */



class ' . ucfirst($objeto->getNome()) . 'Controller {

	protected  $view;
    protected $dao;';
        $codigo .= $this->construct($objeto);
        $codigo .= $this->delete($objeto);
        $codigo .= $this->list();
        $codigo .= $this->add($objeto);
        $codigo .= $this->addAjax($objeto);
        $codigo .= $this->edit($objeto);
        
        $codigo .= '
    
    public function main(){
        
        if (isset($_GET[\'selecionar\'])){
            echo \'<div class="row justify-content-center">\';
                $this->selecionar();
            echo \'</div>\';
            return;
        }
        echo \'
		<div class="row justify-content-center">\';
        echo \'<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">\';
        
        if(isset($_GET[\'edit\'])){
            $this->edit();
        }else if(isset($_GET[\'delete\'])){
            $this->delete();
	    }else{
            $this->add();
        }
        $this->list();
        
        echo \'</div>\';
        echo \'</div>\';
            
    }
    public function mainAjax(){

        $this->addAjax();
        
            
    }
    public function mainREST($iniApiFile = API_INI)
    {
        
        $config = parse_ini_file ( $iniApiFile );
        $user = $config [\'user\'];
        $password = $config [\'password\'];
        
        if(!isset($_SERVER[\'PHP_AUTH_USER\'])){
            header("WWW-Authenticate: Basic realm=\\\\"Private Area\\\\" ");
            header("HTTP/1.0 401 Unauthorized");
            echo \'{"erro":[{"status":"error","message":"Authentication failed"}]}\';
            return;
        }
        if($_SERVER[\'PHP_AUTH_USER\'] == $user && ($_SERVER[\'PHP_AUTH_PW\'] == $password)){
            header(\'Content-type: application/json\');
            
            $this->restGET();
            //$controller->restPOST();
            //$controller->restPUT();
            $this->resDELETE();
        }else{
            header("WWW-Authenticate: Basic realm=\\\\"Private Area\\\\" ");
            header("HTTP/1.0 401 Unauthorized");
            echo \'{"erro":[{"status":"error","message":"Authentication failed"}]}\';
        }

    }';
        

        $codigo .= '

    public function selecionar(){
	    if(!isset($_GET[\'selecionar\'])){
	        return;
	    }
        $selected = new '.$nomeDoObjetoMa.'();
	    $selected->set'.ucfirst ($objeto->getAtributos()[0]->getNome()).'($_GET[\'selecionar\']);
	        
        $this->dao->fillBy'.ucfirst ($objeto->getAtributos()[0]->getNome()).'($selected);

        echo \'<div class="col-xl-7 col-lg-7 col-md-12 col-sm-12">\';
	    $this->view->showSingle($selected);
        echo \'</div>\';
            
';
        
        foreach($atributosNN as $atributoNN){
            $codigo .= '
        $this->dao->fetch'.ucfirst($atributoNN->getNome()).'($selected);
        $'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'Dao = new '.ucfirst(explode(" ", $atributoNN->getTipo())[2]).'DAO($this->dao->getConnection());
        $list = $'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'Dao->fetch();
            
        echo \'<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">\';
        $this->view->show'.ucfirst($atributoNN->getNome()).'($selected);
        echo \'</div>\';
            
            
        if(!isset($_POST[\'add'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'\']) && !isset($_GET[\'remover'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'\'])){
            echo \'<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">\';
            $this->view->add'.ucfirst(explode(" ", $atributoNN->getTipo())[2]).'($list);
            echo \'</div>\';
        }else if(isset($_POST[\'add'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'\'])){
            $'.strtolower(explode(" ", $atributoNN->getTipo())[2]).' = new '.ucfirst(explode(" ", $atributoNN->getTipo())[2]).'();
            $'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'->setId($_POST[\'add'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'\']);
            if($this->dao->insert'.ucfirst(explode(" ", $atributoNN->getTipo())[2]).'($selected, $'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'))
            {
			echo \'

<div class="alert alert-success" role="alert">
  Sucesso 
</div>

\';
		} else {
			echo \'

<div class="alert alert-danger" role="alert">
  Falha 
</div>

\';
		    }
            echo \'<META HTTP-EQUIV="REFRESH" CONTENT="2; URL=index.php?pagina='.$objeto->getNomeSnakeCase().'&selecionar=\'.$selected->get'.ucfirst ($objeto->getAtributos()[0]->getNome()).'().\'">\';
            return;
        }else  if(isset($_GET[\'remover'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'\'])){
            
            $'.strtolower(explode(" ", $atributoNN->getTipo())[2]).' = new '.ucfirst(explode(" ", $atributoNN->getTipo())[2]).'();
            $'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'->setId($_GET[\'remover'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'\']);
            if($this->dao->remover'.ucfirst(explode(" ", $atributoNN->getTipo())[2]).'($selected, $'.strtolower(explode(" ", $atributoNN->getTipo())[2]).'))
            {
		      echo \'

<div class="alert alert-success" role="alert">
  Sucesso 
</div>

\';
		} else {
			echo \'

<div class="alert alert-danger" role="alert">
  Falha 
</div>

\';
		      }
            echo \'<META HTTP-EQUIV="REFRESH" CONTENT="2; URL=index.php?pagina='.$objeto->getNomeSnakeCase().'&selecionar=\'.$selected->get'.ucfirst ($objeto->getAtributos()[0]->getNome()).'().\'">\';
            return;
        }
                
                
        ';
            
            
        }
        $codigo .= '
            
    }';
        $codigo .= '
	public function restGET()
    {

        if ($_SERVER[\'REQUEST_METHOD\'] != \'GET\') {
            return;
        }

        if(!isset($_REQUEST[\'api\'])){
            return;
        }
        $url = explode("/", $_REQUEST[\'api\']);
        if (count($url) == 0 || $url[0] == "") {
            return;
        }
        if ($url[1] != \''.$nomeDoObjeto.'\') {
            return;
        }

        if(isset($url[2])){
            $parametro = $url[2];
            $id = intval($parametro);
            $selected = new '.ucfirst($objeto->getNome()).'();
            $selected->setId($id);
            $selected = $this->dao->preenchePorId($selected);
            if ($selected == null) {
                echo "{}";
                return;
            }

            $selected = array(';
        $i = 0;
        foreach ($atributosComuns as $atributo) {
            $i ++;
            $nomeDoAtributoMA = strtoupper(substr($atributo->getNome(), 0, 1)) . substr($atributo->getNome(), 1, 100);
            $codigo .= '
					\'' . $atributo->getNome() . '\' => $selected->get' . $nomeDoAtributoMA . ' ()';
            if ($i != count($objeto->getAtributos())) {
                $codigo .= ', ';
            }
        }
        
        $codigo .= '
            
            
			);
            echo json_encode($selected);
            return;
        }        
        $list = $this->dao->fetch();
        $listagem = array();
        foreach ( $list as $linha ) {
			$listagem [\'list\'] [] = array (';
        $i = 0;
        foreach ($atributosComuns as $atributo) {
            $i ++;
            $nomeDoAtributoMA = ucfirst($atributo->getNome());
            $codigo .= '
					\'' . $atributo->getNome() . '\' => $linha->get' . $nomeDoAtributoMA . ' ()';
            if ($i != count($objeto->getAtributos())) {
                $codigo .= ', ';
            }
        }
        
        $codigo .= '
            
            
			);
		}
		echo json_encode ( $listagem );
    
		
		
		
		
	}

    public function resDELETE()
    {
        if ($_SERVER[\'REQUEST_METHOD\'] != \'DELETE\') {
            return;
        }
        $path = explode(\'/\', $_GET[\'api\']);
        $parametro = "";
        if (count($path) < 2) {
            return;
        }
        $parametro = $path[1];
        if ($parametro == "") {
            return;
        }
    
        $id = intval($parametro);
        $selected = new '.ucfirst($objeto->getNome()).'();
        $selected->setId($id);
        $selected = $this->dao->pesquisaPorId($selected);
        if ($selected == null) {
            echo "{}";
            return;
        }

        if($this->dao->excluir($selected))
        {
            echo "{}";
            return;
        }
        
        echo "Erro.";
        
    }
    public function restPUT()
    {
        if ($_SERVER[\'REQUEST_METHOD\'] != \'PUT\') {
            return;
        }

        if (! array_key_exists(\'api\', $_GET)) {
            return;
        }
        $path = explode(\'/\', $_GET[\'api\']);
        if (count($path) == 0 || $path[0] == "") {
            echo \'Error. Path missing.\';
            return;
        }
        
        $param1 = "";
        if (count($path) > 1) {
            $parametro = $path[1];
        }

        if ($path[0] != \'info\') {
            return;
        }

        if ($param1 == "") {
            echo \'error\';
            return;
        }

        $id = intval($parametro);
        $selected = new '.ucfirst($objeto->getNome()).'();
        $selected->setId($id);
        $selected = $this->dao->pesquisaPorId($selected);

        if ($selected == null) {
            return;
        }

        $body = file_get_contents(\'php://input\');
        $jsonBody = json_decode($body, true);
        
        ';
        foreach($objeto->getAtributos() as $atributo){
            if($atributo->tipoListado()){
                $codigo .= '
        if (isset($jsonBody[\''.$atributo->getNomeSnakeCase().'\'])) {
            $selected->set'.ucfirst($atributo->getNome()).'($jsonBody[\''.$atributo->getNomeSnakeCase().'\']);
        }
                    
';
            }
        }
  
        $codigo .= '
        if ($this->dao->update($selected)) 
                {
			echo \'

<div class="alert alert-success" role="alert">
  Sucesso 
</div>

\';
		} else {
			echo \'

<div class="alert alert-danger" role="alert">
  Falha 
</div>

\';
		}
    }

    public function restPOST()
    {
        if ($_SERVER[\'REQUEST_METHOD\'] != \'POST\') {
            return;
        }
        if (! array_key_exists(\'path\', $_GET)) {
            echo \'Error. Path missing.\';
            return;
        }

        $path = explode(\'/\', $_GET[\'path\']);

        if (count($path) == 0 || $path[0] == "") {
            echo \'Error. Path missing.\';
            return;
        }

        $body = file_get_contents(\'php://input\');
        $jsonBody = json_decode($body, true);

        if (! ( ';
        $i = 0;
        $numDeComunsSemPK = 0;
        $listIsset = array();
        foreach ($atributosComuns as $atributo) {
            $i ++;
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            $numDeComunsSemPK++;
            
            $listIsset[] = 'isset ( $jsonBody [\'' . $atributo->getNome() . '\'] )';

        }
        $codigo .= implode(" && ", $listIsset);
        $i = 0;
        foreach($atributosObjetos as $atributoObjeto){
            foreach($this->software->getObjetos() as $objeto3){
                if($atributoObjeto->getTipo() == $objeto3->getNome())
                {
                    foreach($objeto3->getAtributos() as $atributo2){
                        if($atributo2->getIndice() == Atributo::INDICE_PRIMARY){
                            
                            if($numDeComunsSemPK > 0 && $i == 0){
                                $codigo .= ' && ';
                            }else if($i > 0){
                                $codigo .= ' && ';
                            }
                            $i++;
                            $codigo .= ' isset($_POST [\'' . $atributoObjeto->getNomeSnakeCase() . '\'])';
                            break;
                        }
                    }
                    break;
                }
            }
        }
        
        $codigo .= ')) {
			echo "Incompleto";
			return;
		}

        $adicionado = new '.ucfirst($objeto->getNome()).'();';
        foreach($objeto->getAtributos() as $atributo){
            if($atributo->tipoListado()){
                
                $codigo .= '
        $adicionado->set'.ucfirst($atributo->getNome()).'($jsonBody[\''.$atributo->getNomeSnakeCase().'\']);
';
            }
        }
  
        $codigo .= '
        if ($this->dao->inserir($adicionado)) 
                {
			echo \'

<div class="alert alert-success" role="alert">
  Sucesso 
</div>

\';
		} else {
			echo \'

<div class="alert alert-danger" role="alert">
  Falha 
</div>

\';
		}
    }            
            
		';
        
        $codigo .= '
}
?>';
        $caminho = $this->diretorio.'/AppWebPHP/src/classes/controller/'.ucfirst($objeto->getNome()).'Controller.php';
        $this->listaDeArquivos[$caminho] = $codigo;
    }
    
}


?>