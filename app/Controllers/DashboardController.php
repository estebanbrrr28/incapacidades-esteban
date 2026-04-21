<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Security;
use Core\Config;
use App\Models\SolicitudModel;
use App\Exportar\Admin\ExportModel;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $user  = $this->user();
        $model = new SolicitudModel();
        $tipos = TIPOS_SOLICITUD;

        switch ($user['rol']) {
            case ROL_ADMIN:
                $stats = $model->contarPorEstado();
                $todas = $model->getAll();
                $filtros = ['estado' => '', 'tipo' => ''];
                $this->render('admin/dashboard', compact('user', 'stats', 'todas', 'tipos', 'filtros'));
                break;

            case ROL_RRHH:
                $pendientes = $model->getPendientesRRHH();
                $todas      = $model->getAll();
                $this->render('rrhh/dashboard', compact('user', 'pendientes', 'todas', 'tipos'));
                break;

            case ROL_JEFE:
                $pendientes     = $model->getPendientesJefe($user['cedula']);
                $misSolicitudes = $model->getByEmpleado($user['cedula']);
                $gestionadas    = $model->getGestionadasByJefe($user['cedula']);
                $this->render('jefe/dashboard', compact('user', 'pendientes', 'misSolicitudes', 'gestionadas', 'tipos'));
                break;

            default:
                $solicitudes = $model->getByEmpleado($user['cedula']);
                $this->render('empleado/dashboard', compact('user', 'solicitudes', 'tipos'));
        }
    }

    public function listar(): void
    {
        $this->requireRole([ROL_ADMIN, ROL_RRHH, ROL_JEFE]);
        $user  = $this->user();
        $model = new SolicitudModel();

        $filtros = [
            'estado' => Security::sanitizeString($_GET['estado'] ?? ''),
            'tipo'   => Security::sanitizeString($_GET['tipo'] ?? ''),
        ];

        $todas = $model->getAll($filtros);
        $tipos = TIPOS_SOLICITUD;
        $stats = $model->contarPorEstado();

        $this->render('admin/dashboard', compact('user', 'todas', 'tipos', 'stats', 'filtros'));
    }

    public function analytics(): void
    {
        $this->requireRole([ROL_ADMIN]);

        $user = $this->user();
        $filtros = $this->obtenerFiltrosAnalitica();
        $payload = $this->buildAnalyticsPayload($filtros);
        $tipos = TIPOS_SOLICITUD;
        $labelsEstado = $this->getAnalyticsLabelsEstado();
        $kpis = $payload['kpis'];
        $porEstado = $payload['porEstado'];
        $porTipo = $payload['porTipo'];
        $porMes = $payload['porMes'];
        $ultimas = $payload['ultimas'];
        $totalRegistros = $payload['totalRegistros'];
        $analyticsJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $this->render(
            'admin/analytics',
            compact(
                'user',
                'tipos',
                'labelsEstado',
                'filtros',
                'kpis',
                'porEstado',
                'porTipo',
                'porMes',
                'ultimas',
                'totalRegistros',
                'analyticsJson'
            )
        );
    }

    public function analyticsData(): void
    {
        $this->requireRole([ROL_ADMIN]);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            $this->buildAnalyticsPayload($this->obtenerFiltrosAnalitica()),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    private function obtenerFiltrosAnalitica(): array
    {
        return [
            'estado' => Security::sanitizeString($_GET['estado'] ?? ''),
            'tipo' => Security::sanitizeString($_GET['tipo'] ?? ''),
            'nit' => Security::sanitizeString($_GET['nit'] ?? ''),
            'fecha_desde' => $this->sanitizeDate($_GET['fecha_desde'] ?? ''),
            'fecha_hasta' => $this->sanitizeDate($_GET['fecha_hasta'] ?? ''),
        ];
    }

    private function sanitizeDate(string $value): string
    {
        $value = trim($value);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
    }

    private function construirKpis(array $rows): array
    {
        $resumen = [
            'total' => count($rows),
            'pendientes' => 0,
            'en_rrhh' => 0,
            'aprobadas' => 0,
            'rechazadas' => 0,
            'empleados' => [],
        ];

        foreach ($rows as $row) {
            $estado = (string) ($row['ESTADO'] ?? '');

            if (!empty($row['NIT_EMPLEADO'])) {
                $resumen['empleados'][(string) $row['NIT_EMPLEADO']] = true;
            }

            if ($estado === 'PENDIENTE_JEFE') {
                $resumen['pendientes']++;
            }
            if ($estado === 'APROBADO_JEFE') {
                $resumen['en_rrhh']++;
            }
            if ($estado === 'APROBADO_RRHH') {
                $resumen['aprobadas']++;
            }
            if (in_array($estado, ['RECHAZADO_JEFE', 'RECHAZADO_RRHH'], true)) {
                $resumen['rechazadas']++;
            }
        }

        return [
            ['label' => 'Registros exportables', 'value' => $resumen['total'], 'hint' => 'Base usada para Excel'],
            ['label' => 'Pendientes de jefe', 'value' => $resumen['pendientes'], 'hint' => 'Solicitudes aún por revisar'],
            ['label' => 'En gestión RRHH', 'value' => $resumen['en_rrhh'], 'hint' => 'Aprobadas por jefe'],
            ['label' => 'Aprobadas finales', 'value' => $resumen['aprobadas'], 'hint' => 'Cierre aprobado en RRHH'],
            ['label' => 'Rechazadas', 'value' => $resumen['rechazadas'], 'hint' => 'Rechazos jefe o RRHH'],
            ['label' => 'Empleados únicos', 'value' => count($resumen['empleados']), 'hint' => 'Personas incluidas en la exportación'],
        ];
    }

    private function buildAnalyticsPayload(array $filtros): array
    {
        $tipos = TIPOS_SOLICITUD;
        $labelsEstado = $this->getAnalyticsLabelsEstado();
        $exportModel = new ExportModel();
        $rows = $exportModel->getTodasLasSolicitudes($filtros);
        $porEstado = $this->construirDistribucionEstado($rows, $labelsEstado);
        $porTipo = $this->construirDistribucionTipo($rows, $tipos);
        $porMes = array_reverse($exportModel->getConteoPorMes($filtros, 6));
        $ultimas = array_slice($rows, 0, 12);

        foreach ($porMes as &$fila) {
            $fila['LABEL'] = $this->formatPeriodLabel((string) ($fila['PERIODO'] ?? ''));
            $fila['TOTAL'] = (int) ($fila['TOTAL'] ?? 0);
        }
        unset($fila);

        return [
            'kpis' => $this->construirKpis($rows),
            'porEstado' => $porEstado,
            'porTipo' => $porTipo,
            'porMes' => $porMes,
            'ultimas' => $ultimas,
            'totalRegistros' => count($rows),
            'tableHtml' => $this->renderAnalyticsTableHtml($ultimas, $tipos),
            'excelUrl' => Config::baseUrl() . '/exportar/todas/excel' . $this->buildQueryString($filtros),
        ];
    }

    private function renderAnalyticsTableHtml(array $filas, array $tipos): string
    {
        ob_start();
        require __DIR__ . '/../../views/shared/tabla_solicitudes.php';
        return (string) ob_get_clean();
    }

    private function buildQueryString(array $filtros): string
    {
        $query = http_build_query(array_filter($filtros, static fn($value): bool => $value !== '' && $value !== null));
        return $query ? '?' . $query : '';
    }

    private function getAnalyticsLabelsEstado(): array
    {
        return [
            'PENDIENTE_JEFE' => 'Pendiente Jefe',
            'APROBADO_JEFE' => 'En RRHH',
            'RECHAZADO_JEFE' => 'Rechazado Jefe',
            'APROBADO_RRHH' => 'Aprobado RRHH',
            'RECHAZADO_RRHH' => 'Rechazado RRHH',
        ];
    }

    private function construirDistribucionEstado(array $rows, array $labelsEstado): array
    {
        $conteo = array_fill_keys(array_keys($labelsEstado), 0);
        foreach ($rows as $row) {
            $estado = (string) ($row['ESTADO'] ?? '');
            if (isset($conteo[$estado])) {
                $conteo[$estado]++;
            }
        }

        return $this->mapBreakdown($conteo, $labelsEstado);
    }

    private function construirDistribucionTipo(array $rows, array $tipos): array
    {
        $conteo = [];
        foreach ($rows as $row) {
            $tipo = (string) ($row['TIPO_SOLICITUD'] ?? '');
            if ($tipo === '') {
                continue;
            }
            $conteo[$tipo] = ($conteo[$tipo] ?? 0) + 1;
        }

        arsort($conteo);
        $labels = [];
        foreach (array_keys($conteo) as $tipo) {
            $labels[$tipo] = $tipos[$tipo] ?? $tipo;
        }

        return $this->mapBreakdown($conteo, $labels);
    }

    private function mapBreakdown(array $conteo, array $labels): array
    {
        $total = array_sum($conteo) ?: 1;
        $data = [];

        foreach ($conteo as $key => $cantidad) {
            $data[] = [
                'key' => $key,
                'label' => $labels[$key] ?? $key,
                'total' => (int) $cantidad,
                'percent' => (float) round((((int) $cantidad) / $total) * 100, 1),
            ];
        }

        usort($data, static fn(array $a, array $b): int => $b['total'] <=> $a['total']);
        return $data;
    }

    private function formatPeriodLabel(string $period): string
    {
        if (!preg_match('/^(\d{4})-(\d{2})$/', $period, $matches)) {
            return $period;
        }

        $months = [
            '01' => 'Ene',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Abr',
            '05' => 'May',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Ago',
            '09' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dic',
        ];

        return ($months[$matches[2]] ?? $matches[2]) . ' ' . $matches[1];
    }
}
