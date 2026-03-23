document.addEventListener('DOMContentLoaded', () => {
    initRippleEffect();
    initCardTilt();
    initScrollAnimations();
    initSearchBar();
    initToasts();
    initMobileMenu();
    initDragDropUpload();
    initStatCounters();
    initTooltips();
});

function initRippleEffect() {
    document.querySelectorAll('.glass-card, .archive-card, .stat-card').forEach(card => {
        card.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const ripple = document.createElement('span');
            const size = Math.max(rect.width, rect.height);
            ripple.style.cssText = `
                width: ${size}px; height: ${size}px;
                left: ${e.clientX - rect.left - size/2}px;
                top: ${e.clientY - rect.top - size/2}px;
            `;
            ripple.classList.add('ripple');
            this.style.position = 'relative';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 700);
        });
    });
}

function initCardTilt() {
    const cards = document.querySelectorAll('.glass-card[data-tilt], .stat-card');
    cards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = ((y - centerY) / centerY) * -6;
            const rotateY = ((x - centerX) / centerX) * 6;
            this.style.transform = `translateY(-6px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
            this.style.transition = 'none';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
        });
    });
}

function initScrollAnimations() {
    const elements = document.querySelectorAll('[data-animate]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, i * 80);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    elements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(24px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
        observer.observe(el);
    });
}

function initStatCounters() {
    document.querySelectorAll('.stat-value[data-count]').forEach(el => {
        const target = parseInt(el.dataset.count);
        const duration = 1200;
        const step = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = Math.floor(current).toLocaleString('id');
            if (current >= target) clearInterval(timer);
        }, 16);
    });
}

function initSearchBar() {
    const searchInputs = document.querySelectorAll('.search-bar input, #globalSearch');
    searchInputs.forEach(input => {
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                const query = input.value.trim();
                if (query) {
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('search', query);
                    window.location.href = currentUrl.toString();
                }
            }
        });
    });
}

function initMobileMenu() {
    const btn = document.getElementById('mobileMenuBtn');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('mobileOverlay');
    if (!btn || !sidebar) return;

    btn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    });
    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }
}

function initToasts() {
    const container = document.getElementById('toastContainer');
    if (!container) {
        const div = document.createElement('div');
        div.id = 'toastContainer';
        div.style.cssText = `
            position: fixed; bottom: 24px; right: 24px;
            z-index: 9999; display: flex; flex-direction: column; gap: 10px;
        `;
        document.body.appendChild(div);
    }
}

function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toastContainer');
    const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };
    const toast = document.createElement('div');
    toast.style.cssText = `
        display: flex; align-items: center; gap: 12px;
        padding: 14px 20px;
        background: rgba(1,44,68,0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(168,232,249,0.18);
        border-radius: 14px;
        color: #E8F4F8;
        font-family: 'DM Sans', sans-serif;
        font-size: 13.5px; font-weight: 500;
        box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        transform: translateX(100px); opacity: 0;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        min-width: 280px; max-width: 380px;
        cursor: pointer;
    `;
    toast.innerHTML = `<span style="font-size:20px">${icons[type]}</span><span>${message}</span>`;
    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    });

    toast.addEventListener('click', () => dismissToast(toast));
    setTimeout(() => dismissToast(toast), duration);
}

function dismissToast(toast) {
    toast.style.transform = 'translateX(100px)';
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 400);
}

function initDragDropUpload() {
    const areas = document.querySelectorAll('.file-upload-area');
    areas.forEach(area => {
        ['dragenter', 'dragover'].forEach(e => {
            area.addEventListener(e, () => area.classList.add('drag-over'));
        });
        ['dragleave', 'drop'].forEach(e => {
            area.addEventListener(e, () => area.classList.remove('drag-over'));
        });
        area.addEventListener('drop', e => {
            e.preventDefault();
            const files = e.dataTransfer.files;
            const input = area.querySelector('input[type="file"]');
            if (input && files.length) {
                const dt = new DataTransfer();
                Array.from(files).forEach(f => dt.items.add(f));
                input.files = dt.files;
                updateFileDisplay(area, files[0]);
            }
        });
        const input = area.querySelector('input[type="file"]');
        if (input) {
            input.addEventListener('change', function() {
                if (this.files.length) updateFileDisplay(area, this.files[0]);
            });
        }
    });
}

function updateFileDisplay(area, file) {
    const existing = area.querySelector('.file-selected');
    if (existing) existing.remove();

    const div = document.createElement('div');
    div.className = 'file-selected';
    div.style.cssText = `
        margin-top: 12px; padding: 10px 16px;
        background: rgba(0,83,122,0.3);
        border: 1px solid rgba(168,232,249,0.2);
        border-radius: 10px;
        font-size: 13px; color: #A8E8F9;
        display: flex; align-items: center; gap: 10px;
    `;
    const size = file.size > 1048576 ? (file.size/1048576).toFixed(1) + ' MB' : (file.size/1024).toFixed(0) + ' KB';
    div.innerHTML = `<span style="font-size:20px">📎</span><span><strong>${file.name}</strong><br><small style="opacity:0.6">${size}</small></span>`;
    area.appendChild(div);

    area.querySelector('.upload-icon').style.display = 'none';
    area.querySelector('.upload-text').textContent = 'File dipilih!';
}

function initTooltips() {
}

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        modal.querySelector('.modal')?.focus?.();
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => {
            m.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

function confirmDelete(url, itemName) {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.querySelector('#deleteItemName').textContent = itemName || 'item ini';
        modal.querySelector('#deleteConfirmBtn').onclick = () => {
            window.location.href = url;
        };
        openModal('deleteModal');
    } else {
        if (confirm(`Yakin ingin menghapus ${itemName || 'item ini'}?`)) {
            window.location.href = url;
        }
    }
}

function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
    btn.classList.add('active');
    const panel = document.getElementById(tabId);
    if (panel) {
        panel.style.display = 'block';
        panel.style.animation = 'fadeIn 0.3s ease';
    }
}

function filterByDept(deptId) {
    const divSelect = document.getElementById('division_id');
    if (!divSelect) return;
    const options = divSelect.querySelectorAll('option[data-dept]');
    options.forEach(opt => {
        opt.style.display = (!deptId || opt.dataset.dept === deptId) ? '' : 'none';
    });
    divSelect.value = '';
}

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('photoPreview');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function liveSearch(inputId, targetClass) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll(`.${targetClass}`).forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    });
}

function createStars() {
    const bg = document.querySelector('.bg-animated');
    if (!bg) return;
    for (let i = 0; i < 50; i++) {
        const star = document.createElement('div');
        const size = Math.random() * 3 + 1;
        star.style.cssText = `
            position: absolute;
            width: ${size}px; height: ${size}px;
            border-radius: 50%;
            background: rgba(168,232,249,${Math.random() * 0.4 + 0.1});
            left: ${Math.random() * 100}%;
            top: ${Math.random() * 100}%;
            animation: twinkle ${Math.random() * 4 + 2}s ease-in-out ${Math.random() * 4}s infinite;
        `;
        bg.appendChild(star);
    }
}

const starStyle = document.createElement('style');
starStyle.textContent = `
    @keyframes twinkle {
        0%, 100% { opacity: 0.2; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.5); }
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;
document.head.appendChild(starStyle);
createStars();

const currentPath = window.location.pathname;
document.querySelectorAll('.nav-item').forEach(link => {
    const href = link.getAttribute('href');
    if (href && (currentPath.includes(href) && href !== '/')) {
        link.classList.add('active');
    }
});
