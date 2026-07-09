<?php // index.php - Front Controller
    session_start(); // Criar um ID de sessão único

    require_once '../src/controllers/controller_r.php';
    
    $page = $_GET['page'] ?? 'home';
    $acao = $_GET['acao'] ?? null;

    // ROTA ASSÍNCRONA (Disparada exclusivamente pelo JavaScript Fetch)
    if ($page === 'acao') {
        // Se a ação for de login ou cadastro via fetch, garante a inclusão do model de login
        if ($acao === 'efetuar_login' || $acao === 'efetuar_cadastro' || $acao === 'logar' || $acao === 'cadastrar') {
            require_once '../src/models/model_login.php';
            $auth = new userAccess();
            
            if ($acao === 'efetuar_cadastro' || $acao === 'cadastrar') {
                $auth->cadastrar();
            } else {
                $auth->login();
            }
            exit;
        }

        $homecontrol = new HomeController();
        $homecontrol->gerenciarAcao($acao);
        
        exit; // Garante que o script pare aqui e não carregue o HTML abaixo
    }

    switch($page){ // ROTEAMENTO DE NAVEGAÇÃO TRADICIONAL (Carregamento de páginas completas)
        case 'home':
            $homecontrol = new HomeController();
            $homecontrol->gerenciarAcao('exibir_home');
        break;

        case 'logar':
            require_once '../src/models/model_login.php';
            $auth = new userAccess(); 
            $auth->login();
        break;

        case 'cadastrar':
            require_once '../src/models/model_login.php';
            $auth = new userAccess(); 
            $auth->cadastrar();
        break;

        case 'logout':
            session_unset();   // Remove todas as variáveis salvas na sessão ($_SESSION)
            session_destroy(); // Destrói o arquivo físico da sessão no servidor
            header('Location: index.php'); // Redireciona o usuário para a Home de forma limpa
            exit;              // Interrompe o script imediatamente
        break;

        case 'teste_listar':
            $model = new recipe();

            $receitas = $model->listarReceitas_nf(10, 0);

            echo '<pre>';
            print_r($receitas);
            echo '</pre>';
        break;

        default: // Segurança/UX: Se a página solicitada não existir, renderiza a Home por padrão
            $homecontrol = new HomeController();
            $homecontrol->gerenciarAcao('exibir_home');
        break;
    }
?>
