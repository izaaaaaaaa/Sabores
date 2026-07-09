// Seletor global do Modal de Login
const modalLogin = document.querySelector('.modal_login');

// ==========================================
// 1. OPERAÇÃO DE LOGIN
// ==========================================
function fazer_login(event) {
    event.preventDefault();

    const campoUsuarioLogin = document.getElementById('login_usuario');
    const campoSenhaLogin = document.getElementById('login_senha');

    let formularioValido = true;

    if (!campoUsuarioLogin || !campoSenhaLogin) {
        console.error("Erro: Inputs de login não encontrados.");
        return;
    }

    if (campoUsuarioLogin.value.trim() === "") {
        campoUsuarioLogin.classList.add('erro_borda');
        formularioValido = false;
    } else {
        campoUsuarioLogin.classList.remove('erro_borda');
    }

    if (campoSenhaLogin.value.trim() === "") {
        campoSenhaLogin.classList.add('erro_borda');
        formularioValido = false;
    } else {
        campoSenhaLogin.classList.remove('erro_borda');
    }

    if (!formularioValido) {
        alert("Usuário/E-mail e senha são obrigatórios!");
    } else {
        logar_db(campoUsuarioLogin.value.trim(), campoSenhaLogin.value.trim());
    }
}

function logar_db(usuario, senha) {
    fetch("index.php?page=logar", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ usuario: usuario, senha: senha })
    })
        .then(response => {
            if (response.ok) {
                return response.json().then(dados => {
                    if (dados.url) {
                        window.location.href = dados.url;
                    } else {
                        window.location.reload();
                    }
                });
            } else if (response.status === 401) {
                alert("Usuário/E-mail ou senha incorretos!");
            } else {
                alert("Erro na validação dos dados no servidor.");
            }
        })
        .catch(error => console.error('Erro de rede no login: ', error));
}

// ==========================================
// 2. OPERAÇÃO DE CADASTRO
// ==========================================
function fazer_cadastro(event) {
    event.preventDefault();

    const campoNomeCad = document.getElementById('cad_nome');
    const campoEmailCad = document.getElementById('cad_email');
    const campoSenhaCad = document.getElementById('cad_senha');

    let formularioValido = true;

    if (!campoNomeCad || !campoEmailCad || !campoSenhaCad) {
        console.error("Erro: Inputs de cadastro não encontrados.");
        return;
    }

    [campoNomeCad, campoEmailCad, campoSenhaCad].forEach(input => {
        if (input.value.trim() === "") {
            input.classList.add('erro_borda');
            formularioValido = false;
        } else {
            input.classList.remove('erro_borda');
        }
    });

    if (!formularioValido) {
        alert("Todos os campos do cadastro são obrigatórios!");
    } else {
        cadastrar_db(campoNomeCad.value.trim(), campoEmailCad.value.trim(), campoSenhaCad.value.trim());
    }
}

function cadastrar_db(nome, email, senha) {
    fetch("index.php?page=cadastrar", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nome: nome, email: email, senha: senha })
    })
        .then(response => {
            return response.json().then(dados => {
                if (response.ok && dados.sucesso) {
                    alert(dados.mensagem);
                    alternarParaLogin();
                } else {
                    alert(dados.erro || "Erro inesperado ao realizar o cadastro.");
                }
            });
        })
        .catch(error => console.error('Erro de rede no cadastro: ', error));
}

// ==========================================
// 3. OPERAÇÃO: CRIAR RECEITA (NOVOS REQUISITOS)
// ==========================================
function validar_e_enviar_receita(event) {
    event.preventDefault();

    const form = document.getElementById('form_criar_receita');
    if (!form) return;

    const campos = form.querySelectorAll('input, select, textarea');
    let formularioValido = true;

    // Regex aceitando apenas alfanuméricos, acentuação, espaços e pontuações básicas (Req 3.4)
    const regexAlfanumerico = /^[a-zA-Z0-9À-ÿ\s,.\-\(\)\n\r\/%]+$/;

    campos.forEach(campo => {
        const grupo = campo.closest('.grupo_campo');
        if (!grupo) return;

        const txtErro = grupo.querySelector('.erro_txt');
        let campoInvalido = false;

        // Requisito 5.1: Se algum dado estiver ausente
        if (campo.value.trim() === "") {
            campoInvalido = true;
            if (txtErro) txtErro.innerText = "Campo obrigatório";
        }
        // Requisito 3.4: Validação estrita para ingredientes e modo de preparo
        else if ((campo.id === 'ingredientes' || campo.id === 'modo_preparo') && !regexAlfanumerico.test(campo.value)) {
            campoInvalido = true;
            if (txtErro) txtErro.innerText = "Este campo deve conter apenas textos alfanuméricos!";
        }

        // Aplicação visual dos destaques de validação
        if (campoInvalido) {
            campo.classList.add('erro_borda'); // Borda em vermelho
            if (txtErro) txtErro.style.display = 'block'; // Mostra a mensagem
            formularioValido = false;
        } else {
            campo.classList.remove('erro_borda');
            if (txtErro) txtErro.style.display = 'none';
        }
    });

    // Requisito 5.2: Envio assíncrono via Fetch
    if (formularioValido) {
        const dadosReceita = {
            tipo_receita: document.getElementById('tipo_receita').value,
            titulo_receita: document.getElementById('titulo_rece_campo').value,
            custo_total: document.getElementById('custo_total').value,
            tempo_preparo_minutos: document.getElementById('tempo_preparo_minutos').value,
            rendimento_porcoes: document.getElementById('rendimento_porcoes').value,
            ingredientes: document.getElementById('ingredientes').value,
            modo_preparo: document.getElementById('modo_preparo').value
        };

        fetch("index.php?page=salvar_receita", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dadosReceita)
        })
            .then(response => response.json())
            .then(dados => {
                if (dados.sucesso) {
                    alert("Receita criada com sucesso!");
                    // Redireciona para atualizar e colocar no topo da listagem (Req 5.3)
                    window.location.href = "index.php?page=home";
                } else {
                    alert(dados.erro || "Erro interno ao salvar no banco.");
                }
            })
            .catch(error => console.error('Erro de rede ao salvar receita:', error));
    }
}

// ==========================================
// 4. FUNÇÕES DE TRANSIÇÃO DO MODAL
// ==========================================
function alternarParaCadastro() {
    document.getElementById('secao_login').style.display = 'none';
    document.getElementById('secao_cadastro').style.display = 'block';
}

window.alternarParaLogin = function () {
    document.getElementById('secao_cadastro').style.display = 'none';
    document.getElementById('secao_login').style.display = 'block';
}

function fecharModal() {
    if (modalLogin) modalLogin.style.display = 'none';
}

// ==========================================
// 5. INICIALIZAÇÃO E ESCUTADORES
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    // Escuta o envio do formulário de Login
    const formLogin = document.querySelector('#secao_login form');
    if (formLogin) {
        formLogin.addEventListener('submit', fazer_login);
    }

    // Escuta o envio do formulário de Cadastro
    const formCadastro = document.querySelector('#secao_cadastro form');
    if (formCadastro) {
        formCadastro.addEventListener('submit', fazer_cadastro);
    }

    // Fecha o modal ao clicar na área escura de fundo
    if (modalLogin) {
        modalLogin.addEventListener('click', (event) => {
            if (event.target === modalLogin) {
                modalLogin.style.display = 'none';
            }
        });
    }

    // Requisito 1: Alteração de estilo do menu "Adicionar Receita" ao ser clicado
    const btnMenuReceita = document.querySelector('.sidebar a[href*="addRecipe"]') || document.querySelector('.btn_menu_receita');
    if (btnMenuReceita) {
        btnMenuReceita.addEventListener('click', function () {
            this.style.backgroundColor = '#E5A93C';
            this.style.color = '#FFFDF3';
        });
    }

    // Monitora dinamicamente envios do formulário de receitas (evita problemas se carregado via AJAX)
    document.addEventListener('submit', function (e) {
        if (e.target && e.target.id === 'form_criar_receita') {
            validar_e_enviar_receita(e);
        }
    });
});
