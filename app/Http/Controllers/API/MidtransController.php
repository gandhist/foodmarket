<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;
use App\Models\Transactions;

class MidtransController extends Controller
{

    // fungsi untuk menerima callback dari midtrans
    public function callback(Request $request){

        // set konfigurasi midtrans
        Config::$serverKey = config('service.midtrans.serverKey');
        Config::$isProduction = config('service.midtrans.isProduction');
        Config::$isSanitized = config('service.midtrans.isSanitized');
        Config::$is3ds = config('service.midtrans.is3ds');

        // buat instance midtrans notification
        $notifiy = new Notification();

        // assign ke variable untuk memudahkan coding
        $status = $notifiy->transaction_status;
        $type = $notifiy->payment_type;
        $fraud = $notifiy->fraud_status;
        $order_id = $notifiy->order_id;

        // cari transaksi by id
        $transaction = Transactions::findOrFail($order_id);

        // handle notifikasi status midtrans
        if ($transaction == 'capture') {
            // jika credit card
            if($type == 'credit_card'){
                if ($fraud == 'challenge') {
                    $transaction->status = "PENDING";
                    // TODO Set payment status in merchant's database to 'challenge'
                }
                else {
                    $transaction->status = "SUCCESS";
                    // TODO Set payment status in merchant's database to 'success'
                }
            }
            
        }
        else if($status == 'settlement'){
            $transaction->status = "SUCCESS";
        }
        else if($status == 'pending'){
            $transaction->status = "PENDING";
        }
        else if($status == 'expire'){
            $transaction->status = "CANCELLED";
        }
        else if ($transaction == 'cancel') {
            $transaction->status = "CANCELLED";
        }
        else if ($transaction == 'deny') {
            $transaction->status = "CANCELLED";
              // TODO Set payment status in merchant's database to 'failure'
        }

        // simpan transaksi
        $transaction->save();
    }

    // halaman berhasil transaksi
    public function success(){
        return view('midtrans.success');
    }

    // trx gagal
    public function unfinish(){
        return view('midtrans.unfinish');
    }

    // trx unfinished
    public function error(){
        return view('midtrans.error');
    }

}
