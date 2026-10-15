<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;

class SaleProccess extends Controller
{
    public function create()
    {
        $products = Product::all();
        $categories = Category::all();
        return view("sale_proccess.create", compact("products", "categories"));
    }


    public function createInvoice(Request $request)
    {
        $itemsData = $request->get('items', []);

        if (is_string($itemsData)) {
            $itemsData = json_decode($itemsData, true);
        }

        if (!is_array($itemsData)) {
            $itemsData = [];
        }

        $items = [];
        foreach ($itemsData as $item) {
            // تم التعديل هنا ليطابق الحقل المرسل من الفورم
            $productId = $item['product_id'] ?? $item['id'] ?? null;

            $product = Product::find($productId);

            if ($product) {
                $items[] = [
                    'id'    => $product->id,
                    'name'  => $product->name,
                    'price' => $product->price,
                    'cost'  => $product->cost,
                    'qty'   => $item['qty'] ?? 1,
                    'total' => $product->price * ($item['qty'] ?? 1),
                ];
            }
        }

        return view("sale_proccess.invoice", compact("items"));
    }
}
