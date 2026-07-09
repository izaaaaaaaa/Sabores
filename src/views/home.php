<?php
// Simulando a verificação de sessão do PHP. 
// Certifique-se de que seu controller dá um session_start() e preenche $_SESSION['usuario_id'] e $_SESSION['nome_usuario']
$usuarioLogadoId = $_SESSION['usuario_id'] ?? null;
$usuarioLogadoNome = $_SESSION['nome_usuario'] ?? null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sabores</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <style>
        /* Estilos auxiliares para as novas funcionalidades do modal e botões */
        .alternar_modal {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .alternar_modal a {
            color: #d35400;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        .alternar_modal a:hover {
            text-decoration: underline;
        }
        .usuario_logado_container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        #btn_sair {
            background-color: #c0392b;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        #btn_sair:hover {
            background-color: #e74c3c;
        }
    </style>
</head>

<script>
    function enviarAcao(url) { 
        fetch(url).then(response => {
            if (response.status === 401) { 
                console.log('Acesso negado: Abrindo modal de login.');
                abrirModalLogin();
                return null; 
            }
            
            if (!response.ok) {
                throw new Error('Erro no servidor.');
            }
            
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return response.json();
            } else {
                return response.text(); 
            }
        })
        .then(dados => {
            if (!dados) return;

            if (typeof dados === 'string') {
                document.querySelector('main').innerHTML = dados;
            } else if (dados.sucesso) {
                console.log(dados.mensagem);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
        });
    }

    function curtirReceita(idReceita, botaoElemento) {
        const dadosForm = new FormData();
        dadosForm.append('recipe_id', idReceita); 

        fetch('index.php?page=acao&acao=curtir', {
            method: 'POST',
            body: dadosForm
        })
        .then(response => {
            if (response.status === 401) {
                abrirModalLogin(); 
                return null;
            }
            if (!response.ok) throw new Error('Erro no servidor.');
            return response.json(); 
        })
        .then(dados => {
            if (!dados || !dados.sucesso) return;

            if (dados.curtido) {
                botaoElemento.classList.add('ativo');
            } else {
                botaoElemento.classList.remove('ativo');
            }

            const contadorSpan = botaoElemento.querySelector('.count');
            if (contadorSpan) {
                contadorSpan.textContent = dados.novas_curtidas;
            }
        })
        .catch(error => console.error('Erro ao curtir receita:', error));
    }

    // Gerenciamento dos Modais (Login / Cadastro)
    function abrirModalLogin() {
        const modal = document.querySelector('.modal_login');
        if (modal) {
            // Garante que mostre a seção de Login primeiro
            document.getElementById('secao_login').style.display = 'block';
            document.getElementById('secao_cadastro').style.display = 'none';
            modal.style.display = 'flex'; 
        }
    }

    function fecharModal() {
        const modal = document.querySelector('.modal_login');
        if (modal) modal.style.display = 'none';
    }

    function alternarParaCadastro() {
        document.getElementById('secao_login').style.display = 'none';
        document.getElementById('secao_cadastro').style.display = 'block';
    }

    function alternarParaLogin() {
        document.getElementById('secao_cadastro').style.display = 'none';
        document.getElementById('secao_login').style.display = 'block';
    }

    // Função para realizar o Logout
    function efetuarLogout() {
        if (confirm("Deseja realmente sair?")) {
            window.location.href = 'index.php?page=acao&acao=logout'; 
        }
    }
</script>

<body>
    <div id="fundo">
        <div id="perfil"> 
            <div class="logo">
                <img id="logo_img" src="img/sabores.png" alt="Logo da empresa sabores">
                <label id="nome_empresa">Sabores</label>
            </div>
            <div id="totais">
                <div>Total de receitas: <?= htmlspecialchars($totalReceitas ?? 0) ?></div>
                <div>Total de porções: <?= htmlspecialchars($totalPorcoes ?? 0) ?></div>
            </div>
            <div id="meio_perfil">
                <button id="add_receita" onclick="enviarAcao('index.php?page=acao&acao=add_receita')"> Adicionar Receita </button>
            </div>
            <footer id="rodape_perfil">
                <label> Sabores </label>
                <div id="icones">
                    <a href="https://instagram.com.br" class="icone">
                        <img src="img/icones/instagram.svg" alt="logo do instagram">
                    </a>
                    <a href="https://x.com.br" class="icone">
                        <img src="img/icones/twitter.svg" alt="logo do X">
                    </a>
                    <a href="https://tiktok.com.br" class="icone">
                        <img src="img/icones/tiktok.svg" alt="logo do tiktok">
                    </a>
                </div>
                <label>Copyright-2026</label>
            </footer>
        </div>
        <main> 
            <header class="header_">
                <div class="m"></div>
                <?php if ($usuarioLogadoId): ?>
                    <div class="usuario_logado_container">
                        <span>Olá, <strong><?= htmlspecialchars($usuarioLogadoNome) ?></strong></span>
                        <button id="btn_sair" onclick="efetuarLogout()">Sair</button>
                    </div>
                <?php else: ?>
                    <button id="login" onclick="abrirModalLogin()">Entrar</button>
                <?php endif; ?>
            </header>
            <div id="div_filtro">
                <div class="filtros" >Entrada</div>
                <div class="filtros" >Prato Principal</div>
                <div class="filtros" >Sobremesa</div>
            </div>
            <section id="display_" >
                <?php if (isset($receitas) && count($receitas) > 0): ?>
                    <?php foreach ($receitas as $receita): ?>
                        <div class="recipe_card">
                            <div class="recipe_header">
                                <div class="user_info">
                                    <img class="user_avatar" 
                                         src="<?= !empty($receita['imagem']) ? 'img/imagens_perfil/' . htmlspecialchars($receita['imagem']) : 'img/imagens_perfil/avatar-padrao.png' ?>" 
                                         alt="Foto de <?= htmlspecialchars($receita['nome_usuario'] ?? 'Anônimo') ?>">
                                    <span class="user_name"><?= htmlspecialchars($receita['nome_usuario'] ?? 'Anônimo') ?></span>
                                </div>
                                <h2 class="recipe_title"><?= htmlspecialchars($receita['titulo_receita']) ?></h2>
                                <span class="recipe_tipe"><?= htmlspecialchars($receita['tipo_receita']) ?></span>
                                <span class="recipe_date"><?= htmlspecialchars($receita['criado_em']) ?></span>
                            </div>
                            <div class="recipe_body">
                                <div class="meta_info">
                                    <div class="meta_item">
                                        <span class="meta_label">CUSTO TOTAL:</span>
                                        <span class="meta_value">R$ <?= number_format($receita['custo_total'], 2, ',', '.') ?></span>
                                    </div>
                                    <div class="meta_item">
                                        <span class="meta_label">TEMPO DE PREPARO:</span>
                                        <span class="meta_value"><?= htmlspecialchars($receita['tempo_preparo_minutos']) ?> min</span>
                                    </div>
                                    <div class="meta_item">
                                        <span class="meta_label">RENDIMENTO:</span>
                                        <span class="meta_value"><?= htmlspecialchars($receita['rendimento_porcoes']) ?> porções</span>
                                    </div>
                                </div>
                            </div>
                            <div class="recipe_footer">
                                <div class="interacoes">
                                    <button class="btn_action curtir_bt <?= !empty($receita['usuario_ja_curtiu']) ? 'ativo' : '' ?>" 
                                            onclick="curtirReceita(<?= $receita['id'] ?>, this)">
                                        <img class="icon" src="img/icones/coracao.svg" alt="Curtir">
                                        <span class="count"><?= htmlspecialchars($receita['curtidas']) ?></span>
                                    </button>
                                    <button class="btn_action comentar_bt" onclick="enviarAcao('index.php?page=acao&acao=comentar&id=<?= htmlspecialchars($receita['id']) ?>')">
                                        <img class="icon" src="img/icones/comentario.svg" alt="Comentar">
                                        <span class="count"><?= htmlspecialchars($receita['comentarios']) ?></span>
                                    </button>
                                </div>
                                <span class="recipe_id">ID: #<?= htmlspecialchars($receita['id']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhuma receita encontrada.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <div class="modal_login" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div class="conteudo_modal" style="background: white; padding: 30px; border-radius: 8px; width: 350px; position: relative; color: #333;">
        <span onclick="fecharModal()" style="position: absolute; top: 10px; right: 15px; cursor: pointer; font-weight: bold; font-size: 18px;">X</span>
        
        <div id="secao_login">
            <h2 style="margin-top: 0;">Login</h2>
            <form style="display: flex; flex-direction: column; gap: 10px;">
                <label>Usuário/E-mail:</label>
                <input type="text" id="login_usuario" placeholder="Digite seu usuário ou e-mail" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                
                <label>Senha:</label>
                <input type="password" id="login_senha" placeholder="Digite sua senha" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                
                <button type="submit" style="background: #d35400; color: white; border: none; padding: 10px; font-weight: bold; border-radius: 4px; cursor: pointer; margin-top: 10px;">Entrar</button>
            </form>
            <div class="alternar_modal">
                Não tem uma conta? <a onclick="alternarParaCadastro()">Cadastre-se aqui</a>
            </div>
        </div>

        <div id="secao_cadastro" style="display: none;">
            <h2 style="margin-top: 0;">Cadastro</h2>
            <form style="display: flex; flex-direction: column; gap: 10px;">
                <label>Nome Completo:</label>
                <input type="text" id="cad_nome" placeholder="Digite seu nome completo" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                
                <label>E-mail:</label>
                <input type="email" id="cad_email" placeholder="Digite seu e-mail" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                
                <label>Senha:</label>
                <input type="password" id="cad_senha" placeholder="Crie uma senha" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                
                <button type="submit" style="background: #27ae60; color: white; border: none; padding: 10px; font-weight: bold; border-radius: 4px; cursor: pointer; margin-top: 10px;">Cadastrar</button>
            </form>
            <div class="alternar_modal">
                Já possui uma conta? <a onclick="alternarParaLogin()">Faça login aqui</a>
            </div>
        </div>
    </div>
</div>

    <script src="js/script.js"></script>
</body>
</html>
