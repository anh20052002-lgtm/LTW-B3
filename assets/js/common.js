'use strict';

window.App = (() => {
    const base = document.querySelector('meta[name="app-base"]')?.content || '/';
    let csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const services = {website: 'Thiết kế website', crm: 'Giải pháp CRM', marketing: 'Digital Marketing', consulting: 'Tư vấn chuyển đổi số'};
    const statuses = {new: 'Mới', processing: 'Đang xử lý', completed: 'Hoàn thành'};
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const url = path => base.replace(/\/$/, '') + '/' + path.replace(/^\//, '');

    async function request(path, options = {}) {
        const {method = 'GET', body, signal} = options;
        const controller = new AbortController();
        const abort = () => controller.abort(signal?.reason);
        if (signal?.aborted) abort();
        else signal?.addEventListener('abort', abort, {once: true});
        let timedOut = false;
        const timeout = setTimeout(() => { timedOut = true; controller.abort(); }, 15000);
        try {
            const response = await fetch(url(path), {
                method, credentials: 'same-origin', signal: controller.signal,
                headers: {'Accept':'application/json', ...(body !== undefined ? {'Content-Type':'application/json'} : {}), ...(method !== 'GET' ? {'X-CSRF-Token': csrf} : {})},
                ...(body !== undefined ? {body: JSON.stringify(body)} : {})
            });
            let result;
            try { result = await response.json(); }
            catch (_) { throw new Error('Máy chủ trả về dữ liệu không hợp lệ. Vui lòng thử lại.'); }
            if (result.data?.csrf_token) csrf = result.data.csrf_token;
            if (!response.ok || !result.success) {
                const error = new Error(result.message || 'Không thể thực hiện thao tác.');
                error.status = response.status;
                error.errors = result.errors || {};
                if (response.status === 401 && document.body.dataset.admin === 'true') {
                    window.location.assign(url('admin/login.php'));
                }
                if (response.status === 403 && !path.includes('session.php')) {
                    // Renew the token for a manual retry; never repeat a mutation automatically.
                    fetch(url('api/session.php'), {credentials:'same-origin'}).then(r => r.json()).then(r => { if (r.data?.csrf_token) csrf = r.data.csrf_token; }).catch(() => {});
                }
                throw error;
            }
            return result.data;
        } catch (error) {
            if (error.name === 'AbortError') {
                if (timedOut) throw new Error('Kết nối quá thời gian chờ. Vui lòng kiểm tra lại trước khi gửi lần nữa.');
                throw error;
            }
            if (error instanceof TypeError) throw new Error('Không kết nối được máy chủ. Hãy kiểm tra mạng rồi thử lại.');
            throw error;
        } finally {
            clearTimeout(timeout);
            signal?.removeEventListener('abort', abort);
        }
    }

    function toast(message, type = 'success') {
        let region = document.getElementById('toastRegion');
        if (!region) {
            region = document.createElement('div');
            region.id = 'toastRegion';
            region.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            region.setAttribute('aria-live', 'polite');
            region.style.zIndex = '1090';
            document.body.append(region);
        }
        const node = document.createElement('div');
        node.className = 'toast app-toast border-0';
        node.setAttribute('role', type === 'danger' ? 'alert' : 'status');
        const tone = type === 'danger' ? 'text-danger' : 'text-success';
        node.innerHTML = `<div class="d-flex align-items-center"><div class="toast-body d-flex gap-2 align-items-start"><i class="bi ${type === 'danger' ? 'bi-exclamation-circle' : 'bi-check-circle'} ${tone}" aria-hidden="true"></i><span>${escape(message)}</span></div><button type="button" class="btn-close me-3" data-bs-dismiss="toast" aria-label="Đóng thông báo"></button></div>`;
        region.append(node);
        node.addEventListener('hidden.bs.toast', () => node.remove());
        bootstrap.Toast.getOrCreateInstance(node, {delay: 5500}).show();
    }

    function setBusy(button, busy, label) {
        if (!button) return;
        if (busy) {
            button.dataset.originalHtml = button.innerHTML;
            button.disabled = true;
            button.setAttribute('aria-busy','true');
            button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>${escape(label || 'Đang xử lý…')}`;
        } else {
            button.disabled = false;
            button.removeAttribute('aria-busy');
            if (button.dataset.originalHtml) button.innerHTML = button.dataset.originalHtml;
        }
    }
    function clearErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(field => { field.classList.remove('is-invalid'); field.removeAttribute('aria-invalid'); });
        form.querySelectorAll('[data-error-for]').forEach(node => { node.textContent = ''; });
        const summary = form.querySelector('[data-form-error]');
        if (summary) { summary.textContent = ''; summary.hidden = true; }
    }
    function showErrors(form, errors = {}) {
        let first = null;
        Object.entries(errors).forEach(([name, message]) => {
            const field = form.elements.namedItem(name);
            if (field?.classList) { field.classList.add('is-invalid'); field.setAttribute('aria-invalid','true'); first ||= field; }
            const error = [...form.querySelectorAll('[data-error-for]')].find(node => node.dataset.errorFor === name);
            if (error) error.textContent = Array.isArray(message) ? message[0] : message;
        });
        first?.focus();
    }
    const debounce = (fn, delay = 300) => { let timer; return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), delay); }; };
    function date(value, withTime = false) {
        if (!value) return '—';
        const parsed = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(parsed.getTime())) return '—';
        return new Intl.DateTimeFormat('vi-VN', {day:'2-digit', month:'2-digit', year:'numeric', ...(withTime ? {hour:'2-digit', minute:'2-digit'} : {})}).format(parsed);
    }
    const initials = value => String(value || '?').trim().split(/\s+/).slice(-2).map(part => [...part][0]).join('').toUpperCase();
    const statusBadge = status => `<span class="status-badge status-${Object.prototype.hasOwnProperty.call(statuses,status) ? status : 'new'}"><span class="status-dot" aria-hidden="true"></span>${escape(statuses[status] || status)}</span>`;
    const formObject = form => Object.fromEntries(new FormData(form));
    return {request, url, toast, setBusy, showErrors, clearErrors, escape, debounce, services, statuses, date, initials, statusBadge, formObject};
})();

document.querySelectorAll('[data-logout]').forEach(button => button.addEventListener('click', async () => {
    App.setBusy(button, true, 'Đang đăng xuất…');
    try { await App.request('api/auth/logout.php', {method:'POST', body:{}}); window.location.assign(App.url('admin/login.php')); }
    catch (error) { App.toast(error.message, 'danger'); App.setBusy(button, false); }
}));
