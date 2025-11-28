<?php

require_once __DIR__ . '/HistorialDto.php';

/**
 * DTO detallado para historial médico
 * Incluye información completa del paciente y análisis médicos
 */
class HistorialDetailDto extends HistorialDto
{
    public $edad_paciente;
    public $analisis_imc;
    public $signos_vitales_estado;
    public $recomendaciones;
    public $alertas_medicas;

    public function __construct($data = [])
    {
        parent::__construct($data);
        
        // Calcular edad si tenemos fecha de nacimiento
        $this->edad_paciente = $this->calcularEdad($data['fechanacimiento'] ?? null);
        
        // Análisis médico adicional
        $this->analisis_imc = $this->obtenerAnalisisIMC();
        $this->signos_vitales_estado = $this->evaluarSignosVitales();
        $this->recomendaciones = $this->generarRecomendaciones();
        $this->alertas_medicas = $this->identificarAlertas();
    }

    /**
     * Calcula la edad del paciente
     */
    protected function calcularEdad($fechaNacimiento)
    {
        if (empty($fechaNacimiento)) {
            return null;
        }

        $nacimiento = new DateTime($fechaNacimiento);
        $hoy = new DateTime();
        $edad = $nacimiento->diff($hoy);
        
        return $edad->y;
    }

    /**
     * Análisis detallado del IMC
     */
    protected function obtenerAnalisisIMC()
    {
        if ($this->imc === null) {
            return [
                'valor' => null,
                'categoria' => 'No disponible',
                'descripcion' => 'No se puede calcular sin peso y talla',
                'riesgo' => 'No evaluable'
            ];
        }

        $analisis = [
            'valor' => $this->imc,
            'categoria' => $this->categoria_imc
        ];

        if ($this->imc < 16) {
            $analisis['descripcion'] = 'Desnutrición severa';
            $analisis['riesgo'] = 'Alto';
        } elseif ($this->imc < 18.5) {
            $analisis['descripcion'] = 'Peso por debajo del normal';
            $analisis['riesgo'] = 'Moderado';
        } elseif ($this->imc < 25) {
            $analisis['descripcion'] = 'Peso saludable';
            $analisis['riesgo'] = 'Bajo';
        } elseif ($this->imc < 30) {
            $analisis['descripcion'] = 'Exceso de peso';
            $analisis['riesgo'] = 'Moderado';
        } elseif ($this->imc < 35) {
            $analisis['descripcion'] = 'Obesidad grado I';
            $analisis['riesgo'] = 'Alto';
        } elseif ($this->imc < 40) {
            $analisis['descripcion'] = 'Obesidad grado II';
            $analisis['riesgo'] = 'Alto';
        } else {
            $analisis['descripcion'] = 'Obesidad mórbida';
            $analisis['riesgo'] = 'Muy alto';
        }

        return $analisis;
    }

    /**
     * Evalúa el estado de los signos vitales
     */
    protected function evaluarSignosVitales()
    {
        $evaluacion = [];

        // Evaluar frecuencia cardíaca (edad adulta general)
        if (!empty($this->fchistorial)) {
            $fc = (int) $this->fchistorial;
            if ($fc < 60) {
                $evaluacion['frecuencia_cardiaca'] = ['estado' => 'Bradicardia', 'alerta' => true];
            } elseif ($fc > 100) {
                $evaluacion['frecuencia_cardiaca'] = ['estado' => 'Taquicardia', 'alerta' => true];
            } else {
                $evaluacion['frecuencia_cardiaca'] = ['estado' => 'Normal', 'alerta' => false];
            }
        }

        // Evaluar frecuencia respiratoria
        if (!empty($this->frhistorial)) {
            $fr = (int) $this->frhistorial;
            if ($fr < 12) {
                $evaluacion['frecuencia_respiratoria'] = ['estado' => 'Bradipnea', 'alerta' => true];
            } elseif ($fr > 20) {
                $evaluacion['frecuencia_respiratoria'] = ['estado' => 'Taquipnea', 'alerta' => true];
            } else {
                $evaluacion['frecuencia_respiratoria'] = ['estado' => 'Normal', 'alerta' => false];
            }
        }

        return $evaluacion;
    }

    /**
     * Genera recomendaciones basadas en los datos
     */
    protected function generarRecomendaciones()
    {
        $recomendaciones = [];

        // Recomendaciones basadas en IMC
        if ($this->imc !== null) {
            if ($this->imc < 18.5) {
                $recomendaciones[] = 'Considerar evaluación nutricional para aumento de peso';
            } elseif ($this->imc >= 25 && $this->imc < 30) {
                $recomendaciones[] = 'Implementar plan de ejercicio y dieta balanceada';
            } elseif ($this->imc >= 30) {
                $recomendaciones[] = 'Referir a especialista en obesidad y endocrinología';
            }
        }

        // Recomendaciones por signos vitales
        if (!empty($this->fchistorial)) {
            $fc = (int) $this->fchistorial;
            if ($fc < 60 || $fc > 100) {
                $recomendaciones[] = 'Monitoreo cardiovascular continuo recomendado';
            }
        }

        // Recomendaciones por alergias
        if (!empty($this->alergiashistorial) && $this->alergiashistorial !== 'Ninguna') {
            $recomendaciones[] = 'Verificar alergias antes de prescribir medicamentos';
        }

        return $recomendaciones;
    }

    /**
     * Identifica alertas médicas importantes
     */
    protected function identificarAlertas()
    {
        $alertas = [];

        // Alertas por IMC extremo
        if ($this->imc !== null) {
            if ($this->imc < 16) {
                $alertas[] = [
                    'tipo' => 'critica',
                    'mensaje' => 'IMC crítico - Posible desnutrición severa'
                ];
            } elseif ($this->imc >= 40) {
                $alertas[] = [
                    'tipo' => 'critica',
                    'mensaje' => 'IMC crítico - Obesidad mórbida'
                ];
            }
        }

        // Alertas por signos vitales
        if (!empty($this->fchistorial)) {
            $fc = (int) $this->fchistorial;
            if ($fc < 50) {
                $alertas[] = [
                    'tipo' => 'urgente',
                    'mensaje' => 'Bradicardia severa detectada'
                ];
            } elseif ($fc > 120) {
                $alertas[] = [
                    'tipo' => 'urgente',
                    'mensaje' => 'Taquicardia significativa detectada'
                ];
            }
        }

        if (!empty($this->frhistorial)) {
            $fr = (int) $this->frhistorial;
            if ($fr < 10 || $fr > 25) {
                $alertas[] = [
                    'tipo' => 'atencion',
                    'mensaje' => 'Frecuencia respiratoria fuera del rango normal'
                ];
            }
        }

        // Alertas por alergias críticas
        if (!empty($this->alergiashistorial)) {
            $alergiasCriticas = ['penicilina', 'aspirina', 'látex', 'mariscos'];
            $alergiaText = strtolower($this->alergiashistorial);
            
            foreach ($alergiasCriticas as $alergia) {
                if (strpos($alergiaText, $alergia) !== false) {
                    $alertas[] = [
                        'tipo' => 'critica',
                        'mensaje' => "Alergia crítica identificada: {$alergia}"
                    ];
                }
            }
        }

        return $alertas;
    }

    /**
     * Convierte a array con información extendida
     */
    public function toArray()
    {
        $base = parent::toArray();
        
        return array_merge($base, [
            'edad_paciente' => $this->edad_paciente,
            'analisis_imc' => $this->analisis_imc,
            'signos_vitales_estado' => $this->signos_vitales_estado,
            'recomendaciones' => $this->recomendaciones,
            'alertas_medicas' => $this->alertas_medicas,
            'resumen_clinico' => $this->generarResumenClinico()
        ]);
    }

    /**
     * Genera un resumen clínico
     */
    protected function generarResumenClinico()
    {
        $resumen = [];
        
        if ($this->edad_paciente) {
            $resumen[] = "Paciente de {$this->edad_paciente} años";
        }
        
        if ($this->imc !== null) {
            $resumen[] = "IMC: {$this->imc} ({$this->categoria_imc})";
        }
        
        if (!empty($this->alergiashistorial) && $this->alergiashistorial !== 'Ninguna') {
            $resumen[] = "Alergias reportadas";
        }
        
        $alertasCriticas = array_filter($this->alertas_medicas, function($alerta) {
            return $alerta['tipo'] === 'critica';
        });
        
        if (!empty($alertasCriticas)) {
            $resumen[] = "Requiere atención especial";
        }
        
        return implode(' | ', $resumen);
    }
}

?>