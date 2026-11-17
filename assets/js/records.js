'use strict';
(() => {
    const isRequest = document.body.dataset.page === 'requests';
    const entity = isRequest ? 'requests' : 'customers';
    const noun = isRequest ? 'yêu cầu' : 'khách hàng';
    const endpoint = action => `api/admin/${entity}/${action}.php`;
    const $ = id => document.getElementById(id);
    const form = $('recordForm');
    const modalElement = $('recordModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const detailElement = $('detailModal');
    const detailModal = bootstrap.Modal.getOrCreateInstance(detailElement);
    const deleteElement = $('deleteModal');
    const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteElement);
    const shownModals = new WeakSet();
    [modalElement, detailElement, deleteElement].forEach(element => {
        element.addEventListener('shown.bs.modal',() => shownModals.add(element));
        element.addEventListener('hidden.bs.modal',() => shownModals.delete(element));
    });
    // Bootstrap ignores hide() while its opening animation is running.
    function dismiss(instance, element) {
        if (shownModals.has(element)) instance.hide();
        else element.addEventListener('shown.bs.modal',() => instance.hide(),{once:true});
    }
    const defaults = {page:1, per_page:10, search:'', status:'', service:'', sort:'created_desc', has_requests:'', customer_id:''};
    const state = {...defaults};
    let records = new Map(), selected = null, deleteId = null, controller, requestNumber = 0, saving = false, deleting = false, opening = false, statusSaving = false;
    let editorGeneration = 0, editorLoading = false, optionsController, detailGeneration = 0, detailController, searchTimer;
    const params = new URLSearchParams(location.search);
    function restoreState(query) {
        clearTimeout(searchTimer);
        Object.assign(state, defaults);
        const page = Number(query.get('page'));
        if (Number.isSafeInteger(page) && page > 0) state.page = page;
        if ([10,25,50].includes(Number(query.get('per_page')))) state.per_page = Number(query.get('per_page'));
        state.search = [...(query.get('search') || '')].slice(0,100).join('');
        if (['created_desc','created_asc','name_asc','updated_desc'].includes(query.get('sort'))) state.sort = query.get('sort');
        if (isRequest) {
            if (Object.prototype.hasOwnProperty.call(App.statuses, query.get('status'))) state.status = query.get('status');
            if (Object.prototype.hasOwnProperty.call(App.services, query.get('service'))) state.service = query.get('service');
            if (/^[1-9]\d*$/.test(query.get('customer_id') || '') && Number.isSafeInteger(Number(query.get('customer_id')))) state.customer_id = query.get('customer_id');
        } else if (['with','without'].includes(query.get('has_requests'))) state.has_requests = query.get('has_requests');
        $('searchInput').value = state.search;
        $('sortFilter').value = state.sort;
        $('pageSize').value = String(state.per_page);
        if (isRequest) { $('statusFilter').value = state.status; $('serviceFilter').value = state.service; }
        else $('relationshipFilter').value = state.has_requests;
    }
    function syncUrl(mode = 'replace') {
        const next = new URL(location.href);
        for (const key of Object.keys(defaults)) {
            if (state[key] === defaults[key]) next.searchParams.delete(key);
            else next.searchParams.set(key, String(state[key]));
        }
        next.searchParams.delete('new');
        if (next.href !== location.href) history[mode === 'push' ? 'pushState' : 'replaceState'](null, '', next);
    }
    function filterSummary() {
        const labels = [];
        if (state.search) labels.push(`Từ khóa: “${state.search}”`);
        if (state.status) labels.push(App.statuses[state.status]);
        if (state.service) labels.push(App.services[state.service]);
        if (state.customer_id) labels.push(`Hồ sơ KH-${String(state.customer_id).padStart(4,'0')}`);
        if (state.has_requests) labels.push(state.has_requests === 'with' ? 'Đã có yêu cầu liên kết' : 'Chưa có yêu cầu liên kết');
        $('filterSummary').textContent = labels.length ? labels.join(' · ') : (isRequest ? 'Toàn bộ yêu cầu tư vấn' : 'Toàn bộ hồ sơ khách hàng');
        $('clearFilters').hidden = !labels.length && state.sort === defaults.sort;
    }
    restoreState(params);

    function emptyRow(title = 'Chưa có dữ liệu phù hợp', description = 'Thử thay đổi từ khóa hoặc bộ lọc để tìm kết quả khác.') {
        return `<tr><td colspan="${isRequest ? 6 : 5}"><div class="empty-state"><div class="empty-icon"><i class="bi bi-inbox" aria-hidden="true"></i></div><h3>${App.escape(title)}</h3><p>${App.escape(description)}</p></div></td></tr>`;
    }
    function person(record) {
        return `<button type="button" class="person-cell person-open" data-action="view" data-id="${record.id}" aria-label="Mở chi tiết ${App.escape(record.full_name)}"><span class="person-avatar" aria-hidden="true">${App.escape(App.initials(record.full_name))}</span><span><strong>${App.escape(record.full_name)}</strong><small>${isRequest ? App.escape(record.phone) : 'KH-' + String(record.id).padStart(4, '0')}</small></span></button>`;
    }
    function actions(record) {
        return `<div class="row-actions"><button type="button" data-action="view" data-id="${record.id}" aria-label="Xem ${App.escape(record.full_name)}"><i class="bi bi-eye" aria-hidden="true"></i><span>Xem</span></button><button type="button" data-action="edit" data-id="${record.id}" aria-label="Sửa ${App.escape(record.full_name)}"><i class="bi bi-pencil" aria-hidden="true"></i><span>Sửa</span></button><button type="button" data-action="delete" data-id="${record.id}" aria-label="Xóa ${App.escape(record.full_name)}"><i class="bi bi-trash3" aria-hidden="true"></i><span>Xóa</span></button></div>`;
    }
    function row(record) {
        return isRequest ? `<tr><td><span class="tracking-code">${App.escape(record.tracking_code)}</span></td><td>${person(record)}</td><td>${App.escape(App.services[record.service] || record.service)}</td><td>${App.statusBadge(record.status)}</td><td class="table-date">${App.date(record.created_at)}</td><td>${actions(record)}</td></tr>` : `<tr><td>${person(record)}</td><td>${App.escape(record.phone)}<span class="cell-subline">${App.escape(record.email || 'Chưa có email')}</span></td><td>${App.escape(record.company || '—')}</td><td class="table-date">${App.date(record.created_at)}</td><td>${actions(record)}</td></tr>`;
    }
    function pagination(info) {
        state.page = Number(info.page);
        const total = Number(info.total), pages = Number(info.total_pages);
        $('recordCount').textContent = new Intl.NumberFormat('vi-VN').format(total);
        const start = total ? (state.page - 1) * state.per_page + 1 : 0;
        const end = Math.min(state.page * state.per_page, total);
        $('paginationInfo').textContent = total ? `Hiển thị ${start}–${end} trong ${total} ${noun}` : `0 ${noun}`;
        const buttons = [];
        const button = (page, label, disabled, active = false, aria = '') => `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}"><button type="button" class="page-link" data-page="${page}" ${disabled ? 'disabled' : ''} ${active ? 'aria-current="page"' : ''} ${aria ? `aria-label="${aria}"` : ''}>${label}</button></li>`;
        buttons.push(button(state.page - 1, '<i class="bi bi-chevron-left" aria-hidden="true"></i>', state.page <= 1, false, 'Trang trước'));
        const startPage = Math.max(1, Math.min(state.page - 1, pages - 2));
        for (let page = startPage; page <= Math.min(pages, startPage + 2); page++) buttons.push(button(page, page, false, page === state.page, `Trang ${page}`));
        buttons.push(button(state.page + 1, '<i class="bi bi-chevron-right" aria-hidden="true"></i>', state.page >= pages || pages === 0, false, 'Trang sau'));
        $('pagination').innerHTML = buttons.join('');
    }
    async function load(historyMode = 'replace') {
        syncUrl(historyMode); filterSummary();
        const thisRequest = ++requestNumber;
        controller?.abort(); controller = new AbortController();
        $('listError').hidden = true;
        $('tableRegion').setAttribute('aria-busy','true');
        $('tableRegion').dataset.state = 'loading';
        $('refreshRecords').disabled = true;
        $('recordsBody').innerHTML = `<tr><td colspan="${isRequest ? 6 : 5}"><div class="loading-state"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Đang tải dữ liệu…</div></td></tr>`;
        try {
            const data = await App.request(endpoint('list') + '?' + new URLSearchParams(state), {signal:controller.signal});
            if (thisRequest !== requestNumber) return;
            records = new Map(data.items.map(record => [Number(record.id), record]));
            $('tableRegion').dataset.state = data.items.length ? 'ready' : 'empty';
            const filtered = state.search || state.status || state.service || state.customer_id || state.has_requests;
            $('recordsBody').innerHTML = data.items.length ? data.items.map(row).join('') : emptyRow(filtered ? 'Không tìm thấy kết quả' : `Chưa có ${noun}`, filtered ? 'Thử thay đổi từ khóa hoặc bộ lọc để tìm kết quả khác.' : `Nhấn “Thêm ${noun}” để bắt đầu.`);
            pagination(data.pagination);
            syncUrl();
        } catch (error) {
            if (error.name === 'AbortError' || thisRequest !== requestNumber) return;
            $('listError').textContent = error.message; $('listError').hidden = false;
            $('tableRegion').dataset.state = 'error';
            $('recordsBody').innerHTML = emptyRow('Không tải được danh sách', 'Nhấn nút làm mới để thử lại.');
            $('recordCount').textContent = '—'; $('paginationInfo').textContent = 'Chưa tải được dữ liệu'; $('pagination').innerHTML = '';
        } finally {
            if (thisRequest === requestNumber) { $('tableRegion').setAttribute('aria-busy','false'); $('refreshRecords').disabled = false; }
        }
    }
    function resetPage() { state.page = 1; load('push'); }
    $('searchInput').addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { state.search = $('searchInput').value.trim(); resetPage(); }, 300);
    });
    ['status','service','sort'].forEach(key => $(`${key}Filter`)?.addEventListener('change', event => { state[key] = event.target.value; resetPage(); }));
    $('relationshipFilter')?.addEventListener('change', event => { state.has_requests = event.target.value; resetPage(); });
    $('clearFilters').addEventListener('click', () => { restoreState(new URLSearchParams()); load('push'); $('searchInput').focus(); });
    window.addEventListener('popstate', () => {
        // History navigation changes the list; keep an older popup from obscuring it.
        if (!saving && !deleting && !statusSaving) {
            for (const [instance, element] of [[modal,modalElement], [detailModal,detailElement], [deleteModal,deleteElement]]) {
                if (element.classList.contains('show')) dismiss(instance, element);
            }
        }
        restoreState(new URLSearchParams(location.search)); load();
    });
    $('pageSize').addEventListener('change', event => { state.per_page = Number(event.target.value); resetPage(); });
    $('refreshRecords').addEventListener('click',() => load());
    $('pagination').addEventListener('click',event => { const button = event.target.closest('button[data-page]'); if (button && !button.disabled) { state.page = Number(button.dataset.page); load('push'); } });

    async function customerOptions(record, generation) {
        if (!isRequest) return;
        const select = $('customerId');
        select.innerHTML = '<option value="">Không liên kết hồ sơ</option>';
        select.disabled = true;
        optionsController?.abort(); optionsController = new AbortController();
        try {
            const result = await App.request('api/admin/customers/list.php?per_page=100&sort=name_asc', {signal:optionsController.signal});
            const list = [...result.items];
            for (let page = 2; page <= Number(result.pagination.total_pages); page++) {
                if (generation !== editorGeneration) return;
                const next = await App.request(`api/admin/customers/list.php?per_page=100&sort=name_asc&page=${page}`, {signal:optionsController.signal});
                list.push(...next.items);
            }
            if (record?.customer_id && !list.some(item => Number(item.id) === Number(record.customer_id))) {
                const customer = await App.request('api/admin/customers/detail.php?id=' + record.customer_id, {signal:optionsController.signal});
                list.unshift(customer);
            }
            if (generation !== editorGeneration) return;
            for (const customer of list) select.add(new Option(`${customer.full_name} · ${customer.phone}`, customer.id));
            select.value = record?.customer_id || '';
            $('customerHelp').textContent = 'Chọn hồ sơ để liên kết. Thông tin liên hệ trên yêu cầu được lưu riêng.';
        } catch (error) {
            if (error.name === 'AbortError' || generation !== editorGeneration) return;
            if (record?.customer_id) select.add(new Option('Hồ sơ đã liên kết',record.customer_id));
            select.value = record?.customer_id || '';
            App.toast('Không tải được hồ sơ liên kết: ' + error.message,'danger');
        } finally { if (generation === editorGeneration) select.disabled = false; }
    }
    async function openEditor(record = null) {
        const generation = ++editorGeneration;
        form.reset(); App.clearErrors(form); selected = record;
        form.elements.id.value = record?.id || '';
        $('recordModalTitle').textContent = record ? `Chỉnh sửa ${noun}` : `Thêm ${noun} mới`;
        for (const [key, value] of Object.entries(record || {})) {
            const field = form.elements.namedItem(key);
            if (field && key !== 'customer_id') field.value = value ?? '';
        }
        modal.show();
        editorLoading = isRequest;
        $('saveRecord').disabled = editorLoading;
        await customerOptions(record, generation);
        if (generation !== editorGeneration) return;
        editorLoading = false; $('saveRecord').disabled = false;
    }
    modalElement.addEventListener('shown.bs.modal', () => form.elements.full_name.focus());
    modalElement.addEventListener('hide.bs.modal', event => { if (saving) event.preventDefault(); });
    modalElement.addEventListener('hidden.bs.modal', () => { editorGeneration++; optionsController?.abort(); editorLoading = false; });
    detailElement.addEventListener('hide.bs.modal', event => { if (statusSaving) event.preventDefault(); });
    detailElement.addEventListener('hidden.bs.modal', () => {
        detailGeneration++; detailController?.abort();
        const next = new URL(location.href); next.searchParams.delete('id'); history.replaceState(null, '', next);
    });
    deleteElement.addEventListener('hide.bs.modal', event => { if (deleting) event.preventDefault(); });
    $('addRecord').addEventListener('click',() => openEditor());
    function validate() {
        const fields = App.formObject(form), errors = {};
        if ([...fields.full_name.trim()].length < 2 || [...fields.full_name.trim()].length > 100) errors.full_name = 'Họ tên cần từ 2 đến 100 ký tự.';
        const phone = fields.phone.replace(/[\s.()\-]/g, '');
        if (!/^(?:0[35789][0-9]{8}|\+84[35789][0-9]{8})$/.test(phone)) errors.phone = 'Nhập số di động Việt Nam hợp lệ (10 số hoặc +84).';
        if (!form.elements.email.validity.valid) errors.email = 'Vui lòng nhập địa chỉ email hợp lệ.';
        if (isRequest) {
            if (!fields.service) errors.service = 'Vui lòng chọn dịch vụ.';
            if ([...fields.message.trim()].length < 10 || [...fields.message.trim()].length > 5000) errors.message = 'Nội dung cần từ 10 đến 5.000 ký tự.';
        } else if ([...fields.notes.trim()].length > 3000) errors.notes = 'Ghi chú tối đa 3.000 ký tự.';
        return errors;
    }
    form.addEventListener('submit',async event => {
        event.preventDefault(); if (saving || editorLoading) return;
        App.clearErrors(form); const errors = validate();
        if (Object.keys(errors).length) { App.showErrors(form,errors); return; }
        saving = true; App.setBusy($('saveRecord'),true,'Đang lưu…');
        try {
            const body = App.formObject(form);
            if (isRequest) body.customer_id = body.customer_id ? Number(body.customer_id) : null;
            const saved = await App.request(endpoint(body.id ? 'update' : 'create'),{method:'POST',body});
            // Keep the current list filters; show the new record even when it does not match them.
            if (!body.id) modalElement.addEventListener('hidden.bs.modal', () => openRecord(Number(saved.id)), {once:true});
            saving = false; dismiss(modal,modalElement);
            App.toast(`Đã ${body.id ? 'cập nhật' : 'thêm'} ${noun} thành công.`);
            load();
        } catch (error) {
            App.showErrors(form,error.errors);
            const summary = form.querySelector('[data-form-error]'); summary.textContent = error.message; summary.hidden = false;
        } finally { saving = false; App.setBusy($('saveRecord'),false); }
    });
    function detailField(label, value) { return `<div class="detail-field"><dt>${App.escape(label)}</dt><dd>${App.escape(value || '—')}</dd></div>`; }
    function renderDetail(record) {
        selected = record;
        $('detailTitle').textContent = isRequest ? 'Chi tiết yêu cầu tư vấn' : 'Chi tiết khách hàng';
        const relatedLink = isRequest
            ? (record.customer_id ? `<a class="detail-related-link" href="${App.escape(App.url('admin/customers.php?id=' + encodeURIComponent(record.customer_id)))}"><i class="bi bi-person-vcard" aria-hidden="true"></i> Mở hồ sơ khách hàng <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>` : '')
            : `<a class="detail-related-link" href="${App.escape(App.url('admin/requests.php?customer_id=' + encodeURIComponent(record.id)))}"><i class="bi bi-chat-square-text" aria-hidden="true"></i> Xem ${Number(record.request_count) || 0} yêu cầu liên kết <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>`;
        $('detailBody').innerHTML = `<div class="detail-top"><span class="person-avatar" aria-hidden="true">${App.escape(App.initials(record.full_name))}</span><div><h3>${App.escape(record.full_name)}</h3><small>${isRequest ? App.escape(record.tracking_code) : 'KH-' + String(record.id).padStart(4,'0')}</small></div>${isRequest ? `<span class="ms-auto">${App.statusBadge(record.status)}</span>` : ''}</div><dl class="detail-grid">${detailField('Số điện thoại',record.phone)}${detailField('Email',record.email)}${isRequest ? detailField('Dịch vụ',App.services[record.service]) + detailField('Hồ sơ liên kết',record.customer_id ? `KH-${String(record.customer_id).padStart(4,'0')}` : 'Khách vãng lai / chưa liên kết') : detailField('Công ty',record.company) + detailField('Yêu cầu liên kết',String(record.request_count || 0))}${detailField('Ngày tạo',App.date(record.created_at,true))}${detailField('Cập nhật gần nhất',App.date(record.updated_at,true))}</dl>${relatedLink}<div class="detail-message"><h3>${isRequest ? 'Nội dung cần tư vấn' : 'Ghi chú chăm sóc'}</h3><p>${App.escape((isRequest ? record.message : record.notes) || 'Chưa có ghi chú.')}</p></div>${isRequest ? `<form id="statusForm" class="status-update-row"><label class="form-label" for="quickStatus">Cập nhật tiến độ</label><select id="quickStatus" name="status" class="form-select">${Object.entries(App.statuses).map(([key,label]) => `<option value="${key}" ${record.status === key ? 'selected' : ''}>${App.escape(label)}</option>`).join('')}</select><button type="submit" class="btn btn-primary" disabled><i class="bi bi-check2" aria-hidden="true"></i> Cập nhật</button><p class="status-error" id="statusError" role="alert" hidden></p><p class="status-success" id="statusSuccess" role="status" hidden></p></form>` : ''}`;
        $('editFromDetail').disabled = false;
        $('quickStatus')?.addEventListener('change', event => { $('statusForm').querySelector('button').disabled = event.target.value === record.status; $('statusSuccess').hidden = true; });
        $('statusForm')?.addEventListener('submit', async event => {
            event.preventDefault(); if (statusSaving || $('quickStatus').value === record.status) return;
            statusSaving = true;
            const button = event.target.querySelector('button'); App.setBusy(button,true,'Đang lưu…');
            $('quickStatus').disabled = true; $('editFromDetail').disabled = true; $('statusError').hidden = true;
            try {
                const updated = await App.request(endpoint('status'),{method:'POST',body:{id:record.id,status:$('quickStatus').value}});
                renderDetail(updated); load();
                $('statusSuccess').textContent = 'Đã cập nhật trạng thái xử lý.'; $('statusSuccess').hidden = false;
            } catch (error) {
                $('statusError').textContent = error.message; $('statusError').hidden = false;
                App.setBusy(button,false); $('quickStatus').disabled = false; $('editFromDetail').disabled = false;
            } finally { statusSaving = false; }
        });
    }
    async function openRecord(id, edit = false) {
        if (opening) return; opening = true;
        const generation = ++detailGeneration;
        detailController?.abort(); detailController = new AbortController();
        if (!edit) {
            $('detailBody').innerHTML = '<div class="loading-state"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Đang tải chi tiết…</div>';
            $('editFromDetail').disabled = true; detailModal.show();
        }
        try {
            const record = await App.request(endpoint('detail') + '?id=' + id, {signal:detailController.signal});
            if (generation !== detailGeneration) return;
            if (edit) await openEditor(record);
            else {
                renderDetail(record);
                const next = new URL(location.href); next.searchParams.set('id',String(record.id)); history.replaceState(null, '', next);
            }
        } catch (error) {
            if (error.name === 'AbortError' || generation !== detailGeneration) return;
            if (!edit) $('detailBody').innerHTML = `<div class="alert alert-danger" role="alert">${App.escape(error.message)}</div>`;
            else App.toast(error.message,'danger');
        } finally { opening = false; }
    }
    $('editFromDetail').addEventListener('click',() => {
        if (!selected || statusSaving) return;
        const record = selected;
        detailElement.addEventListener('hidden.bs.modal',() => openEditor(record),{once:true});
        dismiss(detailModal,detailElement);
    });
    $('recordsBody').addEventListener('click',event => {
        const button = event.target.closest('button[data-action]'); if (!button) return;
        const id = Number(button.dataset.id);
        if (button.dataset.action === 'delete') {
            deleteId = id; $('deleteTarget').textContent = records.get(id)?.full_name || noun;
            $('deleteError').hidden = true;
            $('deleteDescription').textContent = isRequest ? 'Yêu cầu sẽ được xóa khỏi hệ thống. Bạn không thể hoàn tác.' : 'Hồ sơ sẽ được xóa. Các yêu cầu tư vấn liên quan vẫn được giữ lại với thông tin liên hệ đã lưu.';
            deleteModal.show();
        } else openRecord(id,button.dataset.action === 'edit');
    });
    $('confirmDelete').addEventListener('click',async () => {
        if (deleting || !deleteId) return;
        deleting = true; App.setBusy($('confirmDelete'),true,'Đang xóa…');
        try {
            await App.request(endpoint('delete'),{method:'POST',body:{id:deleteId}});
            deleting = false; dismiss(deleteModal,deleteElement); App.toast(`Đã xóa ${noun}.`);
            if (records.size === 1 && state.page > 1) state.page--;
            load();
        } catch (error) { $('deleteError').textContent = error.message; $('deleteError').hidden = false; }
        finally { deleting = false; App.setBusy($('confirmDelete'),false); }
    });
    load();
    if (params.get('new') === '1') openEditor();
    else if (/^[1-9]\d*$/.test(params.get('id') || '') && Number.isSafeInteger(Number(params.get('id')))) openRecord(Number(params.get('id')));
})();
