<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

final class NotificacionModel extends Model
{
    /**
     * Crear una nueva notificación
     */
    public function crear(string $nitDestinatario, string $tipo, string $mensaje, int $idSolicitud): bool
    {
        return $this->db->execute(
            "INSERT INTO ICEBERG.NOTIFICACIONES
                (NIT_DESTINATARIO, TIPO, MENSAJE, ID_SOLICITUD, LEIDA, FECHA_CREACION)
             VALUES
                (:nit, :tipo, :mensaje, :solicitud, 0, SYSDATE)",
            [
                ':nit'       => $nitDestinatario,
                ':tipo'      => $tipo,
                ':mensaje'   => $mensaje,
                ':solicitud' => $idSolicitud,
            ]
        );
    }

    /**
     * Obtener notificaciones no leídas de un usuario
     */
    public function getNoLeidas(string $nit): array
    {
        return $this->db->query(
            "SELECT n.ID, n.NIT_DESTINATARIO, n.TIPO, n.MENSAJE, n.ID_SOLICITUD, n.LEIDA,
                    TO_CHAR(n.FECHA_CREACION, 'YYYY-MM-DD\"T\"HH24:MI:SS') AS FECHA_CREACION,
                    TO_CHAR(n.FECHA_LECTURA,  'YYYY-MM-DD\"T\"HH24:MI:SS') AS FECHA_LECTURA,
                    s.TIPO_SOLICITUD, s.NIT_EMPLEADO, s.ACTIVO as SOLICITUD_ACTIVA
             FROM ICEBERG.NOTIFICACIONES n
             JOIN ICEBERG.SOLICITUDES_PERMISOS s ON s.ID = n.ID_SOLICITUD
             WHERE n.NIT_DESTINATARIO = :nit AND n.LEIDA = 0 AND s.ACTIVO = 1
             ORDER BY n.FECHA_CREACION DESC
             FETCH FIRST 20 ROWS ONLY",
            [':nit' => $nit]
        ) ?: [];
    }

    /**
     * Obtener todas las notificaciones de un usuario (paginadas)
     */
    public function getTodas(string $nit, int $limite = 50): array
    {
        return $this->db->query(
            "SELECT n.ID, n.NIT_DESTINATARIO, n.TIPO, n.MENSAJE, n.ID_SOLICITUD, n.LEIDA,
                    TO_CHAR(n.FECHA_CREACION, 'YYYY-MM-DD\"T\"HH24:MI:SS') AS FECHA_CREACION,
                    TO_CHAR(n.FECHA_LECTURA,  'YYYY-MM-DD\"T\"HH24:MI:SS') AS FECHA_LECTURA,
                    s.TIPO_SOLICITUD, s.NIT_EMPLEADO, s.ACTIVO as SOLICITUD_ACTIVA
             FROM ICEBERG.NOTIFICACIONES n
             JOIN ICEBERG.SOLICITUDES_PERMISOS s ON s.ID = n.ID_SOLICITUD
             WHERE n.NIT_DESTINATARIO = :nit AND s.ACTIVO = 1
             ORDER BY n.FECHA_CREACION DESC
             FETCH FIRST :limite ROWS ONLY",
            [':nit' => $nit, ':limite' => $limite]
        ) ?: [];
    }

    /**
     * Contar notificaciones no leídas
     */
    public function contarNoLeidas(string $nit): int
    {
        $result = $this->db->query(
            "SELECT COUNT(*) AS TOTAL FROM ICEBERG.NOTIFICACIONES n
             JOIN ICEBERG.SOLICITUDES_PERMISOS s ON s.ID = n.ID_SOLICITUD
             WHERE n.NIT_DESTINATARIO = :nit AND n.LEIDA = 0 AND s.ACTIVO = 1",
            [':nit' => $nit]
        );
        return (int) ($result[0]['TOTAL'] ?? 0);
    }

    /**
     * Marcar una notificación como leída
     */
    public function marcarLeida(int $idNotificacion, string $nit): bool
    {
        return $this->db->execute(
            "UPDATE ICEBERG.NOTIFICACIONES
             SET LEIDA = 1, FECHA_LECTURA = SYSDATE
             WHERE ID = :id AND NIT_DESTINATARIO = :nit",
            [':id' => $idNotificacion, ':nit' => $nit]
        );
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas
     */
    public function marcarTodasLeidas(string $nit): bool
    {
        return $this->db->execute(
            "UPDATE ICEBERG.NOTIFICACIONES
             SET LEIDA = 1, FECHA_LECTURA = SYSDATE
             WHERE NIT_DESTINATARIO = :nit AND LEIDA = 0",
            [':nit' => $nit]
        );
    }

    /**
     * Eliminar notificaciones antiguas (para mantenimiento)
     */
    public function eliminarAntiguas(int $dias): bool
    {
        return $this->db->execute(
            "DELETE FROM ICEBERG.NOTIFICACIONES
             WHERE FECHA_CREACION < SYSDATE - :dias AND LEIDA = 1",
            [':dias' => $dias]
        );
    }
}
