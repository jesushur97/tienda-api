<?php

namespace App\Http\Controllers;

use App\Models\CarritoItem;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CarritoController extends Controller
{
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
            'user_id' => auth()->id(), // ← corregido aquí
            'producto_id' => $request->producto_id,
            'cantidad' => $request->cantidad
        ]);
    }

    return response()->json([
        'message' => 'Producto agregado al carrito',
        'item' => $item
    ]);
}


    public function ver()
    {
        $items = CarritoItem::with('producto')
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($items);
    }

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
