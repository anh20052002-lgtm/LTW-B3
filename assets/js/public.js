'use strict';

(() => {
    const consultationForm = document.getElementById('consultation-form');
    const lookupForm = document.getElementById('lookup-form');
    const successPanel = document.getElementById('consultation-success');
    const resultPanel = document.getElementById('lookup-result');
    const submittedCode = document.getElementById('submitted-code');
    const inFlight = new WeakSet();
    let latestPhone = '';

    function formData(form) {
        return Object.fromEntries([...new FormData(form).entries()].map(([key, value]) => [key, String(value).trim()]));
    }

    function clearErrors(form) {
        App.clearErrors(form);
        form.querySelectorAll('.invalid-feedback').forEach(node => {
            node.dataset.defaultMessage ||= node.textContent;
            node.textContent = node.dataset.defaultMessage;
        });
        const error = form.querySelector('.form-error');
        error.classList.add('d-none');
        error.textContent = '';
    }

    function showFailure(form, error) {
        if (error.errors && Object.keys(error.errors).length) {
            App.showErrors(form, error.errors);
            Object.entries(error.errors).forEach(([name, message]) => {
                const field = form.elements.namedItem(name);
                const feedback = field?.parentElement.querySelector('.invalid-feedback');
                if (feedback) feedback.textContent = Array.isArray(message) ? message[0] : message;
            });
        }
        const alert = form.querySelector('.form-error');
        alert.textContent = error.message || 'Không thể kết nối. Vui lòng thử lại sau.';
        alert.classList.remove('d-none');
        App.toast(alert.textContent, 'danger');
        const invalid = form.querySelector('.is-invalid');
        if (invalid) invalid.focus();
    }

    function validateField(field) {
        field.setCustomValidity('');
        const value = field.value.trim();
        const length = [...value].length;
        if (field.name === 'phone') {
            const normalized = value.replace(/[\s.()\-]/g, '');
            if (!/^(?:0[35789][0-9]{8}|\+84[35789][0-9]{8})$/.test(normalized)) {
                field.setCustomValidity('Nhập số di động Việt Nam hợp lệ (10 số hoặc +84).');
            }
        } else if (field.name === 'full_name' && (length < 2 || length > 100)) {
            field.setCustomValidity('Họ tên cần từ 2 đến 100 ký tự.');
        } else if (field.name === 'message' && (length < 10 || length > 5000)) {
            field.setCustomValidity('Nội dung cần từ 10 đến 5.000 ký tự.');
        } else if (field.name === 'tracking_code' && !/^NX-[A-Z0-9]{12}$/.test(value.toUpperCase())) {
            field.setCustomValidity('Mã tra cứu có dạng NX- và 12 ký tự.');
        }
        const valid = field.checkValidity();
        field.setAttribute('aria-invalid', String(!valid));
        return valid;
    }

    function validate(form) {
        // Trim before native validation so whitespace cannot satisfy required fields.
        form.querySelectorAll('input, textarea').forEach(field => {
            field.value = field.value.trim();
        });
        form.querySelectorAll('input, select, textarea').forEach(validateField);
        form.classList.add('was-validated');
        if (!form.checkValidity()) {
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) firstInvalid.focus();
            return false;
        }
        return true;
    }

    consultationForm.addEventListener('submit', async event => {
        event.preventDefault();
        if (inFlight.has(consultationForm)) return;
        clearErrors(consultationForm);
        if (!validate(consultationForm)) return;
        const button = consultationForm.querySelector('[type="submit"]');
        const payload = formData(consultationForm);
        latestPhone = payload.phone;
        inFlight.add(consultationForm);
        consultationForm.setAttribute('aria-busy', 'true');
        App.setBusy(button, true, 'Đang gửi yêu cầu…');
        try {
            const result = await App.request('api/public/create.php', {method: 'POST', body: payload});
            submittedCode.textContent = result.tracking_code;
            document.getElementById('tracking-code').value = result.tracking_code;
            document.getElementById('lookup-phone').value = latestPhone;
            consultationForm.classList.add('d-none');
            successPanel.classList.remove('d-none');
            successPanel.focus({preventScroll: true});
            App.toast('Đã gửi yêu cầu. Hãy lưu mã tra cứu của bạn.', 'success');
        } catch (error) {
            showFailure(consultationForm, error);
        } finally {
            inFlight.delete(consultationForm);
            consultationForm.removeAttribute('aria-busy');
            App.setBusy(button, false);
        }
    });

    document.getElementById('message').addEventListener('input', event => {
        document.getElementById('message-count').textContent = `${event.target.value.length}/5000`;
    });

    document.getElementById('send-another').addEventListener('click', () => {
        consultationForm.reset();
        clearErrors(consultationForm);
        consultationForm.querySelectorAll('input, select, textarea').forEach(field => {
            field.setCustomValidity('');
            field.removeAttribute('aria-invalid');
        });
        consultationForm.classList.remove('was-validated', 'd-none');
        successPanel.classList.add('d-none');
        document.getElementById('message-count').textContent = '0/5000';
        document.getElementById('full-name').focus();
    });

    document.getElementById('copy-code').addEventListener('click', async () => {
        const code = submittedCode.textContent;
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(code);
            } else {
                const field = document.createElement('textarea');
                field.value = code;
                field.setAttribute('readonly', '');
                field.style.position = 'fixed';
                field.style.opacity = '0';
                document.body.appendChild(field);
                field.select();
                const copied = document.execCommand('copy');
                field.remove();
                if (!copied) throw new Error('copy failed');
            }
            App.toast('Đã sao chép mã yêu cầu.', 'success');
        } catch (_) {
            App.toast('Hãy chọn và sao chép mã yêu cầu đang hiển thị.', 'danger');
        }
    });

    document.querySelectorAll('[data-select-service]').forEach(link => {
        link.addEventListener('click', () => {
            if (!successPanel.classList.contains('d-none')) document.getElementById('send-another').click();
            const select = document.getElementById('service');
            select.value = link.dataset.selectService;
            select.dispatchEvent(new Event('input', {bubbles: true}));
        });
    });

    document.querySelectorAll('#main-nav .nav-link, #main-nav .nav-cta').forEach(link => {
        link.addEventListener('click', () => {
            const navigation = document.getElementById('main-nav');
            if (navigation.classList.contains('show')) bootstrap.Collapse.getOrCreateInstance(navigation).hide();
        });
    });

    function formatDate(value) {
        if (!value) return 'Chưa có thông tin';
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return String(value);
        return date.toLocaleString('vi-VN', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'});
    }

    const statuses = {
        new: {label: 'Mới tiếp nhận', icon: 'inbox', note: 'Yêu cầu đã được tiếp nhận. Đội ngũ sẽ xem xét và liên hệ qua thông tin bạn đã cung cấp.'},
        processing: {label: 'Đang xử lý', icon: 'hourglass-split', note: 'Đội ngũ đang xử lý yêu cầu và trao đổi giải pháp phù hợp với bạn.'},
        completed: {label: 'Hoàn thành', icon: 'check-circle', note: 'Yêu cầu tư vấn đã hoàn thành. Bạn có thể gửi yêu cầu mới nếu cần trao đổi thêm.'}
    };

    lookupForm.addEventListener('submit', async event => {
        event.preventDefault();
        if (inFlight.has(lookupForm)) return;
        clearErrors(lookupForm);
        resultPanel.classList.add('d-none');
        resultPanel.replaceChildren();
        if (!validate(lookupForm)) return;
        const button = lookupForm.querySelector('[type="submit"]');
        const payload = formData(lookupForm);
        payload.tracking_code = payload.tracking_code.toUpperCase();
        inFlight.add(lookupForm);
        lookupForm.setAttribute('aria-busy', 'true');
        App.setBusy(button, true, 'Đang tra cứu…');
        try {
            const result = await App.request('api/public/lookup.php', {method: 'POST', body: payload});
            const status = statuses[result.status] || statuses.new;
            const services = App.services || {
                website: 'Thiết kế website', crm: 'Giải pháp CRM', marketing: 'Digital Marketing', consulting: 'Tư vấn chuyển đổi số'
            };
            const statusKey = Object.prototype.hasOwnProperty.call(statuses, result.status) ? result.status : 'new';
            resultPanel.innerHTML = `<div class="lookup-result-header"><div><span class="lookup-result-code">${App.escape(result.tracking_code)}</span><h3>${App.escape(services[result.service] || result.service)}</h3></div><span class="status-badge status-${statusKey}"><i class="bi bi-${status.icon}" aria-hidden="true"></i>${status.label}</span></div><div class="lookup-result-info"><div>Ngày gửi<strong>${App.escape(formatDate(result.created_at))}</strong></div><div>Cập nhật gần nhất<strong>${App.escape(formatDate(result.updated_at))}</strong></div></div><p class="status-description">${status.note}</p>`;
            resultPanel.classList.remove('d-none');
            resultPanel.focus({preventScroll: true});
        } catch (error) {
            showFailure(lookupForm, error);
        } finally {
            inFlight.delete(lookupForm);
            lookupForm.removeAttribute('aria-busy');
            App.setBusy(button, false);
        }
    });

    [consultationForm, lookupForm].forEach(form => {
        form.addEventListener('input', event => {
            const field = event.target;
            if (field.matches('input,select,textarea')) {
                field.classList.remove('is-invalid');
                if (form.classList.contains('was-validated')) validateField(field);
                else {
                    field.setCustomValidity('');
                    field.removeAttribute('aria-invalid');
                }
            }
        });
    });
})();
