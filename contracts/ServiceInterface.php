<?php

/**
 * Interface base para todos los servicios
 * Define la estructura básica que deben seguir los servicios de negocio
 */
interface ServiceInterface
{
    /**
     * Obtiene un elemento por ID
     * @param mixed $id
     * @return mixed|null
     */
    public function getById($id);

    /**
     * Obtiene todos los elementos
     * @return array
     */
    public function getAll();

    /**
     * Crea un nuevo elemento
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Actualiza un elemento existente
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data);

    /**
     * Elimina un elemento
     * @param mixed $id
     * @return bool
     */
    public function delete($id);

    /**
     * Valida los datos según las reglas de negocio
     * @param array $data
     * @param mixed $id (opcional, para validaciones de actualización)
     * @return array Array de errores (vacío si es válido)
     */
    public function validate(array $data, $id = null);
}

?>