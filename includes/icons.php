<?php
/**
 * icons.php — Centralizador de Ícones SVG do Oyama Hub
 *
 * Uso:
 *   require_once __DIR__ . '/icons.php'; // ou via include
 *   echo render_icon('treinos', 20);
 *   echo render_icon('arrow-right', 16, 'quick-arrow');
 */

function get_oyama_icons(): array {
    return [
        'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        
        'treinos' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        
        'horas' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        
        'atividade' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        
        'zap' => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>',
        
        'katas' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>',
        
        'kihons' => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/>',
        
        'progresso' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        
        'anotacoes' => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        
        'perfil' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        
        'arrow-right' => '<polyline points="9 18 15 12 9 6"/>',
        
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        
        'close' => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        
        'warning' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
    ];
}

/**
 * Renderiza uma tag <svg> completa para o ícone solicitado.
 *
 * @param string $name   Nome do ícone
 * @param int    $size   Largura e altura em pixels (padrão 20)
 * @param string $class  Classes CSS adicionais
 * @param int    $stroke Espessura da linha (stroke-width, padrão 2)
 * @return string
 */
function render_icon(string $name, int $size = 20, string $class = '', int $stroke = 2): string {
    $icons = get_oyama_icons();
    $inner = $icons[$name] ?? '';

    if (empty($inner)) {
        return '';
    }

    $classAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';

    return sprintf(
        '<svg%s viewBox="0 0 24 24" width="%d" height="%d" stroke="currentColor" stroke-width="%d" fill="none" stroke-linecap="round" stroke-linejoin="round">%s</svg>',
        $classAttr,
        $size,
        $size,
        $stroke,
        $inner
    );
}
