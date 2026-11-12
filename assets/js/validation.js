/* Validasi form sisi klien (validasi sebenarnya tetap dilakukan di PHP) */
(function () {
  const rules = {
    email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) || 'Format email tidak valid.',
    username: v => /^[a-z0-9_]{3,30}$/.test(v) || 'Username 3–30 karakter: huruf kecil, angka, atau _.',
    password: v => v.length >= 6 || 'Password minimal 6 karakter.',
    phone: v => v === '' || /^[0-9+\-\s]{8,20}$/.test(v) || 'Nomor telepon tidak valid.',
  };
  function check(form) {
    let ok = true;
    form.querySelectorAll('.field-error').forEach(n => n.remove());
    form.querySelectorAll('.invalid').forEach(n => n.classList.remove('invalid'));
    const err = (el, msg) => {
      ok = false; el.classList.add('invalid');
      const p = document.createElement('div'); p.className = 'field-error'; p.textContent = msg;
      (el.closest('.pw-wrap') || el).after(p);
    };
    form.querySelectorAll('input,select,textarea').forEach(el => {
      if (el.type === 'hidden' || el.disabled) return;
      const v = el.value.trim();
      if (el.required && v === '') return err(el, 'Kolom ini wajib diisi.');
      if (v === '' && !el.required) return;
      const rule = el.dataset.rule || (el.type === 'email' ? 'email' : null);
      if (rule && rules[rule]) { const r = rules[rule](el.type === 'password' ? el.value : v); if (r !== true) return err(el, r); }
      if (el.dataset.match) { const o = form.querySelector('[name="' + el.dataset.match + '"]'); if (o && o.value !== el.value) return err(el, 'Konfirmasi tidak sama.'); }
      if (el.type === 'number' && el.min !== '' && Number(v) < Number(el.min)) return err(el, 'Nilai minimal ' + el.min + '.');
    });
    return ok;
  }
  document.querySelectorAll('form[data-validate]').forEach(f => {
    f.setAttribute('novalidate', '');
    f.addEventListener('submit', e => { if (!check(f)) { e.preventDefault(); const x = f.querySelector('.invalid'); if (x) x.focus(); } });
  });
})();
