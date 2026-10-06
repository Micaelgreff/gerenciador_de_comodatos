document.querySelector('[data-toggle-password]')?.addEventListener('click', event => {
    const button = event.currentTarget;
    const passwordInput = document.getElementById('senha');
    const icon = document.getElementById('toggleIcon');
    const visible = passwordInput.type === 'password';
    passwordInput.type = visible ? 'text' : 'password';
    icon.classList.toggle('fa-eye', !visible);
    icon.classList.toggle('fa-eye-slash', visible);
    button.setAttribute('aria-pressed', String(visible));
    button.setAttribute('aria-label', visible ? 'Ocultar senha' : 'Mostrar senha');
});
