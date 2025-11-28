<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/RecetaMedicaRepository.php';
require_once __DIR__ . '/../entities/RecetaMedicaEntity.php';

/**
 * RecetaMedicaRepositoryTest - Pruebas unitarias para RecetaMedicaRepository
 * 
 * Prueba las operaciones CRUD y consultas del repositorio.
 * 
 * NOTA: Estas pruebas requieren una base de datos de prueba configurada.
 * Ejecutar: php tests/RecetaMedicaRepositoryTest.php
 */
class RecetaMedicaRepositoryTest
{
    private RecetaMedicaRepository $repository;
    private $testRecetaId = null;
    private $testMedicoId = 1; // Asume que existe un médico con ID 1
    
    public function __construct()
    {
        $this->repository = new RecetaMedicaRepository();
    }

    /**
     * Test: Crear una receta médica
     */
    public function testCreate(): void
    {
        echo "Test: Crear receta médica...\n";
        
        $data = [
            'detalle' => 'Paracetamol 500mg cada 8 horas por 5 días - TEST',
            'fecha' => date('Y-m-d'),
            'id_medico' => $this->testMedicoId,
            'created_by' => $this->testMedicoId,
            'updated_by' => $this->testMedicoId
        ];

        try {
            $id = $this->repository->create($data);
            $this->testRecetaId = $id;
            
            if ($id > 0) {
                echo "✓ Receta creada exitosamente con ID: {$id}\n";
            } else {
                echo "✗ Error: No se pudo crear la receta\n";
            }
        } catch (Exception $e) {
            echo "✗ Error al crear receta: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Obtener una receta por ID
     */
    public function testGetById(): void
    {
        echo "Test: Obtener receta por ID...\n";
        
        if (!$this->testRecetaId) {
            echo "✗ No hay ID de receta de prueba\n\n";
            return;
        }

        try {
            $entity = $this->repository->getById($this->testRecetaId);
            
            if ($entity && $entity->getId() === $this->testRecetaId) {
                echo "✓ Receta obtenida correctamente\n";
                echo "  - ID: {$entity->getId()}\n";
                echo "  - Detalle: {$entity->getDetalle()}\n";
                echo "  - Médico: {$entity->getNombreMedico()}\n";
            } else {
                echo "✗ No se encontró la receta\n";
            }
        } catch (Exception $e) {
            echo "✗ Error al obtener receta: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Obtener todas las recetas con paginación
     */
    public function testGetAll(): void
    {
        echo "Test: Obtener todas las recetas (con límite)...\n";
        
        try {
            $entities = $this->repository->getAll(5, 0);
            
            if (is_array($entities)) {
                echo "✓ Recetas obtenidas: " . count($entities) . "\n";
                foreach ($entities as $entity) {
                    echo "  - ID {$entity->getId()}: {$entity->getDetalle()}\n";
                }
            } else {
                echo "✗ Error al obtener recetas\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Obtener recetas por médico
     */
    public function testGetByMedico(): void
    {
        echo "Test: Obtener recetas por médico...\n";
        
        try {
            $entities = $this->repository->getByMedico($this->testMedicoId);
            
            if (is_array($entities)) {
                echo "✓ Recetas del médico {$this->testMedicoId}: " . count($entities) . "\n";
                foreach ($entities as $entity) {
                    echo "  - ID {$entity->getId()}: {$entity->getDetalle()}\n";
                }
            } else {
                echo "✗ Error al obtener recetas del médico\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Búsqueda con filtros
     */
    public function testSearch(): void
    {
        echo "Test: Buscar recetas con filtros...\n";
        
        try {
            $filters = [
                'id_medico' => $this->testMedicoId,
                'fecha_desde' => date('Y-m-d', strtotime('-30 days')),
                'fecha_hasta' => date('Y-m-d')
            ];
            
            $entities = $this->repository->search($filters);
            
            if (is_array($entities)) {
                echo "✓ Búsqueda completada: " . count($entities) . " resultados\n";
            } else {
                echo "✗ Error en búsqueda\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Actualizar una receta
     */
    public function testUpdate(): void
    {
        echo "Test: Actualizar receta...\n";
        
        if (!$this->testRecetaId) {
            echo "✗ No hay ID de receta de prueba\n\n";
            return;
        }

        try {
            $updateData = [
                'detalle' => 'Paracetamol 500mg cada 6 horas por 7 días - ACTUALIZADO',
                'updated_by' => $this->testMedicoId
            ];
            
            $success = $this->repository->update($this->testRecetaId, $updateData);
            
            if ($success) {
                echo "✓ Receta actualizada correctamente\n";
                
                // Verificar actualización
                $entity = $this->repository->getById($this->testRecetaId);
                echo "  - Nuevo detalle: {$entity->getDetalle()}\n";
            } else {
                echo "✗ Error al actualizar receta\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Verificar si existe una receta
     */
    public function testExists(): void
    {
        echo "Test: Verificar existencia de receta...\n";
        
        if (!$this->testRecetaId) {
            echo "✗ No hay ID de receta de prueba\n\n";
            return;
        }

        try {
            $exists = $this->repository->exists($this->testRecetaId);
            
            if ($exists) {
                echo "✓ La receta existe\n";
            } else {
                echo "✗ La receta no existe\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Verificar si un médico existe
     */
    public function testMedicoExists(): void
    {
        echo "Test: Verificar existencia de médico...\n";
        
        try {
            $exists = $this->repository->medicoExists($this->testMedicoId);
            
            if ($exists) {
                echo "✓ El médico existe\n";
            } else {
                echo "✗ El médico no existe\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Contar recetas
     */
    public function testCount(): void
    {
        echo "Test: Contar recetas...\n";
        
        try {
            $total = $this->repository->count();
            echo "✓ Total de recetas en el sistema: {$total}\n";
            
            $totalMedico = $this->repository->count(['id_medico' => $this->testMedicoId]);
            echo "✓ Total de recetas del médico {$this->testMedicoId}: {$totalMedico}\n";
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Obtener ID del médico propietario
     */
    public function testGetIdMedicoPropietario(): void
    {
        echo "Test: Obtener ID del médico propietario...\n";
        
        if (!$this->testRecetaId) {
            echo "✗ No hay ID de receta de prueba\n\n";
            return;
        }

        try {
            $idMedico = $this->repository->getIdMedicoPropietario($this->testRecetaId);
            
            if ($idMedico) {
                echo "✓ ID del médico propietario: {$idMedico}\n";
            } else {
                echo "✗ No se pudo obtener el ID del médico propietario\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Eliminar una receta (último test)
     */
    public function testDelete(): void
    {
        echo "Test: Eliminar receta...\n";
        
        if (!$this->testRecetaId) {
            echo "✗ No hay ID de receta de prueba\n\n";
            return;
        }

        try {
            $success = $this->repository->delete($this->testRecetaId);
            
            if ($success) {
                echo "✓ Receta eliminada correctamente\n";
                
                // Verificar eliminación
                $exists = $this->repository->exists($this->testRecetaId);
                if (!$exists) {
                    echo "✓ Confirmado: La receta ya no existe\n";
                } else {
                    echo "✗ Error: La receta aún existe después de eliminar\n";
                }
            } else {
                echo "✗ Error al eliminar receta\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Ejecuta todos los tests
     */
    public function runAllTests(): void
    {
        echo "\n";
        echo "========================================\n";
        echo "   PRUEBAS RECETA MEDICA REPOSITORY\n";
        echo "========================================\n\n";

        $this->testCreate();
        $this->testGetById();
        $this->testGetAll();
        $this->testGetByMedico();
        $this->testSearch();
        $this->testUpdate();
        $this->testExists();
        $this->testMedicoExists();
        $this->testCount();
        $this->testGetIdMedicoPropietario();
        $this->testDelete();

        echo "========================================\n";
        echo "   PRUEBAS COMPLETADAS\n";
        echo "========================================\n\n";
    }
}

// Ejecutar tests si se llama directamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $test = new RecetaMedicaRepositoryTest();
    $test->runAllTests();
}
