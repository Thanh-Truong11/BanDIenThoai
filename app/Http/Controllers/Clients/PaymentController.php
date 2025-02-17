<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Mail\OrderInvoiceMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    // public function processOrder(Request $request)
    // {
    //     // Validate the input
    //     $request->validate([
    //         'fullName' => 'required|string|max:255',
    //         'city' => 'required|string|max:255',
    //         'district' => 'required|string|max:255',
    //         'ward' => 'required|string|max:255',
    //         'address' => 'required|string|max:255',
    //         'phone' => 'required|string|max:10',
    //         'note' => 'nullable|string',
    //         'paymentMethod' => 'required|string',
    //     ]);
    //     $paymentMethod = $request->input('paymentMethod');
    //     if ($paymentMethod === 'VNPay') {
    //         // Chuyển hướng tới trang thanh toán của VNPay
    //         return redirect()->route('vnpay_payment', [
    //             'amount' => $request->input('totalAmount'),
    //             'orderInfo' => 'Thanh toán đơn hàng'
    //         ]);
    //     } else if ($paymentMethod === 'Cod') {
    //         // Tạo đơn hàng cho thanh toán COD
    //         return $this->createOrder($request, 'Cod');
    //     }

    //     return back()->with('error', 'Phương thức thanh toán không hợp lệ.');
    // }
    public function processOrder(Request $request)
    {
        // Validate the input
        $request->validate([
            'fullName' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:10',
            'note' => 'nullable|string',
            'paymentMethod' => 'required|string',
        ]);
    
        $paymentMethod = $request->input('paymentMethod');
    
        if ($paymentMethod === 'VNPay') {
            // Xử lý thanh toán qua VNPay
            return view('payment.vnpay_form', ['totalAmount' => $request->input('total_amount')]);
        } else if ($paymentMethod === 'Cod') {
            // Tạo đơn hàng cho thanh toán COD
            return $this->createOrder($request, 'Cod');
        } else if ($paymentMethod === 'MoMo') {
            // Xử lý thanh toán qua MoMo
            return $this->momoPayment($request);
        }
    
        return back()->with('error', 'Phương thức thanh toán không hợp lệ.');
    }
    
    // public function vnpay_payment()
    // {
    //     error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    //     date_default_timezone_set('Asia/Ho_Chi_Minh');

    //     $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    //     $vnp_Returnurl = "https://6db0-125-235-236-116.ngrok-free.app ";
    //     $vnp_TmnCode = "N5W6AA0O"; //Mã website tại VNPAY 
    //     $vnp_HashSecret = "LMFQ08Y3JOATOR2QECTGA7DTOZC76RRS"; //Chuỗi bí mật

    //     $vnp_TxnRef = date("YmdHis"); //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY

    //     $vnp_OrderInfo = 'Thanh toán đơn hàng';
    //     $vnp_OrderType = 'Thanh toán vnpay';
    //     $vnp_Amount = $_POST['total_amount'] * 100;
    //     $vnp_Locale = 'VN';
    //     $vnp_BankCode = 'NCB';
    //     $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
    //     $inputData = array(
    //         "vnp_Version" => "2.1.0",
    //         "vnp_TmnCode" => $vnp_TmnCode,
    //         "vnp_Amount" => $vnp_Amount,
    //         "vnp_Command" => "pay",
    //         "vnp_CreateDate" => date('YmdHis'),
    //         "vnp_CurrCode" => "VND",
    //         "vnp_IpAddr" => $vnp_IpAddr,
    //         "vnp_Locale" => $vnp_Locale,
    //         "vnp_OrderInfo" => $vnp_OrderInfo,
    //         "vnp_OrderType" => $vnp_OrderType,
    //         "vnp_ReturnUrl" => $vnp_Returnurl,
    //         "vnp_TxnRef" => $vnp_TxnRef
    //     );

    //     if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    //         $inputData['vnp_BankCode'] = $vnp_BankCode;
    //     }
    //     ksort($inputData);
    //     $query = "";
    //     $i = 0;
    //     $hashdata = "";
    //     foreach ($inputData as $key => $value) {
    //         if ($i == 1) {
    //             $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    //         } else {
    //             $hashdata .= urlencode($key) . "=" . urlencode($value);
    //             $i = 1;
    //         }
    //         $query .= urlencode($key) . "=" . urlencode($value) . '&';
    //     }

    //     $vnp_Url = $vnp_Url . "?" . $query;
    //     if (isset($vnp_HashSecret)) {
    //         $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
    //         $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
    //     }
    //     $returnData = array(
    //         'code' => '00', 'message' => 'success', 'data' => $vnp_Url
    //     );
    //     return redirect($vnp_Url);
    // }

    public function vnpay_payment(Request $request)
{
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    $vnp_Returnurl = "http://localhost/Website-FPT-SHOP-LARAVEL/mobile-ecommerce-laravel/public/don-hang-cua-toi"; // Đảm bảo URL trả về đúng
    $vnp_TmnCode = "N5W6AA0O"; // Mã website tại VNPAY 
    $vnp_HashSecret = "LMFQ08Y3JOATOR2QECTGA7DTOZC76RRS"; // Chuỗi bí mật

    $vnp_TxnRef = date("YmdHis"); // Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY

    $vnp_OrderInfo = 'Thanh toán đơn hàng';
    $vnp_OrderType = 'Thanh toán vnpay';
    $vnp_Amount = $request->input('total_amount') * 100;
    $vnp_Locale = 'VN';
    $vnp_BankCode = 'NCB';
    $vnp_IpAddr = $request->ip();
    
    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef
    );

    if (!empty($vnp_BankCode)) {
        $inputData['vnp_BankCode'] = $vnp_BankCode;
    }

    ksort($inputData);
    $query = "";
    $hashdata = "";
    foreach ($inputData as $key => $value) {
        $hashdata .= urlencode($key) . "=" . urlencode($value) . '&';
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }

    $vnp_Url .= "?" . $query;
    if (isset($vnp_HashSecret)) {
        $vnpSecureHash = hash_hmac('sha512', rtrim($hashdata, '&'), $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
    }

    return redirect($vnp_Url);
}


    private function createOrder(Request $request, $paymentMethod, $transactionId = null, $status = 'pending')
    {
        // Lấy giỏ hàng của người dùng
        $cart = Cart::where('user_id',  auth()->id())->where('status', 'active')->first();

        // Kiểm tra nếu giỏ hàng không tồn tại
        if (!$cart) {
            return redirect()->back()->with('error', 'Giỏ hàng không tồn tại hoặc đã hết hạn.');
        }

        // Tạo mã đơn hàng duy nhất
        $orderCode = date('YmdHis') . strtoupper(uniqid());

        // Kiểm tra và cập nhật voucher nếu có
        if ($request->voucher) {
            $voucher = Voucher::where('code', $request->voucher)->first();

            if (!$voucher) {
                return redirect()->back()->with('error', 'Mã voucher không hợp lệ.');
            }

            if ($voucher->used >= $voucher->quantity) {
                return redirect()->back()->with('error', 'Voucher này đã hết lượt sử dụng.');
            }

            // Cập nhật số lần sử dụng của voucher
            $voucher->increment('used');
        }

        // Tạo đơn hàng
        $order = Order::create([
            'user_id' => auth()->id(),
            'order_code' => $orderCode,
            'full_name' => $request->fullName,
            'phone' => $request->phone,
            'city' => $request->city,
            'district' => $request->district,
            'ward' => $request->ward,
            'address' => $request->address,
            'note' => $request->note,
            'total_amount' => $cart->items->sum(function ($item) {
                return $item->price * $item->quantity;
            }),
            'discount_amount' => $request->discount_amount ?? 0,
            'final_amount' => $cart->items->sum(function ($item) {
                return $item->price * $item->quantity;
            }) - ($request->discount_amount ?? 0),
            'voucher_code' => $request->voucher,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'status' => $status,
        ]);

        // Tạo các chi tiết đơn hàng
        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total_price' => $item->price * $item->quantity,
            ]);

            // Trừ số lượng sản phẩm trong kho
            if ($item->variant_id) {
                // Nếu sản phẩm có biến thể
                $variant = ProductVariant::find($item->variant_id);
                if ($variant) {
                    $variant->decrement('stock', $item->quantity);
                }
            } else {
                // Nếu sản phẩm không có biến thể
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->decrement('store_quantity', $item->quantity);
                }
            }
        }

        // Xóa các mục trong giỏ hàng
        CartItem::where('cart_id', $cart->id)->delete();

        // Xóa giỏ hàng
        $cart->delete();

        return redirect()->route('orderReceived', ['id' => $order->id])->with('success', 'Đặt hàng thành công.');
    }
    public function momoPayment(Request $request)
{
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    // Các tham số cấu hình
    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
    $partnerCode = 'MOMOBKUN20180529'; // Thay thế bằng Partner Code của bạn
    $accessKey = 'klm05TvNBzhg7h7j'; // Thay thế bằng Access Key của bạn
    $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'; // Thay thế bằng Secret Key của bạn
    $orderId = time(); // Mã đơn hàng
    $orderInfo = "Thanh toán đơn hàng MoMo";
    $amount = $request->input('total_amount');
    $redirectUrl = "https://yourwebsite.com/payment/momo-return"; // Đảm bảo URL trả về đúng
    $ipnUrl = "https://yourwebsite.com/payment/momo-ipn"; // Đảm bảo URL IPN đúng
    $extraData = ""; // Dữ liệu bổ sung nếu có

    // Tạo chữ ký MoMo
    $requestId = time();
    $requestType = "captureWallet";
    $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    // Tạo dữ liệu để gửi tới MoMo
    $data = [
        'partnerCode' => $partnerCode,
        'partnerName' => "Test",
        "storeId" => "MoMoTestStore",
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'lang' => 'vi',
        'extraData' => $extraData,
        'requestType' => $requestType,
        'signature' => $signature
    ];

    // Gửi yêu cầu POST tới MoMo
    $result = $this->execPostRequest($endpoint, json_encode($data));
    $jsonResult = json_decode($result, true); // Phân tích kết quả trả về

    // Chuyển hướng tới URL thanh toán MoMo
    if (isset($jsonResult['payUrl'])) {
        return redirect($jsonResult['payUrl']);
    }

    return back()->with('error', 'Có lỗi xảy ra khi tạo yêu cầu thanh toán MoMo.');
}

// Hàm hỗ trợ gửi yêu cầu POST
private function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    // Để kiểm tra SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}
public function momoReturn(Request $request)
    {
        // Nhận các tham số trả về từ MoMo
        $orderId = $request->input('orderId'); // Mã đơn hàng của bạn
        $resultCode = $request->input('resultCode'); // Mã kết quả thanh toán từ MoMo

        // Tìm đơn hàng dựa vào orderId
        $order = Order::where('order_code', $orderId)->first();

        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng.');
        }

        // Kiểm tra trạng thái thanh toán
        if ($resultCode == '0') { // 0 là mã trả về thành công từ MoMo
            $order->update([
                'status' => 'paid',
                'payment_method' => 'MoMo'
            ]);

            return redirect()->route('orderReceived', ['id' => $order->id])->with('success', 'Thanh toán thành công!');
        } else {
            $order->update(['status' => 'failed']);
            return redirect()->route('home')->with('error', 'Thanh toán thất bại. Vui lòng thử lại.');
        }
    }

    public function momoIpn(Request $request)
    {
        // Nhận các tham số từ MoMo
        $orderId = $request->input('orderId'); // Mã đơn hàng của bạn
        $resultCode = $request->input('resultCode'); // Mã kết quả thanh toán từ MoMo

        // Tìm đơn hàng dựa vào orderId
        $order = Order::where('order_code', $orderId)->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        // Kiểm tra trạng thái thanh toán
        if ($resultCode == '0') { // 0 là mã trả về thành công từ MoMo
            $order->update([
                'status' => 'paid',
                'payment_method' => 'MoMo'
            ]);

            return response()->json(['status' => 'success', 'message' => 'Cập nhật đơn hàng thành công'], 200);
        } else {
            $order->update(['status' => 'failed']);
            return response()->json(['status' => 'error', 'message' => 'Thanh toán thất bại'], 400);
        }
    }

}
