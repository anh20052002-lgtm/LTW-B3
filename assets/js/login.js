'use strict';
(() => {
    const form = document.getElementById('loginForm');
    const toggle = document.getElementById('togglePassword');
    const password = form.elements.password;
    toggle.addEventListener('click', () => {
        const show = password.type === 'password';
        password.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(show));
        toggle.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        toggle.innerHTML = `<i class="bi ${show ? 'bi-eye-slash' : 'bi-eye'}" aria-hidden="true"></i>`;
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        App.clearErrors(form);
        if (!form.checkValidity()) {
            const errors = {};
            if (!form.elements.email.validity.valid) errors.email = 'Vui lòng nhập địa chỉ email hợp lệ.';
            if (!password.value) errors.password = 'Vui lòng nhập mật khẩu.';
            App.showErrors(form, errors);
            return;
        }
        const button = form.querySelector('[type="submit"]');
        App.setBusy(button, true, 'Đang đăng nhập…');
        try {
            await App.request('api/auth/login.php', {method:'POST', body:App.formObject(form)});
            window.location.assign(App.url('admin/index.php'));
        } catch (error) {
            App.showErrors(form, error.errors);
            const summary = form.querySelector('[data-form-error]');
            summary.textContent = error.message; summary.hidden = false;
        } finally { App.setBusy(button, false); }
    });
})();
