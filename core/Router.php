<?php

class Router
{
    private $routes = [];

    /**
     * Registra una ruta GET
     */
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Registra una ruta POST
     */
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Registra una ruta PUT
     */
    public function put($path, $handler)
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Registra una ruta DELETE
     */
    public function delete($path, $handler)
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Agrega una ruta al registro
     */
    private function addRoute($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->convertToPattern($path)
        ];
    }

    /**
     * Convierte una ruta con parámetros a un patrón regex
     */
    private function convertToPattern($path)
    {
        // Reemplazar directamente los parámetros por el patrón de captura
        $pattern = str_replace('{', '(?P<', $path);
        $pattern = str_replace('}', '>[^/]+)', $pattern);
        $pattern = str_replace('/', '\/', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    /**
     * Despacha la ruta actual
     */
    public function dispatch($method, $uri)
    {
        // Normalizar URI (remover slash final si existe)
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                // Remover el primer elemento (coincidencia completa)
                array_shift($matches);
                
                // Extraer parámetros de la ruta
                $params = $this->extractParams($route['path'], $matches);
                
                // Ejecutar el handler
                return $this->executeHandler($route['handler'], $params);
            }
        }

        // Ruta no encontrada
        $this->notFound();
    }

    /**
     * Extrae los parámetros de la ruta
     */
    private function extractParams($path, $matches)
    {
        $params = [];
        preg_match_all('/\{([^}]+)\}/', $path, $paramNames);
        
        if (!empty($paramNames[1])) {
            foreach ($paramNames[1] as $index => $paramName) {
                if (isset($matches[$index])) {
                    $params[$paramName] = $matches[$index];
                }
            }
        }
        
        return $params;
    }

    /**
     * Ejecuta el handler de la ruta
     */
    private function executeHandler($handler, $params = [])
    {
        if (is_string($handler) && strpos($handler, '@') !== false) {
            // Formato: 'ControllerName@methodName'
            list($controllerName, $methodName) = explode('@', $handler);
            
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                
                if (method_exists($controller, $methodName)) {
                    return call_user_func_array([$controller, $methodName], [$params]);
                } else {
                    throw new Exception("Método '$methodName' no encontrado en '$controllerName'");
                }
            } else {
                throw new Exception("Controlador '$controllerName' no encontrado");
            }
        } elseif (is_callable($handler)) {
            // Handler es una función
            return call_user_func($handler, $params);
        } else {
            throw new Exception("Handler inválido");
        }
    }

    /**
     * Respuesta para ruta no encontrada
     */
    private function notFound()
    {
        http_response_code(404);
        echo json_encode([
            'error' => 'Ruta no encontrada',
            'message' => 'El endpoint solicitado no existe'
        ]);
    }

    /**
     * Obtiene todas las rutas registradas (útil para debugging)
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}

?>