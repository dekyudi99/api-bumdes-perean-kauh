<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Order_Item;
use App\Models\Products;
use App\Models\Cart;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PaymentController;
use App\Http\Resources\ApiResponseResources;
use App\Mail\OrderNotificationMail;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    // Menambahkan produk ke cart
    public function cart(Request $request, $id) {
        $messages = [
            'quantity.required' => 'Kuantitas Wajib Diisi!',
            'quantity.numeric' => 'Kuantitas Wajib Berupa Nomor!',
            'quantity.min' => 'Kuantitas Minimal 1!',
        ];

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:1',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $idUser = Auth::id();

        $cart = Cart::where('user_id', $idUser)->where('product_id', $id)->first();
        
        if ($cart) {
            $cart->increment('quantity', $request->quantity);
            
            return new ApiResponseResources(true, 'Kuantitas Produk di Keranjang Berhasil Diperbarui!', $cart);
        } else {
            $newCartItem = Cart::create([
                'user_id' => $idUser,
                'product_id' => $id,
                'quantity' => $request->quantity,
            ]);

            if (!$newCartItem) {
                return new ApiResponseResources(false, 'Produk Gagal Ditambahkan Di Keranjang!', null, 500);
            }

            return new ApiResponseResources(true, 'Produk Berhasil Ditambahkan Di Keranjang!', $$newCartItem, 201);
        }
    }

    // Melihat Keranjang saya
    public function mycart() {
        $id = Auth::id();
        $cart = Cart::where('user_id', $id)->with('product')->get();

        if (!$cart) {
            return new ApiResponseResources(false, 'Gagal Mengambil Data Keranjang!', null, 500);
        }

        return new ApiResponseResources(true, 'Daftar Produk di Keranjang Anda Berhasil Ditampilkan!', $cart);
    }

    public function orderCart(Request $request)
    {
        $messages = [
            'cart_ids.required' => 'ID Keranjang Wajib Diisi!',
            'cart_ids.array' => 'ID Keranjang Wajib Bertipe Array!',
            'cart_ids.*.exists' => 'Keranjang Tidak Ada!',
            'shipping_address.required' => 'Lokasi Tujuan Wajib Diisi!',
            'shipping_address.string' => 'Lokasi Tujuan Wajib Bertipe String!',
        ];

        $validator = Validator::make($request->all(), [
            'cart_ids'       => 'required|array',
            'cart_ids.*'     => 'exists:cart,id',
            'shipping_address' => 'required|string',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $idUser = Auth::id();
        $selectedCartIds = $request->input('cart_ids');

        $cartItems = Cart::where('user_id', $idUser)
                        ->whereIn('id', $selectedCartIds)
                        ->with('product')
                        ->get();

        if ($cartItems->count() != count($selectedCartIds)) {
            return new ApiResponseResources(false, 'Beberapa Item Tidak Valid!', null, 400);
        }

        try {
            $order = DB::transaction(function () use ($cartItems, $idUser, $request, $selectedCartIds) {
                
                $totalAmount = 0;
                foreach ($cartItems as $item) {
                    if (!$item->product || $item->product->stock < $item->quantity) {
                        throw new \Exception('Stok produk ' . $item->product->name . ' tidak mencukupi.');
                    }
                    $totalAmount += $item->product->price * $item->quantity;
                }

                $newOrder = Orders::create([
                    'user_id' => $idUser,
                    'invoice_number' => 'INV-' . time(),
                    'total_amount' => $totalAmount,
                    'shipping_address' => $request->input('shipping_address'),
                    'status' => 'unpaid',
                ]);

                foreach ($cartItems as $item) {
                    Order_Item::create([
                        'order_id' => $newOrder->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'name_at_purchase' => $item->product->name,
                        'price_at_purchase' => $item->product->price,
                        'description_at_purchase' => $item->product->description,
                        'subtotal' => $item->quantity * $item->product->price,
                    ]);
                    $item->product->decrement('stock', $item->quantity);
                }
                
                Cart::whereIn('id', $selectedCartIds)->delete();

                return $newOrder;
            });

            $paymentController = new PaymentController();

            $paymentResponse = $paymentController->payProduct($order->id);

            $paymentData = json_decode($paymentResponse->getContent(), true);

            $paymentUrl = $paymentData['payment_url'] ?? null;

            $adminEmails = \App\Models\User::where('role', 'adminMinimarket')->pluck('email');
            Mail::to($adminEmails)->send(new OrderNotificationMail($order));

            return new ApiResponseResources(true, 'Pesanan Berhasil Dibuat!', [$order, $paymentUrl], 201);
        } catch (\Exception $e) {
            return new ApiResponseResources(false, 'Gagal Membuat Pesanan: '.$e, null, 500);
        }
    }

    public function directOrder(Request $request, $id) {
        $messages = [
            'quantity.required' => 'Kuantitas Wajib Diisi!',
            'quantity.numeric' => 'Kuantitas Wajib Berupa Nomor!',
            'quantity.min' => 'Kuantitas Minimal 1!',
            'shipping_address.required' => 'Lokasi Tujuan Wajib Diisi!',
            'shipping_address.string' => 'Lokasi Tujuan Wajib Bertipe String!',
        ];

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:1',
            'shipping_address' => 'required|string',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $idUser = Auth::id();
        $quantity = $request->input('quantity');

        $product = Products::find($id);

        if (!$product) {
            return new ApiResponseResources(false, 'Produk Tidak Ditemukan!', null, 404);
        }

        if ($product->stock < $quantity) {
            return new ApiResponseResources(false, 'Stok Produk Tidak Mencukupi!', null, 400);
        }

        try {
            $order = DB::transaction(function () use ($idUser, $product, $quantity, $request) {
                
                $newOrder = Orders::create([
                    'user_id' => $idUser,
                    'invoice_number' => 'INV-' . time(),
                    'total_amount' => $quantity * $product->price,
                    'shipping_address' => $request->input('shipping_address'),
                    'status' => 'unpaid',
                ]);

                Order_Item::create([
                    'order_id' => $newOrder->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'name_at_purchase' => $product->name, 
                    'price_at_purchase' => $product->price,
                    'description_at_purchase' => $product->description,
                    'subtotal' => $quantity * $product->price,
                ]);

                $product->decrement('stock', $quantity);
                
                return $newOrder;
            });

            $paymentController = new PaymentController();

            $paymentResponse = $paymentController->payProduct($order->id);

            $paymentData = json_decode($paymentResponse->getContent(), true);
            
            $paymentUrl = $paymentData['payment_url'] ?? null;

            $adminEmails = \App\Models\User::where('role', 'adminMinimarket')->pluck('email');
            Mail::to($adminEmails)->send(new OrderNotificationMail($order));

            return new ApiResponseResources(true, 'Pesanan Berhasil Dibuat!', [$order, $paymentUrl], 201);
        } catch (\Exception $e) {
            return new ApiResponseResources(false, 'Gagal Membuat Pesanan: '.$e, null, 500);
        }
    }

    public function myOrder() {
        $id = Auth::id();
        $order = Orders::where('user_id', $id)
                   ->orderBy('created_at', 'desc')
                   ->get();

        if (!$order) {
            return new ApiResponseResources(false, 'Gagal Mengambil Data Pesanan!', null, 500);
        }

        return new ApiResponseResources(true, 'Berhasil Mengambil Data Pesanan!', $order);
    }

    // untuk admin melihat semua pesanan
    public function orders() {
        $orders = Orders::all();

        if (!$orders) {
            return new ApiResponseResources(false, 'Gagal Mengambil Data Pesanan', null, 500);
        }

        return new ApiResponseResources(false, 'Berhasil Menampilkan Seluruh Data Pesanan!', $orders);
    }

    public function show($id)
    {
        $order = Orders::find($id);

        if (!$order) {
            return new ApiResponseResources(false, 'Pesanan Tidak Ditemukan!', null, 404);
        }

        return new ApiResponseResources(true, 'Detail Pesanan Berhasil Ditempilkan!', $order);
    }
}