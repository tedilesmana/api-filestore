<?php

namespace App\Services;

use App\Http\Resources\Order\OrderResource;
use App\Models\CreditCustomerTransaction;
use App\Models\CreditDriverTransaction;
use App\Models\CreditSellerTransaction;
use App\Models\Customer;
use App\Models\DebetCustomerTransaction;
use App\Models\DebetDriverTransaction;
use App\Models\DebetSellerTransaction;
use App\Models\LogOrder;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Seller;
use App\Models\SellerPromotion;
use App\Models\SharingProfit;
use App\Models\TopUpTransaction;
use App\Models\UsedPromotion;
use App\Models\WalletCustomer;
use App\Models\WalletDriver;
use App\Models\WalletSeller;
use App\Models\WithdrawalTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewayService
{
    public function getPaymentMethod($payment_amount)
    {
        $merchant_key = env('PAYMENT_GATEWAY_MERCHANT_KEY');
        $merchant_code = env('PAYMENT_GATEWAY_MERCHANT_CODE');

        $datetime = date('Y-m-d H:i:s');
        $signature = hash('sha256', $merchant_code . $payment_amount . $datetime . $merchant_key);

        $items_param = array(
            'merchantcode'  => $merchant_code,
            'amount'        => $payment_amount,
            'datetime'      => $datetime,
            'signature'     => $signature
        );

        $params_string = json_encode($items_param);

        $url = env('PAYMENT_GATEWAY_BASE_API') . '/paymentmethod/getpaymentmethod';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params_string)
            )
        );

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $request = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 200) {
            $result = json_decode($request, true);
            return $result;
        } else {
            return null;
        }
    }

    public function callbackTransaction($invoice)
    {
        $order = Order::where('invoice', $invoice)->first();
        $order_detail = $order->orderDetail()->first();
        $order_detail->status = 'paid';
        $order_detail->save();

        $sellerSharingProfit = SharingProfit::where('sharing_type', 'seller')->first();

        $sharingProfitAmount = $order->grand_total - $sellerSharingProfit->fixed_value;

        $seller = Seller::find($order->seller_id);

        $walletSeller = WalletSeller::where('seller_id', $seller->id)->first();
        $walletSeller->saldo = $walletSeller->saldo + $sharingProfitAmount;
        $walletSeller->save();

        $latestData = DebetSellerTransaction::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->dst_code : null, DebetSellerTransaction::CODE);
        $inputDebetSeller['dst_code'] = $code;
        $inputDebetSeller['wallet_seller_id'] = $walletSeller->id;
        $inputDebetSeller['invoice'] = $order->invoice;
        $inputDebetSeller['status'] = $order_detail->status;
        $inputDebetSeller['nominal'] = $sharingProfitAmount;
        $inputDebetSeller['description'] = 'Debet from order product';
        DebetSellerTransaction::create($inputDebetSeller);

        return $order_detail;
    }

    public function callbackWallet($invoice, $type)
    {
        if ($type == 'customer') {
            $creditCustomer = CreditCustomerTransaction::where('invoice', $invoice)->first();
            $creditCustomer->status = 'success';
            $creditCustomer->save();

            $topUpTransaction = TopUpTransaction::where('invoice', $invoice)->first();
            $topUpTransaction->status = 'success';
            $topUpTransaction->save();

            $walletCustomer = $creditCustomer->walletCustomer;
            $walletCustomer->saldo = $walletCustomer->saldo + $creditCustomer->nominal;
            $walletCustomer->save();
        } else if ($type == 'seller') {
            $creditSeller = CreditSellerTransaction::where('invoice', $invoice)->first();
            $creditSeller->status = 'success';
            $creditSeller->save();

            $topUpTransaction = TopUpTransaction::where('invoice', $invoice)->first();
            $topUpTransaction->status = 'success';
            $topUpTransaction->save();

            $walletSeller = $creditSeller->walletSeller;
            $walletSeller->saldo = $walletSeller->saldo + $creditSeller->nominal;
            $walletSeller->save();
        } else if ($type == 'driver') {
            $creditDriver = CreditDriverTransaction::where('invoice', $invoice)->first();
            $creditDriver->status = 'success';
            $creditDriver->save();

            $topUpTransaction = TopUpTransaction::where('invoice', $invoice)->first();
            $topUpTransaction->status = 'success';
            $topUpTransaction->save();

            $walletDriver = $creditDriver->walletDriver;
            $walletDriver->saldo = $walletDriver->saldo + $creditDriver->nominal;
            $walletDriver->save();
        }
    }

    public function productOrderTransaction($request)
    {
        $merchant_key = env('PAYMENT_GATEWAY_MERCHANT_KEY');
        $merchant_code = env('PAYMENT_GATEWAY_MERCHANT_CODE');
        $base_url = env('APP_URL');

        $customer = Customer::find(Crypt::decryptString($request->customer_id));

        $callbackUrl = $base_url . '/api/callback-transaction';
        $returnUrl = $base_url . '/return-transaction?user_i    d=' . Crypt::encryptString($customer->id) . "&email=" . $customer->email;

        $orderData = $this->createOrderData($request);
        $paymentAmount = $this->calculatePaymentAmount($request->products, $orderData);

        $this->storeUsedPromotion($orderData, $request->promotions);

        $expiryPeriod = 10;
        $signature = md5($merchant_code . $orderData->invoice . $paymentAmount . $merchant_key);

        $address = array(
            'firstname'     => $customer->username,
            'lastname'      => $customer->username,
            'address'       => $request->address_destination['description'],
            'city'          => $request->address_destination['city'],
            'postalcode'    => $request->address_destination['postal_code'],
            'phone'         => $customer->phone_number,
            'countrycode'   => 'id'
        );

        $customerDetail = array(
            'firstname'         => $customer->username,
            'lastname'          => $customer->username,
            'email'             => $customer->email,
            'phonenumber'       => $customer->phone_number,
            'billingaddress'    => $address,
            'shippingaddress'   => $address
        );

        $requestOrderParameters = array(
            'merchantcode'      => $merchant_code,
            'merchantorderid'   => $orderData->invoice,

            'productdetails'    => 'Payment for order product',
            'paymentamount'     => $paymentAmount,
            'paymentmethod'     => $request->payment_method,
            'additionalparam'   => '',

            'merchantuserinfo'  => '',
            'customervaname'    => $customer->username,
            'email'             => $customer->email,
            'phonenumber'       => $customer->phone_number,

            'itemdetails'       => '',
            'customerdetail'    => $customerDetail,

            'callbackurl'       => $callbackUrl,
            'returnurl'         => $returnUrl,
            'signature'         => $signature,
            'expiryperiod'      => $expiryPeriod
        );

        $orderDetail = $this->createOrderDetail($orderData, $customer, $request);
        $logOrder = $this->createLogOrder($orderData, $customer);

        if ($orderDetail && $logOrder && $orderData) {
            switch ($request->type_payment) {
                case 'gateway':
                    return ["type_payment" => $request->type_payment, "invoice" => $orderData->invoice, "data" => $this->requestOrderToPaymentGatewayService($requestOrderParameters), "response" => $orderData];
                case 'wallet':
                case 'cash':
                    return ["type_payment" => $request->type_payment, "invoice" => $orderData->invoice,  "data" => $orderDetail, "response" => $orderData];
                default:
                    return ["type_payment" => $request->type_payment, "invoice" => $orderData->invoice,  "data" => "Kamu belum memilih jeni metode pembayaran yang mau kamu gunakan", "response" => $orderData];
            }
        } else {
            return ["data" => "Mohon maaf order tidak dapat di proses server sedang dalam perbaikan", "status" => false];
        }
    }

    public function depositWalletTransaction($instance, $user_data, $payment_method, $order_description, $amount, $type)
    {
        $merchant_key = env('PAYMENT_GATEWAY_MERCHANT_KEY');
        $merchant_code = env('PAYMENT_GATEWAY_MERCHANT_CODE');
        $base_url = env('APP_URL');

        $callbackUrl = $base_url . '/api/callback-wallet?type=' . $type;
        $returnUrl = $base_url . '/return-transaction?user_id=' . Crypt::encryptString($user_data->user_id) . "&nominal=" . $amount;

        $expiryPeriod = 10;

        $paymentDetail = $this->storePaymentDetail($instance, $user_data, $amount, $type);
        $signature = md5($merchant_code . $paymentDetail->invoice . $amount . $merchant_key);

        $address = array(
            'firstName' => $user_data->username,
            'lastName' => $user_data->username,
            'address' => '',
            'city' => '',
            'postalCode' => '10000',
            'phone' => $user_data->phone_number,
            'countryCode' => 'ID'
        );

        $customerDetail = array(
            'firstName' => $user_data->username,
            'lastName' => $user_data->username,
            'email' => $user_data->email,
            'phoneNumber' => $user_data->phone_number,
            'billingAddress' => $address,
            'shippingAddress' => $address
        );

        $requestOrderParameters = array(
            'merchantCode'      => $merchant_code,
            'merchantOrderId'   => $paymentDetail->invoice,

            'productDetails'    => $order_description,
            'paymentAmount'     => $amount,
            'paymentMethod'     => $payment_method,
            'additionalParam'   => '',

            'merchantUserInfo'  => '',
            'customerVaName'    => $user_data->username,
            'email'             => $user_data->email,
            'phoneNumber'       => $user_data->phone_number,

            'itemDetails'       => '',
            'customerDetail'    => $customerDetail,

            'callbackUrl'       => $callbackUrl,
            'returnUrl'         => $returnUrl,
            'signature'         => $signature,
            'expiryPeriod'      => $expiryPeriod
        );

        return $this->requestOrderToPaymentGatewayService($requestOrderParameters);
    }

    private function createOrderData($request)
    {
        $customer = Customer::find(Crypt::decryptString($request->customer_id));
        $latestData = Order::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->invoice : null, Order::CODE);

        $input['customer_id'] = $customer->id;
        $input['driver_id'] = is_null($request->driver_id) ? $request->driver_id : Crypt::decryptString($request->driver_id);
        $input['seller_id'] = Crypt::decryptString($request->seller_id);
        $input['invoice'] = $code;
        $input['grand_total'] = $request->grand_total;
        $input['total_discount'] = $request->total_discount;
        $input['fee_application'] = $request->fee_application;
        $input['fee_packaging'] = $request->fee_packaging;
        $input['fee_delivery'] = $request->fee_delivery;
        $input['total_order_price'] = $request->total_order_price;
        $input['total_discount_delivery'] = $request->total_discount_delivery;
        $input['total_discount_partner'] = $request->total_discount_partner;
        $input['total_discount_saiantar'] = $request->total_discount_saiantar;
        $input['sharing_profit_seller'] = $request->sharing_profit_seller;
        $input['sharing_profit_driver'] = $request->sharing_profit_driver;
        $input['fixed_sharing_driver'] = $request->fixed_sharing_driver;
        $input['percen_sharing_driver'] = $request->percen_sharing_driver;
        $input['fixed_sharing_seller'] = $request->fixed_sharing_seller;
        $input['percen_sharing_seller'] = $request->percen_sharing_seller;
        $input['sharing_driver_to_owner'] = $request->sharing_driver_to_owner;

        $orderList = Order::create($input);

        return new OrderResource($orderList);
    }

    private function createOrderDetail($orderData, $userData, $request)
    {
        $latestData = OrderDetail::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->order_detail_code : null, OrderDetail::CODE);
        $orderDetailStatus = $request->type_payment == 'wallet' || $request->type_payment == 'cash' ? 'paid' : 'unpaid';

        $input_order_detail['order_detail_code'] = $code;
        $input_order_detail['order_id'] = $orderData->id;
        $input_order_detail['name_recipient'] = $userData->user->username;
        $input_order_detail['phone_number'] = $userData->user->phone_number;
        $input_order_detail['longitude'] = $request->address_destination['longitude'];
        $input_order_detail['latitude'] = $request->address_destination['latitude'];
        $input_order_detail['description'] = $request->address_destination['address'];
        $input_order_detail['remark'] = $request->address_destination['description'] . " - " . $request->address_destination['remark'];
        $input_order_detail['city'] = $request->address_destination['city'];
        $input_order_detail['postal_code'] = $request->address_destination['postal_code'];
        $input_order_detail['status'] = $orderDetailStatus;
        $input_order_detail['slug'] = $userData->user->username;
        $input_order_detail['distance_delivery'] = $request->distance_delivery;
        $input_order_detail['time_delivery'] = $request->time_delivery;
        $input_order_detail['payment_method'] = $request->type_payment;
        $input_order_detail['status_order'] = 'waiting approve seller';

        return OrderDetail::create($input_order_detail);
    }

    private function createLogOrder($orderData, $userData)
    {
        $latestData = LogOrder::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->log_order_code : null, LogOrder::CODE);
        $input_log_order['log_order_code'] = $code;
        $input_log_order['order_id'] = $orderData->id;
        $input_log_order['description'] = 'menunggu konfirmasi seller';
        $input_log_order['status'] = 'waiting approve seller';

        return LogOrder::create($input_log_order);
    }

    private function calculatePaymentAmount($products, $orderData)
    {
        $payment_amount = 0;

        foreach ($products as $product => $dataProduct) {
            $latestData = ProductOrder::withTrashed()->orderBy('id', 'desc')->latest()->first();
            $code = generateCode($latestData !== null ? $latestData->product_order_code : null, ProductOrder::CODE);
            $product = Product::find(Crypt::decryptString($dataProduct['id']));

            $input_product_order['product_order_code'] = $code;
            $input_product_order['product_id'] = $product->id;
            $input_product_order['order_id'] = $orderData->id;
            $input_product_order['price'] = $product->price;
            $input_product_order['custom_menu'] = $dataProduct['custom_menu'];
            $input_product_order['discount_fixed'] = 0;
            $input_product_order['discount_percentage'] = 0;
            $input_product_order['total_product'] = $dataProduct['quantity'];
            $input_product_order['original_price'] = $dataProduct['original_price'];
            $input_product_order['markup_price'] = $dataProduct['markup_price'];
            $input_product_order['total_amount'] = $dataProduct['price'] * $dataProduct['quantity'];
            $input_product_order['note_order'] = $dataProduct['note_order'];
            $product_order = ProductOrder::create($input_product_order);

            $payment_amount += $product_order->total_amount;
        }

        return $payment_amount;
    }

    private function requestOrderToPaymentGatewayService($requestOrderParameters)
    {
        $url = env('PAYMENT_GATEWAY_BASE_API') . '/v2/inquiry';

        $requestOrderParametersString = json_encode($requestOrderParameters);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestOrderParametersString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($requestOrderParametersString)
            )
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $requestExec = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 200) {
            return ["data" => json_decode($requestExec, true), "status" => true];
        } else {
            return ["data" => json_decode($requestExec, true), "status" => false];
        }
    }

    private function calculatePromotion($seller_promotions, $total_amount)
    {
        $grand_total = 0;

        foreach ($seller_promotions as $promotion => $dataPromotion) {
            $promotion = SellerPromotion::find(Crypt::decryptString($dataPromotion['id']));

            $grand_total += $total_amount - $promotion->promotion_fixed;
        }

        return $grand_total;
    }

    private function storePaymentDetail($instance, $user_data, $amount, $type)
    {
        switch ($type) {
            case 'customer':
                $wallet = WalletCustomer::where('customer_id', $instance->id)->first();

                $topUpHistory = $this->storeTopUpHistory($user_data->id, $amount);

                $latestCreditCustomer = CreditCustomerTransaction::withTrashed()->orderBy('id', 'desc')->latest()->first();
                $input_credit_customer['wallet_customer_id'] = $wallet->id;
                $input_credit_customer['invoice'] = $topUpHistory->invoice;
                $input_credit_customer['status'] = 'pending';
                $input_credit_customer['nominal'] = $amount;
                $input_credit_customer['cct_code'] = generateCode($latestCreditCustomer !== null ? $latestCreditCustomer->cct_code : null, CreditCustomerTransaction::CODE);
                $input_credit_customer['description'] = 'Top up wallet customer';
                $input_credit_customer['saldo_before'] = $wallet->saldo;
                return CreditCustomerTransaction::create($input_credit_customer);

            case 'driver':
                $wallet = WalletDriver::where('driver_id', $instance->id)->first();

                $topUpHistory = $this->storeTopUpHistory($user_data->id, $amount);

                $latestCreditDriver = CreditDriverTransaction::withTrashed()->orderBy('id', 'desc')->latest()->first();
                $input_credit_driver['wallet_driver_id'] = $wallet->id;
                $input_credit_driver['invoice'] = $topUpHistory->invoice;
                $input_credit_driver['status'] = 'pending';
                $input_credit_driver['nominal'] = $amount;
                $input_credit_driver['cdt_code'] = generateCode($latestCreditDriver !== null ? $latestCreditDriver->cdt_code : null, CreditDriverTransaction::CODE);
                $input_credit_driver['description'] = 'Top up wallet driver';
                $input_credit_driver['saldo_before'] = $wallet->saldo;
                return CreditDriverTransaction::create($input_credit_driver);

            case 'seller':
                $wallet = WalletSeller::where('seller_id', $instance->id)->first();

                $topUpHistory = $this->storeTopUpHistory($user_data->id, $amount);

                $latestCreditSeller = CreditSellerTransaction::withTrashed()->orderBy('id', 'desc')->latest()->first();
                $input_credit_seller['wallet_seller_id'] = $wallet->id;
                $input_credit_seller['invoice'] = $topUpHistory->invoice;
                $input_credit_seller['status'] = 'pending';
                $input_credit_seller['nominal'] = $amount;
                $input_credit_seller['cst_code'] = generateCode($latestCreditSeller !== null ? $latestCreditSeller->cst_code : null, CreditSellerTransaction::CODE);
                $input_credit_seller['description'] = 'Deposit wallet seller';
                $input_credit_seller['saldo_before'] = $wallet->saldo;
                return CreditSellerTransaction::create($input_credit_seller);

            default:
                return null;
        }
    }

    private function storeTopUpHistory($user_id, $amount)
    {
        $latestTopUpHistoryData = TopUpTransaction::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $input_top_up_history['user_id'] = $user_id;
        $input_top_up_history['invoice'] = generateCode($latestTopUpHistoryData !== null ? $latestTopUpHistoryData->invoice : null, TopUpTransaction::CODE);
        $input_top_up_history['nominal'] = $amount;
        $input_top_up_history['status'] = 'pending';
        return TopUpTransaction::create($input_top_up_history);
    }

    private function storeUsedPromotion($order, $promotions)
    {
        foreach ($promotions as $promotion => $value) {
            $input['order_id'] = $order->id;
            $input['promotion_code'] = $value['promotion_code'];
            $input['promotion_percentage'] = $value['promotion_percentage'];
            $input['promotion_fixed'] = $value['promotion_fixed'];
            $input['seller_id'] = is_null($value['seller_id']) ? $value['seller_id'] : Crypt::decryptString($value['seller_id']);
            $input['name'] = $value['name'];
            $input['description'] = $value['description'];
            $input['minimum_order'] = $value['minimum_order'];
            $input['minimum_amount'] = $value['minimum_amount'];
            $input['ongkir_percentage'] = $value['ongkir_percentage'];
            $input['ongkir_fixed'] = $value['ongkir_fixed'];

            UsedPromotion::create($input);
        }
    }

    public function withdrawalWalletTransaction($instance, $user, $amount, $type)
    {
        switch ($type) {
            case 'customer':
                $wallet = WalletCustomer::where('customer_id', $instance->id)->first();

                $withdrawalHistory = $this->storeWithdrawalHistory($user->id, $amount);

                $latestDebitCustomer = DebetCustomerTransaction::withTrashed()->orderBy('id', 'desc')->latest()->first();
                $input_debit_customer['wallet_customer_id'] = $wallet->id;
                $input_debit_customer['invoice'] = $withdrawalHistory->invoice;
                $input_debit_customer['status'] = 'pending';
                $input_debit_customer['nominal'] = $amount;
                $input_debit_customer['dct_code'] = generateCode($latestDebitCustomer !== null ? $latestDebitCustomer->dct_code : null, DebetCustomerTransaction::CODE);
                $input_debit_customer['description'] = 'Withdrawal wallet customer';
                $input_debit_customer['saldo_before'] = $wallet->saldo;
                DebetCustomerTransaction::create($input_debit_customer);

                $wallet->saldo = $wallet->saldo - $amount;
                $wallet->save();

                return $withdrawalHistory;

            case 'driver':
                $wallet = WalletDriver::where('driver_id', $instance->id)->first();

                $withdrawalHistory = $this->storeWithdrawalHistory($user->id, $amount);

                $latestDebitDriver = DebetDriverTransaction::withTrashed()->orderBy('id', 'desc')->latest()->first();
                $input_debit_driver['wallet_driver_id'] = $wallet->id;
                $input_debit_driver['invoice'] = $withdrawalHistory->invoice;
                $input_debit_driver['status'] = 'pending';
                $input_debit_driver['nominal'] = $amount;
                $input_debit_driver['ddt_code'] = generateCode($latestDebitDriver !== null ? $latestDebitDriver->ddt_code : null, DebetDriverTransaction::CODE);
                $input_debit_driver['description'] = 'Withdrawal wallet driver';
                $input_debit_driver['saldo_before'] = $wallet->saldo;

                DebetDriverTransaction::create($input_debit_driver);

                $wallet->saldo = $wallet->saldo - $amount;
                $wallet->save();

                return $withdrawalHistory;

            case 'seller':
                $wallet = WalletSeller::where('seller_id', $instance->id)->first();

                $withdrawalHistory = $this->storeWithdrawalHistory($user->id, $amount);

                $latestDebitSeller = DebetSellerTransaction::withTrashed()->orderBy('id', 'desc')->latest()->first();
                $input_debit_seller['wallet_seller_id'] = $wallet->id;
                $input_debit_seller['invoice'] = $withdrawalHistory->invoice;
                $input_debit_seller['status'] = 'pending';
                $input_debit_seller['nominal'] = $amount;
                $input_debit_seller['dst_code'] = generateCode($latestDebitSeller !== null ? $latestDebitSeller->dst_code : null, DebetSellerTransaction::CODE);
                $input_debit_seller['description'] = 'Withdrawal wallet seller';
                $input_debit_seller['saldo_before'] = $wallet->saldo;

                DebetSellerTransaction::create($input_debit_seller);

                $wallet->saldo = $wallet->saldo - $amount;
                $wallet->save();

                return $withdrawalHistory;

            default:
                return null;
        }
    }

    private function storeWithdrawalHistory($user_id, $amount)
    {
        $latestWithdrawalHistoryData = WithdrawalTransaction::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $input_withdrawal_history['user_id'] = $user_id;
        $input_withdrawal_history['invoice'] = generateCode($latestWithdrawalHistoryData !== null ? $latestWithdrawalHistoryData->invoice : null, WithdrawalTransaction::CODE);
        $input_withdrawal_history['nominal'] = $amount;
        $input_withdrawal_history['status'] = 'pending';
        return WithdrawalTransaction::create($input_withdrawal_history);
    }
}
