<?php

namespace App\Repositories\Interfaces\SeederDb;

interface SeederDbRepositoryInterface
{
    public function insertMahasiswa($page);
    public function insertEmployees($page);
    public function insertDlbEmployees($page);
}
