        </div> <!-- /.admin-content -->
    </main> <!-- /.admin-main -->
</div> <!-- /.admin-layout -->

<script>
    // Theme Toggle Handler
    document.getElementById('themeToggleBtn')?.addEventListener('click', function() {
        const isLight = document.documentElement.classList.toggle('light-mode');
        localStorage.setItem('oyama-theme', isLight ? 'light' : 'dark');
    });

    // Helper: Chamadas às APIs administrativas
    // Centraliza o tratamento de HTTP/JSON para que qualquer falha gere uma
    // mensagem visível, em vez de o painel simplesmente não reagir.
    async function adminApi(url, method = 'GET', payload = null) {
        const options = { method: method, headers: { 'Accept': 'application/json' } };

        if (payload !== null) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(payload);
        }

        let res;
        try {
            res = await fetch(url, options);
        } catch (err) {
            throw new Error('Falha de conexão com o servidor. Verifique se o Apache/MySQL está ativo.');
        }

        const text = await res.text();
        let data = null;
        try {
            data = text ? JSON.parse(text) : null;
        } catch (err) {
            data = null;
        }

        if (!data) {
            if (res.status === 401 || res.status === 403) {
                throw new Error('Sessão expirada ou sem permissão de administrador. Faça login novamente.');
            }
            throw new Error(`Resposta inválida do servidor (HTTP ${res.status}). Verifique o log de erros do PHP.`);
        }

        if (!res.ok || data.success === false) {
            throw new Error(data.error || `Erro HTTP ${res.status}`);
        }

        return data;
    }

    // Helper: Toast Notifications
    function showNotification(msg, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `admin-toast admin-toast-${type}`;
        toast.style.cssText = `
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: #fff; padding: 12px 20px; border-radius: 6px;
            font-family: 'Oswald', sans-serif; font-weight: 600;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); opacity: 0;
            transform: translateY(20px); transition: all 0.3s ease;
        `;
        toast.textContent = msg;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        }, 50);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }
</script>
</body>
</html>
