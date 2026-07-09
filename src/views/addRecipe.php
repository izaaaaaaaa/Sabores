<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Receita</title>
    <style>
        .container_receita {
            padding: 20px 40px;
            max-width: 600px;
            font-family: sans-serif;
            margin: 0 auto;
        }
        .grupo_campo {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 15px;
        }
        .grupo_campo label {
            font-weight: bold;
            color: #3A2312;
        }
        .grupo_campo input, .grupo_campo select, .grupo_campo textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        /* Requisito 5.1: Destaque de borda em vermelho */
        .erro_borda {
            border: 2px solid #ff0000 !important;
        }
        /* Requisito 5.1: Texto identificando campo obrigatório */
        .erro_txt {
            color: #ff0000;
            font-size: 13px;
            font-weight: bold;
            display: none;
        }
    </style>
</head>
<body>

<div class="container_receita">
    <h2 style="text-align: center; color: #3A2312; margin-bottom: 25px;">Crie sua receita</h2>

    <form id="form_criar_receita" novalidate>
        
        <div class="grupo_campo">
            <label for="tipo_receita">Tipo de Receita</label>
            <select id="tipo_receita">
                <option value="" disabled selected>Selecione o tipo de receita</option>
                <option value="entrada">entrada</option>
                <option value="prato principal">prato principal</option>
                <option value="sobremesa">sobremesa</option>
            </select>
            <span class="erro_txt">Campo obrigatório</span>
        </div>

        <div class="grupo_campo">
            <label for="titulo_receita">Título da Receita</label>
            <input type="text" id="titulo_rece_campo" placeholder="Ex: Panqueca Americana">
            <span class="erro_txt">Campo obrigatório</span>
        </div>

        <div class="grupo_campo">
            <label for="custo_total">Custo Total dos Ingredientes (em gramas)</label>
            <input type="number" id="custo_total" min="1" placeholder="Ex: 500">
            <span class="erro_txt">Campo obrigatório</span>
        </div>

        <div class="grupo_campo">
            <label for="tempo_preparo_minutos">Tempo de Preparo (em minutos)</label>
            <input type="number" id="tempo_preparo_minutos" min="1" placeholder="Ex: 90">
            <span class="erro_txt">Campo obrigatório</span>
        </div>

        <div class="grupo_campo">
            <label for="rendimento_porcoes">Rendimento (em porções)</label>
            <input type="number" id="rendimento_porcoes" min="1" placeholder="Ex: 11">
            <span class="erro_txt">Campo obrigatório</span>
        </div>

        <div class="grupo_campo">
            <label for="ingredientes">Ingredientes</label>
            <textarea id="ingredientes" rows="4" placeholder="Ex: 200g de farinha, 2 ovos, 1 colher de manteiga..."></textarea>
            <span class="erro_txt">Campo obrigatório</span>
        </div>

        <div class="grupo_campo">
            <label for="modo_preparo">Modo de Preparo</label>
            <textarea id="modo_preparo" rows="4" placeholder="Ex: Misture os ingredientes secos, adicione os líquidos e bata..."></textarea>
            <span class="erro_txt">Campo obrigatório</span>
        </div>

        <button type="submit" style="background-color: #3A2312; color: #FFFDF3; border: none; padding: 12px; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; width: 100%;">
            Criar Receita
        </button>
    </form>
</div>

</body>
</html>
