<?php

namespace App\Http\Controllers;

use App\Models\CarritoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarritoController extends Controller
{
    // Añadir o actualizar producto en el carrito
    public function agregar(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1'
        ]);

        $item = CarritoItem::where('user_id', Auth::id())
            ->where('producto_id', $request->producto_id)
            ->first();

        if ($item) {
            $item->cantidad += $request->cantidad;
            $item->save();
        } else {
            $item = CarritoItem::create([
                'user_id' => Auth::id(),
                'producto_id' => $request->producto_id,
                'cantidad' => $request->cantidad
            ]);
        }

        return response()->json([
            'message' => 'Producto agregado al carrito',
            'item' => $item
        ]);
    }

    // Ver contenido del carrito
    public function ver()
    {
        $items = CarritoItem::with('producto')
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($items);
    }

    // Eliminar un producto del carrito
    public function eliminar($id)
    {
        $item = CarritoItem::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $item->delete();

        return response()->json([
            'message' => 'Producto eliminado del carrito'
        ]);
    }
}
