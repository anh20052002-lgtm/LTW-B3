'use strict';
(() => {
    const $ = id => document.getElementById(id);
    const form = $('passwordForm');
    let saving = false;
    async function loadProfile() {
        $('accountProfile').setAttribute('aria-busy','true'); $('accountError').hidden = true; $('retryAccount').hidden = true;
        try {
            const account = await App.request('api/admin/account.php');
            $('accountName').textContent = account.name;
            $('accountInitials').textContent = App.initials(account.name);
            $('accountEmail').textContent = account.email;
            $('accountCreated').textContent = App.date(account.created_at,true);
        } catch (error) {
            $('accountName').textContent = 'Chưa tải được thông tin';
            $('accountError').textContent = error.message; $('accountError').hidden = false; $('retryAccount').hidden = false;
        } finally { $('accountProfile').setAttribute('aria-busy','false'); }
    }
    $('retryAccount').addEventListener('click',loadProfile);
    document.querySelectorAll('[data-password-toggle]').forEach(button => {
        const originalLabel = button.getAttribute('aria-label');
        button.addEventListener('click', () => {
            const field = $(button.dataset.passwordToggle), show = field.type === 'password';
            field.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed',String(show));
            button.setAttribute('aria-label',show ? originalLabel.replace('Hiện','Ẩn') : originalLabel);
            button.innerHTML = `<i class="bi ${show ? 'bi-eye-slash' : 'bi-eye'}" aria-hidden="true"></i>`;
        });
    });
    function validate(fields) {
        const errors = {}, bytes = value => new TextEncoder().encode(value).length;
        if (!fields.current_password || bytes(fields.current_password) > 1024 || fields.current_password.includes('\0')) errors.current_password = 'Vui lòng nhập mật khẩu hiện tại hợp lệ.';
        if (bytes(fields.new_password) < 12 || bytes(fields.new_password) > 72 || fields.new_password.includes('\0')) errors.new_password = 'Mật khẩu mới cần từ 12 đến 72 byte và không chứa ký tự null.';
        if (!fields.confirm_password || fields.confirm_password !== fields.new_password) errors.confirm_password = 'Mật khẩu xác nhận chưa khớp.';
        if (!errors.new_password && fields.new_password === fields.current_password) errors.new_password = 'Mật khẩu mới cần khác mật khẩu hiện tại.';
        return errors;
    }
    form.addEventListener('submit',async event => {
        event.preventDefault(); if (saving) return;
        App.clearErrors(form); $('passwordSuccess').hidden = true;
        const fields = App.formObject(form), errors = validate(fields);
        if (Object.keys(errors).length) { App.showErrors(form,errors); return; }
        saving = true; App.setBusy($('changePassword'),true,'Đang lưu mật khẩu…');
        try {
            await App.request('api/admin/password.php',{method:'POST',body:fields});
            form.reset();
            document.querySelectorAll('[data-password-toggle]').forEach(button => {
                $(button.dataset.passwordToggle).type = 'password'; button.setAttribute('aria-pressed','false');
                button.setAttribute('aria-label',button.getAttribute('aria-label').replace('Ẩn','Hiện'));
                button.innerHTML = '<i class="bi bi-eye" aria-hidden="true"></i>';
            });
            $('passwordSuccess').textContent = 'Đã đổi mật khẩu thành công. Phiên hiện tại được giữ; các phiên đăng nhập khác sẽ cần đăng nhập lại.';
            $('passwordSuccess').hidden = false; $('passwordSuccess').focus();
        } catch (error) {
            App.showErrors(form,error.errors);
            const summary = form.querySelector('[data-form-error]'); summary.textContent = error.message; summary.hidden = false;
        } finally { saving = false; App.setBusy($('changePassword'),false); }
    });
    loadProfile();
})();
