'use strict';
(() => {
    const $ = id => document.getElementById(id);
    const number = new Intl.NumberFormat('vi-VN');
    const statKeys = ['customers', 'total', 'new', 'processing', 'completed'];
    let controller, requestNumber = 0, hasData = false;

    function stateRow(icon, title, description) {
        return `<tr class="recent-state-row"><td colspan="4"><div class="empty-state"><div class="empty-icon"><i class="bi ${icon}" aria-hidden="true"></i></div><h3>${App.escape(title)}</h3><p>${App.escape(description)}</p></div></td></tr>`;
    }

    function recentRow(record) {
        const link = App.escape(App.url('admin/requests.php?id=' + encodeURIComponent(record.id)));
        return `<tr><td class="recent-link" data-label="Khách hàng"><a class="person-cell" href="${link}"><span class="person-avatar" aria-hidden="true">${App.escape(App.initials(record.full_name))}</span><span><strong>${App.escape(record.full_name)}</strong><small>${App.escape(record.tracking_code)}</small></span><i class="bi bi-arrow-up-right recent-open" aria-hidden="true"></i></a></td><td class="recent-service" data-label="Dịch vụ">${App.escape(App.services[record.service] || record.service)}</td><td class="recent-status" data-label="Trạng thái">${App.statusBadge(record.status)}</td><td class="recent-date table-date" data-label="Ngày gửi">${App.escape(App.date(record.created_at))}</td></tr>`;
    }

    function validateData(data) {
        if (!data?.stats || !Array.isArray(data.recent) || !Array.isArray(data.services)
            || statKeys.some(key => !Number.isSafeInteger(Number(data.stats[key])) || Number(data.stats[key]) < 0)
            || data.services.some(item => !Number.isSafeInteger(Number(item.total)) || Number(item.total) < 0)) {
            throw new Error('Dữ liệu thống kê chưa hợp lệ. Vui lòng làm mới để thử lại.');
        }
    }

    function render(data) {
        document.querySelectorAll('[data-stat]').forEach(node => {
            node.textContent = number.format(Number(data.stats[node.dataset.stat]));
            node.classList.remove('skeleton-stat');
        });
        $('recentRequests').innerHTML = data.recent.length
            ? data.recent.map(recentRow).join('')
            : stateRow('bi-inbox', 'Chưa có yêu cầu tư vấn', 'Yêu cầu sẽ xuất hiện tại đây khi khách hàng gửi form hoặc bạn thêm mới.');

        if (Number(data.stats.total) === 0) {
            $('serviceBreakdown').innerHTML = '<div class="breakdown-empty"><i class="bi bi-bar-chart" aria-hidden="true"></i><p>Chưa có yêu cầu để thống kê dịch vụ.</p></div>';
        } else {
            const totals = Object.fromEntries(data.services.map(item => [item.service, Number(item.total)]));
            $('serviceBreakdown').innerHTML = Object.entries(App.services).map(([key, label]) => {
                const total = totals[key] || 0;
                const percent = Math.min(100, total / Number(data.stats.total) * 100);
                return `<div class="breakdown-item"><div class="breakdown-label"><span>${App.escape(label)}</span><strong>${number.format(total)}</strong></div><div class="breakdown-track" role="img" aria-label="${App.escape(label)}: ${number.format(total)} yêu cầu, ${Math.round(percent)}%"><span style="width:${percent}%"></span></div></div>`;
            }).join('');
        }

        const waiting = Number(data.stats.new);
        $('queueSummary').textContent = waiting > 0
            ? `${number.format(waiting)} yêu cầu mới đang chờ phản hồi.`
            : 'Không có yêu cầu mới đang chờ tiếp nhận.';
        const time = new Intl.DateTimeFormat('vi-VN', {hour:'2-digit', minute:'2-digit', timeZone:'Asia/Ho_Chi_Minh'}).format(new Date());
        $('dashboardUpdatedAt').textContent = `Cập nhật lúc ${time}`;
    }

    async function load() {
        const thisRequest = ++requestNumber;
        controller?.abort();
        controller = new AbortController();
        const refresh = $('refreshDashboard');
        if (!refresh.disabled) App.setBusy(refresh, true, 'Đang cập nhật…');
        ['statsGrid', 'recentRegion', 'serviceBreakdown'].forEach(id => $(id).setAttribute('aria-busy', 'true'));
        $('dashboardError').hidden = true;
        if (!hasData) $('dashboardUpdatedAt').textContent = 'Đang cập nhật dữ liệu…';
        try {
            const data = await App.request('api/admin/dashboard.php', {signal:controller.signal});
            if (thisRequest !== requestNumber) return;
            validateData(data);
            render(data);
            hasData = true;
        } catch (error) {
            if (error.name === 'AbortError' || thisRequest !== requestNumber) return;
            $('dashboardError').textContent = error.message;
            $('dashboardError').hidden = false;
            $('dashboardUpdatedAt').textContent = hasData ? 'Chưa cập nhật được. Đang hiển thị dữ liệu lần trước.' : 'Chưa tải được dữ liệu';
            if (!hasData) {
                document.querySelectorAll('[data-stat]').forEach(node => node.classList.remove('skeleton-stat'));
                $('recentRequests').innerHTML = stateRow('bi-wifi-off', 'Chưa tải được yêu cầu', 'Nhấn Làm mới để kết nối lại với hệ thống.');
                $('serviceBreakdown').innerHTML = '<div class="breakdown-empty"><p>Chưa tải được thống kê dịch vụ.</p></div>';
                $('queueSummary').textContent = 'Mở danh sách để kiểm tra yêu cầu chờ tiếp nhận.';
            }
        } finally {
            if (thisRequest === requestNumber) {
                App.setBusy(refresh, false);
                ['statsGrid', 'recentRegion', 'serviceBreakdown'].forEach(id => $(id).setAttribute('aria-busy', 'false'));
            }
        }
    }
    $('refreshDashboard').addEventListener('click', load);
    load();
})();
