<?php

abstract class BaseController
{
    protected $conexion;

    public function __construct()
    {
        require_once __DIR__ . '/../conexion.php';
        $this->conexion = conexion();
    }

    /**
     * Obtiene el cuerpo de la petición JSON
     */
    protected function getJsonInput()
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }

    /**
     * Envía una respuesta JSON exitosa
     */
    protected function jsonResponse($data, $message = null, $statusCode = 200)
    {
        http_response_code($statusCode);
        
        $response = [
            'success' => true
        ];

        // Flatten common envelope structures { data, pagination }
        if (is_array($data) && array_key_exists('data', $data) && array_key_exists('pagination', $data)) {
            $response['data'] = $data['data'];
            $response['pagination'] = $data['pagination'];
        } else {
            $response['data'] = $data;
        }
        
        if ($message) {
            $response['message'] = $message;
        }
        
        echo json_encode($response);
    }

    /**
     * Envía una respuesta JSON de error
     */
    protected function jsonError($message, $statusCode = 400, $errors = null)
    {
        http_response_code($statusCode);
        
        $response = [
            'success' => false,
            'error' => $message
        ];
        
        if ($errors) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response);
    }

    /**
     * Valida que los campos requeridos estén presentes
     */
    protected function validateRequired($data, $requiredFields)
    {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }

    /**
     * Sanitiza una cadena para prevenir inyección SQL
     */
    protected function sanitize($string)
    {
        return mysqli_real_escape_string($this->conexion, $string);
    }

    /**
     * Sanitiza una cadena de texto para prevenir XSS
     */
    protected function sanitizeString($string)
    {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Ejecuta una consulta y maneja errores
     */
    protected function executeQuery($query)
    {
        $result = mysqli_query($this->conexion, $query);
        
        if (!$result) {
            throw new Exception("Error en la consulta: " . mysqli_error($this->conexion));
        }
        
        return $result;
    }

    /**
     * Obtiene todos los resultados de una consulta como array asociativo
     */
    protected function fetchAll($query)
    {
        $result = $this->executeQuery($query);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Obtiene un solo resultado de una consulta
     */
    protected function fetchOne($query)
    {
        $result = $this->executeQuery($query);
        return $result->fetch_assoc();
    }
}

?>