<x-mail::message>

<h1 style="text-align: center;">
@if($expiryInDays === 0)
    您购买的[{{$categoryName}}]服务已经过期
@else
    您的[{{$categoryName}}]还有{{$expiryInDays}}天过期
@endif
</h1>

为保证您的服务请立即续费

<x-mail::button :url="$categoryUrl">
立即续费
</x-mail::button>

谢谢,\
{{ config('app.name') }}
</x-mail::message>
