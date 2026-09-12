<?php
require_once __DIR__ . '/../php/session.php';
require '../php/config.php';
require_once '../php/csrf.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.php");
    exit();
}

$usuario_id = $_SESSION['id'];

// Mensagens de retorno de foto de perfil
$msg_sucesso = '';
$msg_erro    = '';

if (isset($_GET['foto'])) {
    if ($_GET['foto'] === 'salva') {
        $msg_sucesso = 'Foto de perfil atualizada com sucesso!';
    } elseif ($_GET['foto'] === 'removida') {
        $msg_sucesso = 'Foto de perfil removida com sucesso!';
    } elseif ($_GET['foto'] === 'tipo_invalido') {
        $msg_erro = 'Formato inválido. Use JPG, PNG ou WEBP.';
    } elseif ($_GET['foto'] === 'erro') {
        $msg_erro = 'Erro ao processar imagem (máx. 5MB). Tente novamente.';
    }
}

$abrir_modal_edicao = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar_perfil') {
    validar_csrf();
    $nome      = trim($_POST['nome'] ?? '');
    $nascimento = $_POST['nascimento'] ?? '';
    $stmt_faixa_atual = mysqli_prepare($conn, 'SELECT faixa_id FROM usuarios WHERE id = ?');
    mysqli_stmt_bind_param($stmt_faixa_atual, 'i', $usuario_id);
    mysqli_stmt_execute($stmt_faixa_atual);
    $faixa_atual = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_faixa_atual));
    $faixa_id = intval($faixa_atual['faixa_id'] ?? 0);
    $nova_senha = $_POST['nova_senha'] ?? '';
    $conf_senha = $_POST['confirmar_senha'] ?? '';

    $dataNascimento = DateTime::createFromFormat('!Y-m-d', $nascimento);

    if (empty($nome) || empty($nascimento) || !$dataNascimento || $dataNascimento->format('Y-m-d') !== $nascimento || $dataNascimento > new DateTime('today')) {
        $msg_erro = 'Preencha todos os campos obrigatórios.';
        $abrir_modal_edicao = true;
    } elseif (!empty($nova_senha) && $nova_senha !== $conf_senha) {
        $msg_erro = 'As senhas não coincidem.';
        $abrir_modal_edicao = true;
    } else {
        if (!empty($nova_senha)) {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE usuarios SET nome=?, nascimento=?, faixa_id=?, senha_hash=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssisi", $nome, $nascimento, $faixa_id, $hash, $usuario_id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE usuarios SET nome=?, nascimento=?, faixa_id=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssii", $nome, $nascimento, $faixa_id, $usuario_id);
        }
        if ($stmt && mysqli_stmt_execute($stmt)) {
            $_SESSION['nome'] = $nome;
            $msg_sucesso = 'Perfil atualizado com sucesso!';
        } else {
            $msg_erro = 'Erro ao atualizar perfil. Tente novamente.';
            $abrir_modal_edicao = true;
        }
    }
}

// RF06 – Buscar dados do usuário
$sql_user = "SELECT u.nome, u.email, u.nascimento, u.tipo, u.criado_em, u.foto_perfil,
                    f.nome AS faixa_nome, f.ordem AS faixa_ordem, f.id AS faixa_id
             FROM usuarios u
             LEFT JOIN faixas f ON u.faixa_id = f.id
             WHERE u.id = $usuario_id";
$result = mysqli_query($conn, $sql_user);
$usuario = mysqli_fetch_assoc($result);

// RF08 – Buscar progresso geral (resumo rápido)
$sql_treinos_count = "SELECT COUNT(*) as total, SUM(duracao_min) as total_min FROM treinos WHERE usuario_id = $usuario_id";
$r_treinos = mysqli_query($conn, $sql_treinos_count);
$dados_treino = mysqli_fetch_assoc($r_treinos);
$total_treinos = $dados_treino['total'] ?? 0;
$total_minutos = $dados_treino['total_min'] ?? 0;

$sql_katas_concluidos = "SELECT COUNT(*) as total FROM progresso WHERE usuario_id = $usuario_id AND tipo = 'kata' AND concluido = 1";
$r_katas = mysqli_query($conn, $sql_katas_concluidos);
$katas_concluidos = mysqli_fetch_assoc($r_katas)['total'] ?? 0;

$sql_kihons_concluidos = "SELECT COUNT(*) as total FROM progresso WHERE usuario_id = $usuario_id AND tipo = 'kihon' AND concluido = 1";
$r_kihons = mysqli_query($conn, $sql_kihons_concluidos);
$kihons_concluidos = mysqli_fetch_assoc($r_kihons)['total'] ?? 0;

// Buscar todas as faixas para o select
$sql_faixas = "SELECT id, nome, ordem FROM faixas ORDER BY ordem";
$r_faixas   = mysqli_query($conn, $sql_faixas);
$faixas     = [];
if ($r_faixas) {
    while ($f = mysqli_fetch_assoc($r_faixas)) $faixas[] = $f;
}

// Calcular idade
$nascimento_dt = new DateTime($usuario['nascimento'] ?? 'now');
$hoje = new DateTime();
$idade = $hoje->diff($nascimento_dt)->y;

// Membro há...
$criado = new DateTime($usuario['criado_em'] ?? 'now');
$membro_dias = $hoje->diff($criado)->days;

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil | Oyama Hub</title>
    <meta name="description" content="Visualize e edite seus dados pessoais no Oyama Hub.">
    <link rel="icon" href="../img/kyokushinicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;600;700&family=Barlow+Condensed:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/perfil.css">
    <!-- Tema aplicado antes de renderizar para evitar flash -->
    <script>
        (function () {
            const t = localStorage.getItem('oyama-theme');
            if (t === 'light') {
                document.documentElement.classList.add('light');
                document.body && document.body.classList.add('light-mode');
            }
        })();
    </script>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<main class="perfil-container">

    <!-- ── Header ── -->
    <section class="perfil-hero">
        <button type="button" class="perfil-avatar perfil-avatar-button" id="openProfilePhotoModal" aria-label="Alterar foto de perfil" title="Alterar foto de perfil">
            <?php if (!empty($usuario['foto_perfil']) && $usuario['foto_perfil'] !== 'default_avatar.png'): ?>
                <img src="../<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
            <?php else: ?>
                <span><?= strtoupper(substr($usuario['nome'], 0, 1)) ?></span>
            <?php endif; ?>
        </button>
        <div class="perfil-hero-info">
            <h1><?= htmlspecialchars($usuario['nome']) ?></h1>
            <p class="perfil-tipo"><?= ucfirst($usuario['tipo'] ?? 'Aluno') ?> · <?= htmlspecialchars($usuario['faixa_nome'] ?? 'Sem faixa') ?></p>
            <p class="perfil-meta">Membro há <strong><?= $membro_dias ?></strong> dias · <?= $idade ?> anos</p>
        </div>
    </section>

    <!-- ── Modal de Foto de Perfil (Dialog Unificado) ── -->
    <div class="profile-photo-modal" id="profilePhotoModal" aria-hidden="true">
        <div class="profile-photo-dialog">
            <div class="perfil-card-header">
                <div class="photo-modal-header-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                    <h2>FOTO DE PERFIL</h2>
                </div>
                <button type="button" class="profile-photo-close" id="closeProfilePhotoModal" aria-label="Fechar">&times;</button>
            </div>
            <div class="profile-photo-layout">
                <div class="profile-photo-preview" aria-hidden="true">
                    <?php if (!empty($usuario['foto_perfil']) && $usuario['foto_perfil'] !== 'default_avatar.png'): ?>
                        <img src="../<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
                    <?php else: ?>
                        <span><?= strtoupper(substr($usuario['nome'], 0, 1)) ?></span>
                    <?php endif; ?>
                </div>
                <div class="profile-photo-content">
                    <p>Personalize seu perfil com uma foto. Ela será exibida na sua área de aluno.</p>
                    <form method="POST" action="foto_perfil.php" enctype="multipart/form-data" class="profile-photo-form">
                        <?= csrf_input() ?>
                        <label class="profile-photo-picker" for="foto_perfil">
                            <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><path d="M12 16V4"/><path d="m8 8 4-4 4 4"/><path d="M4 14v5a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-5"/></svg>
                            <span>Escolher nova foto</span>
                            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png,image/webp" required>
                        </label>
                        <span class="profile-photo-file" id="profilePhotoFile">Nenhum arquivo selecionado</span>
                        <div class="profile-photo-actions">
                            <button type="submit" class="btn-salvar-perfil">Salvar foto</button>
                            <?php if (!empty($usuario['foto_perfil']) && $usuario['foto_perfil'] !== 'default_avatar.png'): ?>
                                <button type="button" class="profile-photo-remove" id="btnRemoverFotoPerfil">Remover</button>
                            <?php endif; ?>
                        </div>
                    </form>
                    <small class="profile-photo-help">Formatos aceitos: JPG, PNG ou WEBP · Tamanho máximo: 5 MB.</small>
                </div>
            </div>
            <?php if (!empty($usuario['foto_perfil']) && $usuario['foto_perfil'] !== 'default_avatar.png'): ?>
                <form method="POST" action="foto_perfil.php" id="removeProfilePhoto">
                    <?= csrf_input() ?>
                    <input type="hidden" name="remover" value="1">
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de edição do perfil -->
    <div class="profile-edit-modal" id="profileEditModal" aria-hidden="true">
        <div class="profile-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="profileEditTitle">
            <div class="profile-edit-header">
                <div class="photo-modal-header-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    <div>
                        <span class="profile-edit-kicker">IDENTIDADE DO DOJÔ</span>
                        <h2 id="profileEditTitle">EDITAR PERFIL</h2>
                    </div>
                </div>
                <button type="button" class="profile-photo-close" id="closeProfileEditModal" aria-label="Fechar">&times;</button>
            </div>
            <form method="POST" class="perfil-form profile-edit-form">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="editar_perfil">
                <div class="profile-edit-intro">
                    <span class="profile-edit-mark">OSU</span>
                    <p>Mantenha seus dados atualizados para que sua jornada de graduação acompanhe o seu momento no tatame.</p>
                </div>
                <div class="profile-edit-fields">
                    <div class="form-group">
                        <label for="edit_nome">Nome completo</label>
                        <input type="text" id="edit_nome" name="nome" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required autocomplete="name">
                    </div>
                    <div class="form-group">
                        <label for="edit_nascimento">Data de nascimento</label>
                        <input type="date" id="edit_nascimento" name="nascimento" value="<?= htmlspecialchars($usuario['nascimento'] ?? '') ?>" required>
                    </div>
                    <div class="form-group profile-edit-full">
                        <label for="edit_faixa">Faixa atual</label>
                        <select id="edit_faixa" name="faixa_id" disabled aria-describedby="faixa-help">
                            <?php foreach ($faixas as $faixa): ?>
                                <option value="<?= intval($faixa['id']) ?>" <?= intval($faixa['id']) === intval($usuario['faixa_id']) ? 'selected' : '' ?>><?= htmlspecialchars($faixa['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small id="faixa-help">A graduação é atualizada pelo administrador do dojo.</small>
                    </div>
                </div>
                <div class="form-separator"><span>SEGURANÇA</span></div>
                <div class="profile-edit-fields">
                    <div class="form-group">
                        <label for="edit_nova_senha">Nova senha <span>(opcional)</span></label>
                        <input type="password" id="edit_nova_senha" name="nova_senha" autocomplete="new-password" placeholder="Deixe em branco para manter">
                    </div>
                    <div class="form-group">
                        <label for="edit_confirmar_senha">Confirmar senha</label>
                        <input type="password" id="edit_confirmar_senha" name="confirmar_senha" autocomplete="new-password">
                    </div>
                </div>
                <div class="profile-edit-actions">
                    <button type="button" class="profile-edit-cancel" id="cancelProfileEdit">Cancelar</button>
                    <button type="submit" class="btn-salvar-perfil profile-edit-submit">
                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        <span>Salvar alterações</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Mensagens ── -->
    <?php if ($msg_erro): ?>
        <div class="perfil-alert perfil-alert-erro"><?= $msg_erro ?></div>
    <?php endif; ?>
    <?php if ($msg_sucesso): ?>
        <div class="perfil-alert perfil-alert-sucesso"><?= $msg_sucesso ?></div>
    <?php endif; ?>

    <!-- ── Grid Principal ── -->
    <div class="perfil-grid">

        <!-- Coluna 1: Dados + Edição -->
        <div class="perfil-col-left">

            <!-- RF06 – Visualizar dados pessoais -->
            <section class="perfil-card">
                <div class="perfil-card-header">
                    <div class="card-header-title-wrap">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <h2>DADOS PESSOAIS</h2>
                    </div>
                    <button type="button" class="btn-abrir-edicao" id="openProfileEditModal">
                        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Editar perfil
                    </button>
                </div>
                <ul class="dados-list">
                    <li>
                        <span class="dado-label">Nome</span>
                        <span class="dado-valor"><?= htmlspecialchars($usuario['nome']) ?></span>
                    </li>
                    <li>
                        <span class="dado-label">E-mail</span>
                        <span class="dado-valor"><?= htmlspecialchars($usuario['email']) ?></span>
                    </li>
                    <li>
                        <span class="dado-label">Nascimento</span>
                        <span class="dado-valor"><?= date('d/m/Y', strtotime($usuario['nascimento'])) ?> (<?= $idade ?> anos)</span>
                    </li>
                    <li>
                        <span class="dado-label">Faixa Atual</span>
                        <span class="dado-valor faixa-badge"><?= htmlspecialchars($usuario['faixa_nome'] ?? '—') ?></span>
                    </li>
                    <li>
                        <span class="dado-label">Tipo de Conta</span>
                        <span class="dado-valor"><?= ucfirst($usuario['tipo'] ?? 'aluno') ?></span>
                    </li>
                    <li>
                        <span class="dado-label">Membro desde</span>
                        <span class="dado-valor"><?= date('d/m/Y', strtotime($usuario['criado_em'])) ?></span>
                    </li>
                </ul>
            </section>

        </div>

        <!-- Coluna 2: Progresso Geral & Jornada Gamificada (RF08) -->
        <div class="perfil-col-right">

            <!-- Cards Gamificados de Progresso Geral -->
            <section class="perfil-card kpi-card-section">
                <div class="perfil-card-header">
                    <div class="card-header-title-wrap">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <h2>PROGRESSO GERAL</h2>
                    </div>
                    <span class="badge-gamified">STATUS DO DOJO</span>
                </div>

                <div class="progresso-gamified-grid">
                    <!-- Card 1: Treinos -->
                    <?php 
                        $meta_treinos = 12;
                        $pct_treinos = min(100, round(($total_treinos / $meta_treinos) * 100));
                    ?>
                    <div class="kpi-gamified-card kpi-red">
                        <div class="kpi-top">
                            <span class="kpi-label">TREINOS REALIZADOS</span>
                            <div class="kpi-icon-box icon-red">
                                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="kpi-number-wrap">
                            <span class="kpi-number"><?= $total_treinos ?></span>
                            <span class="kpi-unit">sessões</span>
                        </div>
                        <div class="kpi-footer-progress">
                            <div class="kpi-sub-text">
                                <span>Meta mensal</span>
                                <strong><?= $pct_treinos ?>%</strong>
                            </div>
                            <div class="kpi-mini-bar">
                                <div class="kpi-mini-fill fill-red" style="width: <?= $pct_treinos ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Horas -->
                    <?php 
                        $meta_horas = 15;
                        $horas_calc = round($total_minutos / 60, 1);
                        $pct_horas = min(100, round(($horas_calc / $meta_horas) * 100));
                    ?>
                    <div class="kpi-gamified-card kpi-gold">
                        <div class="kpi-top">
                            <span class="kpi-label">HORAS TREINADAS</span>
                            <div class="kpi-icon-box icon-gold">
                                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                        </div>
                        <div class="kpi-number-wrap">
                            <span class="kpi-number"><?= $horas_calc ?></span>
                            <span class="kpi-unit">horas</span>
                        </div>
                        <div class="kpi-footer-progress">
                            <div class="kpi-sub-text">
                                <span>Tempo em tatame</span>
                                <strong><?= $total_minutos ?> min</strong>
                            </div>
                            <div class="kpi-mini-bar">
                                <div class="kpi-mini-fill fill-gold" style="width: <?= $pct_horas ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Katas -->
                    <?php 
                        $total_katas_oficiais = 15;
                        $pct_katas = min(100, round(($katas_concluidos / $total_katas_oficiais) * 100));
                    ?>
                    <div class="kpi-gamified-card kpi-green">
                        <div class="kpi-top">
                            <span class="kpi-label">KATAS CONCLUÍDOS</span>
                            <div class="kpi-icon-box icon-green">
                                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                                </svg>
                            </div>
                        </div>
                        <div class="kpi-number-wrap">
                            <span class="kpi-number"><?= $katas_concluidos ?></span>
                            <span class="kpi-unit">/ <?= $total_katas_oficiais ?></span>
                        </div>
                        <div class="kpi-footer-progress">
                            <div class="kpi-sub-text">
                                <span>Domínio técnico</span>
                                <strong><?= $pct_katas ?>%</strong>
                            </div>
                            <div class="kpi-mini-bar">
                                <div class="kpi-mini-fill fill-green" style="width: <?= $pct_katas ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Kihons -->
                    <?php 
                        $total_kihons_oficiais = 30;
                        $pct_kihons = min(100, round(($kihons_concluidos / $total_kihons_oficiais) * 100));
                    ?>
                    <div class="kpi-gamified-card kpi-blue">
                        <div class="kpi-top">
                            <span class="kpi-label">KIHONS DOMINADOS</span>
                            <div class="kpi-icon-box icon-blue">
                                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>
                                </svg>
                            </div>
                        </div>
                        <div class="kpi-number-wrap">
                            <span class="kpi-number"><?= $kihons_concluidos ?></span>
                            <span class="kpi-unit">/ <?= $total_kihons_oficiais ?></span>
                        </div>
                        <div class="kpi-footer-progress">
                            <div class="kpi-sub-text">
                                <span>Golpes e bases</span>
                                <strong><?= $pct_kihons ?>%</strong>
                            </div>
                            <div class="kpi-mini-bar">
                                <div class="kpi-mini-fill fill-blue" style="width: <?= $pct_kihons ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Jornada de Faixas Gamificada com Stepper Conectado -->
            <section class="perfil-card journey-card-section">
                <div class="perfil-card-header">
                    <div class="card-header-title-wrap">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                        <h2>JORNADA DE FAIXAS</h2>
                    </div>
                    <span class="badge-gamified">KYU / DAN SYSTEM</span>
                </div>

                <?php
                $faixa_atual_ordem = intval($usuario['faixa_ordem'] ?? 1);
                $total_faixas_cnt = count($faixas);
                $progresso_faixa_pct = ($total_faixas_cnt > 0) ? min(100, round(($faixa_atual_ordem / $total_faixas_cnt) * 100)) : 0;

                $active_idx = 0;
                foreach ($faixas as $idx => $f) {
                    if (intval($f['ordem'] ?? ($idx + 1)) === $faixa_atual_ordem) {
                        $active_idx = $idx;
                        break;
                    }
                }
                $rail_pct = ($total_faixas_cnt > 1) ? min(100, max(0, round(($active_idx / ($total_faixas_cnt - 1)) * 100))) : 0;

                $faixas_detalhes = [
                    1 => ['nome_curto' => 'Branca',   'cor' => '#f5f5f5', 'borda' => '#d4d4d8', 'visual' => 'belt-branca'],
                    2 => ['nome_curto' => 'Laranja',  'cor' => '#f97316', 'borda' => '#ea580c', 'visual' => 'belt-laranja'],
                    3 => ['nome_curto' => 'Azul',     'cor' => '#2563eb', 'borda' => '#1d4ed8', 'visual' => 'belt-azul'],
                    4 => ['nome_curto' => 'Amarela',  'cor' => '#eab308', 'borda' => '#ca8a04', 'visual' => 'belt-amarela'],
                    5 => ['nome_curto' => 'Verde',    'cor' => '#16a34a', 'borda' => '#15803d', 'visual' => 'belt-verde'],
                    6 => ['nome_curto' => 'Marrom',   'cor' => '#854d0e', 'borda' => '#713f12', 'visual' => 'belt-marrom'],
                    7 => ['nome_curto' => 'M./Preta', 'cor' => '#854d0e', 'borda' => '#5a2d0c', 'stripe_black' => true, 'visual' => 'belt-marrom-preta'],
                    8 => ['nome_curto' => 'Preta',    'cor' => '#111114', 'borda' => '#eab308', 'stripe_gold' => true, 'visual' => 'belt-preta'],
                ];
                ?>

                <div class="journey-stepper-wrap">
                    <!-- Barra de Progresso Geral da Graduação -->
                    <div class="journey-progress-header">
                        <div class="journey-header-text">
                            <span class="journey-status-label">PROGRESSO DO GRADUANDO</span>
                            <span class="journey-percentage"><?= $progresso_faixa_pct ?>%</span>
                        </div>
                        <div class="journey-progress-bar">
                            <div class="journey-progress-fill" style="width: <?= $progresso_faixa_pct ?>%;"></div>
                        </div>
                    </div>

                    <!-- Stepper Conectado de Faixas com Trilho Contínuo -->
                    <div class="journey-stepper-track-wrap">
                        <!-- Linha de Fundo do Trilho -->
                        <div class="stepper-rail-bg"></div>
                        <!-- Linha Preenchida com Brilho Neon até a Faixa Atual -->
                        <div class="stepper-rail-fill" style="width: <?= $rail_pct ?>%;"></div>

                        <!-- Círculos das Faixas e Rótulos -->
                        <div class="journey-stepper-nodes">
                            <?php foreach ($faixas as $i => $f): 
                                $ordem = intval($f['ordem'] ?? ($i + 1));
                                $concluida = $ordem < $faixa_atual_ordem;
                                $atual = $ordem === $faixa_atual_ordem;
                                $bloqueada = $ordem > $faixa_atual_ordem;
                                $info = $faixas_detalhes[$ordem] ?? ['nome_curto' => $f['nome'], 'cor' => '#888', 'borda' => '#666'];
                                
                                $status_str = $concluida ? 'Concluída ✓' : ($atual ? 'Graduação Atual 🥋' : 'Bloqueada 🔒');
                                $tooltip_text = htmlspecialchars($f['nome'] . ' — ' . $status_str);
                            ?>
                                <div class="stepper-node <?= $concluida ? 'done' : ($atual ? 'active' : 'locked') ?>" 
                                     data-tooltip="<?= $tooltip_text ?>">
                                    
                                    <?php if ($bloqueada): ?>
                                        <span class="stepper-lock-badge" aria-label="Bloqueada">
                                            <svg viewBox="0 0 24 24" width="9" height="9" stroke="currentColor" stroke-width="2.5" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        </span>
                                    <?php endif; ?>

                                    <div class="stepper-circle" style="background: <?= $info['cor'] ?>; border: 2px solid <?= $info['borda'] ?>;">
                                        <?php if (!empty($info['stripe_black'])): ?>
                                            <span class="stepper-stripe stripe-black-mini"></span>
                                        <?php elseif (!empty($info['stripe_gold'])): ?>
                                            <span class="stepper-stripe stripe-gold-mini"></span>
                                        <?php endif; ?>

                                        <?php if ($concluida): ?>
                                            <span class="stepper-check-badge" aria-label="Faixa concluída"><svg viewBox="0 0 24 24" width="13" height="13" stroke="#ffffff" stroke-width="3" fill="none" class="check-icon"><polyline points="20 6 9 17 4 12"/></svg></span>
                                        <?php elseif ($atual): ?>
                                            <span class="stepper-pulse-ring"></span>
                                            <span class="stepper-pulse-dot"></span>
                                        <?php endif; ?>
                                    </div>

                                    <span class="stepper-name"><?= htmlspecialchars($info['nome_curto']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Rodapé com Faixa Atual e Citação Marcial -->
                    <div class="journey-footer-info">
                        <div class="journey-current-badge">
                            <span class="badge-dot-live"></span>
                            <span>Faixa Atual: <strong class="text-accent-belt"><?= htmlspecialchars($usuario['faixa_nome'] ?? 'Iniciante') ?></strong></span>
                        </div>
                        <span class="journey-quote">"O segredo do Kyokushin é perseverar sem desculpas."</span>
                    </div>
                </div>
            </section>

            <!-- Botão CTA Elegante para o Relatório Completo -->
            <a href="progresso.php" class="btn-ver-progresso-cta">
                <div class="cta-left">
                    <div class="cta-icon-wrap">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                        </svg>
                    </div>
                    <div class="cta-text-wrap">
                        <span class="cta-main-title">Relatório de Progresso Completo</span>
                        <span class="cta-sub-title">Acompanhe estatísticas, exames e metas detalhadas</span>
                    </div>
                </div>
                <div class="cta-arrow">
                    <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                    </svg>
                </div>
            </a>

        </div>
    </div>

</main>
<script>
document.getElementById('foto_perfil')?.addEventListener('change', function () {
    document.getElementById('profilePhotoFile').textContent = this.files[0]?.name || 'Nenhum arquivo selecionado';
});

const profilePhotoModal = document.getElementById('profilePhotoModal');
const profileEditModal = document.getElementById('profileEditModal');
const closeProfilePhotoModal = () => {
    profilePhotoModal.classList.remove('open');
    profilePhotoModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
};
document.getElementById('openProfilePhotoModal')?.addEventListener('click', () => {
    profilePhotoModal.classList.add('open');
    profilePhotoModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
});
document.getElementById('closeProfilePhotoModal')?.addEventListener('click', closeProfilePhotoModal);
profilePhotoModal?.addEventListener('click', event => { if (event.target === profilePhotoModal) closeProfilePhotoModal(); });
document.addEventListener('keydown', event => { if (event.key === 'Escape' && profilePhotoModal?.classList.contains('open')) closeProfilePhotoModal(); });

const closeProfileEditModal = () => {
    profileEditModal?.classList.remove('open');
    profileEditModal?.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
};
const openProfileEditModal = () => {
    profileEditModal?.classList.add('open');
    profileEditModal?.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    document.getElementById('edit_nome')?.focus();
};
document.getElementById('openProfileEditModal')?.addEventListener('click', openProfileEditModal);
document.getElementById('closeProfileEditModal')?.addEventListener('click', closeProfileEditModal);
document.getElementById('cancelProfileEdit')?.addEventListener('click', closeProfileEditModal);
profileEditModal?.addEventListener('click', event => { if (event.target === profileEditModal) closeProfileEditModal(); });
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && profileEditModal?.classList.contains('open')) closeProfileEditModal();
});
<?php if ($abrir_modal_edicao): ?>
openProfileEditModal();
<?php endif; ?>

// Confirmação para remover foto de perfil via AppModal
document.getElementById('btnRemoverFotoPerfil')?.addEventListener('click', () => {
    if (window.AppModal) {
        AppModal.confirm({
            title: 'Remover Foto?',
            message: 'Tem certeza que deseja remover sua foto de perfil? Ela será substituída pela inicial do seu nome.',
            confirmText: 'Sim, remover',
            cancelText: 'Cancelar',
            type: 'warning'
        }).then(confirmed => {
            if (confirmed) {
                document.getElementById('removeProfilePhoto')?.submit();
            }
        });
    } else {
        if (confirm('Tem certeza que deseja remover sua foto de perfil?')) {
            document.getElementById('removeProfilePhoto')?.submit();
        }
    }
});

// Mensagens com Toast
<?php if ($msg_erro): ?>
    if (window.AppModal) {
        AppModal.toast({ message: <?= json_encode($msg_erro) ?>, type: 'error', duration: 4500 });
    }
<?php endif; ?>
<?php if ($msg_sucesso): ?>
    if (window.AppModal) {
        AppModal.toast({ message: <?= json_encode($msg_sucesso) ?>, type: 'success', duration: 4000 });
    }
<?php endif; ?>
</script>
</body>
</html>
