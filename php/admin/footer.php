        </div> <!-- /.admin-content -->
    </main> <!-- /.admin-main -->
</div> <!-- /.admin-layout -->

<script>
    // Theme Toggle Handler
    document.getElementById('themeToggleBtn')?.addEventListener('click', function() {
        const isLight = document.documentElement.classList.toggle('light-mode');
        localStorage.setItem('oyama-theme', isLight ? 'light' : 'dark');
    });

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
