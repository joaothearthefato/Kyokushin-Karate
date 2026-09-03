document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('theme-toggle');
    if (!themeToggle) return;
    
    const themeIcon = themeToggle.querySelector('.theme-icon');
    const themeLabel = themeToggle.querySelector('.theme-label');
    const html = document.documentElement;
    
    // Check for saved theme preference
    const currentTheme = localStorage.getItem('oyama-theme') || localStorage.getItem('theme') || 'dark';
    
    // html.classList.add('light') is handled in the <head> to prevent FOUT,
    // here we just sync the body class and button text.
    if (currentTheme === 'light') {
      document.body.classList.add('light-mode');
      if (themeIcon) themeIcon.textContent = '🌙';
      if (themeLabel) themeLabel.textContent = 'Dark';
    }
    
    themeToggle.addEventListener('click', () => {
      html.classList.toggle('light');
      document.body.classList.toggle('light-mode');
      const isLight = html.classList.contains('light');
    
      // Update button appearance with animation
      if (isLight) {
        if (themeIcon) themeIcon.textContent = '🌙';
        if (themeLabel) themeLabel.textContent = 'Dark';
        localStorage.setItem('oyama-theme', 'light');
      } else {
        if (themeIcon) themeIcon.textContent = '☀️';
        if (themeLabel) themeLabel.textContent = 'Light';
        localStorage.setItem('oyama-theme', 'dark');
      }
    
      // Add click animation
      themeToggle.style.transform = 'scale(0.95)';
      setTimeout(() => {
        themeToggle.style.transform = '';
      }, 150);
    });
});
