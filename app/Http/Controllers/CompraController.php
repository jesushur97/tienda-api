<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CarritoItem;
use App\Models\Producto;

class CompraController extends Controller
{
    public function confirmar()
    {
        $user = Auth::user();
        $carrito = CarritoItem::with('producto')->where('user_id', $user->id)->get();

        if ($carrito->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío'], 400);
        }

        DB::beginTransaction();

        try {
            $total = 0;
            $items = [];

            foreach ($carrito as $item) {
                $producto = $item->producto;

                if ($producto->stock < $item->cantidad) {
                    throw new \Exception("No hay suficiente stock de {$producto->nombre}");
                }

                $producto->stock -= $item->cantidad;
                $producto->save();

                $total += $producto->precio * $item->cantidad;

                $items[] = new OrderItem([
                    'producto_id' => $producto->id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $producto->precio
                ]);
            }

            $order = new Order([
                'user_id' => $user->id,
                'total' => $total
            ]);
            $order->save();
            $order->items()->saveMany($items);

            CarritoItem::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json(['message' => 'Compra confirmada', 'order' => $order->load('items')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function historial()
{
    $user = Auth::user();

    $orders = Order::with('items.producto')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json(['compras' => $orders]);
}

}
