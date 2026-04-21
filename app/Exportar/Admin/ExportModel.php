<?php
declare(strict_types=1);

namespace App\Exportar\Admin;

use Core\Model;

final class ExportModel extends Model
{
    public function getTodasLasSolicitudes(array $filtros = []): array
    {
        [$whereSql, $binds] = $this->buildWhereClause($filtros);

        $sql = "SELECT 
                    ID,
                    NIT_EMPLEADO,
                    TIPO_SOLICITUD,
                    FECHA_INICIO,
                    FECHA_FIN,
                    DURACION_HORAS,
                    DURACION_DIAS,
                    ESTADO,
                    OBSERVACIONES,
                    FECHA_CREACION
                FROM SOLICITUDES_PERMISOS
                {$whereSql}
                ORDER BY FECHA_CREACION DESC";

        return $this->db->query($sql, $binds);
    }

    public function getSolicitudesAgrupadas(): array
    {
        $rows = $this->getTodasLasSolicitudes();

        $agrupadas = [
            'PENDIENTE_JEFE' => [],
            'APROBADO_JEFE'  => [],
            'RECHAZADO_JEFE' => [],
            'APROBADO_RRHH'  => [],
            'RECHAZADO_RRHH' => [],
            'TODAS'          => $rows
        ];

        foreach ($rows as $row) {
            $estado = strtoupper(trim($row['ESTADO'] ?? ''));

            if (isset($agrupadas[$estado])) {
                $agrupadas[$estado][] = $row;
            }
        }

        return $agrupadas;
    }

    public function getConteoPorMes(array $filtros = [], int $limite = 6): array
    {
        [$whereSql, $binds] = $this->buildWhereClause($filtros);
        $limite = max(1, min($limite, 12));

        return $this->db->query(
            "SELECT TO_CHAR(FECHA_CREACION, 'YYYY-MM') AS PERIODO, COUNT(*) AS TOTAL
             FROM SOLICITUDES_PERMISOS
             {$whereSql}
             GROUP BY TO_CHAR(FECHA_CREACION, 'YYYY-MM')
             ORDER BY PERIODO DESC
             FETCH FIRST {$limite} ROWS ONLY",
            $binds
        );
    }

    public function getTopEmpleados(array $filtros = [], int $limite = 5): array
    {
        [$whereSql, $binds] = $this->buildWhereClause($filtros);
        $limite = max(1, min($limite, 10));

        return $this->db->query(
            "SELECT NIT_EMPLEADO,
                    COUNT(*) AS TOTAL,
                    NVL(SUM(DURACION_HORAS), 0) AS TOTAL_HORAS,
                    NVL(SUM(DURACION_DIAS), 0) AS TOTAL_DIAS
             FROM SOLICITUDES_PERMISOS
             {$whereSql}
             GROUP BY NIT_EMPLEADO
             ORDER BY TOTAL DESC, NIT_EMPLEADO ASC
             FETCH FIRST {$limite} ROWS ONLY",
            $binds
        );
    }

    private function buildWhereClause(array $filtros): array
    {
        $where = [];
        $binds = [];

        if (!empty($filtros['estado'])) {
            $where[] = 'ESTADO = :estado';
            $binds[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['tipo'])) {
            $where[] = 'TIPO_SOLICITUD = :tipo';
            $binds[':tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['nit'])) {
            $where[] = 'NIT_EMPLEADO = :nit';
            $binds[':nit'] = $filtros['nit'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $where[] = "FECHA_CREACION >= TO_DATE(:fecha_desde, 'YYYY-MM-DD')";
            $binds[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $where[] = "FECHA_CREACION < TO_DATE(:fecha_hasta, 'YYYY-MM-DD') + 1";
            $binds[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $binds];
    }
}