<?php

namespace App\Jobs;

use App\Http\Controllers\FrontEnd\Shop\OrderController;
use App\Models\Earning;
use App\Models\ShopManagement\ProductOrder;
use App\Models\Transaction;
use Config\Iyzipay;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IyzicoProductOrderPendingPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $order_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $productOrder = ProductOrder::where('id', $this->order_id)->first();
        $conversion_id = $productOrder->conversation_id;

        $options = Iyzipay::options();

        $request = new \Iyzipay\Request\ReportingPaymentDetailRequest();
        $request->setPaymentConversationId($conversion_id);


        $paymentResponse = \Iyzipay\Model\ReportingPaymentDetail::create($request, $options);

        $result = (array) $paymentResponse;

        foreach ($result as $key => $data) {
            $data = json_decode($data, true);
            if ($data['status'] == 'success' && !is_null($data['payments'])) {
                if (is_array($data['payments'])) {
                    if ($data['payments'][0]['paymentStatus'] == 1) {
                        $store = new OrderController();
                        $productOrder->update(['payment_status' => 'completed']);
                        // generate an invoice in pdf format
                        $invoice = $store->generateInvoice($productOrder);

                        // then, update the invoice field info in database
                        $productOrder->update(['invoice_number' => $invoice]);

                        $earning = Earning::first();
                        $earning->total_revenue = $earning->total_revenue + $productOrder->total;
                        $earning->total_earning = $earning->total_earning + $productOrder->total;
                        $earning->save();

                        //store data to transcation table 
                        Transaction::create([
                            'transcation_id' => time(),
                            'booking_id' => $productOrder->id,
                            'transcation_type' => 2,
                            'customer_id' => $productOrder->user_id,
                            'organizer_id' => null,
                            'payment_status' => 1,
                            'payment_method' => $productOrder->method,
                            'grand_total' => $productOrder->total,
                            'commission' => $productOrder->total,
                            'gateway_type' => $productOrder->gateway_type,
                            'currency_symbol' => $productOrder->currency_symbol,
                            'currency_symbol_position' => $productOrder->currency_symbol_position,
                        ]);

                        // send a mail to the customer with the invoice
                        $store->sendMail($productOrder);


                        \Artisan::call("queue:work --stop-when-empty");
                    }
                }
            }
        }
    }
}
