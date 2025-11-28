<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/RecetaMedicaService.php';
require_once __DIR__ . '/../dto/CreateRecetaMedicaDto.php';
require_once __DIR__ . '/../dto/RecetaMedicaSearchDto.php';

/**
 * RecetaMedicaServiceTest - Pruebas unitarias para RecetaMedicaService
 * 
 * Prueba la lógica de negocio, validaciones y permisos del servicio.
 * 
 * NOTA: Estas pruebas requieren una base de datos de prueba configurada.
 * Ejecutar: php tests/RecetaMedicaServiceTest.php
 */
class RecetaMedicaServiceTest
{
    private RecetaMedicaService $service;
    private $testRecetaId = null;
    private $testMedicoId = 1; // Asume que existe un médico con ID 1
    
    public function __construct()
    {
        $this->service = new RecetaMedicaService();
    }

    /**
     * Test: Crear receta como médico
     */
    public function testCreateRecetaComoMedico(): void
    {
        echo "Test: Crear receta como médico...\n";
        
        try {
            $data = [
                'detalle' => 'Ibuprofeno 400mg cada 8 horas por 3 días - TEST SERVICE',
                'fecha' => date('Y-m-d'),
                'id_medico' => $this->testMedicoId,
                'created_by' => $this->testMedicoId
            ];
            
            $createDto = new CreateRecetaMedicaDto($data);
            $result = $this->service->createReceta($createDto, $this->testMedicoId, 'Medico');
            
            if ($result['success']) {
                $this->testRecetaId = $result['data']['id'];
                echo "✓ Receta creada exitosamente como médico\n";
                echo "  - ID: {$this->testRecetaId}\n";
            } else {
                echo "✗ Error: {$result['message']}\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Intentar crear receta como cuidador (debe fallar)
     */
    public function testCreateRecetaComoCuidador(): void
    {
        echo "Test: Intentar crear receta como cuidador (debe fallar)...\n";
        
        try {
            $data = [
                'detalle' => 'Intento de crear receta como cuidador',
                'fecha' => date('Y-m-d'),
                'id_medico' => $this->testMedicoId,
                'created_by' => 999
            ];
            
            $createDto = new CreateRecetaMedicaDto($data);
            $result = $this->service->createReceta($createDto, 999, 'Cuidador');
            
            if (!$result['success']) {
                echo "✓ Correctamente denegado: {$result['message']}\n";
            } else {
                echo "✗ Error: Un cuidador no debería poder crear recetas\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Intentar crear receta para otro médico (debe fallar)
     */
    public function testCreateRecetaParaOtroMedico(): void
    {
        echo "Test: Médico intenta crear receta para otro médico (debe fallar)...\n";
        
        try {
            $data = [
                'detalle' => 'Intento de crear receta para otro médico',
                'fecha' => date('Y-m-d'),
                'id_medico' => 999, // Otro médico
                'created_by' => $this->testMedicoId
            ];
            
            $createDto = new CreateRecetaMedicaDto($data);
            $result = $this->service->createReceta($createDto, $this->testMedicoId, 'Medico');
            
            if (!$result['success']) {
                echo "✓ Correctamente denegado: {$result['message']}\n";
            } else {
                echo "✗ Error: Un médico no debería crear recetas para otro médico\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Obtener todas las recetas
     */
    public function testGetAllRecetas(): void
    {
        echo "Test: Obtener todas las recetas...\n";
        
        try {
            $result = $this->service->getAllRecetas(10, 0);
            
            if ($result['success']) {
                echo "✓ Recetas obtenidas correctamente\n";
                echo "  - Total: {$result['total']}\n";
                echo "  - Mostrando: {$result['showing']}\n";
            } else {
                echo "✗ Error: {$result['message']}\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Obtener receta por ID
     */
    public function testGetRecetaById(): void
    {
        echo "Test: Obtener receta por ID...\n";
        
        if (!$this->testRecetaId) {
            echo "✗ No hay ID de receta de prueba\n\n";
            return;
        }

        try {
            $result = $this->service->getRecetaById($this->testRecetaId);
            
            if ($result['success']) {
                echo "✓ Receta obtenida correctamente\n";
                echo "  - ID: {$result['data']['id']}\n";
                echo "  - Detalle: {$result['data']['detalle']}\n";
            } else {
                echo "✗ Error: {$result['message']}\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Obtener recetas por médico
     */
    public function testGetRecetasByMedico(): void
    {
        echo "Test: Obtener recetas por médico...\n";
        
        try {
            $result = $this->service->getRecetasByMedico($this->testMedicoId);
            
            if ($result['success']) {
                echo "✓ Recetas del médico obtenidas correctamente\n";
                echo "  - Total: {$result['total']}\n";
                echo "  - Mostrando: {$result['showing']}\n";
            } else {
                echo "✗ Error: {$result['message']}\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Búsqueda con filtros
     */
    public function testSearchRecetas(): void
    {
        echo "Test: Buscar recetas con filtros...\n";
        
        try {
            $searchDto = new RecetaMedicaSearchDto([
                'id_medico' => $this->testMedicoId,
                'fecha_desde' => date('Y-m-d', strtotime('-30 days')),
                'limit' => 5
            ]);
            
            $result = $this->service->searchRecetas($searchDto);
            
            if ($result['success']) {
                echo "✓ Búsqueda completada correctamente\n";
                echo "  - Total: {$result['total']}\n";
                echo "  - Mostrando: {$result['showing']}\n";
            } else {
                echo "✗ Error: {$result['message']}\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Actualizar receta como propietario
     */
    public function testUpdateRecetaComoPropietario(): void
    {
        echo "Test: Actualizar receta como propietario...\n";
        
        if (!$this->testRecetaId) {
            echo "✗ No hay ID de receta de prueba\n\n";
            return;
        }

        try {
            $updateData = [
                'detalle' => 'Ibuprofeno 400mg cada 6 horas por 5 días - ACTUALIZADO'
            ];
            
            $result = $this->service->updateReceta(
                $this->testRecetaId, 
                $updateData, 
                $this->testMedicoId, 
                'Medico'
            );
            
            if ($result['success']) {
                echo "✓ Receta actualizada correctamente\n";
                echo "  - Nuevo detalle: {$result['data']['detalle']}\n";
            } else {
                echo "✗ Error: {$result['message']}\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Intentar actualizar receta de otro médico (debe fallar)
     */
    public function testUpdateRecetaDeOtroMedico(): void
    {
        echo "Test: Médico intenta actualizar receta de otro (debe fallar)...\n";
        
        if (!$this->testRecetaId) {
            echo "✗ No hay ID de receta de prueba\n\n";
            return;
        }

        try {
            $updateData = [
                'detalle' => 'Intento de actualización no autorizada'
            ];
            
            $result = $this->service->updateReceta(
                $this->testRecetaId, 
                $updateData, 
                999, // Otro médico
                'Medico'
            );
            
            if (!$result['success']) {
                echo "✓ Correctamente denegado: {$result['message']}\n";
            } else {
                echo "✗ Error: No debería poder actualizar receta de otro médico\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Actualizar receta como administrador
     */
    public function testUpdateRecetaComoAdministrador(): void
    {
        echo "Test: Actualizar receta como administrador...\n";
        
        if (!$this->testRecetaId) {
            echo "✗ No hay ID de receta de prueba\n\n";
            return;
        }

        try {
            $updateData = [
                'detalle' => 'Actualización realizada por administrador'
            ];
            
            $result = $this->service->updateReceta(
                $this->testRecetaId, 
                $updateData, 
                1, // ID admin
                'Administrador'
            );
            
            if ($result['success']) {
                echo "✓ Administrador puede actualizar cualquier receta\n";
            } else {
                echo "✗ Error: {$result['message']}\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Obtener estadísticas por médico
     */
    public function testGetEstadisticasByMedico(): void
    {
        echo "Test: Obtener estadísticas por médico...\n";
        
        try {
            $result = $this->service->getEstadisticasByMedico($this->testMedicoId);
            
            if ($result['success']) {
                echo "✓ Estadísticas obtenidas correctamente\n";
                echo "  - Total recetas: {$result['data']['total_recetas']}\n";
                echo "  - Recetas último mes: {$result['data']['recetas_ultimo_mes']}\n";
            } else {
                echo "✗ Error: {$result['message']}\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Intentar eliminar como médico (debe fallar)
     */
    public function testDeleteRecetaComoMedico(): void
    {
        echo "Test: Médico intenta eliminar receta (debe fallar)...\n";
        
        if (!$this->testRecetaId) {
            echo "✗ No hay ID de receta de prueba\n\n";
            return;
        }

        try {
            $result = $this->service->deleteReceta($this->testRecetaId, 'Medico');
            
            if (!$result['success']) {
                echo "✓ Correctamente denegado: {$result['message']}\n";
            } else {
                echo "✗ Error: Un médico no debería poder eliminar recetas\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    /**
     * Test: Eliminar receta como administrador
     */
    public function testDeleteRecetaComoAdministrador(): void
    {
        echo "Test: Eliminar receta como administrador...\n";
        
        if (!$this->testRecetaId) {
            echo "✗ No hay ID de receta de prueba\n\n";
            return;
        }

        try {
            $result = $this->service->deleteReceta($this->testRecetaId, 'Administrador');
            
            if ($result['success']) {
                echo "✓ Receta eliminada correctamente por administrador\n";
                $this->testRecetaId = null; // Marcar como eliminada
            } else {
                echo "✗ Error: {$result['message']}\n";
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
        echo "   PRUEBAS RECETA MEDICA SERVICE\n";
        echo "========================================\n\n";

        // Tests de creación y permisos
        $this->testCreateRecetaComoMedico();
        $this->testCreateRecetaComoCuidador();
        $this->testCreateRecetaParaOtroMedico();
        
        // Tests de lectura
        $this->testGetAllRecetas();
        $this->testGetRecetaById();
        $this->testGetRecetasByMedico();
        $this->testSearchRecetas();
        
        // Tests de actualización y permisos
        $this->testUpdateRecetaComoPropietario();
        $this->testUpdateRecetaDeOtroMedico();
        $this->testUpdateRecetaComoAdministrador();
        
        // Tests de estadísticas
        $this->testGetEstadisticasByMedico();
        
        // Tests de eliminación y permisos
        $this->testDeleteRecetaComoMedico();
        $this->testDeleteRecetaComoAdministrador();

        echo "========================================\n";
        echo "   PRUEBAS COMPLETADAS\n";
        echo "========================================\n\n";
    }
}

// Ejecutar tests si se llama directamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $test = new RecetaMedicaServiceTest();
    $test->runAllTests();
}
