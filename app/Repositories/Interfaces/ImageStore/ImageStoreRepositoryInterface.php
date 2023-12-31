<?php

namespace App\Repositories\Interfaces\ImageStore;

interface ImageStoreRepositoryInterface
{
    public function getAll($request);
    public function getById($id);
    public function create($request);
    public function update($request, $id);
    public function delete($id);
    public function getTotalImageByCategory();
}
