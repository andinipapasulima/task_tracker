function applyTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    const btn = document.getElementById('theme-toggle');
    if (btn) {
        // Logika: Jika tema dark, tawarkan tombol ke light. Jika light, tawarkan ke dark.
        btn.innerHTML = savedTheme === 'dark' ? '☀️ Mode Terang' : '🌙 Mode Gelap';
    }
}

// WAJIB: Panggil fungsi ini tepat setelah script dimuat
applyTheme();