<?php

namespace App\Jobs;

use App\Http\Controllers\FrontEnd\Event\BookingController;
use App\Models\Earning;
use App\Models\Event\Booking;
use Config\Iyzipay;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IyzicoEventPendingPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $booking_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($booking_id)
    {
        $this->booking_id = $booking_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $eventBooking = Booking::where('id', $this->booking_id)->first();
        $conversion_id = $eventBooking->conversation_id;

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
                        // generate an invoice in pdf format
                        $booking = new BookingController();
                        $eventBooking->update(['paymentStatus' => 'completed']);
                        $invoice = $booking->generateInvoice($eventBooking, $eventBooking->event_id);
                        //unlink qr code
                        @unlink(public_path('assets/admin/qrcodes/') . $eventBooking->booking_id . '.svg');
                        //end unlink qr code


                        // then, update the invoice field info in database
                        $eventBooking->update(['invoice' => $invoice]);


                        //add blance to admin revinue
                        $earning = Earning::first();
                        $earning->total_revenue = $earning->total_revenue + $eventBooking->price + $eventBooking->tax;
                        if ($eventBooking->organizer_id != null) {
                            $earning->total_earning = $earning->total_earning + ($eventBooking->tax + $eventBooking->commission);
                        } else {
                            $earning->total_earning = $earning->total_earning + $eventBooking->price + $eventBooking->tax;
                        }
                        $earning->save();

                        //storeTransaction
                        $eventBooking['paymentStatus'] = 1;
                        $eventBooking['transcation_type'] = 1;

                        storeTranscation($eventBooking);

                        //store amount to organizer
                        $organizerData['organizer_id'] = $eventBooking->organizer_id;
                        $organizerData['price'] = $eventBooking->price;
                        $organizerData['tax'] = $eventBooking->tax;
                        $organizerData['commission'] = $eventBooking->commission;
                        storeOrganizer($organizerData);
                        \Artisan::call("queue:work --stop-when-empty");
                    }
                }
            }
        }
    }
}
