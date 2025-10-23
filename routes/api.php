<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Producto;
use App\Models\CarritoItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use Tymon\JWTAuth\Facades\JWTAuth;

// Ruta de prueba
Route::get('/ping', fn() => response()->json(['message' => 'pong']));

// Autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->get('/me', [AuthController::class, 'me']);
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);

// Registro
Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    $token = auth('api')->login($user);

    return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60
    ]);
});

// Productos
Route::get('/productos', fn() => Producto::all());

Route::middleware('auth:api')->post('/productos', function (Request $request) {
    $validated = $request->validate([
        'nombre' => 'required|string|max:255',
        'precio' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
    ]);

    $producto = Producto::create($validated);

    return response()->json(['mensaje' => 'Producto creado', 'producto' => $producto]);
});

Route::middleware('auth:api')->delete('/productos/{id}', function ($id) {
    $producto = Producto::findOrFail($id);
    $producto->delete();

    CarritoItem::where('producto_id', $id)->delete();

    return response()->json(['mensaje' => 'Producto eliminado']);
});

// Carrito
Route::middleware('auth:api')->post('/carrito', function () {
    try {
        $user = JWTAuth::parseToken()->authenticate();

        $validated = request()->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = Producto::find($validated['producto_id']);
        if (! $producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $item = CarritoItem::updateOrCreate(
            ['user_id' => $user->id, 'producto_id' => $producto->id],
            ['cantidad' => DB::raw("cantidad + {$validated['cantidad']}")]
        );

        // ✅ Corrección aquí
        $item = CarritoItem::with('producto')->find($item->id);

        return response()->json(['mensaje' => 'Producto añadido al carrito', 'item' => $item]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Token inválido o expirado'], 401);
    }
});

Route::middleware('auth:api')->get('/carrito', function () {
    try {
        $user = JWTAuth::parseToken()->authenticate();

        $items = CarritoItem::with('producto')
            ->where('user_id', $user->id)
            ->get()
            ->filter(fn($item) => $item->producto !== null)
            ->values();

        return response()->json($items);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Token inválido o expirado'], 401);
    }
});

Route::middleware('auth:api')->delete('/carrito/{id}', [CarritoController::class, 'eliminar']);

// Confirmar compra
Route::middleware('auth:api')->post('/confirmar-compra', function () {
    try {
        $user = JWTAuth::parseToken()->authenticate();

        $items = CarritoItem::where('user_id', $user->id)->with('producto')->get();
        $validos = $items->filter(fn($item) => $item->producto !== null);

        if ($validos->isEmpty()) {
            return response()->json(['error' => 'Carrito vacío o productos no disponibles'], 400);
        }

        foreach ($validos as $item) {
            if ($item->producto->stock < $item->cantidad) {
                return response()->json([
                    'error' => "Stock insuficiente para el producto {$item->producto->nombre}"
                ], 400);
            }
        }

        $total = $validos->sum(fn($item) => $item->producto->precio * $item->cantidad);

        $order = Order::create([
            'user_id' => $user->id,
            'total' => $total,
        ]);

        foreach ($validos as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'producto_id' => $item->producto_id,
                'cantidad' => $item->cantidad,
                'precio_unitario' => $item->producto->precio,
            ]);

            $item->producto->decrement('stock', $item->cantidad);
        }

        CarritoItem::where('user_id', $user->id)->delete();

        return response()->json(['mensaje' => 'Compra confirmada', 'order_id' => $order->id]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Token inválido o expirado'], 401);
    }
});

// Historial de compras
Route::middleware('auth:api')->get('/mis-compras', function () {
    try {
        $user = JWTAuth::parseToken()->authenticate();
        return Order::with(['items.producto'])->where('user_id', $user->id)->latest()->get();
    } catch (\Exception $e) {
        return response()->json(['error' => 'Token inválido o expirado'], 401);
    }
});
