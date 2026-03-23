document.addEventListener('DOMContentLoaded', () => {
    createParticles();
    initLoginEffects();
});

function createParticles() {
    const bg = document.querySelector('.login-bg');
    if (!bg) return;
    const colors = ['rgba(168,232,249,0.25)', 'rgba(245,162,1,0.18)', 'rgba(0,83,122,0.4)'];
    for (let i = 0; i < 30; i++) {
        const p = document.createElement('div');
        p.classList.add('particle');
        const size = Math.random() * 8 + 3;
        const color = colors[Math.floor(Math.random() * colors.length)];
        const duration = Math.random() * 20 + 15;
        const delay = Math.random() * 20;
        p.style.cssText = `
            width: ${size}px; height: ${size}px;
            background: ${color};
            left: ${Math.random() * 100}%;
            animation-duration: ${duration}s;
            animation-delay: -${delay}s;
        `;
        bg.appendChild(p);
    }
}

function initLoginEffects() {
    const form = document.querySelector('.login-form-container');
    if (form) {
        const children = form.children;
        Array.from(children).forEach((el, i) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = `opacity 0.5s ease ${i * 0.1}s, transform 0.5s ease ${i * 0.1}s`;
            setTimeout(() => {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, 100 + i * 100);
        });
    }

    // for input focus effects
    document.querySelectorAll('.login-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.closest('.login-input-group').style.transform = 'scale(1.01)';
        });
        input.addEventListener('blur', function() {
            this.closest('.login-input-group').style.transform = '';
        });
    });

    // show/hide password
    const showPwdBtn = document.getElementById('showPasswordBtn');
    const passwordInput = document.getElementById('password');
    if (showPwdBtn && passwordInput) {
        showPwdBtn.addEventListener('click', () => {
            const isText = passwordInput.type === 'text';
            passwordInput.type = isText ? 'password' : 'text';
            showPwdBtn.textContent = isText ? '👁️' : '🙈';
        });
    }
}
