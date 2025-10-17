<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Producto;
use App\Models\CarritoItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Ruta de prueba
Route::get('/ping', fn() => response()->json(['message' => 'pong']));

// Autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth.api')->get('/me', [AuthController::class, 'me']);
Route::middleware('auth.api')->post('/logout', [AuthController::class, 'logout']);

// Listar productos (público)
Route::get('/productos', fn() => Producto::all());

// Crear producto (requiere token)
Route::middleware('auth.api')->post('/productos', function (Request $request) {
    $validated = $request->validate([
        'nombre' => 'required|string|max:255',
        'precio' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
    ]);

    $producto = Producto::create($validated);

    return response()->json(['mensaje' => 'Producto creado', 'producto' => $producto]);
});

// Añadir al carrito
Route::middleware('auth.api')->post('/carrito', function (Request $request) {
    $validated = $request->validate([
        'producto_id' => 'required|exists:productos,id',
        'cantidad' => 'required|integer|min:1',
    ]);

    $item = CarritoItem::updateOrCreate(
        ['user_id' => $request->user()->id, 'producto_id' => $validated['producto_id']],
        ['cantidad' => \DB::raw("cantidad + {$validated['cantidad']}")]
    );

    return response()->json(['mensaje' => 'Producto añadido al carrito', 'item' => $item]);
});

// Ver carrito
Route::middleware('auth.api')->get('/carrito', function (Request $request) {
    return CarritoItem::with('producto')->where('user_id', $request->user()->id)->get();
});

// Confirmar compra
Route::middleware('auth.api')->post('/confirmar-compra', function (Request $request) {
    $user = $request->user();
    $items = CarritoItem::where('user_id', $user->id)->with('producto')->get();

    if ($items->isEmpty()) {
        return response()->json(['error' => 'Carrito vacío'], 400);
    }

    $total = $items->sum(fn($item) => $item->producto->precio * $item->cantidad);

    $order = Order::create([
        'user_id' => $user->id,
        'total' => $total,
    ]);

    foreach ($items as $item) {
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
});

// Ver historial de compras
Route::middleware('auth.api')->get('/mis-compras', function (Request $request) {
    return Order::with(['items.producto'])->where('user_id', $request->user()->id)->latest()->get();
});


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

    $token = auth()->login($user);

    return response()->json(['token' => $token]);
});