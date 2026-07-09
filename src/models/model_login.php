<?php
    require_once '../config/database.php';
    
    class userAccess {
        
        // --- FUNÇÃO DE LOGIN ADAPTADA AO SEU BANCO ---
        public function login() {
            $dados_recebidos = file_get_contents('php://input');
            $input = json_decode($dados_recebidos, true) ?? [];

            $usuarioInput = $input['usuario'] ?? $input['nome'] ?? '';
            $senha = $input['senha'] ?? '';

            header('Content-Type: application/json; charset=utf-8');

            if (empty($usuarioInput) || empty($senha)) {
                http_response_code(400); 
                echo json_encode(['erro' => 'Preencha todos os campos!']);
                exit();
            }

            $db = getConnection();

            $stmt = $db->prepare("SELECT * FROM usuarios WHERE nome_usuario = :usuario OR email = :usuario");
            $stmt->execute([':usuario' => $usuarioInput]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && $senha === $usuario['senha']) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['usuario_id'] = $usuario['usuario_id'];
                $_SESSION['nome_usuario'] = $usuario['nome_usuario'];

                if (ob_get_length()){
                    ob_clean();
                }
                
                http_response_code(200);
                echo json_encode([
                    'sucesso' => true,
                    'url' => 'index.php?page=home' 
                ]);
                exit();
                
            } else {
                if (ob_get_length()){
                    ob_clean(); 
                }

                http_response_code(401);
                echo json_encode(['erro' => 'Usuário/E-mail ou senha inválidos!']);
                exit();
            }
        }

        // --- FUNÇÃO DE CADASTRO CORRIGIDA ---
        public function cadastrar() {
            $dados_recebidos = file_get_contents('php://input');
            $input = json_decode($dados_recebidos, true) ?? [];

            $nome = $input['nome'] ?? '';
            $email = $input['email'] ?? '';
            $senha = $input['senha'] ?? '';

            header('Content-Type: application/json; charset=utf-8');

            if (empty($nome) || empty($email) || empty($senha)) {
                http_response_code(400);
                echo json_encode(['erro' => 'Todos os campos de cadastro são obrigatórios!']);
                exit();
            }

            $db = getConnection();

            try {
                // 1. Verifica se o email ou o nickname já existem
                $stmtCheck = $db->prepare("SELECT 1 FROM usuarios WHERE email = :email OR nome_usuario = :nome_user");
                $stmtCheck->execute([':email' => $email, ':nome_user' => $nome]);
                if ($stmtCheck->fetchColumn()) {
                    http_response_code(409);
                    echo json_encode(['erro' => 'Este e-mail ou nome de usuário já está cadastrado!']);
                    exit();
                }

                // 2. Descobre o maior ID atual da tabela e soma +1 (evita erro de ID duplicado)
                $stmtId = $db->query("SELECT MAX(usuario_id) FROM usuarios");
                $maiorId = (int)$stmtId->fetchColumn();
                $novoId = $maiorId + 1;

                // 3. CORREÇÃO: "atualizado_em" ajustado de forma idêntica à estrutura da tabela
                $sql = "INSERT INTO usuarios (usuario_id, nome, email, nome_usuario, senha, imagem, criado_em, atualizado_em) 
                        VALUES (:id, :nome, :email, :nome_user, :senha, 'img/default_user.png', NOW(), NOW())";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id'        => $novoId,
                    ':nome'      => $nome,
                    ':email'     => $email,
                    ':nome_user' => $nome,
                    ':senha'     => $senha
                ]);

                http_response_code(201);
                echo json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Cadastro realizado com sucesso! Prossiga com o login.'
                ]);
                exit();

            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['erro' => 'Erro interno ao salvar o usuário: ' . $e->getMessage()]);
                exit();
            }
        }
    }
?>
