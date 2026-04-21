<?php
use Core\Config;
use Core\Security;

require_once __DIR__ . '/../shared/badge_estado.php';

$baseUrl = Config::baseUrl();
$query = http_build_query(array_filter($filtros ?? [], static fn($value): bool => $value !== '' && $value !== null));
$excelUrl = $baseUrl . '/exportar/todas/excel' . ($query ? '?' . $query : '');
?>

<div class="analytics-toolbar animate-fade-down">
  <div>
    <h1 class="page-title">Analitica de datos exportables</h1>
    <p class="analytics-subtitle">
      Este tablero usa la misma base de datos que alimenta el Excel. Puedes filtrar por estado, tipo, empleado y rango de fechas para ver exactamente lo que luego exportas.
    </p>
  </div>
  <div class="analytics-actions">
    <a href="<?= $baseUrl ?>/dashboard" class="btn btn-outline">Volver al panel</a>
    <a href="<?= $excelUrl ?>" class="btn btn-green">Exportar este corte a Excel</a>
  </div>
</div>

<form method="get" action="<?= $baseUrl ?>/dashboard/analitica" class="analytics-filter-card animate-fade-up" id="analyticsFilters">
  <div class="analytics-filter-grid">
    <div class="analytics-field">
      <label for="estado">Estado</label>
      <select id="estado" name="estado">
        <option value="">Todos</option>
        <?php foreach ($labelsEstado as $key => $label): ?>
          <option value="<?= htmlspecialchars($key) ?>" <?= (($filtros['estado'] ?? '') === $key) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="analytics-field">
      <label for="tipo">Tipo</label>
      <select id="tipo" name="tipo">
        <option value="">Todos</option>
        <?php foreach ($tipos as $key => $label): ?>
          <option value="<?= htmlspecialchars($key) ?>" <?= (($filtros['tipo'] ?? '') === $key) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="analytics-field">
      <label for="nit">NIT empleado</label>
      <input id="nit" name="nit" type="text" value="<?= htmlspecialchars($filtros['nit'] ?? '') ?>" placeholder="Ej. 900123456"/>
    </div>

    <div class="analytics-field">
      <label for="fecha_desde">Fecha desde</label>
      <input id="fecha_desde" name="fecha_desde" type="date" value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>"/>
    </div>

    <div class="analytics-field">
      <label for="fecha_hasta">Fecha hasta</label>
      <input id="fecha_hasta" name="fecha_hasta" type="date" value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>"/>
    </div>
  </div>

  <div class="analytics-filter-actions" style="margin-top:16px;">
    <button type="submit" class="btn btn-green">Aplicar filtros</button>
    <a href="<?= $baseUrl ?>/dashboard/analitica" class="btn btn-gray" id="analyticsReset">Limpiar</a>
    <span class="badge badge-info" id="analyticsCount"><?= (int) $totalRegistros ?> registros en el corte actual</span>
  </div>
</form>

<div class="stats-row animate-fade-up" id="analyticsKpis">
  <?php foreach ($kpis as $kpi): ?>
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 19h16"></path>
          <path d="M7 16V8"></path>
          <path d="M12 16V5"></path>
          <path d="M17 16v-4"></path>
        </svg>
      </div>
      <div class="num"><?= htmlspecialchars((string) $kpi['value']) ?></div>
      <div class="lbl"><?= htmlspecialchars($kpi['label']) ?></div>
      <div class="analytics-top-meta"><?= htmlspecialchars($kpi['hint']) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="analytics-grid">
  <section class="analytics-panel animate-fade-in">
    <div class="analytics-caption">
      <strong>Distribucion por estado</strong>
      <span>Participacion sobre el total filtrado</span>
    </div>
    <div class="analytics-chart-wrap">
      <canvas id="estadoChart"></canvas>
    </div>
  </section>

  <section class="analytics-panel animate-fade-in">
    <div class="analytics-caption">
      <strong>Distribucion por tipo de solicitud</strong>
      <span>Los tipos con mas uso en el corte actual</span>
    </div>
    <div class="analytics-chart-wrap">
      <canvas id="tipoChart"></canvas>
    </div>
  </section>
</div>

<div class="analytics-grid">
  <section class="analytics-panel animate-fade-in">
    <div class="analytics-caption">
      <strong>Tendencia mensual de registros</strong>
      <span>Ultimos meses detectados en la base consultada</span>
    </div>
    <div class="analytics-chart-wrap analytics-chart-wrap--wide">
      <canvas id="mesChart"></canvas>
    </div>
  </section>

  <section class="analytics-panel animate-fade-in">
    <div class="analytics-caption">
      <strong>Lectura rapida del corte</strong>
      <span>Resumen por estado sobre el total visible</span>
    </div>
    <div class="analytics-summary" id="analyticsSummary">
      <?php if (empty($porEstado)): ?>
        <div class="empty-state"><p>No hay datos para resumir con este filtro.</p></div>
      <?php else: ?>
        <?php foreach ($porEstado as $fila): ?>
          <div class="analytics-summary-item">
            <strong><?= htmlspecialchars($fila['label']) ?></strong>
            <span><?= (int) $fila['total'] ?> registros</span>
            <em><?= number_format((float) $fila['percent'], 1) ?>%</em>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</div>

<div class="section-header section-header--spaced">
  <h2>Vista previa de registros exportables</h2>
  <span class="badge badge-info" id="analyticsPreviewCount">Mostrando <?= count($ultimas) ?> de <?= (int) $totalRegistros ?></span>
</div>

<div id="analyticsTableWrap"><?php $filas = $ultimas; require __DIR__ . '/../shared/tabla_solicitudes.php'; ?></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script <?= Security::scriptNonceAttr() ?>>
document.addEventListener('DOMContentLoaded',()=>{
  const baseUrl = <?= json_encode($baseUrl) ?>;
  const initialData = <?= $analyticsJson ?: '{}' ?>;
  const form = document.getElementById('analyticsFilters');
  const resetLink = document.getElementById('analyticsReset');
  const exportLink = document.querySelector('.analytics-actions .btn-green');
  const kpisContainer = document.getElementById('analyticsKpis');
  const countBadge = document.getElementById('analyticsCount');
  const previewCount = document.getElementById('analyticsPreviewCount');
  const summaryContainer = document.getElementById('analyticsSummary');
  const tableWrap = document.getElementById('analyticsTableWrap');

  const chartPalette = ['#3f8753','#5aa86d','#74bd87','#9ed1ac','#c8e2ce','#5e9870'];
  let estadoChart;
  let tipoChart;
  let mesChart;

  function renderKpis(kpis){
    kpisContainer.innerHTML = kpis.map((kpi)=>`
      <div class="stat-card">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 19h16"></path>
            <path d="M7 16V8"></path>
            <path d="M12 16V5"></path>
            <path d="M17 16v-4"></path>
          </svg>
        </div>
        <div class="num">${kpi.value}</div>
        <div class="lbl">${escapeHtml(kpi.label)}</div>
        <div class="analytics-top-meta">${escapeHtml(kpi.hint)}</div>
      </div>
    `).join('');
  }

  function renderSummary(items){
    if (!items.length) {
      summaryContainer.innerHTML = '<div class="empty-state"><p>No hay datos para resumir con este filtro.</p></div>';
      return;
    }
    summaryContainer.innerHTML = items.map((item)=>`
      <div class="analytics-summary-item">
        <strong>${escapeHtml(item.label)}</strong>
        <span>${item.total} registros</span>
        <em>${Number(item.percent).toFixed(1)}%</em>
      </div>
    `).join('');
  }

  function ensureCharts(){
    if (estadoChart) return;

    estadoChart = new Chart(document.getElementById('estadoChart'), {
      type: 'doughnut',
      data: { labels: [], datasets: [{ data: [], backgroundColor: chartPalette, borderWidth: 0 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    tipoChart = new Chart(document.getElementById('tipoChart'), {
      type: 'bar',
      data: { labels: [], datasets: [{ data: [], backgroundColor: chartPalette[0], borderRadius: 10, borderSkipped: false }] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { ticks: { maxRotation: 0, minRotation: 0 } } }
      }
    });

    mesChart = new Chart(document.getElementById('mesChart'), {
      type: 'line',
      data: { labels: [], datasets: [{ data: [], borderColor: '#3f8753', backgroundColor: 'rgba(63,135,83,.18)', fill: true, tension: .35, pointRadius: 4, pointBackgroundColor: '#3f8753' }] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }

  function updateCharts(data){
    ensureCharts();

    estadoChart.data.labels = data.porEstado.map((item)=>item.label);
    estadoChart.data.datasets[0].data = data.porEstado.map((item)=>item.total);
    estadoChart.data.datasets[0].backgroundColor = data.porEstado.map((_, index)=>chartPalette[index % chartPalette.length]);
    estadoChart.update();

    tipoChart.data.labels = data.porTipo.map((item)=>item.label);
    tipoChart.data.datasets[0].data = data.porTipo.map((item)=>item.total);
    tipoChart.data.datasets[0].backgroundColor = data.porTipo.map((_, index)=>chartPalette[index % chartPalette.length]);
    tipoChart.update();

    mesChart.data.labels = data.porMes.map((item)=>item.LABEL);
    mesChart.data.datasets[0].data = data.porMes.map((item)=>item.TOTAL);
    mesChart.update();
  }

  function renderData(data, updateHistory = false){
    renderKpis(data.kpis || []);
    renderSummary(data.porEstado || []);
    tableWrap.innerHTML = data.tableHtml || '<div class="empty-state"><p>No hay registros para mostrar.</p></div>';
    countBadge.textContent = `${data.totalRegistros || 0} registros en el corte actual`;
    const visibleRows = Array.isArray(data.ultimas) ? data.ultimas.length : 0;
    previewCount.textContent = `Mostrando ${visibleRows} de ${data.totalRegistros || 0}`;
    if (exportLink && data.excelUrl) {
      exportLink.href = data.excelUrl;
    }
    updateCharts(data);
    if (updateHistory) {
      const params = new URLSearchParams(new FormData(form));
      const query = params.toString();
      const nextUrl = `${baseUrl}/dashboard/analitica${query ? `?${query}` : ''}`;
      window.history.replaceState({}, '', nextUrl);
    }
  }

  async function fetchAnalytics(){
    const params = new URLSearchParams(new FormData(form));
    const query = params.toString();
    form.classList.add('analytics-loading');
    try {
      const response = await fetch(`${baseUrl}/api/dashboard/analitica${query ? `?${query}` : ''}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!response.ok) throw new Error('No se pudo cargar la analítica');
      const data = await response.json();
      renderData(data, true);
    } catch (error) {
      console.error(error);
    } finally {
      form.classList.remove('analytics-loading');
    }
  }

  function escapeHtml(value){
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
  }

  form.addEventListener('submit', (event)=>{
    event.preventDefault();
    fetchAnalytics();
  });

  form.querySelectorAll('select,input').forEach((field)=>{
    const eventName = field.tagName === 'SELECT' ? 'change' : 'change';
    field.addEventListener(eventName, ()=>{
      fetchAnalytics();
    });
  });

  resetLink.addEventListener('click', (event)=>{
    event.preventDefault();
    form.reset();
    fetchAnalytics();
  });

  renderData(initialData || {});
});
</script>