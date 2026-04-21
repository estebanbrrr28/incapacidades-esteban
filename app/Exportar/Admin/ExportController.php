<?php
declare(strict_types=1);

namespace App\Exportar\Admin;

use Core\Controller;
use Core\Security;
use App\Exportar\Admin\ExportModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class ExportController extends Controller
{
    private array $cabeceras = [
        'ID', 'NIT EMPLEADO', 'TIPO SOLICITUD', 'FECHA INICIO',
        'FECHA FIN', 'HORAS', 'DÍAS', 'ESTADO', 'OBSERVACIONES', 'FECHA CREACIÓN'
    ];

    private array $campos = [
        'ID', 'NIT_EMPLEADO', 'TIPO_SOLICITUD', 'FECHA_INICIO',
        'FECHA_FIN', 'DURACION_HORAS', 'DURACION_DIAS', 'ESTADO',
        'OBSERVACIONES', 'FECHA_CREACION'
    ];

    public function todasExcel(): void
    {
        $this->requireRole([ROL_ADMIN, ROL_RRHH, ROL_JEFE]);
        $rows = (new ExportModel())->getTodasLasSolicitudes($this->obtenerFiltros());
        $this->generarExcelPorEstado($rows, 'reporte_solicitudes');
    }

    private function obtenerFiltros(): array
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

    private function generarExcelPorEstado(array $rows, string $nombre): void
    {
        $spreadsheet = new Spreadsheet();

        $data = $this->agruparPorEstado($rows);

        $map = [
            'PENDIENTE_JEFE' => 'Pendiente Jefe',
            'APROBADO_JEFE'  => 'Aprobado Jefe',
            'RECHAZADO_JEFE' => 'Rechazado Jefe',
            'APROBADO_RRHH'  => 'Aprobado RRHH',
            'RECHAZADO_RRHH' => 'Rechazado RRHH',
            'TODAS'          => 'Todas'
        ];

        $index = 0;

        foreach ($data as $titulo => $filas) {
            $sheet = $index === 0
                ? $spreadsheet->getActiveSheet()
                : $spreadsheet->createSheet();

            $sheet->setTitle($map[$titulo] ?? $titulo);
            $this->llenarHoja($sheet, $filas);

            $index++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nombre . '_' . date('Ymd') . '.xlsx"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    private function agruparPorEstado(array $rows): array
    {
        $data = [
            'PENDIENTE_JEFE' => [],
            'APROBADO_JEFE'  => [],
            'RECHAZADO_JEFE' => [],
            'APROBADO_RRHH'  => [],
            'RECHAZADO_RRHH' => [],
            'TODAS'          => $rows
        ];

        foreach ($rows as $row) {
            $estado = strtoupper(trim($row['ESTADO'] ?? ''));

            if (isset($data[$estado])) {
                $data[$estado][] = $row;
            }
        }

        return $data;
    }

    private function llenarHoja($sheet, array $rows): void
    {
        foreach ($this->cabeceras as $i => $cab) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '1', $cab);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        foreach ($rows as $fila => $row) {
            foreach ($this->campos as $i => $campo) {
                $col = chr(65 + $i);
                $valor = $row[$campo] ?? '';

                if (isset(TIPOS_SOLICITUD[$valor])) {
                    $valor = TIPOS_SOLICITUD[$valor];
                }

                $sheet->setCellValue($col . ($fila + 2), $valor);
            }
        }
    }
}