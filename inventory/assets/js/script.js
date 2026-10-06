document.addEventListener('DOMContentLoaded', function () {
    // Confirm delete links
    document.querySelectorAll('.confirm-delete').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm('Are you sure you want to delete this?')) e.preventDefault();
        });
    });

    // Checkout modal
    const modal     = document.getElementById('checkoutModal');
    const openBtn   = document.getElementById('openCheckout');
    const closeBtn  = document.getElementById('cancelCheckout');
    const cashInput = document.getElementById('cashInput');
    const changeEl  = document.getElementById('changeDisplay');
    const totalEl   = document.getElementById('modalTotal');
    const hiddenCash= document.getElementById('hiddenCash');
    const form      = document.getElementById('checkoutForm');

    if (openBtn && modal) {
        const total = parseFloat(openBtn.dataset.total || 0);
        openBtn.addEventListener('click', () => {
            totalEl.textContent = total.toFixed(2);
            cashInput.value = '';
            changeEl.textContent = '0.00';
            modal.classList.add('active');
            setTimeout(() => cashInput.focus(), 50);
        });

        closeBtn?.addEventListener('click', () => modal.classList.remove('active'));
        modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('active'); });

        cashInput?.addEventListener('input', () => {
            const cash = parseFloat(cashInput.value) || 0;
            const change = cash - total;
            changeEl.textContent = change >= 0 ? change.toFixed(2) : '—';
            changeEl.style.color = change >= 0 ? '' : 'var(--err)';
        });

        form?.addEventListener('submit', e => {
            const cash = parseFloat(cashInput.value) || 0;
            if (cash < total) {
                e.preventDefault();
                alert('Cash tendered is less than the total.');
                return;
            }
            hiddenCash.value = cash.toFixed(2);
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') modal.classList.remove('active');
        });
    }
});