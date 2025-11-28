<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/HistorialCuidadorRepository.php';

/**
 * Tests unitarios para HistorialCuidadorRepository
 * 
 * Ejecutar: php tests/HistorialCuidadorRepositoryTest.php
 * 
 * NOTA: Estos son tests básicos. Para un entorno de producción,
 * se recomienda usar PHPUnit con una base de datos de prueba.
 */
class HistorialCuidadorRepositoryTest
{
    private HistorialCuidadorRepository $repository;
    private array $testIds = [];

    public function __construct()
    {
        $this->repository = new HistorialCuidadorRepository();
    }

    /**
     * Ejecuta todos los tests
     */
    public function runAllTests(): void
    {
        echo "=== Iniciando Tests de HistorialCuidadorRepository ===\n\n";

        try {
            $this->testCreate();
            $this->testGetById();
            $this->testGetAll();
            $this->testGetByPaciente();
            $this->testGetByCuidador();
            $this->testUpdate();
            $this->testSearch();
            $this->testCount();
            $this->testPacienteExists();
            $this->testCuidadorExists();
            $this->testDelete();

            echo "\n=== ✓ Todos los tests pasaron exitosamente ===\n";
        } catch (Exception $e) {
            echo "\n=== ✗ Error en tests: " . $e->getMessage() . " ===\n";
            echo $e->getTraceAsString() . "\n";
        } finally {
            $this->cleanup();
        }
    }

    /**
     * Test: Crear un historial
     */
    private function testCreate(): void
    {
        echo "Test: Crear historial de cuidador... ";

        $data = [
            'detalle' => 'Test: Registro de prueba del cuidador',
            'id_paciente' => 1, // Asume que existe un paciente con ID 1
            'id_cuidador' => 1, // Asume que existe un cuidador con ID 1
            'fecha_historial' => date('Y-m-d H:i:s'),
            'created_by' => 1,
            'updated_by' => 1
        ];

        $id = $this->repository->create($data);
        $this->testIds[] = $id;

        $this->assert($id > 0, "ID del historial creado debe ser mayor a 0");
        echo "✓ PASS (ID: $id)\n";
    }

    /**
     * Test: Obtener por ID
     */
    private function testGetById(): void
    {
        echo "Test: Obtener historial por ID... ";

        if (empty($this->testIds)) {
            throw new Exception("No hay IDs de prueba disponibles");
        }

        $id = $this->testIds[0];
        $entity = $this->repository->getById($id);

        $this->assert($entity !== null, "El historial debe existir");
        $this->assert($entity->getId() === $id, "El ID debe coincidir");
        $this->assert(!empty($entity->getDetalle()), "El detalle no debe estar vacío");

        echo "✓ PASS\n";
    }

    /**
     * Test: Obtener todos
     */
    private function testGetAll(): void
    {
        echo "Test: Obtener todos los historiales... ";

        $entities = $this->repository->getAll(10, 0);

        $this->assert(is_array($entities), "Debe retornar un array");
        $this->assert(count($entities) >= 1, "Debe haber al menos 1 historial (el de prueba)");

        echo "✓ PASS (Total: " . count($entities) . ")\n";
    }

    /**
     * Test: Obtener por paciente
     */
    private function testGetByPaciente(): void
    {
        echo "Test: Obtener historiales por paciente... ";

        $entities = $this->repository->getByPaciente(1);

        $this->assert(is_array($entities), "Debe retornar un array");

        if (count($entities) > 0) {
            $this->assert($entities[0]->getIdPaciente() === 1, "El ID del paciente debe ser 1");
        }

        echo "✓ PASS (Total: " . count($entities) . ")\n";
    }

    /**
     * Test: Obtener por cuidador
     */
    private function testGetByCuidador(): void
    {
        echo "Test: Obtener historiales por cuidador... ";

        $entities = $this->repository->getByCuidador(1);

        $this->assert(is_array($entities), "Debe retornar un array");

        if (count($entities) > 0) {
            $this->assert($entities[0]->getIdCuidador() === 1, "El ID del cuidador debe ser 1");
        }

        echo "✓ PASS (Total: " . count($entities) . ")\n";
    }

    /**
     * Test: Actualizar historial
     */
    private function testUpdate(): void
    {
        echo "Test: Actualizar historial... ";

        if (empty($this->testIds)) {
            throw new Exception("No hay IDs de prueba disponibles");
        }

        $id = $this->testIds[0];
        $updateData = [
            'detalle' => 'Test: Registro actualizado de prueba',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => 1
        ];

        $updated = $this->repository->update($id, $updateData);

        $this->assert($updated === true, "La actualización debe retornar true");

        // Verificar que se actualizó
        $entity = $this->repository->getById($id);
        $this->assert($entity->getDetalle() === $updateData['detalle'], "El detalle debe estar actualizado");

        echo "✓ PASS\n";
    }

    /**
     * Test: Buscar con criterios
     */
    private function testSearch(): void
    {
        echo "Test: Buscar historiales con criterios... ";

        $searchData = [
            'conditions' => ['hc.id_paciente = ?'],
            'params' => [1],
            'types' => 'i',
            'limit' => 10,
            'offset' => 0,
            'order_by' => 'fecha_historial',
            'order_direction' => 'DESC'
        ];

        $entities = $this->repository->search($searchData);

        $this->assert(is_array($entities), "Debe retornar un array");

        echo "✓ PASS (Resultados: " . count($entities) . ")\n";
    }

    /**
     * Test: Contar registros
     */
    private function testCount(): void
    {
        echo "Test: Contar historiales... ";

        $count = $this->repository->count();

        $this->assert($count >= 1, "Debe haber al menos 1 historial");

        // Contar con condiciones
        $countConCondiciones = $this->repository->count([
            'conditions' => ['hc.id_paciente = ?'],
            'params' => [1],
            'types' => 'i'
        ]);

        $this->assert($countConCondiciones >= 0, "El conteo con condiciones debe ser >= 0");

        echo "✓ PASS (Total: $count, Con condiciones: $countConCondiciones)\n";
    }

    /**
     * Test: Verificar si paciente existe
     */
    private function testPacienteExists(): void
    {
        echo "Test: Verificar existencia de paciente... ";

        $exists = $this->repository->pacienteExists(1);
        $this->assert($exists === true, "El paciente con ID 1 debe existir");

        $notExists = $this->repository->pacienteExists(999999);
        $this->assert($notExists === false, "El paciente con ID 999999 no debe existir");

        echo "✓ PASS\n";
    }

    /**
     * Test: Verificar si cuidador existe
     */
    private function testCuidadorExists(): void
    {
        echo "Test: Verificar existencia de cuidador... ";

        $exists = $this->repository->cuidadorExists(1);
        $this->assert($exists === true, "El cuidador con ID 1 debe existir");

        $notExists = $this->repository->cuidadorExists(999999);
        $this->assert($notExists === false, "El cuidador con ID 999999 no debe existir");

        echo "✓ PASS\n";
    }

    /**
     * Test: Eliminar historial
     */
    private function testDelete(): void
    {
        echo "Test: Eliminar historial... ";

        if (empty($this->testIds)) {
            throw new Exception("No hay IDs de prueba disponibles");
        }

        foreach ($this->testIds as $id) {
            $deleted = $this->repository->delete($id);
            $this->assert($deleted === true, "La eliminación debe retornar true");

            // Verificar que ya no existe
            $entity = $this->repository->getById($id);
            $this->assert($entity === null, "El historial no debe existir después de eliminarlo");
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
                    $this->repository->delete($id);
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
    $test = new HistorialCuidadorRepositoryTest();
    $test->runAllTests();
}
