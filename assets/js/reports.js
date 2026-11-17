'use strict';
(() => {
    const $ = id => document.getElementById(id);
    const number = new Intl.NumberFormat('vi-VN', {maximumFractionDigits: 1});
    const validDays = ['7', '30', '90'];
    let controller, sequence = 0, hasData = false, lastDaily = null;
    const selectedDays = () => validDays.includes(new URLSearchParams(location.search).get('days')) ? new URLSearchParams(location.search).get('days') : '30';
    $('reportDays').value = selectedDays();
    const dateLabel = value => new Intl.DateTimeFormat('vi-VN', {day:'2-digit', month:'2-digit', year:'numeric', timeZone:'Asia/Ho_Chi_Minh'}).format(new Date(value + 'T00:00:00+07:00'));
    const shortDate = value => dateLabel(value).slice(0,5);

    function validate(data, days) {
        const count = value => Number.isSafeInteger(value) && value >= 0;
        const keys = ['customers', 'total', 'new', 'processing', 'completed'];
        if (!data?.range || data.range.days !== Number(days) || !data.totals || !data.period || !Array.isArray(data.daily)
            || data.daily.length !== Number(days) || keys.some(key => !count(data.totals[key]) || !count(data.period[key]))
            || !Number.isFinite(data.period.completion_rate) || data.period.completion_rate < 0 || data.period.completion_rate > 100
            || data.daily.some(item => !/^\d{4}-\d{2}-\d{2}$/.test(item.date) || !count(item.customers) || !count(item.requests))) {
            throw new Error('Dữ liệu báo cáo chưa hợp lệ. Vui lòng làm mới để thử lại.');
        }
    }

    function chart(items) {
        const width = Math.max(280, Math.round($('reportChart').clientWidth)), height = 230, left = 42, right = 14, top = 15, bottom = 38;
        const plotWidth = width - left - right, plotHeight = height - top - bottom;
        const largest = Math.max(0, ...items.map(item => Math.max(item.customers, item.requests)));
        const maximum = Math.max(2, Math.ceil(largest / 2) * 2);
        const x = index => left + index / Math.max(1,items.length - 1) * plotWidth;
        const y = value => top + plotHeight - value / maximum * plotHeight;
        const points = key => items.map((item,index) => `${x(index).toFixed(1)},${y(item[key]).toFixed(1)}`).join(' ');
        const axis = [0,maximum / 2,maximum].map(value => `<line x1="${left}" y1="${y(value)}" x2="${width - right}" y2="${y(value)}" class="chart-grid-line"/><text x="${left - 10}" y="${y(value) + 4}" text-anchor="end" class="chart-axis-text">${number.format(value)}</text>`).join('');
        const indexes = [0, Math.floor((items.length - 1) / 2), items.length - 1];
        const labels = indexes.map(index => `<text x="${x(index)}" y="${height - 10}" text-anchor="${index === 0 ? 'start' : index === items.length - 1 ? 'end' : 'middle'}" class="chart-axis-text">${shortDate(items[index].date)}</text>`).join('');
        const dots = items.length <= 7 ? items.map((item,index) => `<circle cx="${x(index)}" cy="${y(item.requests)}" r="3" class="requests-point"/><circle cx="${x(index)}" cy="${y(item.customers)}" r="3" class="customers-point"/>`).join('') : '';
        $('reportChart').innerHTML = `<svg viewBox="0 0 ${width} ${height}" role="img" aria-labelledby="chartTitle chartDescription"><title id="chartTitle">Khách hàng mới và yêu cầu tư vấn theo ngày</title><desc id="chartDescription">${items.length} ngày từ ${dateLabel(items[0].date)} đến ${dateLabel(items[items.length - 1].date)}. Đường xanh dương là yêu cầu; đường xanh lá là khách hàng. Số liệu chính xác trong bảng theo ngày bên dưới.</desc>${axis}<polyline points="${points('requests')}" class="chart-line requests-line"/><polyline points="${points('customers')}" class="chart-line customers-line"/>${dots}${labels}</svg>`;
        $('chartCaption').textContent = largest ? 'Mỗi điểm thể hiện số hồ sơ hoặc yêu cầu được tạo trong một ngày. Xem bảng bên dưới để đối chiếu.' : 'Chưa có khách hàng hoặc yêu cầu mới trong khoảng thời gian này. Các ngày đều có giá trị 0.';
    }

    function render(data) {
        document.querySelectorAll('[data-report-total]').forEach(node => { node.textContent = number.format(data.totals[node.dataset.reportTotal]); });
        document.querySelectorAll('[data-report-period]').forEach(node => { node.textContent = number.format(data.period[node.dataset.reportPeriod]); });
        $('reportCompletion').textContent = `${number.format(data.period.completion_rate)}%`;
        $('reportRange').textContent = `${dateLabel(data.range.start_date)} – ${dateLabel(data.range.end_date)}`;
        const time = new Intl.DateTimeFormat('vi-VN',{hour:'2-digit', minute:'2-digit', timeZone:'Asia/Ho_Chi_Minh'}).format(new Date());
        $('reportUpdated').textContent = `Bao gồm hôm nay · Giờ Việt Nam · Cập nhật lúc ${time}`;
        lastDaily = data.daily; chart(data.daily);
        $('reportStatus').innerHTML = Object.entries(App.statuses).map(([key,label]) => {
            const total = data.period[key], percent = data.period.total ? total / data.period.total * 100 : 0;
            return `<div class="report-status-item"><div>${App.statusBadge(key)}<strong>${number.format(total)}<small>${number.format(Math.round(percent * 10) / 10)}%</small></strong></div><div class="report-status-track ${key}" role="img" aria-label="${App.escape(label)}: ${number.format(total)} yêu cầu"><span style="width:${percent}%"></span></div></div>`;
        }).join('') + (data.period.total ? '' : '<p class="report-no-requests">Chưa có yêu cầu trong khoảng thời gian này.</p>');
        $('dailyRowCount').textContent = `${data.daily.length} ngày`;
        $('reportDailyBody').innerHTML = data.daily.map(item => `<tr><th scope="row">${dateLabel(item.date)}</th><td class="text-end">${number.format(item.customers)}</td><td class="text-end">${number.format(item.requests)}</td></tr>`).join('');
        $('reportDailyTotal').innerHTML = `<tr><th scope="row">Tổng trong kỳ</th><td class="text-end">${number.format(data.period.customers)}</td><td class="text-end">${number.format(data.period.total)}</td></tr>`;
    }

    async function load(push = false) {
        const days = $('reportDays').value, current = ++sequence;
        const next = new URL(location.href);
        days === '30' ? next.searchParams.delete('days') : next.searchParams.set('days',days);
        if (next.href !== location.href) history[push ? 'pushState' : 'replaceState'](null,'',next);
        controller?.abort(); controller = new AbortController();
        $('reportsError').hidden = true; $('reportContent').setAttribute('aria-busy','true');
        $('refreshReports').disabled = true;
        $('reportUpdated').textContent = hasData ? 'Đang cập nhật báo cáo… Số liệu cũ được giữ đến khi tải xong.' : 'Đang tải dữ liệu báo cáo…';
        try {
            const data = await App.request('api/admin/reports.php?days=' + days, {signal:controller.signal});
            if (current !== sequence) return;
            validate(data,days); render(data); hasData = true;
        } catch (error) {
            if (error.name === 'AbortError' || current !== sequence) return;
            $('reportsError').textContent = error.message; $('reportsError').hidden = false;
            $('reportUpdated').textContent = hasData ? 'Chưa cập nhật được. Đang hiển thị khoảng thời gian của lần tải thành công trước.' : 'Chưa tải được dữ liệu. Nhấn Làm mới để thử lại.';
            if (!hasData) {
                $('reportChart').innerHTML = '<div class="empty-state"><div class="empty-icon"><i class="bi bi-wifi-off" aria-hidden="true"></i></div><h3>Chưa tải được báo cáo</h3><p>Nhấn Làm mới để thử kết nối lại.</p></div>';
                $('reportStatus').textContent = 'Chưa tải được số liệu trạng thái.';
            }
        } finally {
            if (current === sequence) { $('refreshReports').disabled = false; $('reportContent').setAttribute('aria-busy','false'); }
        }
    }
    $('reportDays').addEventListener('change', () => load(true));
    $('refreshReports').addEventListener('click', () => load());
    window.addEventListener('popstate', () => { $('reportDays').value = selectedDays(); load(); });
    window.addEventListener('resize', App.debounce(() => { if (lastDaily) chart(lastDaily); },150));
    load();
})();
