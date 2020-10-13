<?php 


namespace GetCrudByUML\gerador\javaDesktopEscritor\crudMvcDao;
use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Atributo;
use GetCrudByUML\model\Software;
use GetCrudByUML\gerador\sqlGerador\SQLGerador;

class DAOJavaGerador{
    
    
    private $software;
    
    private $listaDeArquivos;
    
    private $diretorio;
    
    public static function main(Software $software, $diretorio)
    {
        $gerador = new DAOJavaGerador($software, $diretorio);
        $gerador->geraCodigo();
    }
    
    public function __construct(Software $software, $diretorio)
    {
        $this->software = $software;
        $this->diretorio = $diretorio;
    }
    
    private function geraCodigo()
    {
        $this->geraDAOGeral();
        foreach($this->software->getObjetos() as $objeto){
            $this->geraDAOs($objeto);
        }
        
        $this->criarArquivos();
        
    }
    private function criarArquivos(){
        
        $caminho = $this->diretorio.'/AppDesktopJava/'.$this->software->getNomeSimples().'/src/main/java/com/'.strtolower($this->software->getNomeSimples()).'/dao/';
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
    
    private function geraDAOGeral(){
        $codigo = '';
        $codigo .= '
package com.'.strtolower($this->software->getNome()).'.dao;
    
    
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;
    
/**
 * Faz conexão com banco de dados e gerencia persistências.
 * @author Jefferson Uchôa Ponte
 *
 */
public class DAO {
    
    
	/**
	 * Sistema gerenciador de banco de dados.
	 */
	private String sgdb;
    
	/**
	 * Conexão com banco.
	 */
	private Connection connection;
    
    
	/**
	 * Constroi objeto DAO com conexão com banco de dados.
	 */
	public DAO() {
		conneconnection	}
    
	/**
	 * Constroi objeto DAO com conexão com banco de dados.
	 */
	public DAO(Connection conexao) {
		this.conexao = conexao;
	}
    
    
	/**
	 * Faz uma conexão com banco de dados de acordo com as informações do arquivo de configuração.
	 */
	public void connect() {
		this.conexao = null;
		try {
			Properties config = new Properties();
			FileInputStream file;
			file = new FileInputStream(ARQUIVO_CONFIGURACAO);
			config.load(file);
			String sgdb, host, porta, bdNome, usuario, senha;
    
			sgdb = config.getProperty("sgdb");
			host = config.getProperty("host");
			porta = config.getProperty("porta");
			bdNome = config.getProperty("bd_nome");
			usuario = config.getProperty("usuario");
			senha = config.getProperty("senha");
    
			file.close();
			if (sgdb.equals("postgres")) {
				Class.forName(DRIVER_POSTGRES);
				this.connection = DriverManager.getConnection(JDBC_BANCO_POSTGRES+ "//" + host + "/" + bdNome, usuario, senha);
    
			} else if (sgdb.equals("sqlite")) {
				Class.forName(DRIVER_SQLITE);
				this.connection = DriverManager.getConnection(JDBC_BANCO_SQLITE+bdNome);
			} else if (sgdb.equals("mysql")) {
				Class.forName(DRIVER_MYSQL);
				this.connection = DriverManager.getConnection(JDBC_BANCO_MYSQL + "//" + host +":"+ porta + "/" + bdNome, usuario, senha);
			}
    
		} catch (ClassNotFoundException e1) {
			e1.printStackTrace();
		} catch (SQLException e) {
			e.printStackTrace();
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}
	}
    
	/**
	 * Retorna a conexão com banco de dados.
	 * @return
	 */
	public Connection getConnection() {
		return connection;
	}
	/**
	 * Atribui a conexão com banco de dados.
	 * @param connection
	 */
	public void setConnection(Connection connection) {
		this.connection = connection;
	}
    
    
    
	/**
	 * @return the sgdb
	 */
	public String getSgdb() {
		return sgdb;
	}
    
	/**
	 * @param sgdb
	 */
	public void setSgdb(String sgdb) {
		this.sgdb = sgdb;
	}
    
	/**
	 * Arquivo de configuração do banco de dados.
	 */
	public static final String ARQUIVO_CONFIGURACAO = "../../'. strtolower($this->software->getNome()) . '_bd.ini";
	    
	/**
	 * Drive jdbc para Sqlite.
	 */
	public static final String DRIVER_SQLITE = "org.sqlite.JDBC";
	/**
	 * Banco de dados squlite
	 */
	    
	public static final String JDBC_BANCO_SQLITE = "jdbc:sqlite:";
	    
	/**
	 * JDBC para postgres.
	 */
	public static final String JDBC_BANCO_POSTGRES = "jdbc:postgresql:";
	/**
	 * Driver JDBC postgres
	 */
	public static final String DRIVER_POSTGRES = "org.postgresql.Driver";
	/**
	 * JDBC Mysql
	 */
	public static final String JDBC_BANCO_MYSQL = "jdbc:mysql:";
	/**
	 * Driver JDBC Mysql
	 */
	public static final String DRIVER_MYSQL = "com.mysql.jdbc.Driver";
	    
}';
        
        $caminho = $this->diretorio.'/AppDesktopJava/'.$this->software->getNomeSimples().'/src/main/java/com/'.strtolower($this->software->getNomeSimples()).'/dao/DAO.java';
        $this->listaDeArquivos[$caminho] = $codigo;
        return $this->listaDeArquivos;
    }
    
    private function geraDAOs(Objeto $objeto)
    {
        $codigo = '';
        
        $nomeDoObjeto = strtolower($objeto->getNome());
        $nomeDoObjetoMA = strtoupper(substr($objeto->getNome(), 0, 1)) . substr($objeto->getNome(), 1, 100);
        $atributosComuns = array();
        $atributosNN = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if(substr($atributo->getTipo(),0,6) == 'Array '){
                if(explode(' ', $atributo->getTipo())[1]  == 'n:n'){
                    $atributosNN[] = $atributo;
                }
            }else if($atributo->getTipo() == Atributo::TIPO_INT || $atributo->getTipo() == Atributo::TIPO_STRING || $atributo->getTipo() == Atributo::TIPO_FLOAT)
            {
                $atributosComuns[] = $atributo;
            }else{
                $atributosObjetos[] = $atributo;
            }
        }
        
        
        $codigo = '
package com.'.strtolower($this->software->getNome()).'.dao;
    
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
    
import com.'.strtolower($this->software->getNome()).'.model.*;
    
/**
 * Classe feita para manipulação do objeto '.ucfirst($objeto->getNome()).'
 * feita automaticamente com programa gerador de software inventado por
 * @author Jefferson Uchôa Ponte
 *
 *
 */
public class ' . ucfirst($objeto->getNome()) . 'DAO extends DAO{';
        
        
        $codigo .= $this->update($objeto);
        $codigo .= $this->fetch($objeto);
        $codigo .= $this->insert($objeto);
        
        $codigo .= '
            

	public boolean excluir(' . ucfirst($objeto->getNome()). ' ' . strtolower($objeto->getNome()). '){
		String sql = "DELETE FROM ' . $nomeDoObjeto . ' WHERE ' . $objeto->getAtributos()[0]->getNome() . ' = ?";
		try{
        	PreparedStatement stmt = this.getConnection().prepareStatement(sql);
        	stmt.setInt(1, '.$nomeDoObjeto.'.get' . ucfirst($objeto->getAtributos()[0]->getNome()). '());
        	stmt.execute();
        	stmt.close();
        	return true;
    	} catch (SQLException e) {
			e.printStackTrace();
			return false;
		}
        	    
	}
        	    
        	    
';
        
        foreach ($atributosComuns as $atributo) {
            
            $nomeDoAtributoMA = strtoupper(substr($atributo->getNome(), 0, 1)) . substr($atributo->getNome(), 1, 100);
            
            
            $codigo .= '
                
    public ArrayList<'.$nomeDoObjetoMA.'> pesquisaPor'.$nomeDoAtributoMA.'(' . $nomeDoObjetoMA . ' ' . $nomeDoObjeto . ') {
        ArrayList<'.$nomeDoObjetoMA.'>lista = new ArrayList<'.$nomeDoObjetoMA.'>();';
            
            $id = $atributo->getNome();
            $codigo .= '
	    String sql = "';
            $sqlGerador = new SQLGerador($this->software);
            $codigo .= $sqlGerador->getSQLSelect($objeto);
            $codigo .= '"';
            if($atributo->getTipo() == Atributo::TIPO_STRING){
                $codigo .=  '
                +" WHERE '.strtolower($objeto->getNome()).'.'.$id.' like \'%?%\'';
                
            }else if($atributo->getTipo() == Atributo::TIPO_INT || $atributo->getTipo() == Atributo::TIPO_FLOAT){
                $codigo .= '
                +" WHERE '.strtolower($objeto->getNome()).'.'.$id.' = ?';
            }
            $codigo .= ' LIMIT 1000";
    		PreparedStatement ps;
    		try {
    			ps = this.getConnection().prepareStatement(sql);';
            
            if($atributo->getTipo() == Atributo::TIPO_INT){
                $codigo .= '
                ps.setInt(1, '.$nomeDoObjeto.'.get'.$nomeDoAtributoMA.'());';
                
            }else if($atributo->getTipo() == Atributo::TIPO_FLOAT){
                $codigo .= '
                ps.setFloat(1, '.$nomeDoObjeto.'.get'.$nomeDoAtributoMA.'());';
                
            }
            else if($atributo->getTipo() == Atributo::TIPO_STRING){
                $codigo .= '
                ps.setString(1, '.$nomeDoObjeto.'.get'.$nomeDoAtributoMA.'());';
                
            }
            
            
            $codigo .= '
    			ResultSet resultSet = ps.executeQuery();
    			while(resultSet.next()){
                    ' . $nomeDoObjeto . ' = new ' . $nomeDoObjetoMA . '();';
            
            foreach ($atributosComuns as $atributo2) {
                
                $nomeDoAtributoMA = strtoupper(substr($atributo2->getNome(), 0, 1)) . substr($atributo2->getNome(), 1, 100);
                if($atributo2->getTipo() == Atributo::TIPO_INT){
                    $codigo .= '
                    '.$nomeDoObjeto.'.set'.$nomeDoAtributoMA.'( resultSet.getInt("'.$atributo2->getNome().'"));';
                }else if($atributo2->getTipo() == Atributo::TIPO_FLOAT)
                {
                    $codigo .= '
                    '.$nomeDoObjeto.'.set'.$nomeDoAtributoMA.'( resultSet.getFloat("'.$atributo2->getNome().'"));';
                }
                else if($atributo2->getTipo() == Atributo::TIPO_STRING)
                {
                    $codigo .= '
	               '.$nomeDoObjeto.'.set'.$nomeDoAtributoMA.'( resultSet.getString("'.$atributo2->getNome().'"));';
                }
                
                
            }
            foreach($atributosObjetos as $atributoObjeto){
                
                foreach($this->software->getObjetos() as $objeto2){
                    if($objeto2->getNome() == $atributoObjeto->getTipo()){
                        foreach($objeto2->getAtributos() as $atributo3){
                            
                            if($atributo3->getTipo() == Atributo::TIPO_INT){
                                $codigo .= '
                    ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getInt("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '"));';
                                
                            }else if($atributo3->getTipo() == Atributo::TIPO_FLOAT){
                                $codigo .= '
                    ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getFloat("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '"));';
                                
                            }
                            else if($atributo3->getTipo() == Atributo::TIPO_STRING){
                                $codigo .= '
                    ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getString("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '"));';
                                
                            }
                            
                            
                        }
                        break;
                    }
                }
                
            }
            $codigo .= '
                
    				lista.add(' . $nomeDoObjeto . ');
    			}
                return lista;
    		} catch (SQLException e) {
    			// TODO Auto-generated catch block
    			e.printStackTrace();
                return null;
    		}
    				    
	}';
            
            
        }
        
        foreach($atributosNN as $atributo){
            $codigo .= '
    public '.ucfirst($objeto->getNome()).' buscar'.ucfirst($atributo->getNome()).'('.ucfirst($objeto->getNome()).' '.strtolower($objeto->getNome()).')
    {
        int id = '.strtolower($objeto->getNome()).'.getId();
        String sql = "SELECT ';
            
            foreach($this->software->getObjetos() as $obj){
                if(strtolower($obj->getNome()) == strtolower(explode(' ', $atributo->getTipo())[2]))
                {
                    $i = 0;
                    foreach($obj->getAtributos() as $atr){
                        
                        $i++;
                        $codigo .= strtolower($atributo->getTipoDeArray().'.'.$atr->getNome().' as '. $atr->getNome().'_'.$atributo->getTipoDeArray());
                        if($i < count($obj->getAtributos())){
                            $codigo .= ', ';
                        }
                    }
                }
            }
            $codigo .= ' FROM '.strtolower($objeto->getNome()).'_'.strtolower(explode(' ', $atributo->getTipo())[2]).' INNER JOIN '.strtolower(explode(' ', $atributo->getTipo())[2]).' ON  '.strtolower($objeto->getNome()).'_'.strtolower(explode(' ', $atributo->getTipo())[2]).'.id'.strtolower(explode(' ', $atributo->getTipo())[2]).' = '.strtolower(explode(' ', $atributo->getTipo())[2]).'.id WHERE '.strtolower($objeto->getNome()).'_'.strtolower(explode(' ', $atributo->getTipo())[2]).'.id'.strtolower($objeto->getNome()).' = "+id;';
            
            $codigo .= '
        PreparedStatement ps;
        try {
            ps = this.getConnection().prepareStatement(sql);
			ResultSet resultSet = ps.executeQuery();
    		while(resultSet.next()){
                '.ucfirst(explode(' ', $atributo->getTipo())[2]).' '.strtolower(explode(' ', $atributo->getTipo())[2]).' = new '.ucfirst(explode(' ', $atributo->getTipo())[2]).'();';
            foreach($this->software->getObjetos() as $obj){
                if(strtolower($obj->getNome()) == strtolower(explode(' ', $atributo->getTipo())[2]))
                {
                    foreach($obj->getAtributos() as $atr){
                        
                        $nomeDoAtributoMA = ucfirst($atr->getNome());
                        
                        if($atr->tipoListado())
                        {
                            
                            if($atr->getTipo() == Atributo::TIPO_INT)
                            {
                                $codigo .= '
                '.strtolower(explode(' ', $atributo->getTipo())[2]).'.set'.$nomeDoAtributoMA.'( resultSet.getInt("'. $atr->getNome().'_'.strtolower($atributo->getTipoDeArray()).'"));';
                            }
                            else if($atr->getTipo() == Atributo::TIPO_FLOAT)
                            {
                                $codigo .= '
                '.strtolower(explode(' ', $atributo->getTipo())[2]).'.set'.$nomeDoAtributoMA.'( resultSet.getFloat("'. $atr->getNome().'_'.strtolower($atributo->getTipoDeArray()).'"));';
                            }
                            else if($atr->getTipo() == Atributo::TIPO_STRING)
                            {
                                
                                $codigo .= '
                '.strtolower(explode(' ', $atributo->getTipo())[2]).'.set'.$nomeDoAtributoMA.'( resultSet.getString("'. $atr->getNome().'_'.strtolower($atributo->getTipoDeArray()).'"));';
                                
                            }
                            
                        }else if(substr($atr->getTipo(), 0, 6) == 'Array '){
                            
                            $codigo .= '
                '.ucfirst(explode(' ', $atributo->getTipo())[2]).'DAO '.strtolower(explode(' ', $atributo->getTipo())[2]).'Dao = new '.ucfirst(explode(' ', $atributo->getTipo())[2]).'DAO($this->getConnection());
                '.strtolower(explode(' ', $atributo->getTipo())[2]).'Dao.buscar'.ucfirst($atr->getNome()).'($'.strtolower(explode(' ', $atributo->getTipo())[2]).');';
                            //$objetoDao->buscar
                        }
                        
                    }
                    $codigo .= '';
                    break;
                }
            }
            
            $codigo .= '
                '.strtolower($objeto->getNome()).'.add'.ucfirst(explode(' ', $atributo->getTipo())[2]).'('.strtolower(explode(' ', $atributo->getTipo())[2]).');
                    
                    
                    
            }
            return '.strtolower($objeto->getNome()).';
		} catch (SQLException e) {
			e.printStackTrace();
            return null;
		}
    }
';
            
            
            
            
            
            
            
            $codigo .= '
                
	public boolean inserir'.ucfirst(explode(" ", $atributo->getTipo())[2]).'('. $nomeDoObjetoMA . ' ' . $nomeDoObjeto . ', '.ucfirst(explode(" ", $atributo->getTipo())[2]).' '.strtolower(explode(" ", $atributo->getTipo())[2]).'){
        int id'.ucfirst($objeto->getNome()).' =  ' . $nomeDoObjeto.'.getId();
        int id'.ucfirst(explode(' ', $atributo->getTipo())[2]).' = '.strtolower(explode(" ", $atributo->getTipo())[2]).'.getId();
		String sql = "INSERT INTO '.strtolower($objeto->getNome()).'_'.strtolower(explode(' ', $atributo->getTipo())[2]).'(';
            $codigo .= ' id'.strtolower($objeto->getNome()).', id'.strtolower(explode(' ', $atributo->getTipo())[2]).')';
            $codigo .= ' VALUES (?, ?)";';
            $codigo .= '
                
		try {
                
			PreparedStatement ps = this.getConnection().prepareStatement(sql);
            ps.setInt(1, id'.ucfirst($objeto->getNome()).');
            ps.setInt(2, id'.ucfirst(explode(' ', $atributo->getTipo())[2]) . ');
			ps.executeUpdate();
			return true;
		} catch (SQLException e) {
			e.printStackTrace();
			return false;
		}
	}
                
                
                
	public boolean remover'.ucfirst(explode(" ", $atributo->getTipo())[2]).'('. $nomeDoObjetoMA . ' ' . $nomeDoObjeto . ', '.ucfirst(explode(" ", $atributo->getTipo())[2]).' '.strtolower(explode(" ", $atributo->getTipo())[2]).'){
        int id'.ucfirst($objeto->getNome()).' =  ' . $nomeDoObjeto.'.getId();
        int id'.ucfirst(explode(' ', $atributo->getTipo())[2]).' = '.strtolower(explode(" ", $atributo->getTipo())[2]).'.getId();
		String sql = "DELETE FROM  '.strtolower($objeto->getNome()).'_'.strtolower(explode(' ', $atributo->getTipo())[2]).' WHERE ';
            $codigo .= ' id'.strtolower($objeto->getNome()).' = ?';
            $codigo .= ' AND id'.strtolower(explode(' ', $atributo->getTipo())[2]).' = ? ";';
            
            $codigo .= '
                
		try {
                
			PreparedStatement ps = this.getConnection().prepareStatement(sql);
            ps.setInt(1, id'.ucfirst($objeto->getNome()).');
            ps.setInt(2, id'.ucfirst(explode(' ', $atributo->getTipo())[2]) . ');
			ps.executeUpdate();
			return true;
		} catch (SQLException e) {
			e.printStackTrace();
			return false;
		}
	}
                
                
';
        }
        
        $codigo .= '
            
            
}';
        
        
        $caminho = $this->diretorio.'/AppDesktopJava/'.$this->software->getNomeSimples().'/src/main/java/com/'.strtolower($this->software->getNomeSimples()).'/dao/'.ucfirst($objeto->getNome()).'DAO.java';
        $this->listaDeArquivos[$caminho] = $codigo;
        return $codigo;
    }
    private function update(Objeto $objeto){
        $codigo = '';
        $codigo = '';
        $atributosComuns = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->tipoListado()) {
                $atributosComuns[] = $atributo;
            }
        }
        $atributoPrimary = null;
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->isPrimary()) {
                $atributoPrimary = $atributo;
                break;
            }
        }
        if ($atributoPrimary == null) {
            $atributoPrimary = $objeto->getAtributos()[0];
        }
        
        $codigo .= '
            
            
    public boolean update('.ucfirst($objeto->getNome()).' '.lcfirst($objeto->getNome()).')
    {
		PreparedStatement ps;';
        foreach($objeto->getAtributos() as $atributo){
            if($atributo->getIndice() == Atributo::INDICE_PRIMARY){
                $codigo .= '
        int id = '.lcfirst($objeto->getNome()).'.get'.ucfirst ($atributo->getNome()).'();';
                
            }else if($atributo->tipoListado()){
                $codigo .= '
        '.$atributo->getTipoJava().' '.lcfirst($atributo->getNome()).' = '.lcfirst($objeto->getNome()).'.get'.ucfirst($atributo->getNome()).'();';
            }
        }
        $codigo .= '
            
        String sql = "UPDATE '.$objeto->getNomeSnakeCase().'"
                +"SET"
                ';
        $listaAtributo = array();
        foreach ($atributosComuns as $atributo) {
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            if(substr($atributo->getTipo(), 0, 6) == 'Array '){
                continue;
            }
            $listaAtributo[] = $atributo;
        }
        $i = 0;
        foreach ($listaAtributo as $atributo) {
            $i ++;
            $codigo .= '+"'.$atributo->getNomeSnakeCase().' = ?';
            if ($i != count($listaAtributo)) {
                $codigo .= ',"
                ';
            }else{
                $codigo .= '"';
            }
        }
        $codigo .= '
                +"WHERE '.$objeto->getNomeSnakeCase().'.id ="+id+";";';
        
        $codigo .= '
		try {
			ps = this.getConnection().prepareStatement(sql);';
        $i = 1;
        foreach ($listaAtributo as $atributo) {
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            
            if($atributo->getTipo() == Atributo::TIPO_INT){
                $codigo .= '
            ps.setInt('.$i.', ' . $atributo->getNome() . ');';
                
            }else if($atributo->getTipo() == Atributo::TIPO_FLOAT){
                $codigo .= '
            ps.setFloat('.$i.', ' . $atributo->getNome() . ');';
                
            }else if($atributo->getTipo() == Atributo::TIPO_STRING){
                $codigo .= '
            ps.setString('.$i.', ' . $atributo->getNome() . ');';
                
            }else if($atributo->getTipo() == Atributo::TIPO_DATE || $atributo->getTipo() == Atributo::TIPO_DATE_TIME){
                $codigo .= '
            ps.setString('.$i.', ' . $atributo->getNome() . ');';
                
            }
            
            $i++;
        }
        $codigo .= '
            
			ps.executeUpdate();
            return true;
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
            return false;
		}
            
            
	}
            
            
                ';
        return $codigo;
    }
    
    private function fetch($objeto) : string {
        $codigo = '';
        $nomeDoObjeto = lcfirst($objeto->getNome());
        
        $atributosComuns = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->tipoListado()) {
                $atributosComuns[] = $atributo;
            } else if ($atributo->isObjeto()) {
                $atributosObjetos[] = $atributo;
            }
        }
        
        $codigo .= '

	public ArrayList<'.ucfirst($objeto->getNome()).'> fetch() {
		ArrayList<'.ucfirst($objeto->getNome()).'>lista = new ArrayList<'.ucfirst($objeto->getNome()).'>();
		String sql = "';
        $sqlGerador = new SQLGerador($this->software);
        $sql = $sqlGerador->getSQLSelect($objeto);
        $codigo .= $sql;
        $codigo .= ' LIMIT 1000";
            
		PreparedStatement ps;
		try {
			ps = this.getConnection().prepareStatement(sql);
			ResultSet resultSet = ps.executeQuery();
			while(resultSet.next()){
				' . ucfirst($objeto->getNome()) . ' ' . lcfirst($objeto->getNome()). ' = new ' . ucfirst($objeto->getNome()). '();';
        foreach ($atributosComuns as $atributo) {
            
            if($atributo->getTipo() == Atributo::TIPO_INT){
                $codigo .= '
                ' . lcfirst($objeto->getNome()) . '.set' . ucfirst($atributo->getNome()) . '( resultSet.getInt("' . $atributo->getNomeSnakeCase() . '"));';
            }else if($atributo->getTipo() == Atributo::TIPO_FLOAT){
                $codigo .= '
                ' . lcfirst($objeto->getNome()) . '.set' . ucfirst($atributo->getNome())  . '( resultSet.getFloat("' . $atributo->getNomeSnakeCase() . '"));';
            }else if($atributo->getTipo() == Atributo::TIPO_STRING || $atributo->getTipo() == Atributo::TIPO_DATE_TIME || $atributo->getTipo() == Atributo::TIPO_DATE){
                $codigo .= '
                ' . lcfirst($objeto->getNome()) . '.set' . ucfirst($atributo->getNome())  . '( resultSet.getString("' . $atributo->getNomeSnakeCase() . '"));';
            }
            
        }
        foreach($atributosObjetos as $atributoObjeto){
            
            foreach($this->software->getObjetos() as $objeto2){
                if($objeto2->getNome() == $atributoObjeto->getTipo()){
                    foreach($objeto2->getAtributos() as $atributo3){
                        if($atributo3->getIndice() == Atributo::INDICE_PRIMARY){
                            
                            if($atributo3->getTipo() == Atributo::TIPO_INT){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getInt("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                            }else if($atributo3->getTipo() == Atributo::TIPO_FLOAT){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getFloat("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                            }else if($atributo3->getTipo() == Atributo::TIPO_STRING){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getString("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                            }
                            
                            
                        }
                        else
                        {
                            
                            if($atributo3->getTipo() == Atributo::TIPO_INT){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getInt("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                                
                            }else if($atributo3->getTipo() == Atributo::TIPO_FLOAT){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getFloat("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                            }else if($atributo3->getTipo() == Atributo::TIPO_STRING){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getString("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                            }
                            
                        }
                        
                    }
                    break;
                }
            }
            
        }
        $codigo .= '
            
        
				lista.add(' . $nomeDoObjeto . ');
			}
            return lista;
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
            return null;
		}
    				    
	}

';
        
        
        
        return $codigo;
    }
    
    private function insert(Objeto $objeto)
    {
        $codigo = '
	public boolean insert(' . ucfirst($objeto->getNome()). ' ' . lcfirst($objeto->getNome()). '){
	    
        String sql = "INSERT into '.$objeto->getNomeSnakeCase().'(';
        $listaAtributos = array();
        $listaAtributosVar = array();
        foreach ($objeto->getAtributos() as $atributo)
        {
            if($atributo->isPrimary()){
                continue;
            }
            if($atributo->tipoListado()){
                $listaAtributos[] = $atributo->getNomeSnakeCase();
                $listaAtributosVar[] = '?';
                
            }else if($atributo->isObjeto()){
                $listaAtributos[] = 'id_' . $atributo->getNomeSnakeCase();
                $listaAtributosVar[] = '?';
                
            }else{
                continue;
            }
        }
        $codigo .= implode(", ", $listaAtributos);
        $codigo .= ') VALUES (';
        $codigo .= implode(", ", $listaAtributosVar);
        $codigo .= ');";';
       
        $codigo .= '
            
		try {
            
			PreparedStatement ps = this.getConnection().prepareStatement(sql);';
        
        $i = 0;
        foreach ($objeto->getAtributos() as $atributo) {
            
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }else{
                $i++;
            }
            if($atributo->tipoListado()){
                $codigo .= '
            ps.set'.$atributo->getTipoJava().'('.$i.', '.strtolower($objeto->getNome()).'.get'.ucfirst($atributo->getNome()).'());';
        
            }else if($atributo->isObjeto()){
                $strCampoPrimary = '';
                foreach($this->software->getObjetos() as $objetoDoAtributo){
                    if($objetoDoAtributo->getNome() == $atributo->getTipo()){
                        foreach($objetoDoAtributo->getAtributos() as $att){
                            if($att->isPrimary()){
                                $strCampoPrimary = ucfirst($att->getNome());
                                break;
                            }
                        }
                        break;
                    }
                }
                
                $codigo .= '
            ps.setInt('.$i.', '.lcfirst($atributo->getNome()).'.get'.ucfirst($atributo->getNome()).'().get'.$strCampoPrimary.'());';
                
                
            }
            
        }
        
        $codigo .= '
            
			ps.executeUpdate();
			return true;
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
			return false;
		}
    }
            
    
';
        
        return $codigo;
        
    }
    
    
}





?>