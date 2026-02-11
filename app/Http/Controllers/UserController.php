<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    // B. Listar usuarios con Filtros y Paginación
    public function index(Request $request)
    {
        $query = User::query();

        // Filtro para usuarios eliminados (Soft Deletes)
        if ($request->has('is_trashed') && $request->is_trashed === 'true') {
            $query->onlyTrashed();
        }

        // Filtros de búsqueda opcionales
        if ($request->has('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // Paginación: divide los resultados, aquí usamos 10 por página
        $users = $query->paginate(10);
        return UserResource::collection($users);
    }

    // A. Crear usuario
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        // Lógica de negocio: Si hiring_date está vacío, asignar la fecha actual
        $data['hiring_date'] = $data['hiring_date'] ?? now()->toDateString();
        $data['password'] = bcrypt(Str::random(8));

        $user = User::create($data);

        return response()->json(new UserResource($user), 201);
    }

    // C. Obtener el detalle de un usuario
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'El usuario solicitado no existe.'], 404);
        }

        return new UserResource($user);
    }

    // D y E. Actualización Completa (PUT) y Parcial (PATCH)
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'El usuario que intentas actualizar no existe.'], 404);
        }

        $user->update($request->validated());

        return response()->json(new UserResource($user), 200);
    }

    // E. Eliminar Usuarios (Soft Delete)
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'El usuario que intentas eliminar no existe.'], 404);
        }

        $user->delete(); // Esto hace el soft delete gracias al trait en el Modelo

        return response()->json(['message' => 'El usuario ha sido eliminado correctamente.'], 200);
    }

    // Evaluación: Endpoint de Restauración
    public function restore($id)
    {
        // Buscamos solo entre los eliminados
        $user = User::onlyTrashed()->find($id);

        if (!$user) {
            return response()->json(['message' => 'El usuario no existe entre los eliminados.'], 404);
        }

        $user->restore();

        return response()->json(['message' => 'Usuario restaurado correctamente.'], 200);
    }
}
