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

function checkStrength(inp) {
    const v = inp.value;
    let score = 0;
    let strengthText = "";
    let strengthClass = "";
    let suggestions = [];
    let fillCount = 0;
    
    if (v.length === 0) {
        strengthText = "🔒 Masukkan password";
        strengthClass = "";
        suggestions = [];
        fillCount = 0;
    } else {
        // Hitung skor
        if (v.length >= 8) score++;
        if (/[A-Z]/.test(v)) score++;
        if (/[0-9]/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;
        
        // Rekomendasi berdasarkan yang kurang
        if (v.length < 8) suggestions.push("📏 Minimal 8 karakter");
        if (!/[A-Z]/.test(v)) suggestions.push("🔠 Tambahkan huruf BESAR");
        if (!/[0-9]/.test(v)) suggestions.push("🔢 Tambahkan angka");
        if (!/[^A-Za-z0-9]/.test(v)) suggestions.push("✨ Tambahkan simbol (!@#$%*)");
        
        fillCount = Math.min(score, 4);
        
        if (score <= 1) {
            strengthText = "❌ LEMAH - Password terlalu mudah ditebak";
            strengthClass = "weak";
        } else if (score <= 2) {
            strengthText = "⚠️ SEDANG - Masih bisa diperkuat";
            strengthClass = "medium";
        } else if (score <= 3) {
            strengthText = "👍 BAIK - Hampir sempurna!";
            strengthClass = "good";
        } else {
            strengthText = "✅ KUAT - Password sudah sangat aman!";
            strengthClass = "strong";
        }
    }
    
    // Update garis
    for (let i = 1; i <= 4; i++) {
        const el = document.getElementById('seg' + i);
        if (i <= fillCount) {
            el.className = 'strength-seg ' + strengthClass;
        } else {
            el.className = 'strength-seg';
        }
    }
    
    // Update teks kekuatan
    const label = document.getElementById('strength-label');
    if (label) {
        label.innerHTML = strengthText;
        label.className = 'strength-' + strengthClass;
    }
    
    // Update rekomendasi
    const suggestionsDiv = document.getElementById('password-suggestions');
    if (suggestionsDiv && suggestions.length > 0 && v.length > 0) {
        suggestionsDiv.innerHTML = '<div style="margin-top: 8px; padding: 8px; background: var(--warning-light); border-radius: 8px; border-left: 3px solid var(--warning);">' +
            '<strong>💡 Tips biar lebih kuat:</strong><br>' +
            suggestions.map(s => '• ' + s).join('<br>') +
            '</div>';
        suggestionsDiv.style.display = 'block';
    } else if (suggestionsDiv && v.length > 0 && score >= 4) {
        suggestionsDiv.innerHTML = '<div style="margin-top: 8px; padding: 8px; background: var(--success-light); border-radius: 8px; border-left: 3px solid var(--success);">' +
            '🎉 Password sudah kuat! Lanjut daftar.</div>';
        suggestionsDiv.style.display = 'block';
    } else if (suggestionsDiv) {
        suggestionsDiv.style.display = 'none';
    }
}