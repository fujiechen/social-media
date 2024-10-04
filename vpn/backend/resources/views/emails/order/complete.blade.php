<x-mail::message>

<h1 style="text-align: center;">
    我们收到了您的订单
</h1>

您收到此电子邮件是因为我们收到了您的订单。

**用户** {{$user}}

**订单号** {{$orderId}}

**订购日期** {{$orderDate}}

<x-mail::button :url="$orderUrl">
查看订单
</x-mail::button>

谢谢,\
{{ config('app.name') }}
</x-mail::message>
