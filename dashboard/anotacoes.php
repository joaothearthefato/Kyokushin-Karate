<?php
session_start();
require '../php/config.php';
if (!isset($_SESSION['id'])) { header('Location: ../php/login.php'); exit(); }
$uid = $_SESSION['id'];
$msg_ok = ''; $msg_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $titulo   = trim(mysqli_real_escape_string($conn, $_POST['titulo']   ?? ''));
    $conteudo = trim(mysqli_real_escape_string($conn, $_POST['conteudo'] ?? ''));
    $cor      = in_array($_POST['cor'] ?? '', ['red','gold','green','blue','purple']) ? $_POST['cor'] : 'red';
    $id       = intval($_POST['id'] ?? 0);

    if ($action === 'criar') {
        if ($titulo && $conteudo) {
            mysqli_query($conn, "INSERT INTO anotacoes (usuario_id,titulo,conteudo,cor) VALUES ($uid,'$titulo','$conteudo','$cor')")
                ? $msg_ok = 'Anotação criada!' : $msg_err = 'Erro ao criar.';
        } else { $msg_err = 'Preencha todos os campos.'; }
    } elseif ($action === 'editar' && $id > 0) {
        mysqli_query($conn, "UPDATE anotacoes SET titulo='$titulo',conteudo='$conteudo',cor='$cor' WHERE id=$id AND usuario_id=$uid")
            ? $msg_ok = 'Atualizado!' : $msg_err = 'Erro ao atualizar.';
    } elseif ($action === 'excluir' && $id > 0) {
        mysqli_query($conn, "DELETE FROM anotacoes WHERE id=$id AND usuario_id=$uid");
        $msg_ok = 'Anotação excluída.';
    }
}

$busca = trim($_GET['q'] ?? '');
$wb = $busca ? "AND (titulo LIKE '%".mysqli_real_escape_string($conn,$busca)."%' OR conteudo LIKE '%".mysqli_real_escape_string($conn,$busca)."%')" : '';
$r = mysqli_query($conn, "SELECT * FROM anotacoes WHERE usuario_id=$uid $wb ORDER BY atualizado DESC");
$anotacoes = [];
if ($r) while ($row = mysqli_fetch_assoc($r)) $anotacoes[] = $row;
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Anotações | Oyama Hub</title>
<link rel="icon" href="../img/kyokushinicon.png">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow+Condensed:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/dashboard.css">
<link rel="stylesheet" href="../css/anotacoes.css">
<script>(function(){const t=localStorage.getItem('oyama-theme');if(t==='light')document.documentElement.classList.add('light');})();</script>
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<main class="anot-container">
    <section class="anot-hero">
        <div>
            <h1>ANOTAÇÕES</h1>
            <p>Registre observações e dicas dos seus treinos.</p>
        </div>
        <button class="btn-nova-anot" onclick="abrirFormNova()">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nova Anotação
        </button>
    </section>
    <?php if ($msg_ok):  ?><div class="anot-alert ok"><?= $msg_ok ?></div><?php endif; ?>
    <?php if ($msg_err): ?><div class="anot-alert err"><?= $msg_err ?></div><?php endif; ?>
    <form method="GET" class="anot-search">
        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="q" value="<?= htmlspecialchars($busca) ?>" placeholder="Buscar anotações...">
        <?php if ($busca): ?><a href="anotacoes.php" class="anot-clear">✕</a><?php endif; ?>
    </form>
    <?php if (!count($anotacoes)): ?>
        <div class="anot-vazio">
            <span>📝</span>
            <p><?= $busca ? 'Nenhuma anotação encontrada para "'.htmlspecialchars($busca).'"' : 'Nenhuma anotação ainda.' ?></p>
            <?php if (!$busca): ?><button class="btn-nova-anot" onclick="abrirFormNova()">+ Criar Anotação</button><?php endif; ?>
        </div>
    <?php else: ?>
        <div class="anot-grid">
            <?php foreach ($anotacoes as $a): ?>
            <article class="anot-card cor-<?= $a['cor'] ?>">
                <div class="anot-card-header">
                    <div class="anot-cor-dot c-<?= $a['cor'] ?>"></div>
                    <h3><?= htmlspecialchars($a['titulo']) ?></h3>
                    <div class="anot-actions">
                        <button class="anot-btn-edit" onclick="abrirEditar(<?= $a['id'] ?>,<?= json_encode($a['titulo']) ?>,<?= json_encode($a['conteudo']) ?>,'<?= $a['cor'] ?>')">
                            <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="anot-btn-del" onclick="confirmarDel(<?= $a['id'] ?>,<?= json_encode($a['titulo']) ?>)">
                            <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>
                <p class="anot-body"><?= nl2br(htmlspecialchars($a['conteudo'])) ?></p>
                <span class="anot-data"><?= date('d/m/Y H:i', strtotime($a['atualizado'])) ?></span>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- Modal criar/editar -->
<div class="anot-overlay" id="anotModal">
    <div class="anot-modal">
        <div class="anot-modal-top">
            <h3 id="anotModalTitle">Nova Anotação</h3>
            <button onclick="fecharModal()">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" id="fAction" value="criar">
            <input type="hidden" name="id" id="fId">
            <div class="anot-fg"><label>Título</label><input type="text" name="titulo" id="fTitulo" required maxlength="150"></div>
            <div class="anot-fg"><label>Conteúdo</label><textarea name="conteudo" id="fConteudo" required rows="6"></textarea></div>
            <div class="anot-fg">
                <label>Cor</label>
                <div class="anot-cores">
                    <label><input type="radio" name="cor" value="red" checked><span class="cc c-red"></span></label>
                    <label><input type="radio" name="cor" value="gold"><span class="cc c-gold"></span></label>
                    <label><input type="radio" name="cor" value="green"><span class="cc c-green"></span></label>
                    <label><input type="radio" name="cor" value="blue"><span class="cc c-blue"></span></label>
                    <label><input type="radio" name="cor" value="purple"><span class="cc c-purple"></span></label>
                </div>
            </div>
            <button type="submit" class="anot-save">Salvar</button>
        </form>
    </div>
</div>

<!-- Modal excluir -->
<div class="anot-overlay" id="anotDelModal">
    <div class="anot-modal anot-del-modal">
        <div class="anot-del-icon">🗑</div>
        <h3>Excluir Anotação?</h3>
        <p id="anotDelMsg"></p>
        <div class="anot-del-btns">
            <button onclick="fecharDelModal()" class="anot-cancel">Cancelar</button>
            <form method="POST" style="flex:1">
                <input type="hidden" name="action" value="excluir">
                <input type="hidden" name="id" id="anotDelId">
                <button type="submit" class="anot-confirmar-del">Excluir</button>
            </form>
        </div>
    </div>
</div>

<script>
const overlay  = document.getElementById('anotModal');
const delOverlay = document.getElementById('anotDelModal');

function abrirFormNova() {
    document.getElementById('anotModalTitle').textContent = 'Nova Anotação';
    document.getElementById('fAction').value = 'criar';
    document.getElementById('fId').value = '';
    document.getElementById('fTitulo').value = '';
    document.getElementById('fConteudo').value = '';
    document.querySelector('input[name="cor"][value="red"]').checked = true;
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    document.getElementById('fTitulo').focus();
}
function abrirEditar(id,titulo,conteudo,cor) {
    document.getElementById('anotModalTitle').textContent = 'Editar Anotação';
    document.getElementById('fAction').value = 'editar';
    document.getElementById('fId').value = id;
    document.getElementById('fTitulo').value = titulo;
    document.getElementById('fConteudo').value = conteudo;
    const rb = document.querySelector(`input[name="cor"][value="${cor}"]`);
    if (rb) rb.checked = true;
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function fecharModal() { overlay.classList.remove('open'); document.body.style.overflow = ''; }
function confirmarDel(id,titulo) {
    document.getElementById('anotDelId').value = id;
    document.getElementById('anotDelMsg').textContent = `Apagar "${titulo}"? Esta ação não pode ser desfeita.`;
    delOverlay.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function fecharDelModal() { delOverlay.classList.remove('open'); document.body.style.overflow = ''; }
overlay.addEventListener('click', e => { if(e.target===overlay) fecharModal(); });
delOverlay.addEventListener('click', e => { if(e.target===delOverlay) fecharDelModal(); });
document.addEventListener('keydown', e => { if(e.key==='Escape'){ fecharModal(); fecharDelModal(); } });
</script>
</body>
</html>
