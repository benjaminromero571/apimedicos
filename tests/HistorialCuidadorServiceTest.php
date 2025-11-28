<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/HistorialCuidadorService.php';

/**
 * Tests unitarios para HistorialCuidadorService
 * 
 * Ejecutar: php tests/HistorialCuidadorServiceTest.php
 * 
 * NOTA: Estos son tests básicos. Para un entorno de producción,
 * se recomienda usar PHPUnit con mocks y una base de datos de prueba.
 */
class HistorialCuidadorServiceTest
{
    private HistorialCuidadorService $service;
    private array $testIds = [];

    public function __construct()
    {
        $this->service = new HistorialCuidadorService();
    }

    /**
     * Ejecuta todos los tests
     */
    public function runAllTests(): void
    {
        echo "=== Iniciando Tests de HistorialCuidadorService ===\n\n";

        try {
            $this->testCreateHistorial();
            $this->testCreateHistorialWithInvalidData();
            $this->testGetHistorialById();
            $this->testGetAllHistoriales();
            $this->testGetHistorialesByPaciente();
            $this->testGetHistorialesByCuidador();
            $this->testSearchHistoriales();
            $this->testUpdateHistorial();
            $this->testUpdateHistorialWithInvalidData();
            $this->testGetEstadisticasPorPaciente();
            $this->testDeleteHistorial();

            echo "\n=== ✓ Todos los tests pasaron exitosamente ===\n";
        } catch (Exception $e) {
            echo "\n=== ✗ Error en tests: " . $e->getMessage() . " ===\n";
            echo $e->getTraceAsString() . "\n";
        } finally {
            $this->cleanup();
        }
    }

    /**
     * Test: Crear historial con datos válidos
     */
    private function testCreateHistorial(): void
    {
        echo "Test: Crear historial con datos válidos... ";

        $data = [
            'detalle' => 'Test: Registro de prueba del servicio',
            'id_paciente' => 1,
            'id_cuidador' => 1,
            'fecha_historial' => date('Y-m-d H:i:s'),
            'created_by' => 1
        ];

        $result = $this->service->createHistorial($data);

        $this->assert($result['success'] === true, "El resultado debe ser exitoso");
        $this->assert(isset($result['data']['id']), "Debe retornar el ID del historial creado");
        $this->assert($result['data']['detalle'] === $data['detalle'], "El detalle debe coincidir");

        $this->testIds[] = $result['data']['id'];

        echo "✓ PASS\n";
    }

    /**
     * Test: Crear historial con datos inválidos
     */
    private function testCreateHistorialWithInvalidData(): void
    {
        echo "Test: Crear historial con datos inválidos... ";

        // Test 1: Sin detalle
        $data1 = [
            'id_paciente' => 1,
            'id_cuidador' => 1,
            'created_by' => 1
        ];
        $result1 = $this->service->createHistorial($data1);
        $this->assert($result1['success'] === false, "Debe fallar sin detalle");

        // Test 2: Detalle muy corto
        $data2 = [
            'detalle' => 'ABC',
            'id_paciente' => 1,
            'id_cuidador' => 1,
            'created_by' => 1
        ];
        $result2 = $this->service->createHistorial($data2);
        $this->assert($result2['success'] === false, "Debe fallar con detalle muy corto");

        // Test 3: Paciente inexistente
        $data3 = [
            'detalle' => 'Test de validación de paciente',
            'id_paciente' => 999999,
            'id_cuidador' => 1,
            'created_by' => 1
        ];
        $result3 = $this->service->createHistorial($data3);
        $this->assert($result3['success'] === false, "Debe fallar con paciente inexistente");

        echo "✓ PASS\n";
    }

    /**
     * Test: Obtener historial por ID
     */
    private function testGetHistorialById(): void
    {
        echo "Test: Obtener historial por ID... ";

        if (empty($this->testIds)) {
            throw new Exception("No hay IDs de prueba disponibles");
        }

        $id = $this->testIds[0];
        $result = $this->service->getHistorialById($id);

        $this->assert($result['success'] === true, "El resultado debe ser exitoso");
        $this->assert($result['data']['id'] === $id, "El ID debe coincidir");
        $this->assert(isset($result['data']['paciente']), "Debe incluir información del paciente");
        $this->assert(isset($result['data']['cuidador']), "Debe incluir información del cuidador");
        $this->assert(isset($result['data']['auditoria']), "Debe incluir información de auditoría");

        echo "✓ PASS\n";
    }

    /**
     * Test: Obtener todos los historiales
     */
    private function testGetAllHistoriales(): void
    {
        echo "Test: Obtener todos los historiales... ";

        $result = $this->service->getAllHistoriales(10, 0);

        $this->assert($result['success'] === true, "El resultado debe ser exitoso");
        $this->assert(is_array($result['data']), "Debe retornar un array de datos");
        $this->assert($result['total'] >= 1, "Debe haber al menos 1 historial");

        echo "✓ PASS (Total: {$result['total']})\n";
    }

    /**
     * Test: Obtener historiales por paciente
     */
    private function testGetHistorialesByPaciente(): void
    {
        echo "Test: Obtener historiales por paciente... ";

        $result = $this->service->getHistorialesByPaciente(1);

        $this->assert($result['success'] === true, "El resultado debe ser exitoso");
        $this->assert(is_array($result['data']), "Debe retornar un array de datos");

        // Test con paciente inexistente
        $result2 = $this->service->getHistorialesByPaciente(999999);
        $this->assert($result2['success'] === false, "Debe fallar con paciente inexistente");

        echo "✓ PASS\n";
    }

    /**
     * Test: Obtener historiales por cuidador
     */
    private function testGetHistorialesByCuidador(): void
    {
        echo "Test: Obtener historiales por cuidador... ";

        $result = $this->service->getHistorialesByCuidador(1);

        $this->assert($result['success'] === true, "El resultado debe ser exitoso");
        $this->assert(is_array($result['data']), "Debe retornar un array de datos");

        // Test con cuidador inexistente
        $result2 = $this->service->getHistorialesByCuidador(999999);
        $this->assert($result2['success'] === false, "Debe fallar con cuidador inexistente");

        echo "✓ PASS\n";
    }

    /**
     * Test: Buscar historiales con criterios
     */
    private function testSearchHistoriales(): void
    {
        echo "Test: Buscar historiales con criterios... ";

        $searchParams = [
            'id_paciente' => 1,
            'fecha_desde' => date('Y-m-d', strtotime('-30 days')),
            'fecha_hasta' => date('Y-m-d'),
            'limit' => 10,
            'offset' => 0
        ];

        $result = $this->service->searchHistoriales($searchParams);

        $this->assert($result['success'] === true, "El resultado debe ser exitoso");
        $this->assert(is_array($result['data']), "Debe retornar un array de datos");
        $this->assert(isset($result['pagination']), "Debe incluir información de paginación");

        echo "✓ PASS\n";
    }

    /**
     * Test: Actualizar historial
     */
    private function testUpdateHistorial(): void
    {
        echo "Test: Actualizar historial... ";

        if (empty($this->testIds)) {
            throw new Exception("No hay IDs de prueba disponibles");
        }

        $id = $this->testIds[0];
        $updateData = [
            'detalle' => 'Test: Registro actualizado desde el servicio'
        ];

        $result = $this->service->updateHistorial($id, $updateData, 1);

        $this->assert($result['success'] === true, "El resultado debe ser exitoso");
        $this->assert($result['data']['detalle'] === $updateData['detalle'], "El detalle debe estar actualizado");

        echo "✓ PASS\n";
    }

    /**
     * Test: Actualizar historial con datos inválidos
     */
    private function testUpdateHistorialWithInvalidData(): void
    {
        echo "Test: Actualizar historial con datos inválidos... ";

        if (empty($this->testIds)) {
            throw new Exception("No hay IDs de prueba disponibles");
        }

        $id = $this->testIds[0];

        // Test 1: Detalle muy corto
        $result1 = $this->service->updateHistorial($id, ['detalle' => 'ABC'], 1);
        $this->assert($result1['success'] === false, "Debe fallar con detalle muy corto");

        // Test 2: Sin datos
        $result2 = $this->service->updateHistorial($id, [], 1);
        $this->assert($result2['success'] === false, "Debe fallar sin datos");

        // Test 3: Fecha inválida
        $result3 = $this->service->updateHistorial($id, ['fecha_historial' => 'fecha-invalida'], 1);
        $this->assert($result3['success'] === false, "Debe fallar con fecha inválida");

        echo "✓ PASS\n";
    }

    /**
     * Test: Obtener estadísticas por paciente
     */
    private function testGetEstadisticasPorPaciente(): void
    {
        echo "Test: Obtener estadísticas por paciente... ";

        $result = $this->service->getEstadisticasPorPaciente(1);

        $this->assert($result['success'] === true, "El resultado debe ser exitoso");
        $this->assert(isset($result['data']['total_registros']), "Debe incluir total de registros");
        $this->assert(isset($result['data']['total_cuidadores']), "Debe incluir total de cuidadores");

        echo "✓ PASS\n";
    }

    /**
     * Test: Eliminar historial
     */
    private function testDeleteHistorial(): void
    {
        echo "Test: Eliminar historial... ";

        if (empty($this->testIds)) {
            throw new Exception("No hay IDs de prueba disponibles");
        }

        foreach ($this->testIds as $id) {
            $result = $this->service->deleteHistorial($id);
            $this->assert($result['success'] === true, "La eliminación debe ser exitosa");

            // Verificar que ya no existe
            $result2 = $this->service->getHistorialById($id);
            $this->assert($result2['success'] === false, "El historial no debe existir después de eliminarlo");
        }

        $this->testIds = [];

        echo "✓ PASS\n";
    }

    /**
     * Limpieza: Eliminar registros de prueba
     */
    private function cleanup(): void
    {
        if (!empty($this->testIds)) {
            echo "\nLimpiando registros de prueba... ";
            foreach ($this->testIds as $id) {
                try {
                    $this->service->deleteHistorial($id);
                } catch (Exception $e) {
                    // Ignorar errores en cleanup
                }
            }
            echo "✓\n";
        }
    }

    /**
     * Asserción simple
     */
    private function assert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new Exception("Assertion failed: $message");
        }
    }
}

// Ejecutar tests si se invoca directamente
if (php_sapi_name() === 'cli') {
    $test = new HistorialCuidadorServiceTest();
    $test->runAllTests();
}
