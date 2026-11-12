<?php

namespace App\Http\Controllers;

use App\Http\Services\Memos\CreateMemoService;
use App\Http\Services\Memos\BuscarProveedoresService;
use Illuminate\Http\Request;

class MemorandumController extends Controller
{
    public function buscarPuntoCuenta(string $numero)
    {
        return CreateMemoService::buscarPuntoCuenta($numero);
    }

    public function buscarProveedores(Request $request)
    {
        return BuscarProveedoresService::buscar((string) $request->query('q', ''));
    }

    public function store(Request $request)
    {
        return CreateMemoService::store($request);
    }

    public function update(Request $request, $id)
    {
        return CreateMemoService::update($request, $id);
    }

    public function destroy($id)
    {
        return CreateMemoService::destroy($id);
    }

    public function index()
    {
        return CreateMemoService::index();
    }
}
