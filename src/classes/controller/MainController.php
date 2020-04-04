<?php 



class MainController{
    public static function main(Sessao $sessao){
        if($sessao->getNivelAcesso() == Sessao::NIVEL_DESLOGADO){
            if(!isset($_GET['pagina'])){
                HomeController::main();
                return;
            }
            switch ($_GET['pagina']) {
                case 'login':
                    $controller = new UsuarioController();
                    $controller->login();
                    break;
                case 'verificar':
                    $controller = new EmailConfirmarController();
                    $controller->verificar();
                    break;
            }            
        }else if($sessao->getNivelAcesso() == Sessao::NIVEL_VERIFICADO)
        {
            $controller = new EmailConfirmarController();
            $controller->verificar();
            return;
            
        }else if(isset($_GET['pagina'])) 
        {
            
            switch ($_GET['pagina']) {
                case 'software':
                    $controller = new SoftwareController();
                    $controller->selecionar();
                    $controller->deletar();
                    $controller->editar();
                    
                    break;
                case 'objeto':
                    ObjetoController::main();
                    break;
                case 'atributo':
                    AtributoController::main();
                    break;
                
                    
            }
        }
        else
        {
            
            HomeController::main();
        }
        
    }
    
    
    
}








?>