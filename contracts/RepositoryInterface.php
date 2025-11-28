<?php

/**
 * Interface base para todos los repositorios
 * Define las operaciones básicas que deben implementar todos los repositorios
 */
interface RepositoryInterface
{
    /**
     * Encuentra un registro por su ID
     * @param mixed $id
     * @return mixed|null
     */
    public function findById($id);

    /**
     * Obtiene todos los registros
     * @param string|null $orderBy
     * @return array
     */
    public function findAll($orderBy = null);

    /**
     * Busca registros que coincidan con las condiciones
     * @param array $conditions
     * @return array
     */
    public function findWhere(array $conditions);

    /**
     * Crea un nuevo registro
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Actualiza un registro existente
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data);

    /**
     * Elimina un registro
     * @param mixed $id
     * @return bool
     */
    public function delete($id);

    /**
     * Verifica si existe un registro
     * @param mixed $id
     * @return bool
     */
    public function exists($id);
}

?>