<?php
session_start();
require_once 'conection.php';
require_once 'db_functions.php';

$feedback_message = '';
if (isset($_SESSION['feedback'])) {
    $feedback_message = $_SESSION['feedback'];
    unset($_SESSION['feedback']);
}

// Função helper para evitar reenvio do formulário
function redirect_self()
{
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Define mensagem na sessão
function set_feedback($message, $type = 'success')
{
    $_SESSION['feedback'] = "<div class='feedback $type'>$message</div>";
}

// -------------------- TRATAMENTO POST --------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;

    try {

        // -------------------- ENDEREÇO --------------------
        if (isset($_POST['submit_endereco'])) {

            $id_endereco = (int)($_POST['id_endereco'] ?? 0);
            $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '');
            $uf = strtoupper(substr(trim($_POST['uf'] ?? ''), 0, 2));
            $cidade = trim($_POST['cidade'] ?? '');
            $rua = trim($_POST['rua'] ?? '');
            $complemento = trim($_POST['complemento'] ?? null);

            if (!preg_match('/^\d{8}$/', $cep) || empty($cidade) || empty($rua) || !preg_match('/^[A-Z]{2}$/', $uf)) {
                set_feedback("Erro: dados de endereço inválidos.", "error");
            } elseif ($id_endereco > 0) {
                if (update_endereco($conn, $id_endereco, $cep, $uf, $cidade, $rua, $complemento)) {
                    set_feedback("Endereço atualizado com sucesso.");
                } else {
                    set_feedback("Erro ao atualizar endereço: " . $conn->error, "error");
                }
            } else {
                if (insert_endereco($conn, $cep, $uf, $cidade, $rua, $complemento)) {
                    set_feedback("Novo endereço cadastrado com sucesso (ID: " . $conn->insert_id . ").");
                } else {
                    set_feedback("Erro ao cadastrar endereço: " . $conn->error, "error");
                }
            }
        }

        // -------------------- ACOMPANHANTE --------------------
        elseif (isset($_POST['submit_acompanhante'])) {

            $id_acompanhante = (int)($_POST['id_acompanhante'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $idade = (int)($_POST['idade'] ?? 0);
            $cpf = preg_replace('/\D/', '', $_POST['cpf_acompanhante'] ?? '');
            $telefone = trim($_POST['telefone_acompanhante'] ?? null);

            if (empty($nome) || $idade <= 0 || !preg_match('/^\d{11}$/', $cpf)) {
                set_feedback("Erro: dados de acompanhante inválidos.", "error");
            } elseif ($id_acompanhante > 0) {
                if (update_acompanhante($conn, $id_acompanhante, $nome, $idade, $cpf, $telefone)) {
                    set_feedback("Acompanhante atualizado com sucesso.");
                } else {
                    set_feedback("Erro ao atualizar acompanhante: " . $conn->error, "error");
                }
            } else {
                if (insert_acompanhante($conn, $nome, $idade, $cpf, $telefone)) {
                    set_feedback("Novo acompanhante cadastrado com sucesso (ID: " . $conn->insert_id . ").");
                } else {
                    set_feedback("Erro ao cadastrar acompanhante: " . $conn->error, "error");
                }
            }
        }

        // -------------------- PACIENTE --------------------
        elseif (isset($_POST['submit_paciente'])) {

            $id_paciente = (int)($_POST['id_paciente'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $idade = (int)($_POST['idade'] ?? 0);
            $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
            $telefone = trim($_POST['telefone'] ?? null);
            $email = trim($_POST['email'] ?? '');
            $senha = trim($_POST['senha'] ?? '');
            $id_endereco = !empty($_POST['id_endereco']) ? (int)$_POST['id_endereco'] : null;
            $id_acompanhante = !empty($_POST['id_acompanhante']) ? (int)$_POST['id_acompanhante'] : null;

            if (empty($nome) || $idade <= 0 || !preg_match('/^\d{11}$/', $cpf) || empty($email)) {
                set_feedback("Erro: dados de paciente inválidos.", "error");
            } elseif ($id_paciente > 0) { // UPDATE

                $senha_hash = null;
                if (!empty($senha)) {
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                }

                if (update_paciente(
                    $conn,
                    $id_paciente,
                    $nome,
                    $idade,
                    $cpf,
                    $telefone,
                    $email,
                    $senha_hash,
                    $id_endereco,
                    $id_acompanhante
                )) {
                    set_feedback("Paciente atualizado com sucesso.");
                } else {
                    set_feedback("Erro ao atualizar paciente: " . $conn->error, "error");
                }
            } else { // INSERT

                if (empty($senha)) {
                    set_feedback("Erro: senha obrigatória para novo cadastro.", "error");
                } else {
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                    if (insert_paciente(
                        $conn,
                        $nome,
                        $idade,
                        $cpf,
                        $telefone,
                        $email,
                        $senha_hash,
                        $id_endereco,
                        $id_acompanhante
                    )) {
                        set_feedback("Novo paciente cadastrado com sucesso (ID: " . $conn->insert_id . ").");
                    } else {
                        set_feedback("Erro ao cadastrar paciente: " . $conn->error, "error");
                    }
                }
            }
        }

        // -------------------- DELETE --------------------
        elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {

            $id = (int)($_POST['id'] ?? 0);
            $table = $_POST['table'] ?? '';

            if ($id <= 0 || empty($table)) {
                set_feedback("Erro: parâmetros inválidos.", "error");
            } else {

                $success = false;

                switch ($table) {
                    case 'endereco_paciente':
                        $success = delete_endereco($conn, $id);
                        break;
                    case 'acompanhante':
                        $success = delete_acompanhante($conn, $id);
                        break;
                    case 'pacientes':
                        $success = delete_paciente($conn, $id);
                        break;
                    default:
                        set_feedback("Erro: tabela desconhecida.", "error");
                        redirect_self();
                }

                if ($success) {
                    set_feedback("Registro excluído com sucesso.");
                } else {
                    set_feedback("Erro ao excluir: " . $conn->error, "error");
                }
            }
        }
    } catch (Exception $e) {
        set_feedback("Erro inesperado: " . $e->getMessage(), "error");
    }

    redirect_self();
}

// -------------------- BUSCA DE DADOS PARA LISTAS --------------------

$enderecos = fetch_all($conn, 'endereco_paciente', 'id_endereco');
$acompanhantes = fetch_all($conn, 'acompanhante', 'id_acompanhante');
$pacientes = fetch_all(
    $conn,
    'pacientes',
    'id_paciente',
    'id_paciente, nome, idade, cpf, telefone, email, id_endereco, id_acompanhante'
);

?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="style_cadastro.css">
    <link rel="icon" type="image/png" href="imgs/logo amapon.jpeg">
    <title>Cadastro - Amapon</title>
</head>

<body>

    <header>
        <div class="header-inner">
            <div class="logo">
                <img src="imgs/logo amapon.jpeg" alt="AMAPON logo">
                <div>
                    <strong style="font-size:18px">amapon</strong><br>
                    <small style="font-style:italic;color:#444">Humanizando e Transformando Vidas.</small>
                </div>
            </div>
            <nav>
                <a href="index.php">Início</a>
                <a href="cadastro.php">Vagas</a>
                <a href="apoie.php">Apoie</a>
            </nav>
        </div>
    </header>

    <main>

        <h1>Cadastro de Pacientes e Dados Relacionados</h1>

        <?= $feedback_message ?>

        <!-- ENDEREÇO -->
        <section>
            <h2>Cadastrar/Atualizar Endereço</h2>
            <form method="POST">
                <input type="hidden" name="id_endereco" value="0">

                <div class="form-grid">
                    <div class="field">
                        <label>CEP</label>
                        <input type="text" name="cep" required>
                    </div>
                    <div class="field small">
                        <label>UF</label>
                        <input type="text" name="uf" maxlength="2" required>
                    </div>
                    <div class="field">
                        <label>Cidade</label>
                        <input type="text" name="cidade" required>
                    </div>
                    <div class="field full-width">
                        <label>Rua</label>
                        <input type="text" name="rua" required>
                    </div>
                    <div class="field full-width">
                        <label>Complemento</label>
                        <input type="text" name="complemento">
                    </div>
                </div>

                <button type="submit" name="submit_endereco">Salvar Endereço</button>
            </form>
        </section>

        <section>
            <h2>Endereços Cadastrados</h2>
            <div id="lista-enderecos">

                <?php if (empty($enderecos)): ?>
                    <p>Nenhum endereço cadastrado.</p>

                <?php else: ?>
                    <?php foreach ($enderecos as $e): ?>
                        <div class="list-item">
                            <span>
                                ID <?= $e['id_endereco'] ?>:
                                <?= htmlspecialchars($e['rua']) ?> —
                                <?= htmlspecialchars($e['cidade']) ?>/<?= htmlspecialchars($e['uf']) ?>
                                (CEP <?= htmlspecialchars($e['cep']) ?>)
                            </span>

                            <form method="POST" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $e['id_endereco'] ?>">
                                <input type="hidden" name="table" value="endereco_paciente">
                                <button onclick="return confirm('Excluir endereço?')">Excluir</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>

        <!-- ACOMPANHANTE -->
        <section>
            <h2>Cadastrar/Atualizar Acompanhante</h2>

            <form method="POST">
                <input type="hidden" name="id_acompanhante" value="0">

                <div class="form-grid">
                    <div class="field">
                        <label>Nome</label>
                        <input type="text" name="nome" required>
                    </div>
                    <div class="field small">
                        <label>Idade</label>
                        <input type="number" name="idade" required>
                    </div>
                    <div class="field">
                        <label>CPF</label>
                        <input type="text" name="cpf_acompanhante" required>
                    </div>
                    <div class="field">
                        <label>Telefone</label>
                        <input type="text" name="telefone_acompanhante">
                    </div>
                </div>

                <button type="submit" name="submit_acompanhante">Salvar Acompanhante</button>
            </form>
        </section>

        <section>
            <h2>Acompanhantes Cadastrados</h2>

            <div id="lista-acompanhantes">

                <?php if (empty($acompanhantes)): ?>
                    <p>Nenhum acompanhante cadastrado.</p>

                <?php else: ?>
                    <?php foreach ($acompanhantes as $a): ?>
                        <div class="list-item">
                            <span>
                                ID <?= $a['id_acompanhante'] ?>:
                                <?= htmlspecialchars($a['nome']) ?>
                                — <?= $a['idade'] ?> anos
                                (CPF <?= htmlspecialchars($a['cpf_acompanhante']) ?>)
                            </span>

                            <form method="POST" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $a['id_acompanhante'] ?>">
                                <input type="hidden" name="table" value="acompanhante">
                                <button onclick="return confirm('Excluir acompanhante?')">Excluir</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>

        <!-- PACIENTE -->
        <section>
            <h2>Cadastrar/Atualizar Paciente</h2>

            <form method="POST">
                <input type="hidden" name="id_paciente" value="0">

                <div class="form-grid">
                    <div class="field">
                        <label>Nome</label>
                        <input type="text" name="nome" required>
                    </div>
                    <div class="field small">
                        <label>Idade</label>
                        <input type="number" name="idade" required>
                    </div>
                    <div class="field">
                        <label>CPF</label>
                        <input type="text" name="cpf" required>
                    </div>
                    <div class="field">
                        <label>Telefone</label>
                        <input type="text" name="telefone">
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="field">
                        <label>Senha (somente novo cadastro ou alteração)</label>
                        <input type="password" name="senha">
                    </div>

                    <div class="field full-width">
                        <label>Endereço</label>
                        <select name="id_endereco">
                            <option value="">-- selecione --</option>
                            <?php foreach ($enderecos as $e): ?>
                                <option value="<?= $e['id_endereco'] ?>">
                                    <?= $e['id_endereco'] ?> —
                                    <?= htmlspecialchars($e['rua']) ?>,
                                    <?= htmlspecialchars($e['cidade']) ?>/<?= htmlspecialchars($e['uf']) ?>
                                    (CEP <?= htmlspecialchars($e['cep']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field full-width">
                        <label>Acompanhante</label>
                        <select name="id_acompanhante">
                            <option value="">-- selecione --</option>
                            <?php foreach ($acompanhantes as $a): ?>
                                <option value="<?= $a['id_acompanhante'] ?>">
                                    <?= $a['id_acompanhante'] ?> —
                                    <?= htmlspecialchars($a['nome']) ?>
                                    (CPF <?= htmlspecialchars($a['cpf_acompanhante']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" name="submit_paciente">Salvar Paciente</button>
            </form>
        </section>

        <section>
            <h2>Pacientes Cadastrados</h2>

            <div id="lista-pacientes">

                <?php if (empty($pacientes)): ?>
                    <p>Nenhum paciente cadastrado.</p>

                <?php else: ?>
                    <?php foreach ($pacientes as $p): ?>
                        <div class="list-item">
                            <span>
                                ID <?= $p['id_paciente'] ?> —
                                <?= htmlspecialchars($p['nome']) ?>
                                — CPF <?= htmlspecialchars($p['cpf']) ?>
                                — Endereço: <?= $p['id_endereco'] ?: "N/A" ?>
                                — Acompanhante: <?= $p['id_acompanhante'] ?: "N/A" ?>
                            </span>

                            <form method="POST" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $p['id_paciente'] ?>">
                                <input type="hidden" name="table" value="pacientes">
                                <button onclick="return confirm('Excluir paciente?')">Excluir</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>

    </main>

    <footer>

        <div class="container foot-inner">
            <div>
                <strong>Grupo Pervinca - DEVMentors Unimar</strong><br>
                <br>
                Avenida Hygino Muzzi Filho, 1001 · Bloco 5 · Mirante · Marília · SP <br>
                www.devmenthors.com.br<br>
                <a href="https://www.instagram.com/devmenthors?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==">@devmenthors</a>
            </div>
            <div style="text-align:right">
                <strong>AMAPON ©</strong><br>
                (14) 98114-0055
            </div>
        </div>
    </footer>
</body>

</html>