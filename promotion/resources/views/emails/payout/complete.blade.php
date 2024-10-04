<x-mail::message>

<h1 style="text-align: center;">
    恭喜您获得推广收益
</h1>

您收到此电子邮件是因为我们获得了以下收益

**来自用户** {{$orderUserNickname}}

**收益金额** {{$userPayoutAmount}}

**日期** {{$userPayoutDate}}


<x-mail::button :url="$userPayoutUrl">
查看收益
</x-mail::button>

谢谢,\
{{ config('app.name') }}
</x-mail::message>
