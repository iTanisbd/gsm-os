<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $shopId = auth()->user()->shop_id;
        $inventories = Inventory::where('shop_id', $shopId)->orderBy('name', 'asc')->get();

        // Summary data for top cards
        $totalItems = $inventories->count();
        $totalStockValue = $inventories->sum(function($item) {
            return $item->purchase_price * $item->stock;
        });

        $lowStockCount = $inventories->filter(function($item) {
            return $item->is_low_stock;
        })->count();

        return view('inventory.index', compact('inventories', 'totalItems', 'totalStockValue', 'lowStockCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'brand' => 'required|string', // Made Required
            'model' => 'required|string', // Made Required
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $data = $request->all();
        $data['shop_id'] = auth()->user()->shop_id;

        Inventory::create($data);

        return back()->with('success', 'New part successfully added to stock!');
    }

    public function destroy($id)
    {
        $inventory = Inventory::where('shop_id', auth()->user()->shop_id)->findOrFail($id);
        $inventory->delete();
        return back()->with('success', 'Part has been removed from stock.');
    }
}
