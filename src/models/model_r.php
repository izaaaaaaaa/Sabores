<?php
    require_once '../config/database.php';

    class recipe{
        public static function getTotal_nf(){ // Retorna o total de receitas para calcular as páginas
            try{
                $pdo = getConnection(); // Faz a conexão com o banco de dados.

                $sql = "SELECT COUNT(*) FROM receitas"; // Prepara a query.
                $stmt = $pdo->query($sql); // Faz a consulta.
                return $stmt->fetchColumn(); // Retorna o número de registros da tabela.

            }catch(PDOException $e){
                die("Erro ao tentar ler a quantidade de receitas" . $e->getMessage());
            }
        }

        public static function getTotalPorcoes_nf(){ // Retorna o total de receitas para calcular as páginas
            try{
                $pdo = getConnection(); // Faz a conexão com o banco de dados.

                $sql = "SELECT SUM(rendimento_porcoes) FROM receitas"; // Prepara a query.
                $stmt = $pdo->query($sql); // Faz a consulta.
                return $stmt->fetchColumn(); // Retorna o número de registros da tabela.

            }catch(PDOException $e){
                die("Erro ao tentar ler o total de porções" . $e->getMessage());
            }
        }

        public static function listarReceitas_nf($limite, $offset, $idUsuarioLogado = null){ 
            try{
                $pdo = getConnection(); 

                $sqlUsuarioJaCurtiu = $idUsuarioLogado 
                    ? "EXISTS(SELECT 1 FROM curtidas WHERE usuario_id = :usuario_logado_id AND receita_id = r.receita_id) AS usuario_ja_curtiu"
                    : "0 AS usuario_ja_curtiu";

                $sql = "SELECT r.receita_id AS id, r.titulo_receita, r.tipo_receita, u.nome_usuario, u.imagem, r.custo_total, r.tempo_preparo_minutos, r.rendimento_porcoes, r.criado_em,
                    COALESCE(curtidas.total_curtidas, 0) AS curtidas,
                    COALESCE(comentarios.total_comentarios, 0) AS comentarios,
                $sqlUsuarioJaCurtiu
                FROM receitas r
 
 /* ALTERADO PARA LEFT JOIN: Traz a receita mesmo se o usuario_id não bater ou não existir */
            LEFT JOIN usuarios u
            ON u.usuario_id = r.usuario_id
                        LEFT JOIN (
                            SELECT
                                receita_id,
                                COUNT(*) AS total_curtidas
                            FROM curtidas
                            GROUP BY receita_id
                        ) curtidas
                            ON curtidas.receita_id = r.receita_id
                        LEFT JOIN (
                            SELECT
                                receita_id, 
                                COUNT(*) AS total_comentarios
                            FROM comentarios
                            GROUP BY receita_id
                        ) comentarios
                            ON comentarios.receita_id = r.receita_id
                        ORDER BY r.receita_id DESC /* Mudado para receita_id, pois o criado_em está zerado no banco */
                        LIMIT :limite OFFSET :offset
                    ";
                $stmt = $pdo->prepare($sql); 
                
                if ($idUsuarioLogado) {
                    $stmt->bindValue(':usuario_logado_id', $idUsuarioLogado, PDO::PARAM_INT);
                }

                $stmt->bindValue(':limite', $limite, PDO::PARAM_INT); 
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT); 
                $stmt->execute(); 
                return $stmt->fetchAll(PDO::FETCH_ASSOC); 
            }catch(PDOException $e){
                die("Erro ao tentar ler as receitas: " . $e->getMessage());
            }
        }

        public static function toggleCurtida($idUsuario, $idReceita) {
            try {
                $pdo = getConnection(); // Faz a conexão com o banco de dados.

                // 1. Verifica se o registro de curtida já existe para esse usuário e receita
                $sqlCheck = "SELECT 1 FROM curtidas WHERE usuario_id = ? AND receita_id = ?";
                $stmtCheck = $pdo->prepare($sqlCheck);
                $stmtCheck->execute([$idUsuario, $idReceita]);
                $jaCurtiu = $stmtCheck->fetchColumn();

                if ($jaCurtiu) {
                    // Se já curtiu, deleta o registro (Descurtir)
                    $sqlDelete = "DELETE FROM curtidas WHERE usuario_id = ? AND receita_id = ?";
                    $stmtDelete = $pdo->prepare($sqlDelete);
                    $stmtDelete->execute([$idUsuario, $idReceita]);
                    $curtidoStatus = false;
                } else {
                    // Se não curtiu, insere o registro (Curtir)
                    $sqlInsert = "INSERT INTO curtidas (usuario_id, receita_id) VALUES (?, ?)";
                    $stmtInsert = $pdo->prepare($sqlInsert);
                    $stmtInsert->execute([$idUsuario, $idReceita]);
                    $curtidoStatus = true;
                }

                // 2. Conta a quantidade totalizada de curtidas atualizada desta receita
                $sqlCount = "SELECT COUNT(*) FROM curtidas WHERE receita_id = ?";
                $stmtCount = $pdo->prepare($sqlCount);
                $stmtCount->execute([$idReceita]);
                $novoTotalCurtidas = $stmtCount->fetchColumn();

                return [
                    'sucesso' => true,
                    'curtido' => $curtidoStatus,
                    'novas_curtidas' => (int)$novoTotalCurtidas
                ];

            } catch (PDOException $e) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Erro ao processar curtida no banco: ' . $e->getMessage()
                ];
            }
        }
    }
?>
