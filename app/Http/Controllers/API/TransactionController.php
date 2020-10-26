<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transactions;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Exception;



class TransactionController extends Controller
{
    //
    // get all Transaction
    public function all(Request $request){
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $food_id = $request->input('food_id');
        $status = $request->input('status');

        // jika id ada isinya
        if($id){
            // cari food berdasarkan id
            $transaction = Transactions::with(['food','user'])->find($id);
            // jika food ditemukan
            if($transaction){
                return ResponseFormatter::success($transaction, "data transaksi berhasil di ambil");
            }
            else {
                return ResponseFormatter::error(
                    null, "data transaksi tidak ditemukan", 404
                );
            }
        }

        // jika ada parametr price from dll
        $trans = Transactions::with(['food','user'])->where('user_id', Auth::id());
        if($food_id){
            $trans->where('food_id',$food_id);
        }
        if($status){
            $trans->where('status',$status);
        }

        return ResponseFormatter::success($trans->paginate($limit), "Data list transaksi berhasil diambil");

    }

    // update transaction
    public function update(Request $request, $id){
        $trans = Transactions::findOrFail($id);
        $trans->update($request->all());

        return ResponseFormatter::success($trans, "Transaksi Berhasil di perbarui");
    }

    // checkout
    public function checkout(Request $request){
        $request->validate(
            [
                'food_id' => 'required|exist:food,id',
                'user_id' => 'required|exist:users,id',
                'quantity' => 'required',
                'total' => 'required',
                'status' => 'required',
            ]
        );

        $trans = Transactions::create([
            'food_id' => $request->food_id,
            'user_id' => $request->user_id,
            'quantity_id' => $request->quantity,
            'total' => $request->total,
            'status' => $request->status,
            'payment_url' => '',
        ]);

        // konfigutasi midtrans
        Config::$serverKey = config('service.midtrans.serverKey');
        Config::$isProduction = config('service.midtrans.isProduction');
        Config::$isSanitized = config('service.midtrans.isSanitized');
        Config::$is3ds = config('service.midtrans.is3ds');

        // call transaksi yang berasil di buat
        $trans = Transactions::with('food','user')->find($trans->id);

        // membuat transaksi midtrans
        $midtrans = [
            'transaction_details' => [
                'order_id' => $trans->id,
                'gross_amount' => (int)$trans->total,
            ],
            'customer_details' => [
                'first_name' => $trans->user->name,
                'email' => $trans->user->email,
            ],
            'enable_payment' => ['gopay','bank_transfer'],
            'vtweb' => []
        ];
        // memanggil midtrans
        try {
            // ambil halaman payemntmidtrans
            $payment = Snap::createTransaction($midtrans)->redirect_url;
            $trans->payment_url = $payment;
            $trans->save();

            // mengembalikan ke api
            return ResponseFormatter::success($trans, "Transaksi berhasil, silahkan bayar dengan link yang diberikan");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(),"Transaksi gagal");
        }

    }
}
