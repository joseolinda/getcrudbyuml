<?php

function autoload($classe)
{
    if (file_exists('classes/dao/' . $classe . '.php')) {
        include_once 'classes/dao/' . $classe . '.php';
    } else if (file_exists('classes/model/' . $classe . '.php')) {
        include_once 'classes/model/' . $classe . '.php';
    } else if (file_exists('classes/controller/' . $classe . '.php')) {
        include_once 'classes/controller/' . $classe . '.php';
    } else if (file_exists('classes/util/' . $classe . '.php')) {
        include_once 'classes/util/' . $classe . '.php';
    } else if (file_exists('classes/view/' . $classe . '.php')) {
        include_once 'classes/view/' . $classe . '.php';
    }
}
spl_autoload_register('autoload');

$sessao = new Sessao();
if (isset($_GET["sair"])) {
    $sessao->mataSessao();
    header("Location:./");
}
if (isset($_GET['enviar_email'])) {
    if (isset($_SESSION['email_ja_foi'])) {
        echo "Nós já enviamos seu e-mail, aguarde um pouco que vai chegar";
        return;
    }

    $dao = new EmailConfirmarDAO();
    $emailConfirmar = new EmailConfirmar();
    $emailConfirmar->setEmail($_GET['enviar_email']);
    $emailConfirmar->setConfirmado(FALSE);
    if(!$dao->inserir($emailConfirmar)){
        echo "Falha ao tentar Inserir Email na Base de Dados";
        return;
    }
    $id = $dao->getConexao()->lastInsertId();
    $emailConfirmar->setCodigo(md5($id));
    $emailConfirmar->setId($id);
    if(!$dao->atualizarCodigo($emailConfirmar)){
        echo "Erro ao tentar atualizar o código de verificação";
        return;
    }
    
    $to = $_GET['enviar_email'];
    $subject = "Verificando o correio do PHP";
    $message = "<p>Confirme seu e-mail clicando no link: <a href=\"https://jefponte.com.br/pagina=verificar&codigo=" . md5($id) . "\"></a>.</p>";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: jefponte.com.br <naoresponda@jefponte.com.br>';

    if (mail($to, $subject, $message, $headers)) {
        echo "<p>E-mail Enviado. Verifique sua caixa de e-mail.</p>";
        $_SESSION['email_ja_foi'] = 1;
    } else {
        echo "<p>E-mail não foi enviado.</p>";
    }

    return;
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport"
	content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet"
	href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link href="css/simple-sidebar.css" rel="stylesheet">
<link href="css/selectize.bootstrap3.css" rel="stylesheet">
<title>EscritorDeSoftware</title>
</head>
<body>
	<div class="d-flex" id="wrapper">

<?php

SideBarController::main($sessao);

?>


		<div id="page-content-wrapper">
<?php

NavBarController::main($sessao);

?>

			<div class="container-fluid">    
<?php

MainController::main($sessao);

?> 
			</div>
		</div>
		

	</div>
	<script src="js/jquery-3.4.1.min.js"></script>
	<script
		src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
		integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
		crossorigin="anonymous"></script>
	<script
		src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
		integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
		crossorigin="anonymous"></script>
	<script src="js/selectize.js"></script>
	<script src="js/escritor.js"></script>
</body>
</html>